<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config;

/**
 * Fetch Paymentmethods HTML Block renderer
 *
 * @author DBS B.V.
 * @package Magento2
 */
class FetchPM extends \Magento\Config\Block\System\Config\Form\Field {

	/**
	 *
	 * @var Config
	 */
	private $config;

	/**
	 *
	 * @param \Magento\Backend\Block\Context $context
	 * @param \Magento\Backend\Model\Auth\Session $authSession
	 * @param \Magento\Framework\View\Helper\Js $jsHelper
	 * @param \Magento\Config\Model\Config $backendConfig
	 * @param array $data
	 */
	public function __construct ( \Magento\Backend\Block\Template\Context $context, Config $backendConfig, array $data = [] ) {
		$this->config = $backendConfig;
		parent::__construct( $context, $data );
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
	 */
	protected function _getElementHtml ( \Magento\Framework\Data\Form\Element\AbstractElement $element ) {
		if (
			! empty( $this->config->getGlobal( 'api_username' ) )
			&& ! empty( $this->config->getGlobal( 'api_password' ) )
			&& ! empty( $this->config->getGlobal( 'site_id' ) )
			&& ! empty( $this->config->getGlobal( 'site_key' ) )
		) {
			$fetchPMUrl = $this->_urlBuilder->getUrl( "cardgate/gateway/fetchpm", [
				'section' => 'gateway'
			] );
			return "<button onclick='window.open(\"{$fetchPMUrl}\");return false;'><span>".__("Refresh active paymentmethods")."</span></button>";
		} else {
			return __("Please enter Site Id, Hash key, Merchant Id and API key first");
		}
	}

}
