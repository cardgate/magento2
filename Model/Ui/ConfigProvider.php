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

        // Get the Cardgate iDEAL config
        $idealconfig = $this->config->getPayment('ideal');

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
}
