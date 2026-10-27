<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Adminhtml\Subscription;

use Cardgate\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Cardgate\Payment\Model\Subscription;
use Cardgate\Payment\Model\Subscription\SubscriptionManager;
use Cardgate\Payment\Model\SubscriptionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Persist admin changes to a subscription.
 */
class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Cardgate_Payment::subscriptions';

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    public function __construct(
        Context $context,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        SubscriptionManager $subscriptionManager
    ) {
        parent::__construct($context);
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $id = (int) $request->getParam('id');

        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, $id);
        if (! $subscription->getId()) {
            $this->messageManager->addErrorMessage(__('This subscription no longer exists.'));
            return $redirect->setPath('*/*/index');
        }

        try {
            $qty = (float) $request->getParam('qty', $subscription->getQty());
            if ($qty > 0) {
                $subscription->setQty($qty);
            }
            if ($request->getParam('amount') !== null && $request->getParam('amount') !== '') {
                $subscription->setAmount(max(0, (int) round((float) $request->getParam('amount') * 100)));
            }
            if ($request->getParam('shipping_amount') !== null && $request->getParam('shipping_amount') !== '') {
                $subscription->setShippingAmount(
                    max(0, (int) round((float) $request->getParam('shipping_amount') * 100))
                );
            }
            $subscription->setSkipNext($request->getParam('skip_next') ? 1 : 0);

            $nextRun = trim((string) $request->getParam('next_run_at'));
            if ($nextRun !== '') {
                $time = strtotime($nextRun);
                if ($time === false) {
                    throw new \InvalidArgumentException((string) __('Invalid next billing date.'));
                }
                $subscription->setNextRunAt(gmdate('Y-m-d H:i:s', $time));
            }
            $this->subscriptionResource->save($subscription);

            $intervalCount = (int) $request->getParam('interval_count');
            $intervalUnit = (string) $request->getParam('interval_unit');
            if ($intervalCount > 0 && $intervalUnit !== ''
                && ($intervalCount !== (int) $subscription->getIntervalCount()
                    || $intervalUnit !== $subscription->getIntervalUnit())
            ) {
                $this->subscriptionManager->alterInterval($subscription, $intervalCount, $intervalUnit);
            }

            $status = (string) $request->getParam('status');
            if ($status !== '' && $status !== $subscription->getStatus()) {
                switch ($status) {
                    case Subscription::STATUS_ACTIVE:
                        $this->subscriptionManager->resume($subscription);
                        break;
                    case Subscription::STATUS_PAUSED:
                        $this->subscriptionManager->pause($subscription);
                        break;
                    case Subscription::STATUS_CANCELLED:
                        $this->subscriptionManager->cancel($subscription);
                        break;
                }
            }

            $this->messageManager->addSuccessMessage(__('The subscription has been saved.'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Could not save subscription: %1', $e->getMessage()));
            return $redirect->setPath('*/*/edit', ['id' => $id]);
        }

        if ($request->getParam('back')) {
            return $redirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $redirect->setPath('*/*/index');
    }
}
