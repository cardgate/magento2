<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Subscription;

use Cardgate\Payment\Model\Subscription;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates the Magento sales order that represents a single subscription billing cycle.
 *
 * A subscription cycle is not merely a gateway charge: every cycle results in a real
 * (renewal) order so the merchant has stock movements, invoices, order mails and the
 * regular CardGate callback handling available.
 */
class OrderCreator
{
    /**
     * Payment additional information key marking an order as a subscription renewal.
     */
    public const RENEWAL_FLAG = 'cardgate_subscription_id';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteFactory $quoteFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteManagement $quoteManagement
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $quoteRepository,
        QuoteManagement $quoteManagement,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Create the renewal order for one billing cycle of the given subscription.
     *
     * The order is a copy of the subscription line-item of the originating order: same
     * customer, same addresses, same shipping and payment method.
     *
     * @param Subscription $subscription
     * @return OrderInterface
     * @throws \Exception When the renewal order cannot be created.
     */
    public function create(Subscription $subscription)
    {
        $sourceOrder = $this->orderRepository->get((int) $subscription->getOrderId());

        $storeId = (int) ($subscription->getStoreId() ?: $sourceOrder->getStoreId());
        $store = $this->storeManager->getStore($storeId);

        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setStoreId($storeId);
        // Bill in the currency the subscription was created in.
        $quote->setQuoteCurrencyCode((string) $subscription->getCurrency());

        $customerId = $subscription->getCustomerId();
        if ($customerId) {
            $quote->assignCustomer($this->customerRepository->getById((int) $customerId));
        } else {
            $quote->setCustomerIsGuest(true);
            $quote->setCustomerEmail($sourceOrder->getCustomerEmail());
            $quote->setCustomerFirstname($sourceOrder->getCustomerFirstname());
            $quote->setCustomerLastname($sourceOrder->getCustomerLastname());
        }
        $quote->setCustomerEmail($sourceOrder->getCustomerEmail());

        $product = $this->productRepository->getById((int) $subscription->getProductId(), false, $storeId);
        $qty = (float) $subscription->getQty();
        if ($qty <= 0) {
            $qty = 1;
        }
        $quote->addProduct($product, $qty);

        $this->copyAddresses($quote, $sourceOrder);

        if (! $quote->isVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->collectShippingRates();
            $shippingMethod = (string) $sourceOrder->getShippingMethod();
            if ('' !== $shippingMethod) {
                $shippingAddress->setShippingMethod($shippingMethod);
            }
        }

        $methodCode = (string) ($subscription->getPaymentMethod() ?: $sourceOrder->getPayment()->getMethod());
        $quote->setPaymentMethod($methodCode);
        $quote->setInventoryProcessed(false);

        // The quote has to be persisted before the payment is imported. Magento only
        // links the payment back to its quote when the quote already has an id (see
        // Magento\Quote\Model\Quote::getPayment()); on an unsaved quote the payment is
        // detached and Quote\Payment::getMethodInstance() fatals on getQuote()->getStoreId().
        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        $payment = $quote->getPayment();
        // Be explicit about the relation: it must hold regardless of how the payment
        // was instantiated (new quote, reloaded quote, custom payment collection).
        $payment->setQuote($quote);
        $payment->importData([
            'method' => $methodCode,
        ]);

        $quote->collectTotals();
        $this->quoteRepository->save($quote);

        /** @var Order $order */
        $order = $this->quoteManagement->submit($quote);
        if (! $order || ! $order->getId()) {
            throw new \RuntimeException(
                'could not create renewal order for subscription #' . $subscription->getId()
            );
        }

        // Mark the order as a subscription renewal so it never spawns a subscription itself.
        $payment = $order->getPayment();
        if ($payment) {
            $payment->setAdditionalInformation(self::RENEWAL_FLAG, (string) $subscription->getId());
        }
        $order->addCommentToStatusHistory(
            __(
                'Subscription renewal order for subscription #%1 (cycle %2).',
                $subscription->getId(),
                (int) $subscription->getCyclesBilled() + 1
            )
        );
        $this->orderRepository->save($order);

        $this->logger->info(
            'CardGate subscription #' . $subscription->getId()
            . ': renewal order ' . $order->getIncrementId() . ' created'
        );

        return $order;
    }

    /**
     * Copy the billing/shipping address of the originating order onto the renewal quote.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param OrderInterface $sourceOrder
     * @return void
     */
    private function copyAddresses($quote, OrderInterface $sourceOrder)
    {
        $billing = $sourceOrder->getBillingAddress();
        if ($billing) {
            $quote->getBillingAddress()->addData($this->extractAddressData($billing->getData()));
        }

        $shipping = $sourceOrder->getShippingAddress();
        if ($shipping && ! $quote->isVirtual()) {
            $quote->getShippingAddress()->addData($this->extractAddressData($shipping->getData()));
        }
    }

    /**
     * Strip all identifiers from an order address so it can be reused on a new quote.
     *
     * @param array $data
     * @return array
     */
    private function extractAddressData(array $data)
    {
        foreach ([
            'entity_id',
            'address_id',
            'parent_id',
            'quote_id',
            'address_type',
            'created_at',
            'updated_at',
        ] as $key) {
            unset($data[$key]);
        }
        return $data;
    }
}
