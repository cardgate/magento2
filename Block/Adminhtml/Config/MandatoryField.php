<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config;

/**
 * Render for mandatory field elements
 *
 * @author DBS B.V.
 * @package Magento2
 */
class MandatoryField extends \Magento\Config\Block\System\Config\Form\Field {

	/**
	 * Config
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
	 * @see \Magento\Config\Block\System\Config\Form\Field::_renderValue()
	 */
	protected function _renderValue ( \Magento\Framework\Data\Form\Element\AbstractElement $element ) {
		if ( $element->getValue() == '' ) {
			$element->setComment( $element->getComment() . "<span style='color:red'>".__("Missing value")."</span>" );
		}
		return parent::_renderValue( $element );
	}

}