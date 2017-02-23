<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\Total;

use \Cardgate\Payment\Model\Config\Master;
use \Magento\Framework\App\ObjectManager;
use \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

/**
 * Inject CardGate fee into totals for quote
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {

	const TYPE_FEE = 'cardgatefee';

	const CODE_FEE = 'cardgatefee';

	/**
	 * Collect grand total address amount
	 *
	 * @param \Magento\Quote\Model\Quote $quote
	 * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
	 * @param \Magento\Quote\Model\Quote\Address\Total $total
	 * @return $this
	 */
	protected $quoteValidator = null;

	/**
	 *
	 * @var Master
	 */
	protected $_cardgateConfig;

	public function __construct ( \Magento\Quote\Model\QuoteValidator $quoteValidator, Master $cardgateConfig ) {
		$this->_cardgateConfig = $cardgateConfig;
		$this->quoteValidator = $quoteValidator;
	}

	public function collect ( \Magento\Quote\Model\Quote $quote, \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total ) {
		parent::collect( $quote, $shippingAssignment, $total );

		if ( $quote->getBillingAddress()->getId() == $shippingAssignment->getShipping()->getAddress()->getId() ) {
			return $this;
		}

		/**
		 *
		 * @var \Cardgate\Payment\Model\Total\FeeData $fee
		 */
		$fee = ObjectManager::getInstance()->create( 'Cardgate\\Payment\\Model\\Total\\FeeData' );
		if ( ! empty( $quote->getPayment()->getMethod() ) && $this->_cardgateConfig->isCardgateCode( $quote->getPayment()
			->getMethod() ) ) {
			$fee = $quote->getPayment()
				->getMethodInstance()
				->getFeeForQuote( $quote, $total );

			$payment = $quote->getPayment();

			$payment->setCardgatefeeAmount( $fee->getAmount() + $payment->getCardgatefeeAmount() );
			$payment->setBaseCardgatefeeAmount( $fee->getAmount() + $payment->getBaseCardgatefeeAmount() );
			$payment->setCardgatefeeTaxAmount( $fee->getTaxAmount() + $payment->getCardgatefeeTaxAmount() );
			$payment->setBaseCardgatefeeTaxAmount( $fee->getTaxAmount() + $payment->getBaseCardgatefeeTaxAmount() );
			$payment->setCardgatefeeInclTax( $fee->getTotal() + $payment->getCardgatefeeInclTax() );
			$payment->setBaseCardgatefeeInclTax( $fee->getTotal() + $payment->getBaseCardgatefeeInclTax() );
		}

		// YYY: Todo: add base_amount
		$total->addTotalAmount( 'cardgatefee', $fee->getAmount() );
		$total->addBaseTotalAmount( 'cardgatefee', $fee->getAmount() );

		$quote->setCardgatefeeAmount( $fee->getAmount() + $quote->getCardgatefeeAmount() );
		$quote->setBaseCardgatefeeAmount( $fee->getAmount() + $quote->getBaseCardgatefeeAmount() );
		$quote->setCardgatefeeTaxAmount( $fee->getTaxAmount() + $quote->getCardgatefeeTaxAmount() );
		$quote->setBaseCardgatefeeTaxAmount( $fee->getTaxAmount() + $quote->getBaseCardgatefeeTaxAmount() );
		$quote->setCardgatefeeInclTax( $fee->getTotal() + $quote->getCardgatefeeInclTax() );
		$quote->setBaseCardgatefeeInclTax( $fee->getTotal() + $quote->getBaseCardgatefeeInclTax() );

		if ( $fee->getAmount() > 0 ) {
			$associatedTaxables = [];
			$associatedTaxables[] = [
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::TYPE_FEE,
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => self::CODE_FEE,
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $fee->getDisplayAmount(),
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $fee->getDisplayAmount(),
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $fee->getTaxClass(),
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => $fee->getFeeIncludesTax(),
				CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE => CommontaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE
			];
			$shippingAssignment->getShipping()->getAddress()->setAssociatedTaxables( $associatedTaxables );
		}

		return $this;
	}

	protected function clearValues ( \Magento\Quote\Model\Quote\Address\Total $total ) {
		$total->setTotalAmount( 'subtotal', 0 );
		$total->setBaseTotalAmount( 'subtotal', 0 );
		$total->setTotalAmount( 'tax', 0 );
		$total->setBaseTotalAmount( 'tax', 0 );
		$total->setTotalAmount( 'discount_tax_compensation', 0 );
		$total->setBaseTotalAmount( 'discount_tax_compensation', 0 );
		$total->setTotalAmount( 'shipping_discount_tax_compensation', 0 );
		$total->setBaseTotalAmount( 'shipping_discount_tax_compensation', 0 );
		$total->setSubtotalInclTax( 0 );
		$total->setBaseSubtotalInclTax( 0 );

		$total->setTotalAmount( 'cardgatefee', 0 );
		$total->setBaseTotalAmount( 'cardgatefee', 0 );
	}

	/**
	 * Assign subtotal amount and label to address object
	 *
	 * @param \Magento\Quote\Model\Quote $quote
	 * @param Address\Total $total
	 * @return array
	 */
	public function fetch ( \Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total ) {
		/**
		 *
		 * @var \Cardgate\Payment\Model\Total\FeeData $fee
		 */
		$fee = ObjectManager::getInstance()->create( 'Cardgate\\Payment\\Model\\Total\\FeeData' );
		if ( ! empty( $quote->getPayment()->getMethod() ) && $this->_cardgateConfig->isCardgateCode( $quote->getPayment()
			->getMethod() ) ) {
			$fee = $quote->getPayment()
				->getMethodInstance()
				->getFeeForQuote( $quote );
		}
		return [
			'code' => 'cardgatefee',
			'title' => __( 'Payment fee' ),
			'value' => $fee->getAmount(),
		];
	}

	/**
	 * Get Subtotal label
	 *
	 * @return \Magento\Framework\Phrase
	 */
	public function getLabel () {
		return __( 'Payment fee getlabel' );
	}
}