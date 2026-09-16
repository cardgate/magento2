<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Subscription;

/**
 * Defines which CardGate payment methods are allowed to be used for subscriptions.
 *
 * Per requirements only the following methods may carry a subscription:
 * Bancontact, Creditcard, Direct Debit, iDEAL, Przelewy24 and onlineueberweisen.
 */
class AllowedMethods
{
    /**
     * Magento payment method codes allowed for subscriptions.
     *
     * @var string[]
     */
    public const ALLOWED = [
        'cardgate_bancontact',
        'cardgate_mistercash',
        'cardgate_creditcard',
        'cardgate_directdebit',
        'cardgate_ideal',
        'cardgate_przelewy24',
        'cardgate_onlineueberweisen',
    ];

    /**
     * Whether the supplied payment method code is allowed for subscriptions.
     *
     * @param string|null $sMethodCode_
     * @return bool
     */
    public function isAllowed($sMethodCode_)
    {
        return in_array((string) $sMethodCode_, self::ALLOWED, true);
    }

    /**
     * Get all allowed payment method codes.
     *
     * @return string[]
     */
    public function getAllowed()
    {
        return self::ALLOWED;
    }
}
