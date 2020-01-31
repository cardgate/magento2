<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use \Magento\Framework\App\ObjectManager;

/**
 * Callback handler action.
 */
class UpdatePM extends \Magento\Framework\App\Action\Action {

	public function execute() {
		$sPaymentMethod = htmlspecialchars($this->getRequest()->getParam( 'pm' ));
		$oSession = ObjectManager::getInstance()->get( \Magento\Checkout\Model\Session::class );
		$oQuote = $oSession->getQuote();
		$oQuote->getPayment()->setMethod( $sPaymentMethod );
		$oQuote->collectTotals()->save();
		$oQuote->save();
	}
}
