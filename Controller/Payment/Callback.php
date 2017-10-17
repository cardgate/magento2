<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Cardgate\Payment\Model\GatewayClient;
use Cardgate\Payment\Model\Config\Master;
use Magento\Framework\Controller\ResultFactory;

/**
 * Callback handler action
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Callback extends \Magento\Framework\App\Action\Action {

	/**
	 *
	 * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
	 */
	protected $orderSender;

	/**
	 *
	 * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
	 */
	protected $invoiceSender;

	/**
	 *
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 *
	 * @var GatewayClient
	 */
	private $_cardgateClient;

	/**
	 *
	 * @var Master
	 */
	private $_cardgateConfig;

	public function __construct ( \Magento\Framework\App\Action\Context $context, \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender, \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, GatewayClient $client, Master $config ) {
		parent::__construct( $context );
		$this->invoiceSender = $invoiceSender;
		$this->orderSender = $orderSender;
		$this->scopeConfig = $scopeConfig;
		$this->_cardgateConfig = $config;
		$this->_cardgateClient = $client;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Framework\App\ActionInterface::execute()
	 */
	public function execute () {
		$transactionId = $this->getRequest()->getParam( 'transaction' );
		$reference = $this->getRequest()->getParam( 'reference' );
		$testmode = $this->getRequest()->getParam( 'testmode' );
		$currency = $this->getRequest()->getParam( 'currency' );
		$status = $this->getRequest()->getParam( 'status' );
		$amount = $this->getRequest()->getParam( 'amount' );
		$siteId = $this->getRequest()->getParam( 'site' );
		$code = $this->getRequest()->getParam( 'code' );
		$hash = $this->getRequest()->getParam( 'hash' );
		$ip = $this->getRequest()->getParam( 'ip' );
		$pt = $this->getRequest()->getParam( 'pt' );

		$pmId = ( ! empty( $pt ) ? $pt : 'unknown' );

		$result = $this->resultFactory->create( ResultFactory::TYPE_RAW );

		// Hash validation
		if ( ! $this->_cardgateClient->validateHash( $hash, $testmode,
			$transactionId, $currency, $amount, $reference, $code ) ) {
			$result->setContents( 'Hash verification failure' );
			return $result;
		}

		/**
		 *
		 * @var \Magento\Sales\Model\Order $order
		 */
		$order = $this->_objectManager->create( 'Magento\Sales\Model\Order' )->loadByIncrementId( $reference );

		try {
			if ( $order->getPayment()->getCardgatePaymentmethod() != $pmId ) {
				$order->addStatusHistoryComment(
						__( "Callback received for transaction %1 with paymentmethod '%2' but paymentmethod should be '%3'. Processing anyway.", $transactionId, $pmId, $order->getPayment()
							->getCardgatePaymentmethod() ) );
				$order->getPayment()->setCardgatePaymentmethod( $pmId );
				$order->save();
			}
		} catch ( \Exception $e ) {
			$result->setContents( __("Error processing callback")." (1)\n\n" . $e->getMessage() );
			return $result;
		}

		try {
			/**
			 *
			 * @var \Cardgate\Payment\Model\PaymentMethods $paymentMethod
			 */
			$paymentMethod = $this->_cardgateConfig->getPMInstanceByCode( $this->_cardgateConfig->getPMCodeById( $pmId ), true );
			$paymentMethod->processTransactionStatus( $order, $this->getRequest()
				->getParams() );
			$result->setContents( $transactionId . '.' . $code );
			return $result;
		} catch ( \Exception $e ) {
			$result->setContents( __("Error processing callback")." (2)\n\n" . $e->getMessage() );
			return $result;
		}
	}
}