<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Config\Processor;

use Cardgate\Payment\Model\Config;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\App\ObjectManager;

class PreProcessorCardgate implements PreProcessorInterface
{

    /**
     * @inheritdoc
     */
    public function process(array $config)
    {

        $masterConfig = ObjectManager::getInstance()->create(Config\Master::class);
        foreach ($masterConfig->getPaymentMethods(true) as $paymentMethod => $paymentMethodName) {
            if (!isset($config['default']['payment'][$paymentMethod])) {
                $config['default']['payment'][$paymentMethod] = [];
            }
            if (is_array($config['default']['payment'][$paymentMethod])) {
                $config['default']['payment'][$paymentMethod] = array_merge(
                    [
                            'model' => $masterConfig->getPMClassByCode($paymentMethod),
                            'label' => $paymentMethod,
                            'group' => 'cardgate',
                            'title' => $paymentMethodName
                        ],
                    $config['default']['payment'][$paymentMethod]
                );
            }
        }
        return $config;
    }
}
