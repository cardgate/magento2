<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Config;

use Cardgate\Payment\Model\Config as CardgateConfig;
use Magento\Config\Block\System\Config\Form\Fieldset;

/**
 * Render for "global" configuration group element
 *
 * @author DBS B.V.
 *
 */
class GroupInfo extends Fieldset
{

    /**
     *
     * @var CardgateConfig
     */
    private $cardgateConfig;

    /**
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param CardgateConfig $cardgateConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        CardgateConfig $cardgateConfig,
        array $data = []
    ) {
        $this->cardgateConfig = $cardgateConfig;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Return header title part of html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $legend = $element->getLegend();
        if (! $this->testConfigurationHealth()) {
            $legend .= " - <span style='color:red'>".__("Attention required")."</span>";
        }
        $element->setLegend($legend);

        return parent::_getHeaderTitleHtml($element);
    }

    /**
     * Tests configuration health
     *
     * @return boolean
     */
    private function testConfigurationHealth()
    {
        $extra = $this->_authSession->getUser()->getExtra();
        if (empty($this->cardgateConfig->getGlobal('active_pm'))) {
            $extra['configState']['cardgate_info'] = true;
            $extra['configState']['cardgate_info_pms'] = true;
            $this->_authSession->getUser()->setExtra($extra);
            return false;
        }
        return true;
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $element->getGroup();

        if (empty($groupConfig['help_url']) || ! $element->getComment()) {
            return parent::_getHeaderCommentHtml($element);
        }

        $html = '<div class="comment">' . $element->getComment() .
                ' <a target="_blank" href="' . $groupConfig['help_url'] . '">' . __('Help') . '</a></div>';

        return $html;
    }

    /**
     * Return collapse state
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isCollapseState($element)
    {
        $extra = $this->_authSession->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        $groupConfig = $element->getGroup();
        if (! empty($groupConfig['expanded'])) {
            return true;
        }

        return false;
    }
}
