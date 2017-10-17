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
	 * CUROPayments order status codes
	 *
	 * @var array
	 */
	public static $ORDER_STATUS_CODES = [
		'0' => 'Transaction in progress',

		'100' => 'Authorization successful',
		'150' => '3D secure status YES',
		'152' => '3D secure status NO',
		'154' => '3D secure status UNKNOWN',
		'156' => '3D secure status ERROR',

		'200' => 'Transaction successful',
		'210' => 'Recurring transaction successful',

		'300' => 'Transaction failed',
		'301' => 'Transaction failed due to anti fraud system',
		'302' => 'Transaction rejected',
		'308' => 'Transaction was expired',
		'309' => 'Transaction was cancelled',
		'310' => 'Recurring transaction failed',
		'330' => 'Authorization failed',
		'350' => 'Transaction failed, time out for 3D secure authentication',
		'351' => 'Transaction failed, non-3DS transactions are not allowed',
		'352' => 'Transaction failed 3DS verification',
		'370' => 'Wait time expired',

		'400' => 'Refund to consumer',
		'404' => 'Reversal by system (transaction was never received)',
		'410' => 'Chargeback by consumer',
		'420' => 'Chargeback (2nd attempt)',
		'450' => 'Authorization cancelled',

		'601' => 'Fraud notification received from bank',
		'604' => 'Retrieval notification received from bank',

		'700' => 'Transaction is waiting for user action',
		'701' => 'Waiting for confirmation',
		'710' => 'Waiting for confirmation recurring'
	];

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
		$this->_code = substr( \get_called_class(), strrpos( \get_called_class(), '\\' ) + 1 );

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
			$info->setAdditionalInformation( $key, $value );
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

	public function refreshTransactionStatus ( $transactionId ) {
		/**
		 *
		 * @var \Cardgate\Payment\Model\GatewayClient $gatewayClient
		 */
		$gatewayClient = ObjectManager::getInstance()->get( "Cardgate\\Payment\\Model\\GatewayClient" );
		$gatewayResult = $gatewayClient->postRequest( 'transaction/' . $transactionId );
		if ( is_object( $gatewayResult ) && isset( $gatewayResult->transaction ) ) {
			return [
				'transaction' => $gatewayResult->transaction->id,
				'reference' => $gatewayResult->transaction->reference,
				'testmode' => $gatewayClient->getTestmode(),
				'currency' => $gatewayResult->transaction->currency_id,
				'status' => $gatewayResult->transaction->status,
				'amount' => $gatewayResult->transaction->amount,
				'site' => $gatewayResult->transaction->site_id,
				'code' => $gatewayResult->transaction->code,
				'ip' => $gatewayResult->transaction->ip,
				'pt' => $gatewayResult->transaction->payment_type
			];
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param Order $order
	 * @param array $requestParams
	 */
	public function processTransactionStatus ( Order $order, $requestParams ) {
		$transactionId = $requestParams['transaction'];
		$reference = $requestParams['reference'];
		$testmode = $requestParams['testmode'];
		$currency = $requestParams['currency'];
		$status = $requestParams['status'];
		$amount = $requestParams['amount'];
		$siteId = $requestParams['site'];
		$code = $requestParams['code'];
		// $hash = $requestParams['hash']; // No hash here!
		$ip = $requestParams['ip'];
		$pt = $requestParams['pt'];

		/**
		 *
		 * @var \Magento\Sales\Model\Order\Payment $payment
		 */
		$payment = $order->getPayment();
		if ( empty( $payment ) ) {
			// Uh? Create payment here?
		}

		$order->addStatusHistoryComment(
				__( "Update for transaction %1. Received status code %2.", $transactionId,
						$code . ( isset( self::$ORDER_STATUS_CODES[$code] ) ? ": " . self::$ORDER_STATUS_CODES[$code] : '' ) ) )->save();

		$triggerError = false;
		$updateCardgateData = ! ( $payment->getCardgateStatus() >= 200 && $payment->getCardgateStatus() < 300 );

		if ( $code < 100 ) {

			// 0xx pending
			if ( $order->getState() != Order::STATE_NEW ) {
				$order->addStatusHistoryComment( __( "Transaction already processed." ) )->save();
			}
		} elseif ( $code < 200 ) {

			// 1xx auth phase
			if ( $order->getState() != Order::STATE_NEW ) {
				$order->addStatusHistoryComment( __( "Transaction already processed." ) )->save();
			}
		} elseif ( $code < 300 ) {

			// 2xx success
			$doCapture = true;

			// uncancel if needed
			if ( $order->isCanceled() ) {
				/**
				 *
				 * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistry
				 */
				$stockRegistry = ObjectManager::getInstance()->get( "Magento\\CatalogInventory\\Model\\Spi\\StockRegistryProviderInterface" );
				foreach ( $order->getItems() as $item ) {
					/**
					 *
					 * @var \Magento\CatalogInventory\Api\Data\StockItemInterface $product
					 */
					$stockitem = $stockRegistry->getStockItem( $item->getProductId(), $order->getStore()
						->getWebsiteId() );
					$stockitem->setQty( $stockitem->getQty() - $item->getQtyCanceled() );
					$stockitem->save();
					$item->setQtyCanceled( 0 );
					$item->setTaxCanceled( 0 );
					$item->setDiscountTaxCompensationCanceled( 0 );
					$item->save();
				}
				$order->addStatusHistoryComment( __( "Transaction rebooked. Product stock reclaimed from inventory." ) );
			}

			// Test if transaction has been processed already
			/**
			 *
			 * @var Transaction $currentTransaction
			 */
			$currentTransaction = $this->transactionRepository->getByTransactionId( $transactionId, $payment->getId(), $order->getId() );
			if ( ! empty( $currentTransaction ) ) {
				if ( $currentTransaction->getTxnType() == Transaction::TYPE_CAPTURE ) {
					$order->addStatusHistoryComment( __( "Transaction already processed." ) );
					$doCapture = false;
					$updateCardgateData = false;
					$triggerError = __( "Transaction already processed." );
				}
			}

			// Test if payment has been processed already
			if ( $doCapture && $payment->getCardgateStatus() >= 200 && $payment->getCardgateStatus() < 300 ) {
				$order->addStatusHistoryComment( __( "Payment already processed in another transaction." ) );
				$doCapture = false;
				$updateCardgateData = false;
				$triggerError = __( "Payment already processed in another transaction." );
			}

			// capture
			if ( $doCapture ) {
				$payment->setTransactionId( $transactionId );
				$payment->setCurrencyCode( $currency );
				$payment->registerCaptureNotification( $amount / 100 );

				if ( $this->cardgateConfig->hasPMId( $pt ) ) {
					$payment->setMethod( $this->cardgateConfig->getPMCodeById( $pt ) );
				}

				// do things
				if ( ! $order->getEmailSent() ) {
					$this->orderSender->send( $order );
				}
				$invoice = $payment->getCreatedInvoice();
				if ( ! empty( $invoice ) ) {
					$this->invoiceSender->send( $invoice );
				} else {
					$order->addStatusHistoryComment( __( "Failed to create invoice." ) );
					$triggerError = __( "Failed to create invoice." );
				}
			}

			$order->save();
		} elseif ( $code < 400 ) {

			// 3xx error
			try {
				$order->registerCancellation( __( "Transaction canceled." ), false );
			} catch ( \Exception $e ) {
				$order->addStatusHistoryComment( __( "Failed to cancel order. Order state was : %1.", $order->getState() . '/' . $order->getStatus() ) );
				$triggerError = __( "Failed to cancel order." );
			}
		} elseif ( $code < 500 ) {

			// 4xx refund
			$order->registerCancellation( __( "Transaction refund received. Amount %1.", $currency . ' ' . round( $amount / 100, 2 ) ) );
		} elseif ( $code >= 600 && $code < 700 ) {

			// 6xx notification from bank
		} elseif ( $code < 800 ) {

			// 7xx waiting for confirmation
		}

		if ( $updateCardgateData ) {
			$payment->setCardgateStatus( $code );
			$payment->setCardgateTransaction( $transactionId );
		}

		$order->save();

		if ( false !== $triggerError ) {
			throw new \Exception( $triggerError );
		}
	}

	public function refund( InfoInterface $payment, $amount ) {
		$order = $payment->getOrder();
		$data = [
			'transaction_id' => $payment->getCardgateTransaction(),
			'amount'         => $amount * 100
		];
		$gatewayClient = ObjectManager::getInstance()->get( "Cardgate\\Payment\\Model\\GatewayClient" );
		$gatewayResult = $gatewayClient->postRequest( 'refund/', $data );

		if ( ! is_object( $gatewayResult ) ) {
			$order->addStatusHistoryComment( __( 'Error occurred while communicating with the payment service provider' ) );
			throw new \Exception( __( 'Error occurred while communicating with the payment service provider' ) );
		} elseif (
			! isset( $gatewayResult->success ) ||
			$gatewayResult->success != true
		) {
			$sDetails = "Unknown";
			if ( ! empty( $gatewayResult->message ) ) {
				$sDetails = $gatewayResult->message;
			}
			$order->addStatusHistoryComment( __( 'Error occurred while registering the refund (%1)', $sDetails ) );
			throw new \Exception( __( 'Error occurred while registering the refund (%1)', $sDetails ) );
		}

		return $this;
	}

}
