<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model;

use Cardgate\Payment\Model\Config\Master;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\App\ObjectManager;

/**
 * Base Payment class from which all paymentmethods extend
 * YYY: This class should not be extended
 * \Magento\Payment\Model\Method\AbstractMethod
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class PaymentMethods extends \Magento\Payment\Model\Method\AbstractMethod {

	const PAYMENT_METHOD_CODE = 'cardgate_unknown';

	const ORDER_STATUS_AUTHORIZED = 'cardgate_authorized';

	const ORDER_STATUS_WAITCONF = 'cardgate_waitconf';

	const ORDER_STATUS_REFUND = 'cardgate_refund';

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
	protected $_code = self::PAYMENT_METHOD_CODE;

	/**
	 *
	 * @var string
	 */
	protected $_formBlockType = 'Cardgate\Payment\Block\Form\DefaultForm';

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
	 * @param Logger $logger
	 * @param \Cardgate\Payment\Model\Config\Master $master
	 * @param \Cardgate\Payment\Model\Config $config
	 * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
	 * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
	 * @param \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 *        	@SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */
	public function __construct (
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
			\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
			\Magento\Payment\Helper\Data $paymentData,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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

		// compose payment_code
		// NOTE as of v2.2.1 of Magento the class name includes Interceptor which needs to be stripped off.
		$sClass = get_called_class();
		if ( 'Interceptor' == substr( $sClass, -11 ) ) {
			$sClass = substr( $sClass, 0, -12 );
		}
		$this->_code = substr( $sClass, strrpos( $sClass, '\\' ) + 1 );

		// YYY: .. nah ..
		if ( $this->_code == 'PaymentMethods' ) {
			// .. naaaah ..
		} else {
			$this->_code = 'cardgate_' . $this->_code;
		}

		parent::__construct( $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource,
				$resourceCollection, $data );

		$this->orderSender = $orderSender;
		$this->invoiceSender = $invoiceSender;
		$this->transactionRepository = $transactionRepository;
		$this->cardgateConfig = $master;
		$this->config = $config;
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
				$debug[] = $address->getBaseGrandTotal();
			}
		}
		$debug[] = 'total: ' . $calculatedTotal;

		$feeFixed = floatval( $this->config->getField( $this->_code, 'paymentfee_fixed' ) );
		$feePercentage = floatval( $this->config->getField( $this->_code, 'paymentfee_percentage' ) );
		$fee = 0;
		if ( $feePercentage > 0 ) {
			$fee = $calculatedTotal * ( $feePercentage / 100 );
		}
		$fee = round( $fee + $feeFixed, 4 );

		$taxClassId = $this->config->getGlobal( 'paymentfee_tax_class' );
		/**
		 *
		 * @var \Magento\Catalog\Helper\Data $catalogHelper
		 */
		$catalogHelper = ObjectManager::getInstance()->get( 'Magento\\Catalog\\Helper\\Data' );

		$pseudoProduct = new \Magento\Framework\DataObject();
		$pseudoProduct->setTaxClassId( $taxClassId );

		$priceExcl = $catalogHelper->getTaxPrice( $pseudoProduct, $fee, false, $quote->getShippingAddress(), $quote->getBillingAddress(),
				$quote->getCustomerTaxClassId(), $quote->getStore(), $this->config->getGlobal( 'paymentfee_includes_tax' ) );

		$priceIncl = $catalogHelper->getTaxPrice( $pseudoProduct, $fee, true, $quote->getShippingAddress(), $quote->getBillingAddress(),
				$quote->getCustomerTaxClassId(), $quote->getStore(), $this->config->getGlobal( 'paymentfee_includes_tax' ) );

		return ObjectManager::getInstance()->create( 'Cardgate\\Payment\\Model\\Total\\FeeData',
				[
					'amount' => $priceExcl,
					'tax_amount' => ( $priceIncl - $priceExcl ),
					'tax_class' => $taxClassId,
					'fee_includes_tax' => $this->config->getGlobal( 'paymentfee_includes_tax' )
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

}
