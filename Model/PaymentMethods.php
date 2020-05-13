<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model;

use Cardgate\Payment\Model\Config\Master;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\App\ObjectManager;

/**
 * Base Payment class from which all payment methods extend
 * YYY: This class should not be extended
 * \Magento\Payment\Model\Method\AbstractMethod
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class PaymentMethods extends \Magento\Payment\Model\Method\AbstractMethod {

	/**
	 * See /web/js/view/payment/method-renderer
	 *
	 * @var string
	 */
	public static $renderer = 'paymentmethods';

	/**
	 * Payment method code
	 *
	 * @var string
	 */
	protected $_code = 'cardgate_unknown';

	/**
	 *
	 * @var string
	 */
	protected $_infoBlockType = 'Cardgate\Payment\Block\Info\DefaultInfo';

	/**
	 * Availability option
	 *
	 * @var bool
	 */
	protected $_isOffline = false;

	/**
	 *
	 * @var boolean
	 */
	protected $_canReviewPayment = true;

	/**
	 * @var boolean
	 */
	protected $_canRefund = true;

	/**
	 * @var boolean
	 */
	protected $_canRefundInvoicePartial = true;

	/**
	 *
	 * @var OrderSender
	 */
	protected $orderSender;

	/**
	 *
	 * @var invoiceSender
	 */
	protected $invoiceSender;

	/**
	 *
	 * @var \Magento\Tax\Model\Calculation
	 */
	protected $taxCalculation;

	/**
	 *
	 * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository
	 */
	protected $transactionRepository;

	/**
	 *
	 * @var \Cardgate\Payment\Model\Config\Master
	 */
	protected $cardgateConfig;

	/**
	 *
	 * @var \Cardgate\Payment\Model\Config
	 */
	protected $config;

	/**
	 *
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
	 * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
	 * @param \Magento\Payment\Helper\Data $paymentData
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Tax\Model\Calculation $taxCalculation,
	 * @param Logger $logger
	 * @param \Cardgate\Payment\Model\Config\Master $master
	 * @param \Cardgate\Payment\Model\Config $config
	 * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
	 * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
	 * @param \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */
	public function __construct (
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
		\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
		\Magento\Payment\Helper\Data $paymentData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Tax\Model\Calculation $taxCalculation,
		\Magento\Payment\Model\Method\Logger $logger,
		\Cardgate\Payment\Model\Config\Master $master,
		\Cardgate\Payment\Model\Config $config,
		\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
		\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
		\Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		parent::__construct( $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data );

		$this->taxCalculation = $taxCalculation;
		$this->cardgateConfig = $master;
		$this->config = $config;
		$this->orderSender = $orderSender;
		$this->invoiceSender = $invoiceSender;
		$this->transactionRepository = $transactionRepository;

	}

	/**
	 *
	 * @param \Magento\Quote\Api\Data\CartInterface $quote
	 * @return boolean
	 */
	public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {
		$customerGroups = $this->config->getField( $this->_code, 'specific_customer_groups' );
		$aCustomerGroups = str_getcsv($customerGroups,',');
		$groupId = $quote->getCustomer()->getGroupId();

		if ($groupId > 0 && strlen( $customerGroups > 0 ) && ! in_array( $groupId, $aCustomerGroups ) ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @param \Magento\Quote\Model\Quote $quote
	 * @return \Cardgate\Payment\Model\Total\FeeData
	 */
	public function getFeeForQuote ( \Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total = null ) {
		if ( ! is_null( $total ) ) {
			$calculatedTotal = array_sum( $total->getAllBaseTotalAmounts() );
			foreach ( $total->getAllBaseTotalAmounts() as $k => $v ) {
				$debug[] = "{$k} = {$v}";
			}
		} else {
			$calculatedTotal = 0 - $quote->getPayment()->getBaseCardgatefeeInclTax();
			foreach ( $quote->getAllAddresses() as $address ) {
				$calculatedTotal += $address->getBaseGrandTotal();
				$debug[]         = $address->getBaseGrandTotal();
			}
		}
		$debug[] = 'total: ' . $calculatedTotal;

		$taxClassId = $this->config->getGlobal( 'paymentfee_tax_class' );
		$request               = new \Magento\Framework\DataObject(
			[
				'country_id'        => $quote->getBillingAddress()->getCountryId(),
				'region_id'         => $quote->getBillingAddress()->getRegionId(),
				'postcode'          => $quote->getBillingAddress()->getPostcode(),
				'customer_class_id' => $quote->getCustomerTaxClassId(),
				'product_class_id'  => $taxClassId
			] );
		$taxRate = $this->taxCalculation->getRate($request);

		$paymentFeeIncludesTax = $this->config->getGlobal( 'paymentfee_includes_tax' );
		$feeFixed      = floatval( $this->config->getField( $this->_code, 'paymentfee_fixed' ) );
		$feePercentage = floatval( $this->config->getField( $this->_code, 'paymentfee_percentage' ) );
		$fee           = round( ( $calculatedTotal * ( $feePercentage / 100 ) ) + $feeFixed, 4 );

		if ($paymentFeeIncludesTax){
			$taxAmount = $fee - round($fee/((100 + $taxRate)/100),4);
			$priceExcl = $fee - $taxAmount;
		} else {
			$priceExcl = $fee;
			$taxAmount = round($fee * (1+($taxRate/100)),4) - $fee;
		}

		$aFee = [
			'amount'             => $priceExcl,
			'tax_amount'         => $taxAmount,
			'tax_class'          => $taxClassId,
			'fee_includes_tax'   => $paymentFeeIncludesTax,
			'currency_converter' => $quote->getBaseToQuoteRate()
		] ;

		$amount = ( $paymentFeeIncludesTax == 1 ? $priceExcl:($priceExcl + $taxAmount));
		return ObjectManager::getInstance()->create( 'Cardgate\\Payment\\Model\\Total\\FeeData',
			[
				'amount'             => $amount,
				'tax_amount'         => $taxAmount,
				'tax_class'          => $taxClassId,
				'fee_includes_tax'   => $this->config->getGlobal( 'paymentfee_includes_tax' ),
				'currency_converter' => $quote->getBaseToQuoteRate()
			] );
	}


	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Payment\Model\Method\AbstractMethod::assignData()
	 */
	public function assignData ( \Magento\Framework\DataObject $data ) {
		$additional = $data->getAdditionalData();
		if ( ! is_array( $additional ) ) {
			return $this;
		}
		$info = $this->getInfoInstance();
		foreach ( $additional as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$info->setAdditionalInformation( $key, $value );
			}
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getPayableTo () {
		return $this->getConfigData( 'payable_to' );
	}

	/**
	 *
	 * @return string
	 */
	public function getMailingAddress () {
		return $this->getConfigData( 'mailing_address' );
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Payment\Model\Method\AbstractMethod::acceptPayment()
	 */
	public function acceptPayment ( InfoInterface $payment ) {
		return true;
	}

	public function refund( InfoInterface $payment, $amount ) {
		$order = $payment->getOrder();
		try {
			$gatewayClient = ObjectManager::getInstance()->get( \Cardgate\Payment\Model\GatewayClient::class );
			$transaction = $gatewayClient->transactions()->get( $payment->getCardgateTransaction() );

			if ( $transaction->canRefund() ) {
				$transaction->refund( (int)( $amount * 100 ) );
			} else {
				throw new \Exception( 'refund not allowed' );
			}
		} catch ( \Exception $e ) {
			$order->addStatusHistoryComment( __( 'Error occurred while registering the refund (%1)', $e->getMessage() ) );
			throw $e;
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getInstructions(){
		$instructions = $this->config->getField( $this->_code, 'instructions' );
		return nl2br($instructions);
	}

}