<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;

/**
 * Inject CardGate fee into Creditmemo
 *
 *
 */
class Fee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{

    /**
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Collect CardGate fee for the credit memo
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $store = $creditmemo->getStore();
        $this->_order = $creditmemo->getOrder();
        $totalFeeAmount = $this->_order->getCardgatefeeAmount();
        $baseTotalFeeAmount = $this->_order->getBaseCardgatefeeAmount();
        $totalTaxAmount = $this->_order->getCardgatefeeTaxAmount();
        $baseTotalTaxAmount = $this->_order->getBaseCardgatefeeTaxAmount();
        $totalFeeAmountInclTax = $this->_order->getCardgatefeeInclTax();
        $baseTotalFeeAmountInclTax = $this->_order->getBaseCardgatefeeAmount();

        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTaxAmount);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTaxAmount);

        $creditmemo->setSubtotalInclTax($creditmemo->getSubtotalInclTax() + $totalFeeAmountInclTax);
        $creditmemo->setBaseSubtotalInclTax($creditmemo->getBaseSubtotalInclTax() + $baseTotalFeeAmountInclTax);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalFeeAmount + $totalTaxAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalFeeAmount + $baseTotalTaxAmount);

        return $this;
    }
}
