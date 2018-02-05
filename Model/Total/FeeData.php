<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Total;

/**
 * FeeData structure object.
 *
 * @author DBS B.V.
 * @package Magento2
 */
class FeeData {

	/**
	 *
	 * @var float
	 */
	protected $amount;

	/**
	 *
	 * @var float
	 */
	protected $tax_amount;

	/**
	 *
	 * @var int
	 */
	protected $tax_class;

	/**
	 *
	 * @var boolean
	 */
	protected $fee_includes_tax;

	/**
	 *
	 * @param float $amount
	 * @param float $tax_amount
	 * @param int $tax_class
	 * @param boolean $fee_includes_tax
	 */
	function __construct ( $amount = 0, $tax_amount = 0, $tax_class = null, $fee_includes_tax = true ) {
		$this->amount = $amount;
		$this->tax_amount = $tax_amount;
		$this->tax_class = $tax_class;
		$this->fee_includes_tax = $fee_includes_tax;
	}

	function getDisplayAmount() {
		if ( $this->getFeeIncludesTax() ) {
			return $this->getTotal();
		} else {
			return $this->getAmount();
		}
	}

	/**
	 * Get fee amount including tax
	 *
	 * @return float
	 */
	function getTotal () {
		return $this->amount + $this->tax_amount;
	}

	/**
	 * Get fee amount
	 *
	 * @return float
	 */
	function getAmount () {
		return $this->amount;
	}

	/**
	 * Get tax amount
	 *
	 * @return float
	 */
	function getTaxAmount () {
		return $this->tax_amount;
	}

	/**
	 * Get Tax class
	 *
	 * @return int
	 */
	function getTaxClass () {
		return $this->tax_class;
	}

	/**
	 * Check if fee should include tax when displayed
	 *
	 * @return boolean
	 */
	function getFeeIncludesTax () {
		return $this->fee_includes_tax;
	}

}
