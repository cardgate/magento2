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
 *
 */
class FeeData
{

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
     * @var float
     */
    protected $currency_converter;

    /**\
     * Calculate Fee and Tax amounts
     *
     * @param float|int $amount
     * @param float|int $tax_amount
     * @param int $tax_class
     * @param boolean $fee_includes_tax
     * @param float|int $currency_converter
     */
    public function __construct(
        $amount = 0,
        $tax_amount = 0,
        $tax_class = null,
        $fee_includes_tax = true,
        $currency_converter = 1
    ) {
        $this->amount = $amount;
        $this->tax_amount = $tax_amount;
        $this->tax_class = $tax_class;
        $this->fee_includes_tax =  $fee_includes_tax;
        $this->currency_converter = $currency_converter;
    }

    /**
     * Get Display amount
     *
     * @return float|int
     */
    public function getDisplayAmount()
    {
            return $this->getAmount();
    }

    /**
     * Get base display amount
     *
     * @return float|int
     */
    public function getBaseDisplayAmount()
    {
            return $this->getBaseAmount();
    }

    /**
     * Get fee amount including tax
     *
     * @return float
     */
    public function getTotal()
    {
        return ($this->amount + $this->tax_amount)* $this->currency_converter;
    }

    /**
     * Get base fee amount including tax
     *
     * @return float
     */
    public function getBaseTotal()
    {
        return ($this->amount + $this->tax_amount) ;
    }

    /**
     * Get fee amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount  * $this->currency_converter;
    }

    /**
     * Get base fee amount
     *
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->amount;
    }

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->tax_amount  * $this->currency_converter;
    }

    /**
     * Get base tax amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->tax_amount;
    }

    /**
     * Get Tax class
     *
     * @return int
     */
    public function getTaxClass()
    {
        return $this->tax_class;
    }

    /**
     * Check if fee should include tax when displayed
     *
     * @return boolean
     */
    public function getFeeIncludesTax()
    {
        return $this->fee_includes_tax;
    }
}
