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
use Magento\Framework\Serialize\SerializerInterface;
use Cardgate\Payment\Model\Config\Master;

/**
 * CardGate Magento2 module config
 *
 * @author DBS B.V.
 *
 */
class Config implements ConfigInterface
{

    public const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     *
     * @var ConfigResource
     */
    private $_configResource;

    /**
     *
     * @var Master
     */
    private $_masterConfig;

    /**
     *
     * @var SerializerInterface
     */
    public $_serializer;

    /**
     * @var string|null
     */
    private $pathPattern;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * Active Payment methods as configured in my.cardgate.com (and fetched from
     * RESTful API)
     *
     * @var array
     */
    private static $activePMIDs = [];



    /**
     * @param MutableScopeConfigInterface $scopeConfig
     * @param ConfigResource $configResource
     * @param Master $master
     * @param SerializerInterface $serializer
     * @param $pathPattern
     */
    public function __construct(
        MutableScopeConfigInterface $scopeConfig,
        ConfigResource $configResource,
        Master $master,
        SerializerInterface $serializer,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_configResource = $configResource;
        $this->_masterConfig = $master;
        $this->_serializer = $serializer;
        $this->pathPattern = $pathPattern;
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
     * Set info CardGate configuration for given payment method and save it
     *
     * @param string $method
     * @param string $field
     * @param mixed $value
     * @param int|null $storeId
     * @return void
     */
    public function setField($method, $field, $value, $storeId = null)
    {
        $this->scopeConfig->setValue(
            'payment/' . $method . '/' . $field,
            $value,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->_configResource->saveConfig(
            'payment/' . $method . '/' . $field,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }

    /**
     * Retrieve information from Global CardGate configuration
     *
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getGlobal($field, $storeId = null)
    {
        return $this->scopeConfig->getValue('cardgate/global/' . $field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Set information info Global CardGate configuration and save configuration
     *
     * @param string $field
     * @param mixed $value
     * @param int|null $storeId
     * @return void
     */
    public function setGlobal($field, $value, $storeId = null)
    {
        $this->scopeConfig->setValue('cardgate/global/' . $field, $value, ScopeInterface::SCOPE_STORE, $storeId);
        $this->_configResource->saveConfig(
            'cardgate/global/' . $field,
            $value,
            MutableScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }

    /**
     * Get active Payment method ID's (CardGate style ID's)
     *
     * @param number $storeId
     * @return mixed
     */
    public function getActivePMIDs($storeId = 0)
    {

        if (isset(self::$activePMIDs[$storeId]) && is_array(self::$activePMIDs[$storeId])) {
            return self::$activePMIDs[$storeId];
        }
        self::$activePMIDs[$storeId] = [];
        try {

            $activePmInfo = $this->_serializer->unserialize($this->getGlobal('active_pm', $storeId));
        } catch (\Exception $e) {
            $activePmInfo = [];
        }

        foreach ($activePmInfo as $activePm) {

            self::$activePMIDs[$storeId][] = $activePm['id'];
        }
        return self::$activePMIDs[$storeId];
    }

    /**
     * Show if not logged in is used as a group
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function loggedInIsGroup($storeId = 0)
    {
        $aActivePaymentIds = $this->getActivePMIDs($storeId);
        $result = false;
        foreach ($aActivePaymentIds as $id) {
            $methodCode = 'cardgate_'.$id;
            $customerGroups = $this->scopeConfig->getValue(
                sprintf(
                    $this->pathPattern,
                    $methodCode,
                    'specific_customer_groups'
                ),
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($customerGroups === null) {
                continue;
            }
            $aCustomerGroups = str_getcsv($customerGroups, ',');

            if (strlen($customerGroups) > 0 && in_array('-1', $aCustomerGroups)) {
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
        $value = $this->scopeConfig->getValue(
            sprintf(
                $this->pathPattern,
                $this->methodCode,
                $field
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return (($value === null) ? $this->getGlobal($field, $storeId) : $value);
    }
}
