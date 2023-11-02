<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Ui;

use Magento\Checkout\Model;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Cardgate\Payment\Model as CardgateModel;
use Cardgate\Payment\Model\Config\Master as MasterConfig;
use Cardgate\Payment\Model\Config;
use Magento\Framework\App\ObjectManager;

/**
 * UI Config provider
 *
 * @author DBS B.V.
 *
 */
class ConfigProvider implements Model\ConfigProviderInterface
{

    /**
     *
     * @var \Magento\Framework\App\Cache\Type\Collection
     */
    private $cache;

    /**
     *
     * @var Config
     */
    protected $config;

    /**
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     *
     * @var MasterConfig
     */
    private $masterConfig;

    /**
     *
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param MasterConfig $masterConfig
     * @param Config $config
     * @param \Magento\Framework\App\Cache\Type\Collection $cache
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        MasterConfig $masterConfig,
        Config $config,
        \Magento\Framework\App\Cache\Type\Collection $cache
    ) {
        $this->escaper = $escaper;
        $this->config = $config;
        $this->masterConfig = $masterConfig;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        /**
         *
         * @var \Magento\Checkout\Model\Session $session
         */
        $session = ObjectManager::getInstance()->get(Model\Session::class);

        $config = [];
        $config['payment'] = [];
        $config['payment']['instructions'] = [];
        // iDeal issuers are globally assigned to the UI config
        $config['payment']['cardgate_ideal_issuers'] = $this->getIDealIssuers();

        foreach ($this->masterConfig->getPaymentMethods() as $method) {
            $methodClass = $this->masterConfig->getPMClassByCode($method);
            /**
             *
             * @var \Cardgate\Payment\Model\Total\FeeData $fee
             */
            $fee = $this->masterConfig->getPMInstanceByCode($method)->getFeeForQuote($session->getQuote());
            $config['payment'][$method] = [
                'renderer' => $methodClass::$renderer,
                'cardgatefee' => $fee->getAmount(),
                'cardgatefeetax' => $fee->getTaxAmount()
            ];
            $config['payment']['instructions'][$method] =
                $this->masterConfig->getPMInstanceByCode($method)->getInstructions();
        }
        return $config;
    }

    /**
     * Get list of iDeal issuers.
     *
     * Read from cache or fetch from CardGate if not cached.
     *
     * @return string|boolean|stdClass[id,name,list]
     */
    public function getIDealIssuers()
    {
        try {
            $gatewayClient = ObjectManager::getInstance()->get(CardgateModel\GatewayClient::class);
            $testmode = boolval($gatewayClient->getTestmode());
            $cacheID = "cgIDealIssuers" . ( $testmode ? 'test' : 'live' );
            if ($this->cache->test($cacheID) !== false) {
                try {
                    $issuers = $this->config->_serializer->unserialize($this->cache->load($cacheID));
                    if (count($issuers) > 0) {
                        return $issuers;
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            }
            $issuers = $gatewayClient->methods()->get(\cardgate\api\Method::IDEAL)->getIssuers();
            $this->cache->save($this->config->_serializer->serialize($issuers), $cacheID, [], 7200);
        } catch (\Exception $e) {
            // YYY: Log error here
            $issuers = [];
        }
        return $issuers;
    }
}
