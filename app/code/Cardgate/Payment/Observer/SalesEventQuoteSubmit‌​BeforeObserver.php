<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
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
class SalesEventQuoteSubmit‌​BeforeObserver extends AbstractDataAssignObserver {

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