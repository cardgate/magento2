<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\Config\Processor;

use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Cardgate\Payment\Model\Config\Master;

class PreProcessorCardgate implements PreProcessorInterface {

	/**
	 * @var Master
	 */
	private $_masterConfig;

	/**
	 *
	 * @param PlaceholderFactory $placeholderFactory
	 * @param ArrayManager $arrayManager
	 */
	public function __construct ( PlaceholderFactory $placeholderFactory, ArrayManager $arrayManager, Master $masterConfig ) {
		$this->_masterConfig = $masterConfig;
	}

	/**
	 * @inheritdoc
	 */
	public function process ( array $config ) {
		foreach ( $this->_masterConfig->getPaymentMethods( true ) as $paymentMethod => $paymentMethodName ) {
			if ( !isset( $config['default']['payment'][$paymentMethod] ) ) {
				$config['default']['payment'][$paymentMethod] = array();
			}
			if ( is_array( $config['default']['payment'][$paymentMethod] ) ) {
				$config['default']['payment'][$paymentMethod] = array_merge(
						[
							'model' => $this->_masterConfig->getPMClassByCode( $paymentMethod ),
							'label' => $paymentMethod,
							'group' => 'cardgate',
							'title' => $paymentMethodName
						], $config['default']['payment'][$paymentMethod] );
			}
		}
		return $config;
	}
}
