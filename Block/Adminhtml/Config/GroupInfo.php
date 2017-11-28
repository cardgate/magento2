<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config;

/**
 * Render for "global" configuration group element
 *
 * @author DBS B.V.
 * @package Magento2
 */
class GroupInfo extends \Magento\Config\Block\System\Config\Form\Fieldset {

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
	 * @param array $data
	 */
	public function __construct ( \Magento\Backend\Block\Context $context, \Magento\Backend\Model\Auth\Session $authSession, \Magento\Framework\View\Helper\Js $jsHelper, Config $backendConfig, array $data = [] ) {
		$this->config = $backendConfig;
		parent::__construct( $context, $authSession, $jsHelper, $data );
	}

	/**
	 * Return header title part of html for fieldset
	 *
	 * @param AbstractElement $element
	 * @return string
	 */
	protected function _getHeaderTitleHtml ( $element ) {
		$legend = $element->getLegend();
		if ( ! $this->testConfigurationHealth() ) {
			$legend .= " - <span style='color:red'>".__("Attention required")."</span>";
		}
		$element->setLegend( $legend );

		return parent::_getHeaderTitleHtml( $element );
	}

	/**
	 * Tests configuration health
	 *
	 * @return boolean
	 */
	private function testConfigurationHealth () {
		$extra = $this->_authSession->getUser()->getExtra();
		if ( empty( $this->config->getGlobal( 'active_pm' ) ) ) {
			$extra['configState']['cardgate_info'] = true;
			$extra['configState']['cardgate_info_pms'] = true;
			$this->_authSession->getUser()->setExtra( $extra );
			return false;
		}
		if ( isset( $_SERVER['CG_API_URL'] ) && $_SERVER['CG_API_URL'] != '' ) {
			$extra['configState']['cardgate_info'] = true;
			$extra['configState']['cardgate_info_test'] = true;
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
		if ( isset( $extra['configState'][$element->getId()] ) ) {
			return $extra['configState'][$element->getId()];
		}

		$groupConfig = $element->getGroup();
		if ( ! empty( $groupConfig['expanded'] ) ) {
			return true;
		}

		return false;
	}
}
