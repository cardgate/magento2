<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Total\Invoice;

/**
 * Inject Fee into invoice (for tax injection see Helper/TaxData)
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Fee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal {

	/**
	 * Constructor
	 * By default is looking for first argument as array and assigns it as
	 * object
	 * attributes This behavior may change in child classes
	 *
	 * @param array $data
	 */
	public function __construct ( array $data = [] ) {
		parent::__construct( $data );
	}

	/**
	 * Collect Weee amounts for the invoice
	 *
	 * @param \Magento\Sales\Model\Order\Invoice $invoice
	 * @return $this
	 */
	public function collect ( \Magento\Sales\Model\Order\Invoice $invoice ) {
		$order = $invoice->getOrder();
		if ( $order->canInvoice() ) {
			$invoice->setTaxAmount( $invoice->getTaxAmount() + $order->getCardgatefeeTaxAmount() );
			$invoice->setBaseTaxAmount( $invoice->getBaseTaxAmount() + $order->getBaseCardgatefeeTaxAmount() );
			$invoice->setGrandTotal( $invoice->getGrandTotal() + $order->getCardgatefeeInclTax() );
			$invoice->setBaseGrandTotal( $invoice->getBaseGrandTotal() + $order->getBaseCardgatefeeInclTax() );
		}
		return $this;
	}
}
