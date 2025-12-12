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
 *
 */
class DefaultInfo extends \Magento\Payment\Block\Info
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * Checkout template
     *
     * @var string
     */
    protected $_template = 'Cardgate_Payment::info/defaultinfo.phtml';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            $this->_instructions = $this->getInfo()->getAdditionalInformation(
                'instructions'
            ).' ' ?: trim($this->getMethod()->getConfigData('instructions')).' ';
        }
        return $this->_instructions;
    }
}
