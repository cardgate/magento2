<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Cardgate\Payment\Model\Config\Master;

/**
 * CardGate Magento2 module config
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Config implements ConfigInterface {

	/**
	 * Active Paymentmethods as configured in my.cardgate.com (and fetched from
	 * RESTful API)
	 *
	 * @var array
	 */
	private static $activePMIDs = [];

	/**
	 *
	 * @var Master
	 */
	private $_masterConfig;

	/**
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param Magento\Config\Model\ResourceModel\Config $configResource
	 */
	public function __construct ( MutableScopeConfigInterface $scopeConfig, ConfigResource $configResource, Master $master ) {
		$this->_scopeConfig = $scopeConfig;
		$this->_configResource = $configResource;
		$this->_masterConfig = $master;
	}

	/**
	 * Retrieve information from CardGate configuration for given paymentmethod
	 *
	 * @param string $method
	 * @param string $field
	 * @param int|null $storeId
	 * @return mixed
	 */
	public function getField ( $method, $field, $storeId = null ) {
		return $this->_scopeConfig->getValue( 'payment/' . $method . '/' . $field, ScopeInterface::SCOPE_STORE, $storeId );
	}

	/**
	 * Set information info CardGate configuration for given paymentmethod and
	 * save configuration
	 *
	 * @param string $method
	 * @param string $field
	 * @param mixed $value
	 * @param int|null $storeId
	 * @return void
	 */
	public function setField ( $method, $field, $value, $storeId = null ) {
		$this->_scopeConfig->setValue( 'payment/' . $method . '/' . $field, $value, ScopeInterface::SCOPE_STORE, $storeId );
		$this->_configResource->saveConfig( 'payment/' . $method . '/' . $field, $value, MutableScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0 );
	}

	/**
	 * Retrieve information from Global CardGate configuration
	 *
	 * @param string $field
	 * @param int|null $storeId
	 * @return mixed
	 */
	public function getGlobal ( $field, $storeId = null ) {
		return $this->_scopeConfig->getValue( 'cardgate/global/' . $field, ScopeInterface::SCOPE_STORE, $storeId );
	}

	/**
	 * Set information info Global CardGate configuration and save configuration
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param int|null $storeId
	 * @return void
	 */
	public function setGlobal ( $field, $value, $storeId = null ) {
		$this->_scopeConfig->setValue( 'cardgate/global/' . $field, $value, ScopeInterface::SCOPE_STORE, $storeId );
		$this->_configResource->saveConfig( 'cardgate/global/' . $field, $value, MutableScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0 );
	}

	/**
	 * Get active Paymentmethod ID's (CardGate style ID's)
	 *
	 * @param number $storeId
	 * @return mixed
	 */
	public function getActivePMIDs ( $storeId = 0 ) {
		if ( isset( self::$activePMIDs[$storeId] ) && is_array( self::$activePMIDs[$storeId] ) ) {
			return self::$activePMIDs[$storeId];
		}
		self::$activePMIDs[$storeId] = [];
		$activePmInfo = unserialize( $this->getGlobal( 'active_pm', $storeId ) );
		if ( !is_array($activePmInfo) ) {
			$activePmInfo = [];
		}
		foreach ( $activePmInfo as $activePm ) {

			self::$activePMIDs[$storeId][] = $activePm['id'];
		}
		return self::$activePMIDs[$storeId];
	}

	/**
	 * Sets method code
	 *
	 * @param string $methodCode
	 * @return void
	 */
	public function setMethodCode($methodCode)
	{
		return null;
		//$this->_methodCode = $methodCode;
	}


	/**
	 * Sets path pattern
	 *
	 * @param string $pathPattern
	 * @return void
	 */
	public function setPathPattern($pathPattern)
	{
		return null;
		//$this->pathPattern = $pathPattern;
	}


	/**
	 * Retrieve information from payment configuration
	 *
	 * @param string $field
	 * @param int|null $storeId
	 *
	 * @return mixed
	 */
	public function getValue ( $field, $storeId = null ) {
		return null;
		//return $this->_scopeConfig->getValue( sprintf( $this->_pathPattern, $this->_methodCode, $field ), ScopeInterface::SCOPE_STORE, $storeId );
	}

}
