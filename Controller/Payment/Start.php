<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Magento\Payment\Helper\Data as PaymentHelper;
use Cardgate\Payment\Model\GatewayClient;
use Cardgate\Payment\Model\Config;
use Cardgate\Payment\Model\Config\Master;
use Cardgate\Payment\Model\PaymentMethods;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Start payment action
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Start extends \Magento\Framework\App\Action\Action {

	/**
	 *
	 * @var \Magento\Customer\Model\Session
	 */
	protected $customerSession;

	/**
	 *
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $checkoutSession;

	/**
	 *
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 *
	 * @var \Magento\Framework\UrlInterface
	 */
	protected $urlBuilder;

	/**
	 *
	 * @var \Magento\Quote\Model\Quote
	 */
	protected $quote = false;

	/**
	 *
	 * @var PaymentHelper
	 */
	protected $_paymentHelper;

	/**
	 *
	 * @var GatewayClient
	 */
	private $_gatewayClient;

	/**
	 *
	 * @var \Cardgate\Payment\Model\Config
	 */
	private $_cardgateConfig;

	/**
	 *
	 * @var \Cardgate\Payment\Model\Config\Master
	 */
	private $_masterConfig;

	/**
	 *
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		PaymentHelper $paymentHelper,
		GatewayClient $gatewayClient,
		Config $cardgateConfig,
		Master $masterConfig
	) {
		// $this->_logger = $logger;
		// $this->_logger->addDebug('some text or variable');
		$this->customerSession = $customerSession;
		$this->checkoutSession = $checkoutSession;
		$this->scopeConfig = $scopeConfig;
		$this->_paymentHelper = $paymentHelper;
		$this->_gatewayClient = $gatewayClient;
		$this->_cardgateConfig = $cardgateConfig;
		$this->_masterConfig = $masterConfig;
		parent::__construct( $context );
	}

	public function execute () {

		try {
			$modList = ObjectManager::getInstance()->get( ModuleListInterface::class );
			$version = $modList->getOne( 'Cardgate_Payment' )['setup_version'];
		} catch ( \Exception $e ) {
			$version = __("UNKOWN");
		}

		$order = $this->checkoutSession->getLastRealOrder();
		$orderid = $order->getIncrementId();

		$billingAddress = $order->getBillingAddress();
		if ( !is_null($billingAddress ) ) {
			$consumer = GatewayClient::convertAddressToConsumer( $billingAddress );
		} else {
			$this->messageManager->addErrorMessage( __( 'Error occurred while registering the transaction' ) );
			$order->registerCancellation( __( 'Error occurred while registering the transaction' ) );
			$this->checkoutSession->restoreQuote();
			$this->_redirect( 'checkout/cart' );
			return;
		}

		$shippingAddress = ! is_null( $order->getShippingAddress() ) ? $order->getShippingAddress() : $order->getBillingAddress();
		if ( !is_null($billingAddress ) ) {
			$shipping = GatewayClient::convertAddressToConsumer( $shippingAddress, true );
		}

		$grandTotal = round( $order->getBaseGrandTotal() * 100 );
		/**
		 *
		 * @var float $calculatedGrandTotal
		 */
		$calculatedGrandTotal = 0.00;
		/**
		 *
		 * @var float $calculatedVatTotal
		 */
		$calculatedVatTotal = 0.00;

		$data = [
			'shop_version' => 'Magento2',
			'plugin_version' => 'Cardgate_Payment',
			'plugin_name' => $version,
			'ip' => $this->_gatewayClient->determineIp(),
			'site_id' => $this->_gatewayClient->getSiteId(),
			'country_id' => $consumer['country_id'],
			'amount' => $grandTotal,
			'reference' => $orderid,
			'description' => str_replace( '%id%', $orderid, $this->_cardgateConfig->getGlobal( 'order_description' ) ),
			'consumer' => array_merge( $consumer, $shipping ),
			'currency_id' => $order->getBaseCurrencyCode(),
			'url_success' => $this->_url->getUrl( 'cardgate/payment/redirect' ),
			'url_failure' => $this->_url->getUrl( 'cardgate/payment/redirect' ),
			'url_callback' => $this->_url->getUrl( 'cardgate/payment/callback' )
		];

		$cartitems = [];

		$stockItem = ObjectManager::getInstance()->get( 'Magento\CatalogInventory\Model\Stock\StockItemRepository' );

		/**
		 * @var \Magento\Sales\Api\Data\OrderItemInterface $item
		 */
		foreach ( $order->getAllVisibleItems() as $item ) {
			$itemQty = ( int ) ( $item->getQtyOrdered() ? $item->getQtyOrdered() : $item->getQty() );
			$cartitem = [
				'sku' => $item->getSku(),
				'name' => $item->getName(),
				'quantity' => $itemQty,
				'vat_amount' => round( ( $item->getTaxAmount() * 100 ) / $itemQty, 0 ),
				'vat' => round( $item->getTaxPercent(), 0 ),
				'price' => round( $item->getPriceInclTax() * 100, 0 ),
				'vat_inc' => 1,
				'type' => 1,
			];

			$productStock = $stockItem->get( $item->getProduct()->getId() )->getData();
			if ( !!$productStock['manage_stock'] ) {
				if ( $productStock['qty'] <= -1 ) { // happens when backorders are allowed
					$cartitem['stock'] = 0;
				} else {
					// The stock qty has already been lowered with the purchased quantity.
					$cartitem['stock'] = $itemQty + $productStock['qty'];
				}
			}

			$cartitems[] = $cartitem;
			$calculatedGrandTotal += $item->getPriceInclTax() * $itemQty;
			$calculatedVatTotal += $item->getTaxAmount();
		}

		$shippingAmount = $order->getShippingAmount();
		if ( $shippingAmount > 0 ) {
			$cartitems[] = [
				'sku' => 'shipping',
				'name' => 'Shipping costs',
				'quantity' => 1,
				'vat_amount' => round( $order->getShippingTaxAmount() * 100, 0 ),
				'vat' => ceil( ( ( $order->getShippingInclTax() / $shippingAmount ) - 1 ) * 1000 ) / 10,
				'price' => round( $order->getShippingInclTax() * 100, 0 ),
				'vat_inc' => 1,
				'type' => 2
			];
			$calculatedGrandTotal += $order->getShippingInclTax();
			$calculatedVatTotal += $order->getShippingTaxAmount();

		}

		$discountAmount = $order->getDiscountAmount();
		if ( $discountAmount < 0 ) { // $discountAmount &&
			$cartitems[] = [
				'sku' => 'discount',
				'name' => 'Discount',
				'quantity' => 1,
				'vat_amount' => round( $order->getDiscountTaxCompensationAmount() * 100, 0 ),
				'vat' => ceil( ( ( $discountAmount / ( $discountAmount - $order->getDiscountTaxCompensationAmount() ) ) - 1 ) * 1000 ) / 10,
				'price' => round( $discountAmount * 100, 0 ),
				'vat_inc' => 1,
				'type' => 4
			];
			$calculatedGrandTotal -= $discountAmount;
			$calculatedVatTotal -= $order->getDiscountTaxCompensationAmount();
		}

		$cardgatefeeAmount = $order->getCardgatefeeInclTax();
		if ( $cardgatefeeAmount > 0 ) {
			$cartitems[] = [
				'sku' => 'cardgatefee',
				'name' => 'Payment Fee',
				'quantity' => 1,
				'vat_amount' => round( $order->getCardgatefeeTaxAmount() * 100, 0 ),
				'vat' => ceil( ( ( $order->getCardgatefeeInclTax() / $cardgatefeeAmount ) - 1 ) * 1000 ) / 10,
				'price' => round( $order->getCardgatefeeInclTax() * 100, 0 ),
				'vat_inc' => 1,
				'type' => 5
			];
			$calculatedGrandTotal += $order->getCardgatefeeInclTax();
			$calculatedVatTotal += $order->getCardgatefeeTaxAmount();
		}

		// Failsafe; correct VAT if needed
		if ( $calculatedVatTotal != $order->getTaxAmount() ) {
			$vatCorrection = $order->getTaxAmount() - $calculatedVatTotal;
			$cartitems[] = [
				'sku' => 'cg-vatcorrection',
				'name' => 'VAT Correction',
				'quantity' => 1,
				'vat_amount' => round( $vatCorrection * 100, 0 ),
				'vat' => 100,
				'price' => round( $vatCorrection * 100, 0 ),
				'vat_inc' => 1,
				'type' => 7
			];
			$calculatedGrandTotal += $vatCorrection;
		}

		// Failsafe; correct grandtotal if needed
		if ( $calculatedGrandTotal != $order->getGrandTotal() ) {
			$grandTotalCorrection = $order->getGrandTotal() - $calculatedGrandTotal;
			$cartitems[] = [
				'sku' => 'cg-correction',
				'name' => 'Correction',
				'quantity' => 1,
				'vat_amount' => 0,
				'vat' => 0,
				'price' => round( $grandTotalCorrection * 100, 0 ),
				'vat_inc' => 1,
				'type' => ( $grandTotalCorrection > 0 ) ? 1 : 4
			];
		}

		$data['cartitems'] = $cartitems;

		$code = $order->getPayment()
			->getMethodInstance()
			->getCode();
		$paymentmethod = substr( $code, 9 );

		/**
		 *
		 * @var \Magento\Sales\Model\Order\Payment $payment
		 */
		$payment = $order->getPayment();

		$additional = $payment->getAdditionalInformation();
		unset( $additional['method_title'] );
		$data = array_merge( $additional, $data );
		//try {
			$gatewayResult = $this->_gatewayClient->postRequest( 'payment/' . $paymentmethod . '/', $data );
		//} catch ( \Exception $e ) {
			// YYY: log error here
		//}

		if (
			! isset( $gatewayResult )
			|| ! is_object( $gatewayResult )
		) {
			$this->messageManager->addErrorMessage( __( 'Error occurred while communicating with the payment service provider' ) );
			$order->registerCancellation( 'Error occurred while communicating with the payment service provider' );
			$order->save();
			$this->checkoutSession->restoreQuote();
			$this->_redirect( 'checkout/cart' );
			return;
		} elseif ( ! isset( $gatewayResult->success ) || $gatewayResult->success != true || ! isset( $gatewayResult->payment ) || ! isset( $gatewayResult->payment->transaction_id ) ) {
			$this->messageManager->addErrorMessage( __( 'Error occurred while registering the transaction' ) . ' (' . $gatewayResult->warning . ( isset( $gatewayResult->error ) ? ' #' . $gatewayResult->error->code : '' ) . ')' );
			$order->registerCancellation(
					__( 'Error occurred while registering the transaction' ) . $gatewayResult->warning . ' // ' . ( isset( $gatewayResult->error ) ? ' #' . $gatewayResult->error->message . ' #' . $gatewayResult->code : '' ) . ')' );
			$order->save();
			$this->checkoutSession->restoreQuote();
			$this->_redirect( 'checkout/cart' );
			return;
		}

		// At this point 'success' is true
		$payment->setCardgateTestmode( $this->_gatewayClient->getTestmode() );
		$payment->setCardgatePaymentmethod( $paymentmethod );
		$payment->setCardgateTransaction( $gatewayResult->payment->transaction_id );
		$payment->save();

		$order->addStatusHistoryComment( __("Transaction registered. Transaction ID %1", $gatewayResult->payment->transaction_id ), PaymentMethods::ORDER_STATUS_AUTHORIZED );
		$order->save();

		$this->getResponse()->setRedirect( $gatewayResult->payment->url );
		return;

	}

	/**
	 * Return checkout quote object
	 *
	 * @return \Magento\Quote\Model\Quote
	 */
	protected function getQuote () {
		if ( ! $this->quote ) {
			$this->quote = $this->checkoutSession->getQuote();
		}
		return $this->quote;
	}

	/**
	 * Returns a list of action flags [flag_key] => boolean
	 *
	 * @return array
	 */
	public function getActionFlagList () {
		return [];
	}

	/**
	 * Returns before_auth_url redirect parameter for customer session
	 *
	 * @return null
	 */
	public function getCustomerBeforeAuthUrl () {
		return;
	}

	/**
	 * Returns login url parameter for redirect
	 *
	 * @return string
	 */
	public function getLoginUrl () {
		return $this->_customerUrl->getLoginUrl();
	}

	/**
	 * Returns action name which requires redirect
	 *
	 * @return string
	 */
	public function getRedirectActionName () {
		return 'start';
	}
}
