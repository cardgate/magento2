<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config as CardgateConfig;

/**
 * Render for "show payment method" element
 *
 *
 */
class ShowPM extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     * @var CardgateConfig
     */
    private $cardgateConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CardgateConfig $cardgateConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CardgateConfig $cardgateConfig,
        array $data = []
    ) {
        $this->cardgateConfig = $cardgateConfig;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (empty($this->cardgateConfig->getGlobal('active_pm'))) {
            return "<span style='color:#ff0000'>" . __("No active payment methods found") . "</span>";
        } else {
            return implode(', ', $this->cardgateConfig->getActivePMIDs());
        }
    }
}
