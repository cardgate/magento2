<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config;

/**
 * Render for "paymentmethod" configuration group elements
 *
 * @author DBS B.V.
 * @package Magento2
 */
class GroupPaymentMethod extends \Magento\Config\Block\System\Config\Form\Fieldset {

	/**
	 *
	 * @var Config
	 */
	private $config;

	/**
	 * paymentmethod id
	 *
	 * @var boolean
	 */
	private $pm_id;

	/**
	 * flag if paymentmethod is active in cardgate platform for configured
	 * siteid
	 *
	 * @var boolean
	 */
	private $pm_enabled;

	/**
	 * flag if paymentmethod is active in magento configuration
	 *
	 * @var boolean
	 */
	private $pm_active;

	/**
	 *
	 * @param \Magento\Backend\Block\Context $context
	 * @param \Magento\Backend\Model\Auth\Session $authSession
	 * @param \Magento\Framework\View\Helper\Js $jsHelper
	 * @param array $data
	 */
	public function __construct ( \Magento\Backend\Block\Context $context, \Magento\Backend\Model\Auth\Session $authSession, \Magento\Framework\View\Helper\Js $jsHelper, Config $backendConfig, array $data = [] ) {
		$this->config = $backendConfig;
		parent::__construct( $context, $authSession, $jsHelper, $data );
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Magento\Config\Block\System\Config\Form\Fieldset::render()
	 */
	public function render ( \Magento\Framework\Data\Form\Element\AbstractElement $element ) {
		$this->pm_id = $element->getData( 'original_data' )['pmid'];
		$this->pm_active = ( $this->config->getField( 'cardgate_' . $this->pm_id, 'active' ) == 1 );
		$activePms = $this->config->getActivePMIDs();
		$this->pm_enabled = in_array( $this->pm_id, $activePms );
		return parent::render( $element );
	}

	/**
	 * Return header title part of html for fieldset
	 *
	 * @param AbstractElement $element
	 * @return string
	 */
	protected function _getHeaderTitleHtml ( $element ) {
		$legend = $element->getLegend();

		if ( ! $this->pm_enabled ) {
			$legend = "<span style='text-decoration:line-through;'>{$legend}</span>";
		} elseif ( ! $this->pm_active ) {
			$legend .= " - <span>(".__("disabled").")</span>";
		}
		if ( ! $this->testConfigurationHealth() ) {
			$legend .= " - <span style='color:red;'>".__("Enabled but not active")."</span>";
		}

		$element->setLegend( $legend );
		return parent::_getHeaderTitleHtml( $element );
	}

	private function testConfigurationHealth () {

		if ( ! $this->pm_enabled && $this->pm_active ) {
			$extra = $this->_authSession->getUser()->getExtra();
			$extra['configState']['cardgate_' . $this->pm_id] = true;
			$this->_authSession->getUser()->setExtra( $extra );
			return false;
		}
		return true;
	}

	/**
	 * Return header comment part of html for fieldset
	 *
	 * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
	 * @return string
	 */
	protected function _getHeaderCommentHtml ( $element ) {

		if ( ! $this->pm_enabled ) {
			return '<div class="comment">' . __("This paymentmethod is not active in the CardGate configuration.") . ' <a target="_blank" href="https://my.cardgate.com">' . __( "Please check CardGate settings" ) . '</a> ' .
					__("or") .' <a target="_blank" href="https://www.cardgate.com">' . __( "contact an accountmanager" ) . '</a>.</div>';
		}
		$groupConfig = $element->getGroup();

		if ( empty( $groupConfig['help_url'] ) || ! $element->getComment() ) {
			return parent::_getHeaderCommentHtml( $element );
		}

		$html = '<div class="comment">' . $element->getComment() . ' <a target="_blank" href="' . $groupConfig['help_url'] . '">' . __( 'Help' ) . '</a></div>';

		return $html;
	}

	/**
	 * Return collapse state
	 *
	 * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
	 * @return bool
	 */
	protected function _isCollapseState ( $element ) {

		$extra = $this->_authSession->getUser()->getExtra();
		if ( isset( $extra['configState']['cardgate_' . $this->pm_id] ) ) {
			return $extra['configState']['cardgate_' . $this->pm_id];
		}

		if ( ! $this->pm_enabled ) {
			return false;
		}

		$groupConfig = $element->getGroup();
		if ( ! empty( $groupConfig['expanded'] ) ) {
			return true;
		}

		return false;
	}
}
