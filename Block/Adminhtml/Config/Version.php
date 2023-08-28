<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config as CardgateConfig;
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
     * CardgateConfig
     *
     * @var CardgateConfig
     */
    private $cardgateConfig;

    /**
     *
     * @param \Magento\Backend\Block\Context $context
     * @param array $data,
     * @paran CardgateConfig $cardgateConfig
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        CardgateConfig $cardgateConfig
    ) {
        $this->cardgateConfig = $cardgateConfig;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
     */
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
        $testmode = $this->cardgateConfig->getGlobal('testmode');

        return
            "Plugin <strong>v" . $pluginVersion . '</strong><br/>'
            . 'Client Library <strong>v' . \cardgate\api\Client::CLIENT_VERSION . '</strong>'
            . ( $testmode ? '<br/><span style="color:red">'. __("TESTMODE ENABLED").'</span>' : '' )
        ;
    }
}
