<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Event to copy CardGate Fee data from an order to an invoice.
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class SalesOrderInvoicePay implements ObserverInterface {

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Framework\Event\ObserverInterface::execute()
	 */
	public function execute ( EventObserver $observer ) {
		$invoice = $observer->getEvent()->getInvoice();
		$order = $invoice->getOrder();

		$invoice->setCardgatefeeAmount( $order->getCardgatefeeAmount() );
		$invoice->setBaseCardgatefeeAmount( $order->getBaseCardgatefeeAmount() );
		$invoice->setCardgatefeeTaxAmount( $order->getCardgatefeeTaxAmount() );
		$invoice->setBaseCardgatefeeTaxAmount( $order->getBaseCardgatefeeTaxAmount() );
		$invoice->setCardgatefeeInclTax( $order->getCardgatefeeInclTax() );
		$invoice->setBaseCardgatefeeInclTax( $order->getBaseCardgatefeeInclTax() );

		return $this;
	}
}