<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Observer;

use Cardgate\Payment\Model\Total\Fee;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Framework\Event\ObserverInterface;

/**
 * Resets quote totals
 *
 *
 *
 */
class SalesEventQuoteResetTotalsObserver implements ObserverInterface
{

    /**
     * @inheritdoc
     *
     * @see \Magento\Framework\Event\ObserverInterface::execute()
     */
    public function execute(EventObserver $observer)
    {
        /**
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $observer->getEvent()->getQuote();
        $quote->setCardgatefeeAmount(0);
        $quote->setBaseCardgatefeeAmount(0);
        $quote->setCardgatefeeTaxAmount(0);
        $quote->setBaseCardgatefeeTaxAmount(0);
        $quote->setCardgatefeeInclTax(0);
        $quote->setBaseCardgatefeeInclTax(0);

        /**
         *
         * @var \Magento\Quote\Model\Quote\Address $address
         */
        foreach ($quote->getAllAddresses() as $address) {
            $associatedTaxables = $address->getAssociatedTaxables();
            if (! $associatedTaxables) {
                continue;
            }
            $newAssociatedTaxables = [];
            foreach ($associatedTaxables as $extraTaxable) {
                if ($extraTaxable[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE] != Fee::TYPE_FEE &&
                    $extraTaxable[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE] != Fee::CODE_FEE) {
                    $newAssociatedTaxables[] = $extraTaxable;
                }
            }
            $address->setAssociatedTaxables($newAssociatedTaxables);
        }

        $payment = $quote->getPayment();
        $payment->setCardgatefeeAmount(0);
        $payment->setBaseCardgatefeeAmount(0);
        $payment->setCardgatefeeTaxAmount(0);
        $payment->setBaseCardgatefeeTaxAmount(0);
        $payment->setCardgatefeeInclTax(0);
        $payment->setBaseCardgatefeeInclTax(0);

        return $this;
    }
}
