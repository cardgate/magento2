<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Helper;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;

/**
 * Taxdata-helper plugin to add CardGate Fee tax to invoices
 *
 * @author DBS B.V.
 *
 */
class TaxData extends \Magento\Tax\Helper\Data
{

    /**
     * Add CardGate fee when calculating taxes for invoices
     *
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Model\Order\Invoice|\Magento\Sales\Model\Order\Creditmemo $source
     * @return array
     */
    public function aroundGetCalculatedTaxes(\Magento\Tax\Helper\Data $taxData, \Closure $proceed, $source)
    {
        $taxClassAmount = [];
        if (empty($source)) {
            return $taxClassAmount;
        }
        $current = $source;
        // YYY: Creditmemo is not finished yet
        if ($source instanceof Invoice || $source instanceof Creditmemo) {
            $source = $current->getOrder();
        }
        if ($current == $source) {
            $taxClassAmount = $this->calculateTaxForOrder($current);
        } else {
            $taxClassAmount = $this->calculateTaxForItems($source, $current);

            // Apply any taxes for cardgatefee
            $cardgatefeeTaxAmount = $source->getCardgatefeeTaxAmount();
            $originalCardgatefeeTaxAmount = $current->getCardgatefeeTaxAmount();
            if ($cardgatefeeTaxAmount &&
                $originalCardgatefeeTaxAmount &&
                $cardgatefeeTaxAmount != 0 &&
                floatval($originalCardgatefeeTaxAmount)
            ) {
                $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($source->getId());

                // An invoice or credit memo can have a different qty than its
                // order
                $cardgatefeeRatio = $cardgatefeeTaxAmount / $originalCardgatefeeTaxAmount;
                $itemTaxDetails = $orderTaxDetails->getItems();
                foreach ($itemTaxDetails as $itemTaxDetail) {

                    // Aggregate taxable items associated with shipping
                    if ($itemTaxDetail->getType() == \Cardgate\Payment\Model\Total\Fee::TYPE_FEE) {
                        $taxClassAmount = $this->__aggregateTaxes($taxClassAmount, $itemTaxDetail, $cardgatefeeRatio);
                    }
                }
            }

        }

        foreach ($taxClassAmount as $key => $tax) {
            $taxClassAmount[$key]['tax_amount'] = $this->priceCurrency->round($tax['tax_amount']);
            $taxClassAmount[$key]['base_tax_amount'] = $this->priceCurrency->round($tax['base_tax_amount']);
        }

        return array_values($taxClassAmount);
    }

    /**
     * Copied from \Magento\Tax\Helper\Data because it's private there...
     *
     * Accumulates the pre-calculated taxes for each tax class
     * This method accepts and returns the 'taxClassAmount' array with format:
     * array(
     * $index => array(
     * 'tax_amount' => $taxAmount,
     * 'base_tax_amount' => $baseTaxAmount,
     * 'title' => $title,
     * 'percent' => $percent
     * )
     * )
     *
     * @param array $taxClassAmount
     * @param OrderTaxDetailsItemInterface $itemTaxDetail
     * @param float $ratio
     * @return array
     */
    private function __aggregateTaxes($taxClassAmount, OrderTaxDetailsItemInterface $itemTaxDetail, $ratio)
    {
        $itemAppliedTaxes = $itemTaxDetail->getAppliedTaxes();
        foreach ($itemAppliedTaxes as $itemAppliedTax) {
            $taxAmount = $itemAppliedTax->getAmount() * $ratio;
            $baseTaxAmount = $itemAppliedTax->getBaseAmount() * $ratio;

            if (0 == $taxAmount && 0 == $baseTaxAmount) {
                continue;
            }
            $taxCode = $itemAppliedTax->getCode();

            if (! isset($taxClassAmount[$taxCode])) {
                $taxClassAmount[$taxCode]['title'] = $itemAppliedTax->getTitle();
                $taxClassAmount[$taxCode]['percent'] = $itemAppliedTax->getPercent();
                $taxClassAmount[$taxCode]['tax_amount'] = $taxAmount;
                $taxClassAmount[$taxCode]['base_tax_amount'] = $baseTaxAmount;
            } else {
                $taxClassAmount[$taxCode]['tax_amount'] += $taxAmount;
                $taxClassAmount[$taxCode]['base_tax_amount'] += $baseTaxAmount;
            }
        }
        return $taxClassAmount;
    }
}
