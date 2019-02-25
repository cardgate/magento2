<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Config;

use Magento\Framework\App\Config\Initial;

/**
 * Initial Config plugin to dynamically add all payment methods.
 *
 * @author DBS B.V.
 * @package Magento2
 */
class InitialPlugin {

	/**
	 *
	 * @var Master $_masterConfig
	 */
	private $_masterConfig = null;

	public function __construct ( Master $masterConfig ) {
		$this->_masterConfig = $masterConfig;
	}

	/**
	 * Alter getData's output
	 *
	 * @param Initial $initialConfig
	 * @param \Closure $proceed
	 * @param unknown $scope
	 * @return array[]
	 */
	public function aroundGetData ( Initial $initialConfig, \Closure $proceed, $scope ) {
		$data = $proceed( $scope );
		foreach ( $this->_masterConfig->getPaymentMethods( true ) as $paymentMethod => $paymentMethodName ) {
			$data['payment'][$paymentMethod] = [
				'model' => $this->_masterConfig->getPMClassByCode( $paymentMethod ),
				'label' => $paymentMethod,
				'group' => 'cardgate',
				'title' => $paymentMethodName
			];
		}
		return $data;
	}

}
