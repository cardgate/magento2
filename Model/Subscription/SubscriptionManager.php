<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Subscription;

use Cardgate\Payment\Model\GatewayClient;
use Cardgate\Payment\Model\Subscription;
use Cardgate\Payment\Model\SubscriptionFactory;
use Cardgate\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Cardgate\Payment\Model\ResourceModel\Subscription\CollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates creation, billing and self-service management of CuroPayments subscriptions.
 *
 * Highlights:
 *  - Mixed cart support: only the subscription line-items of an order are turned into
 *    subscriptions, the remaining one-off items are billed normally by the initial order.
 *  - Product type agnostic: works for simple, configurable, bundle, grouped and virtual
 *    products because it operates on the resolved order line-items and their row totals.
 *  - Shipping recalculation: shipping is recomputed per billing cycle, taking the cycle
 *    length into account (e.g. a yearly cycle consolidates shipping differently than a
 *    weekly cycle).
 */
class SubscriptionManager
{
    /**
     * Product attribute codes used to flag and configure a subscription product.
     */
    public const ATTR_IS_SUBSCRIPTION = 'cardgate_is_subscription';
    public const ATTR_INTERVAL        = 'cardgate_subscription_interval';
    public const ATTR_INTERVAL_UNIT   = 'cardgate_subscription_interval_unit';

    /**
     * Number of consecutive failures after which a subscription is paused for review.
     */
    public const MAX_FAILURES = 5;

    /**
     * Maximum number of days a failing subscription is postponed when the *gateway*
     * refused the charge (exponential back-off).
     */
    public const MAX_RETRY_DELAY_DAYS = 7;

    /**
     * Retry delay (minutes) used when the charge never reached the gateway.
     *
     * Such failures (an unwritable cache directory, a locked table, a temporarily
     * unavailable service) are environmental: the subscription must be retried within
     * the same day instead of being postponed for a full billing period.
     */
    public const RETRY_DELAY_MINUTES = 15;

    /**
     * Upper bound (minutes) for the short retry back-off.
     */
    public const MAX_RETRY_DELAY_MINUTES = 240;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GatewayClient
     */
    private $gatewayClient;

    /**
     * @var AllowedMethods
     */
    private $allowedMethods;

    /**
     * @var OrderCreator
     */
    private $orderCreator;

    /**
     * @var NonFatalErrorGuard
     */
    private $errorGuard;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionResource $subscriptionResource
     * @param CollectionFactory $collectionFactory
     * @param GatewayClient $gatewayClient
     * @param AllowedMethods $allowedMethods
     * @param OrderCreator $orderCreator
     * @param NonFatalErrorGuard $errorGuard
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        CollectionFactory $collectionFactory,
        GatewayClient $gatewayClient,
        AllowedMethods $allowedMethods,
        OrderCreator $orderCreator,
        NonFatalErrorGuard $errorGuard,
        LoggerInterface $logger
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->collectionFactory = $collectionFactory;
        $this->gatewayClient = $gatewayClient;
        $this->allowedMethods = $allowedMethods;
        $this->orderCreator = $orderCreator;
        $this->errorGuard = $errorGuard;
        $this->logger = $logger;
    }

    /**
     * Create subscription records for every subscription line-item of the given order.
     *
     * Designed to be called after an order is placed. One-off items are ignored, making
     * mixed carts work transparently.
     *
     * @param OrderInterface $order
     * @return Subscription[] The created subscriptions (empty when none apply).
     */
    public function createFromOrder(OrderInterface $order)
    {
        $created = [];

        $payment = $order->getPayment();
        $methodCode = $payment ? $payment->getMethod() : null;
        if (! $this->allowedMethods->isAllowed($methodCode)) {
            // Subscriptions are only permitted for the configured payment methods.
            return $created;
        }

        if ($this->isRenewalOrder($order)) {
            // Renewal orders are created *by* a subscription and must never spawn a new one.
            return $created;
        }

        $mandate = $this->resolveMandateTransactionId($order);
        if (empty($mandate)) {
            // Expected when the order is placed: the mandate only exists once the gateway
            // has confirmed the authorizing transaction through the callback.
            $this->logger->debug(
                'CardGate subscription: no mandate transaction id (yet) for order ' . $order->getIncrementId()
            );
            return $created;
        }

        $currency = $order->getOrderCurrencyCode() ?: 'EUR';
        $subscriptionItems = $this->collectSubscriptionItems($order);
        if (empty($subscriptionItems)) {
            return $created;
        }

        // Shipping is split proportionally over the subscription items (mixed cart aware).
        $shippingPerItem = $this->distributeShipping($order, count($subscriptionItems));

        foreach ($subscriptionItems as $item) {
            if ($this->subscriptionExists($order, $item)) {
                // Creation is idempotent: the order place event and the callback may both fire.
                continue;
            }

            $product = $item->getProduct();
            $intervalCount = (int) ($product ? $product->getData(self::ATTR_INTERVAL) : 0);
            $intervalUnit = $product ? (string) $product->getData(self::ATTR_INTERVAL_UNIT) : '';
            if ($intervalCount < 1) {
                $intervalCount = 1;
            }
            if (! in_array($intervalUnit, [
                Subscription::UNIT_DAY,
                Subscription::UNIT_WEEK,
                Subscription::UNIT_MONTH,
                Subscription::UNIT_YEAR,
            ], true)) {
                $intervalUnit = Subscription::UNIT_MONTH;
            }

            $amountCents = (int) round((float) $item->getRowTotalInclTax() * 100);
            $shippingCents = $this->recalculateShippingForCycle($shippingPerItem, $intervalUnit, $intervalCount);

            /** @var Subscription $subscription */
            $subscription = $this->subscriptionFactory->create();
            $subscription->addData([
                'customer_id'            => $order->getCustomerId(),
                'store_id'               => $order->getStoreId(),
                'order_id'               => $order->getEntityId(),
                'increment_id'           => $order->getIncrementId(),
                'product_id'             => $item->getProductId(),
                'sku'                    => $item->getSku(),
                'name'                   => $item->getName(),
                'qty'                    => $item->getQtyOrdered(),
                'mandate_transaction_id' => $mandate,
                'payment_method'         => $methodCode,
                'amount'                 => $amountCents,
                'shipping_amount'        => $shippingCents,
                'currency'               => $currency,
                'interval_count'         => $intervalCount,
                'interval_unit'          => $intervalUnit,
                'status'                 => Subscription::STATUS_ACTIVE,
                'skip_next'              => 0,
                'cycles_billed'          => 0,
                'next_run_at'            => $this->computeNextRun(null, $intervalUnit, $intervalCount),
            ]);
            $this->subscriptionResource->save($subscription);
            $created[] = $subscription;
        }

        return $created;
    }

    /**
     * Charge every subscription that is due.
     *
     * @return int Number of subscriptions processed.
     */
    public function chargeDue()
    {
        $now = gmdate('Y-m-d H:i:s');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', Subscription::STATUS_ACTIVE)
            ->addFieldToFilter('next_run_at', ['lteq' => $now]);

        $processed = 0;
        foreach ($collection as $subscription) {
            try {
                $this->charge($subscription);
                $processed++;
            } catch (\Throwable $e) {
                // Catch Throwable, not just Exception: a PHP Error (e.g. a fatal in the
                // renewal order creation) would otherwise abort the whole cron job and
                // leave every remaining due subscription unbilled.
                $this->logger->error(
                    'CardGate subscription charge failed for #' . $subscription->getId() . ': ' . $e->getMessage()
                );
            }
        }

        return $processed;
    }

    /**
     * Charge a single subscription for one cycle (honouring skip requests).
     *
     * A billing cycle consists of two steps:
     *  1. a renewal order is created for the subscribed line-item;
     *  2. that order is charged at the gateway through the recurring entry point, using
     *     the order increment id as reference so the regular callback completes the order.
     *
     * @param Subscription $subscription
     * @return bool True when a charge was performed, false when the cycle was skipped.
     * @throws \Exception
     */
    public function charge(Subscription $subscription)
    {
        if (! $subscription->isActive()) {
            return false;
        }

        // Honour a customer "skip next" request: roll the schedule forward without charging.
        if ((int) $subscription->getSkipNext() === 1) {
            $subscription->setSkipNext(0);
            $subscription->setNextRunAt($this->computeNextRun(
                $subscription->getNextRunAt(),
                $subscription->getIntervalUnit(),
                (int) $subscription->getIntervalCount()
            ));
            $this->subscriptionResource->save($subscription);
            return false;
        }

        $description = trim((string) $subscription->getName()) ?: ('Subscription ' . $subscription->getId());

        // Step 1: the renewal order. Guarded, because Magento promotes every PHP warning
        // to an exception and an unattended billing cycle must not die on a warning that
        // has nothing to do with the payment itself (e.g. an unwritable cache directory).
        $order = null;
        try {
            $order = $this->errorGuard->run(function () use ($subscription) {
                return $this->orderCreator->create($subscription);
            });
        } catch (\Throwable $e) {
            // The gateway has not been contacted yet, so retry shortly instead of
            // postponing the subscription for days.
            $this->registerFailure(
                $subscription,
                'renewal order creation failed: ' . $e->getMessage(),
                true
            );
            throw $e;
        }

        $reference = (string) $order->getIncrementId();
        $amount = (int) round((float) $order->getGrandTotal() * 100);
        if ($amount <= 0) {
            $amount = $subscription->getCycleAmount();
        }
        // Older subscription records may have no currency stored; fall back to the
        // currency of the renewal order so the gateway never receives an empty value.
        $currency = (string) $subscription->getCurrency();
        if ('' === $currency) {
            $currency = (string) ($order->getOrderCurrencyCode() ?: 'EUR');
        }

        // Step 2: charge the order at the gateway.
        try {
            $result = $this->errorGuard->run(function () use (
                $subscription,
                $amount,
                $currency,
                $reference,
                $description
            ) {
                return $this->gatewayClient->recurring()->create(
                    (string) $subscription->getMandateTransactionId(),
                    $amount,
                    $currency,
                    $reference,
                    $description
                );
            });
        } catch (\Throwable $e) {
            $this->cancelRenewalOrder($order, $e->getMessage());
            $this->registerFailure($subscription, $e->getMessage());
            throw $e;
        }

        $subscription->setLastRunAt(gmdate('Y-m-d H:i:s'));
        $subscription->setCyclesBilled((int) $subscription->getCyclesBilled() + 1);
        $subscription->setLastOrderId($order->getEntityId());
        $subscription->setFailureCount(0);
        $subscription->setLastError(null);
        if (is_array($result) && isset($result['transaction']['id'])) {
            $subscription->setLastTransactionId((string) $result['transaction']['id']);
        }
        $subscription->setNextRunAt($this->computeNextRun(
            $subscription->getNextRunAt(),
            $subscription->getIntervalUnit(),
            (int) $subscription->getIntervalCount()
        ));
        $this->subscriptionResource->save($subscription);

        return true;
    }

    /**
     * Register a failed billing attempt and postpone the subscription (exponential back-off).
     *
     * Without this the very same subscription would be retried on every single cron run,
     * which the gateway rejects ("new transaction ... within 7 days").
     *
     * @param Subscription $subscription
     * @param string $message
     * @param bool $shortRetry Whether the gateway was not involved and a quick retry is safe.
     * @return void
     */
    private function registerFailure(Subscription $subscription, $message, $shortRetry = false)
    {
        $failures = (int) $subscription->getFailureCount() + 1;

        if ($shortRetry) {
            $delayMinutes = min(
                self::MAX_RETRY_DELAY_MINUTES,
                self::RETRY_DELAY_MINUTES * (int) pow(2, $failures - 1)
            );
            $nextRun = gmdate('Y-m-d H:i:s', strtotime('+' . $delayMinutes . ' minute', time()));
        } else {
            $delayDays = min(self::MAX_RETRY_DELAY_DAYS, (int) pow(2, $failures - 1));
            $nextRun = gmdate('Y-m-d 00:00:00', strtotime('+' . $delayDays . ' day', time()));
        }

        $subscription->setFailureCount($failures);
        $subscription->setLastError(substr((string) $message, 0, 255));
        $subscription->setNextRunAt($nextRun);
        if ($failures >= self::MAX_FAILURES) {
            $subscription->setStatus(Subscription::STATUS_PAUSED);
            $this->logger->error(
                'CardGate subscription #' . $subscription->getId()
                . ' paused after ' . $failures . ' failed billing attempts'
            );
        }

        try {
            $this->subscriptionResource->save($subscription);
        } catch (\Throwable $e) {
            $this->logger->error('CardGate subscription: could not persist failure state: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a renewal order for which the gateway charge could not be created.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $message
     * @return void
     */
    private function cancelRenewalOrder($order, $message)
    {
        try {
            $order->addCommentToStatusHistory(
                __('Subscription renewal charge failed: %1', $message)
            );
            if (method_exists($order, 'canCancel') && $order->canCancel()) {
                $order->cancel();
            }
            $order->save();
        } catch (\Throwable $e) {
            $this->logger->error(
                'CardGate subscription: could not cancel renewal order: ' . $e->getMessage()
            );
        }
    }

    /**
     * Pause an active subscription.
     *
     * @param Subscription $subscription
     * @return void
     * @throws \Exception
     */
    public function pause(Subscription $subscription)
    {
        if ($subscription->isCancelled()) {
            return;
        }
        $subscription->setStatus(Subscription::STATUS_PAUSED);
        $this->subscriptionResource->save($subscription);
    }

    /**
     * Resume a paused subscription, rescheduling the next billing date.
     *
     * @param Subscription $subscription
     * @return void
     * @throws \Exception
     */
    public function resume(Subscription $subscription)
    {
        if (! $subscription->isPaused()) {
            return;
        }
        $subscription->setStatus(Subscription::STATUS_ACTIVE);
        $subscription->setFailureCount(0);
        $subscription->setLastError(null);
        $subscription->setNextRunAt($this->computeNextRun(
            null,
            $subscription->getIntervalUnit(),
            (int) $subscription->getIntervalCount()
        ));
        $this->subscriptionResource->save($subscription);
    }

    /**
     * Cancel a subscription permanently.
     *
     * @param Subscription $subscription
     * @return void
     * @throws \Exception
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->setStatus(Subscription::STATUS_CANCELLED);
        $this->subscriptionResource->save($subscription);
    }

    /**
     * Flag the next billing cycle to be skipped.
     *
     * @param Subscription $subscription
     * @return void
     * @throws \Exception
     */
    public function skipNext(Subscription $subscription)
    {
        if (! $subscription->isActive()) {
            return;
        }
        $subscription->setSkipNext(1);
        $this->subscriptionResource->save($subscription);
    }

    /**
     * Alter the billing interval of a subscription (self-service).
     *
     * @param Subscription $subscription
     * @param int $intervalCount
     * @param string $intervalUnit
     * @return void
     * @throws \Exception
     */
    public function alterInterval(Subscription $subscription, $intervalCount, $intervalUnit)
    {
        $intervalCount = max(1, (int) $intervalCount);
        if (! in_array($intervalUnit, [
            Subscription::UNIT_DAY,
            Subscription::UNIT_WEEK,
            Subscription::UNIT_MONTH,
            Subscription::UNIT_YEAR,
        ], true)) {
            $intervalUnit = Subscription::UNIT_MONTH;
        }

        $subscription->setIntervalCount($intervalCount);
        $subscription->setIntervalUnit($intervalUnit);
        // Shipping is recalculated to match the new cycle length.
        $subscription->setShippingAmount(
            $this->recalculateShippingForCycle(
                (int) $subscription->getShippingAmount(),
                $intervalUnit,
                $intervalCount
            )
        );
        $subscription->setNextRunAt($this->computeNextRun(null, $intervalUnit, $intervalCount));
        $this->subscriptionResource->save($subscription);
    }

    /**
     * Whether the given order was created by the subscription engine itself.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function isRenewalOrder(OrderInterface $order)
    {
        $payment = $order->getPayment();
        if (! $payment) {
            return false;
        }
        $info = $payment->getAdditionalInformation();
        return is_array($info) && ! empty($info[OrderCreator::RENEWAL_FLAG]);
    }

    /**
     * Whether a subscription already exists for the given order line-item.
     *
     * @param OrderInterface $order
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return bool
     */
    private function subscriptionExists(OrderInterface $order, $item)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('order_id', (int) $order->getEntityId())
            ->addFieldToFilter('product_id', (int) $item->getProductId())
            ->setPageSize(1);

        return $collection->getSize() > 0;
    }

    /**
     * Resolve the authorizing (mandate) transaction id from the order payment.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    private function resolveMandateTransactionId(OrderInterface $order)
    {
        $payment = $order->getPayment();
        if (! $payment) {
            return null;
        }
        $mandate = $payment->getData('cardgate_transaction');
        if (empty($mandate)) {
            $info = $payment->getAdditionalInformation();
            if (is_array($info) && ! empty($info['cardgate_transaction'])) {
                $mandate = $info['cardgate_transaction'];
            }
        }
        if (empty($mandate)) {
            $mandate = $payment->getLastTransId();
        }
        return $mandate ? (string) $mandate : null;
    }

    /**
     * Collect the order line-items that represent subscription products.
     *
     * Works across product types by inspecting visible items and skipping children that
     * are already represented by their (configurable/bundle) parent.
     *
     * @param OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    private function collectSubscriptionItems(OrderInterface $order)
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (! $product) {
                continue;
            }
            if ((int) $product->getData(self::ATTR_IS_SUBSCRIPTION) === 1) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Split the order shipping amount across the subscription items.
     *
     * @param OrderInterface $order
     * @param int $itemCount
     * @return int Shipping share per item in cents.
     */
    private function distributeShipping(OrderInterface $order, $itemCount)
    {
        if ($itemCount < 1) {
            return 0;
        }
        $shipping = (float) $order->getShippingInclTax();
        return (int) round(($shipping / $itemCount) * 100);
    }

    /**
     * Recalculate the shipping amount for a billing cycle.
     *
     * The base (per-order) shipping share is normalised to the cycle length so that
     * shorter cycles do not over-charge shipping and longer cycles consolidate it.
     *
     * @param int $baseShippingCents
     * @param string $intervalUnit
     * @param int $intervalCount
     * @return int Shipping amount in cents for one cycle.
     */
    private function recalculateShippingForCycle($baseShippingCents, $intervalUnit, $intervalCount)
    {
        $baseShippingCents = max(0, (int) $baseShippingCents);
        if ($baseShippingCents === 0) {
            return 0;
        }

        // Express the cycle length in days to scale shipping fairly across payment cycles.
        $days = $this->intervalToDays($intervalUnit, max(1, (int) $intervalCount));
        // Reference cycle is monthly (30 days); shipping scales with the cycle length but
        // is capped to avoid runaway charges on very long cycles.
        $factor = min(2.0, max(0.25, $days / 30.0));

        return (int) round($baseShippingCents * $factor);
    }

    /**
     * Convert an interval to an approximate number of days.
     *
     * @param string $intervalUnit
     * @param int $intervalCount
     * @return int
     */
    private function intervalToDays($intervalUnit, $intervalCount)
    {
        switch ($intervalUnit) {
            case Subscription::UNIT_DAY:
                return $intervalCount;
            case Subscription::UNIT_WEEK:
                return $intervalCount * 7;
            case Subscription::UNIT_YEAR:
                return $intervalCount * 365;
            case Subscription::UNIT_MONTH:
            default:
                return $intervalCount * 30;
        }
    }

    /**
     * Compute the next billing date.
     *
     * The result is normalised to the start of the day: the billing cron runs on fixed
     * moments, so keeping the clock time of the moment of creation would postpone every
     * cycle until the first run *after* that time of day (up to a full day too late).
     *
     * @param string|null $from A starting datetime (Y-m-d H:i:s) or null for "now".
     * @param string $intervalUnit
     * @param int $intervalCount
     * @return string Next run datetime in 'Y-m-d 00:00:00' (UTC).
     */
    private function computeNextRun($from, $intervalUnit, $intervalCount)
    {
        $intervalCount = max(1, (int) $intervalCount);
        $base = $from ? strtotime($from) : time();
        if (false === $base) {
            $base = time();
        }

        switch ($intervalUnit) {
            case Subscription::UNIT_DAY:
                $modifier = '+' . $intervalCount . ' day';
                break;
            case Subscription::UNIT_WEEK:
                $modifier = '+' . $intervalCount . ' week';
                break;
            case Subscription::UNIT_YEAR:
                $modifier = '+' . $intervalCount . ' year';
                break;
            case Subscription::UNIT_MONTH:
            default:
                $modifier = '+' . $intervalCount . ' month';
                break;
        }

        return gmdate('Y-m-d 00:00:00', strtotime($modifier, $base));
    }
}
