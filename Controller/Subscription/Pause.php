<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Subscription;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Pause a subscription (self-service).
 */
class Pause extends AbstractSubscription implements HttpPostActionInterface
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (! $this->isCustomerLoggedIn()) {
            return $this->redirectToList();
        }

        $subscription = $this->loadOwnedSubscription($this->getRequest()->getParam('id'));
        if (! $subscription) {
            $this->messageManager->addErrorMessage(__('Subscription not found.'));
            return $this->redirectToList();
        }

        try {
            $this->subscriptionManager->pause($subscription);
            $this->messageManager->addSuccessMessage(__('Your subscription has been paused.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not pause the subscription.'));
        }

        return $this->redirectToList();
    }
}
