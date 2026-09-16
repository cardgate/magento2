<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Subscription;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Skip the next billing cycle of a subscription (self-service).
 */
class Skip extends AbstractSubscription implements HttpPostActionInterface
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
            $this->subscriptionManager->skipNext($subscription);
            $this->messageManager->addSuccessMessage(__('The next billing cycle will be skipped.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not skip the next cycle.'));
        }

        return $this->redirectToList();
    }
}
