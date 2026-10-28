<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Observer;

use Cardgate\Payment\Model\Subscription\SubscriptionManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates CuroPayments subscriptions for the subscription line-items of a placed order.
 *
 * Listens on sales_order_place_after so mixed carts (one-off + subscription items)
 * are handled gracefully: only subscription items spawn a recurring subscription.
 */
class SubscriptionOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SubscriptionManager $subscriptionManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubscriptionManager $subscriptionManager,
        LoggerInterface $logger
    ) {
        $this->subscriptionManager = $subscriptionManager;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        if (! $order) {
            return;
        }

        try {
            $this->subscriptionManager->createFromOrder($order);
        } catch (\Exception $e) {
            // Never block order placement because of subscription bookkeeping.
            $this->logger->error('CardGate subscription creation failed: ' . $e->getMessage());
        }
    }
}
