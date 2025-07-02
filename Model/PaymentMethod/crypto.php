<?php

/**
 * Copyright (c) 2025 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */

namespace Cardgate\Payment\Model\PaymentMethod;

/**
 * Crypto class.
 * @author DBS B.V.
 * Creates and manages CardGate Crypto
 */
class Crypto extends \Cardgate\Payment\Model\PaymentMethods {

    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'cardgate_crypto';
}