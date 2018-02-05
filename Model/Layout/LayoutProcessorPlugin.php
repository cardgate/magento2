<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Layout;

use Cardgate\Payment\Model\Config\Master;
use Magento\Checkout\Block\Checkout\LayoutProcessor;

/**
 * Layout Processor plugin to inject paymentmethods in checkout billing-step section
 *
 * @author DBS B.V.
 * @package Magento2
 */
class LayoutProcessorPlugin {

	/**
	 *
	 * @var Master $_masterConfig
	 */
	private $_masterConfig = null;

	public function __construct ( Master $masterConfig ) {
		$this->_masterConfig = $masterConfig;
	}

	/**
	 * Inject paymentmethods in checkout billing-step section
	 *
	 * @param LayoutProcessor $layoutProcessor
	 * @param \Closure $proceed
	 * @param unknown $scope
	 * @return string[]|boolean[]
	 */
	public function aroundProcess ( LayoutProcessor $layoutProcessor, \Closure $proceed, $scope ) {
		$data = $proceed( $scope );
		$arr = [
			'component' => 'Cardgate_Payment/js/view/payment/paymentmethods',
			'label' => 'CardGate',
			'methods' => []
		];
		foreach ( $this->_masterConfig->getPaymentMethods() as $paymentMethod ) {
			$arr['methods'][$paymentMethod] = [
				'isBillingAddressRequired' => true
			];
		}
		$data['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['renders']['children']['cardgate'] = $arr;
		return $data;
	}

}
