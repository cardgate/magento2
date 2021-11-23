<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Info;

/**
 * Default Checkout template
 *
 * @author DBS B.V.
 *
 */
class DefaultInfo extends \Magento\Payment\Block\Info
{

    /**
     * Checkout template
     *
     * @var string
     */
    protected $_template = 'Cardgate_Payment::info/defaultinfo.phtml';
}
