<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

/**
 * Event to copy CardGate Fee data from a quote to an order.
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class SalesEventQuoteSubmitBeforeObserver extends AbstractDataAssignObserver {

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Framework\Event\ObserverInterface::execute()
	 */
	public function execute ( \Magento\Framework\Event\Observer $observer ) {
		$quote = $observer->getEvent()->getQuote();
		$order = $observer->getEvent()->getOrder();

		$order->setCardgatefeeAmount( $quote->getCardgatefeeAmount() );
		$order->setBaseCardgatefeeAmount( $quote->getBaseCardgatefeeAmount() );
		$order->setCardgatefeeTaxAmount( $quote->getCardgatefeeTaxAmount() );
		$order->setBaseCardgatefeeTaxAmount( $quote->getBaseCardgatefeeTaxAmount() );
		$order->setCardgatefeeInclTax( $quote->getCardgatefeeInclTax() );
		$order->setBaseCardgatefeeInclTax( $quote->getBaseCardgatefeeInclTax() );

		return $this;
	}
}
