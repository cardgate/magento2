<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\PaymentMethod;

/**
 * Bitcoin class.
 * @author DBS B.V.
 * Creates and manages CardGate Billink
 */
class Billink extends \Cardgate\Payment\Model\PaymentMethods
{

    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'cardgate_billink';
}
