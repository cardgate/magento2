<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;

/**
 * Client redirect after payment action
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Redirect extends \Magento\Framework\App\Action\Action {

	/**
	 *
	 * @var Session
	 */
	protected $_checkoutSession;

	public function __construct ( \Magento\Framework\App\Action\Context $context, Session $checkoutSession ) {
		$this->_checkoutSession = $checkoutSession;

		parent::__construct( $context );
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Framework\App\ActionInterface::execute()
	 */
	public function execute () {
		$orderid = $this->getRequest()->getParam( 'reference' );
		$status = $this->getRequest()->getParam( 'status' );
		$transactionId = $this->getRequest()->getParam( 'status' );

		$resultRedirect = $this->resultRedirectFactory->create();

		if ( empty( $orderid ) || empty( $transactionId ) ) {
			$this->_checkoutSession->restoreQuote();
			$this->messageManager->addNotice( __( 'Wrong parameters supplied' ) );
			$resultRedirect->setPath( 'checkout/cart' );
			return $resultRedirect;
		}

		/**
		 *
		 * @var Magento\Sales\Model\Order $order
		 */
		$order = $this->_objectManager->create( 'Magento\Sales\Model\Order' )->loadByIncrementId( $orderid );

		if ( $order::STATE_NEW == $order->getState() ) {
			try {
				$data = $order->getPayment()
					->getMethodInstance()
					->refreshTransactionStatus( $transactionId );
				$order->getPayment()
					->getMethodInstance()
					->processTransactionStatus( $order, $data );
			} catch ( \Exception $e ) {
				// ignore
			}
			if ( isset( $data['status'] ) ) {
				$status = $data['status'];
			}
		}

		if ( $status == 'success' || $status == 'pending' ) {
			$this->_checkoutSession->start();
			$resultRedirect->setPath( 'checkout/onepage/success' );
		} else {
			$this->_checkoutSession->restoreQuote();
			$this->messageManager->addNotice( __( 'Payment not completed' ) );
			$resultRedirect->setPath( 'checkout/cart' );
		}
		return $resultRedirect;
	}
}