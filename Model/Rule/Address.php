<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Rule;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{

    // NOTE: the payment method rule for discount / coupons was (temporary) removed from Magento with the commit below:
    // https://github.com/magento/magento2/commit/bb65d05d41f30cd6a10fdfffb10b1dd8f42d3a77#diff-6af1fa68376d90ae611de38779431624
    // This was done because the default cart view doesn't refresh the totals after selecting a payment method. Our
    // plugin makes sure that it does, so this option is reinstated due to popular demand until it is re-added by the
    // Magento developers.

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $currentOptions = $this->getAttributeOption();
        if (is_array($currentOptions) &&
            ! isset($currentOptions['payment_method'])
        ) {
            $currentOptions['payment_method'] = __('Payment Method');
            $this->setAttributeOption($currentOptions);
        }
        return $this;
    }
}
