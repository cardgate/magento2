<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model;

use Magento\Payment\Gateway\ConfigInterface;
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
class Config implements ConfigInterface{

	const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

	/**
	 * @var ScopeConfigInterface
	 */
	private $scopeConfig;

	/**
	 * @var string|null
	 */
	private $methodCode;

	/**
	 * @var string|null
	 */
	private $pathPattern;

	/**
	 * Active Payment methods as configured in my.cardgate.com (and fetched from
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
	 * @var ConfigResource
	 */
	private $_configResource;

	/**
	 *
	 * @var \Magento\Framework\Serialize\SerializerInterface
	 */
	public $serializer;

	/**
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param Magento\Config\Model\ResourceModel\Config $configResource
	 * @param Master $master
	 * @param string $methodCode
	 */
	public function __construct ( MutableScopeConfigInterface $scopeConfig, ConfigResource $configResource, Master $master, $methodCode=null, $pathPattern = self::DEFAULT_PATH_PATTERN) {

		$this->scopeConfig = $scopeConfig;
		$this->methodCode = $methodCode;
		$this->pathPattern = $pathPattern;
		$this->_configResource = $configResource;
		$this->_masterConfig = $master;
		$this->setSerializer();;
	}

	/**
	 * Sets method code
	 *
	 * @param string $methodCode
	 * @return void
	 */
	public function setMethodCode($methodCode)
	{
		$this->methodCode = $methodCode;
	}

	/**
	 * Sets path pattern
	 *
	 * @param string $pathPattern
	 * @return void
	 */
	public function setPathPattern($pathPattern)
	{
		$this->pathPattern = $pathPattern;
	}

	/**
	 * Set information info CardGate configuration for given payment method and
	 * save configuration
	 *
	 * @param string $method
	 * @param string $field
	 * @param mixed $value
	 * @param int|null $storeId
	 * @return void
	 */
	public function setField ( $method, $field, $value, $storeId = null ) {
		$this->scopeConfig->setValue( 'payment/' . $method . '/' . $field, $value, ScopeInterface::SCOPE_STORE, $storeId );
		$this->_configResource->saveConfig( 'payment/' . $method . '/' . $field, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0 );
	}

	/**
	 * Retrieve information from Global CardGate configuration
	 *
	 * @param string $field
	 * @param int|null $storeId
	 * @return mixed
	 */
	public function getGlobal ( $field, $storeId = null ) {
		return $this->scopeConfig->getValue( 'cardgate/global/' . $field, ScopeInterface::SCOPE_STORE, $storeId );
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
		$this->scopeConfig->setValue( 'cardgate/global/' . $field, $value, ScopeInterface::SCOPE_STORE, $storeId );
		$this->_configResource->saveConfig( 'cardgate/global/' . $field, $value, MutableScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0 );
	}

	/**
	 * Get active Payment method ID's (CardGate style ID's)
	 *
	 * @param number $storeId
	 * @return mixed
	 */
	public function getActivePMIDs ( $storeId = 0 ) {

		if ( isset( self::$activePMIDs[$storeId] ) && is_array( self::$activePMIDs[$storeId] ) ) {
			return self::$activePMIDs[$storeId];
		}
		self::$activePMIDs[$storeId] = [];
		try {

			$activePmInfo = $this->serializer->unserialize( $this->getGlobal( 'active_pm', $storeId ) );
		} catch (\Exception $e){
			$activePmInfo = [];
		}

		foreach ( $activePmInfo as $activePm ) {

			self::$activePMIDs[$storeId][] = $activePm['id'];
		}
		return self::$activePMIDs[$storeId];
	}

	/**
	 * Show if not logged in is used as a group
	 * @param int $storeId
	 *
	 * @return bool
	 */
	public function LoggedInIsGroup($storeId = 0)
	{
		$aActivePaymentIds = $this->getActivePMIDs($storeId);
		$result = false;
		foreach ($aActivePaymentIds as $id){
			$methodCode = 'cardgate_'.$id;
			$customerGroups = $this->scopeConfig->getValue( sprintf($this->pathPattern, $methodCode, 'specific_customer_groups'), ScopeInterface::SCOPE_STORE, $storeId );
			if (is_null($customerGroups)){
				continue;
			}
			$aCustomerGroups = str_getcsv($customerGroups,',');

			if (strlen($customerGroups) > 0 && in_array('-1',$aCustomerGroups)){
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * Retrieve information from payment configuration
	 *
	 * @param string $field
	 * @param int|null $storeId
	 *
	 * @return mixed
	 */
	public function getValue($field, $storeId = null)
	{
		if ($this->methodCode === null || $this->pathPattern === null) {
			return null;
		}
		$value = $this->scopeConfig->getValue( sprintf($this->pathPattern, $this->methodCode, $field), ScopeInterface::SCOPE_STORE, $storeId );
		return (is_null($value) ? $this->getGlobal($field, $storeId) : $value);
	}

	/**
	 * @return void
	 */
	public function setSerializer(){
		/**
		 *
		 * @var \Magento\Framework\Serialize\SerializerInterface
		 */
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
		$this->serializer = $serializer;
	}
}
