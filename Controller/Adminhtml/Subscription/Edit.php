<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Adminhtml\Subscription;

use Cardgate\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Cardgate\Payment\Model\SubscriptionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

/**
 * Admin edit page for a single subscription.
 */
class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Cardgate_Payment::subscriptions';
    public const REGISTRY_KEY = 'cardgate_current_subscription';

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, (int) $this->getRequest()->getParam('id'));
        if (! $subscription->getId()) {
            $this->messageManager->addErrorMessage(__('This subscription no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
        $this->registry->register(self::REGISTRY_KEY, $subscription);

        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->setActiveMenu('Cardgate_Payment::subscriptions');
        $page->getConfig()->getTitle()->prepend(__('Subscription #%1', $subscription->getId()));
        return $page;
    }
}
