<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Subscription;

use Cardgate\Payment\Model\Subscription;
use Cardgate\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Cardgate\Payment\Model\SubscriptionFactory;
use Cardgate\Payment\Model\Subscription\SubscriptionManager;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Base controller for customer self-service subscription actions.
 *
 * Enforces that the customer is logged in and only ever operates on subscriptions
 * owned by the current customer.
 */
abstract class AbstractSubscription extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    protected $subscriptionResource;

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionResource $subscriptionResource
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        SubscriptionManager $subscriptionManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Redirect guests to the login page.
     *
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        return (bool) $this->customerSession->isLoggedIn();
    }

    /**
     * Load a subscription if (and only if) it belongs to the current customer.
     *
     * @param int $id
     * @return Subscription|null
     */
    protected function loadOwnedSubscription($id)
    {
        if (! $id) {
            return null;
        }
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, (int) $id);
        if (! $subscription->getId()) {
            return null;
        }
        if ((int) $subscription->getCustomerId() !== (int) $this->customerSession->getCustomerId()) {
            return null;
        }
        return $subscription;
    }

    /**
     * Build a redirect to the subscription overview.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function redirectToList()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('cardgate/subscription/index');
        return $redirect;
    }
}
