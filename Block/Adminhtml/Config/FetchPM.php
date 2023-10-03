<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config as CardgateConfig;
use Magento\Backend\Block\Template\Context as Context;

/**
 * Fetch Payment methods HTML Block renderer
 *
 * @author DBS B.V.
 *
 */
class FetchPM extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     *
     * @var CardgateConfig
     */
    private $cardgateConfig;

    /**
     *
     * @param Context $context
     * @param CardgateConfig $cardgateConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
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
        if (!empty($this->cardgateConfig->getGlobal('api_username'))
             && ! empty($this->cardgateConfig->getGlobal('api_password'))
             && ! empty($this->cardgateConfig->getGlobal('site_id'))
             && ! empty($this->cardgateConfig->getGlobal('site_key'))
        ) {
            $fetchPMUrl = $this->_urlBuilder->getUrl( "cardgate/gateway/fetchpm", [
                'section' => 'gateway'
            ] );

            return "<button onclick='window.open(\"{$fetchPMUrl}\");return false;'><span>" .
                   __("Refresh active payment methods") . "</span></button>";
        } else {
            return __("Please enter Site Id, Hash key, Merchant Id and API key first");
        }
    }
}
