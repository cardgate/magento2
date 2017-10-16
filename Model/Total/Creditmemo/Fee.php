<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;

/**
 * Inject CardGate fee into Creditmemo
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Fee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal {

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
	 * Collect CardGate fee for the credit memo
	 *
	 * @param Creditmemo $creditmemo
	 * @return $this
	 */
	public function collect ( Creditmemo $creditmemo ) {
		$store = $creditmemo->getStore();

		// YYY: Creditmemo is not finished yet
		$totalFeeAmount = $baseTotalFeeAmount = $totalTaxAmount = $baseTotalTaxAmount = $totalFeeAmountInclTax = $baseTotalFeeAmountInclTax = 0;

		$creditmemo->setSubtotal( $creditmemo->getSubtotal() + $totalFeeAmount );
		$creditmemo->setBaseSubtotal( $creditmemo->getBaseSubtotal() + $baseTotalFeeAmount );

		$creditmemo->setTaxAmount( $creditmemo->getTaxAmount() + $totalTaxAmount );
		$creditmemo->setBaseTaxAmount( $creditmemo->getBaseTaxAmount() + $baseTotalTaxAmount );

		$creditmemo->setSubtotalInclTax( $creditmemo->getSubtotalInclTax() + $totalFeeAmountInclTax );
		$creditmemo->setBaseSubtotalInclTax( $creditmemo->getBaseSubtotalInclTax() + $baseTotalFeeAmountInclTax );

		$creditmemo->setGrandTotal( $creditmemo->getGrandTotal() + $totalFeeAmount + $totalTaxAmount );
		$creditmemo->setBaseGrandTotal( $creditmemo->getBaseGrandTotal() + $baseTotalFeeAmount + $baseTotalTaxAmount );

		return $this;
	}
}
