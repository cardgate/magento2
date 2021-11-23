<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Render for "show version" element
 *
 * @author DBS B.V.
 *
 */
class Version extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Config
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Config\Model\Config $backendConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Config $backendConfig,
        array $data = []
    ) {
        $this->config = $backendConfig;
        parent::__construct($context, $data);
    }

    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /**
         *
         * @var ModuleListInterface $modList
         */
        try {
            $modList = ObjectManager::getInstance()->get(ModuleListInterface::class);
            $pluginVersion = $modList->getOne('Cardgate_Payment')['setup_version'];
        } catch (\Exception $e) {
            $pluginVersion = __("UNKOWN");
        }

        $testmode = $this->config->getGlobal('testmode');
        $serverIsSet = isset($_SERVER['CG_API_URL']);
        $apiUrl = ($serverIsSet ? $_SERVER['CG_API_URL']:'');
        return
            "Plugin <strong>v" . $pluginVersion . '</strong><br/>'
            . 'Client Library <strong>v' . \cardgate\api\Client::CLIENT_VERSION . '</strong>'
            . ( $testmode ? '<br/><span style="color:red">'. __("TESTMODE ENABLED").'</span>' : '' )
            . ( $serverIsSet && $apiUrl != '' ? ' <span style="color:red">API OVERRIDE (' . $apiUrl . ')</span>' : '' )
        ;
    }
}
