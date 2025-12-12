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
 *
 */
class Fee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect Weee amounts for the invoice
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->canInvoice()) {
            $invoice->setTaxAmount($invoice->getTaxAmount() + $order->getCardgatefeeTaxAmount());
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $order->getBaseCardgatefeeTaxAmount());
            $invoice->setGrandTotal($invoice->getGrandTotal() + $order->getCardgatefeeInclTax());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $order->getBaseCardgatefeeInclTax());
        }
        return $this;
    }
}
