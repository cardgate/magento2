<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Cron;

use Cardgate\Payment\Model\Subscription\SubscriptionManager;
use Psr\Log\LoggerInterface;

/**
 * Cron entry point that charges all due subscriptions.
 */
class ProcessSubscriptions
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
     * Charge every subscription that is due.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $processed = $this->subscriptionManager->chargeDue();
            if ($processed > 0) {
                $this->logger->info('CardGate subscriptions processed: ' . $processed);
            }
        } catch (\Throwable $e) {
            $this->logger->error('CardGate subscription cron failed: ' . $e->getMessage());
        }
    }
}
