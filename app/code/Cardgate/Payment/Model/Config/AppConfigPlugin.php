<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\Config;

use Magento\Framework\App\Config;


//ConfigSourceAggregated

/**
 * Initial Config plugin to dynamically add all paymentmethods
 *
 * @author DBS B.V.
 * @package Magento2
 */
class AppConfigPlugin {

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
	public function aroundGet ( Config $configSource, \Closure $proceed, $configType, $path = '', $default = null ) {
		$data = $proceed( $configType, $path, $default );
		var_dump($data);die();
		if ( $configType == 'system' && substr( $path, 0, 7 ) == 'payment' ) {
		foreach ( $this->_masterConfig->getPaymentMethods( true ) as $paymentMethod => $paymentMethodName ) {
			$data['payment'][$paymentMethod] = [
				'model' => $this->_masterConfig->getPMClassByCode( $paymentMethod ),
				'label' => $paymentMethod,
				'group' => 'cardgate',
				'title' => $paymentMethodName
			];
		}
		}
		return $data;
	}

}