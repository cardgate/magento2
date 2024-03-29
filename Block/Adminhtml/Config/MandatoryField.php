<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config as CardgateConfig;

/**
 * Render for mandatory field elements
 *
 * @author DBS B.V.
 *
 */
class MandatoryField extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Cardgate configuration data
     *
     * @var CardgateConfig
     */
    private $cardgateConfig;

    /**
     *
     * @param \Magento\Backend\Block\Context $context
     * @param CardgateConfigg $cardgateConfig
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
     *
     * @see \Magento\Config\Block\System\Config\Form\Field::_renderValue()
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getValue() == '') {
            $element->setComment($element->getComment() . "<span style='color:red'>".__("Missing value")."</span>");
        }
        return parent::_renderValue($element);
    }
}
