<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Subscription;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Alter the billing interval of a subscription (self-service).
 */
class Alter extends AbstractSubscription implements HttpPostActionInterface
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (! $this->isCustomerLoggedIn()) {
            return $this->redirectToList();
        }

        $request = $this->getRequest();
        $subscription = $this->loadOwnedSubscription($request->getParam('id'));
        if (! $subscription) {
            $this->messageManager->addErrorMessage(__('Subscription not found.'));
            return $this->redirectToList();
        }

        $intervalCount = (int) $request->getParam('interval_count');
        $intervalUnit = (string) $request->getParam('interval_unit');

        try {
            $this->subscriptionManager->alterInterval($subscription, $intervalCount, $intervalUnit);
            $this->messageManager->addSuccessMessage(__('Your subscription has been updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not update the subscription.'));
        }

        return $this->redirectToList();
    }
}
