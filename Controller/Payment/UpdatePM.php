<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Magento\Framework\App\ActionInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Escaper;

/**
 * Callback handler action.
 */
class UpdatePM implements ActionInterface
{

    public function execute()
    {
        $sPaymentMethod = Escaper::escapeHtml($this->getRequest()->getParam('pm'));
        $oSession = ObjectManager::getInstance()->get(\Magento\Checkout\Model\Session::class);
        $oQuote = $oSession->getQuote();
        $oQuote->getPayment()->setMethod($sPaymentMethod);
        $oQuote->collectTotals()->save();
        $oQuote->save();
    }
}
