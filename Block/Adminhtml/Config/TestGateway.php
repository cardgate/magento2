<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config as CardgateConfig;

/**
 * Render for "test gateway settings" element
 *
 *
 */
class TestGateway extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Config
     *
     * @var CardgateConfig
     */
    private $cardgateConfig;

    /**
     *
     * @param \Magento\Backend\Block\Context $context
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
        if (! empty($this->cardgateConfig->getGlobal('api_username'))
            && ! empty($this->cardgateConfig->getGlobal('api_password'))
            && ! empty($this->cardgateConfig->getGlobal('site_id'))
            && ! empty($this->cardgateConfig->getGlobal('site_key'))
        ) {
            $testGatewayUrl = $this->_urlBuilder->getUrl("cardgate/gateway/test", [
                'section' => 'gateway'
            ]);
            return "<button onclick='window.open(\"{$testGatewayUrl}\");return false;'><span>".
                    __("Test Gateway communication")."</span></button>";
        } else {
            return __("Please enter Site Id, Hash key, Merchant Id and API key first");
        }
    }
}
