<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Payment\Helper\Data as PaymentHelper;
use Cardgate\Payment\Model\GatewayClient;
use Cardgate\Payment\Model\Config;
use Cardgate\Payment\Model\Config\Master;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\OrderRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Start payment action
 *
 *
 */
class Start implements ActionInterface
{

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
     * @var OrderRepository
     */
    protected $orderRepository;

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
     * @var StockRegistryInterface
     */
    private $stockRegistryInterface;

    /**
     *
     * @var  Magento\Framework\Controller\Result\Redirect
     */
    private $redirect;

    /**
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Redirect $redirect
     * @param OrderRepository $orderRepository
     * @param PaymentHelper $paymentHelper
     * @param GatewayClient $gatewayClient
     * @param Config $cardgateConfig
     * @param Master $masterConfig
     * @param StockRegistryInterface $stockRegistryInterface
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        Redirect $redirect,
        OrderRepository $orderRepository,
        PaymentHelper $paymentHelper,
        GatewayClient $gatewayClient,
        Config $cardgateConfig,
        Master $masterConfig,
        StockRegistryInterface $stockRegistryInterface
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->redirect = $redirect;
        $this->orderRepository = $orderRepository;
        $this->_paymentHelper = $paymentHelper;
        $this->_gatewayClient = $gatewayClient;
        $this->_cardgateConfig = $cardgateConfig;
        $this->_masterConfig = $masterConfig;
        $this->stockRegistryInterface = $stockRegistryInterface;
    }

    /**
     * Initiate CardGate transaction
     *
     * @return \Magento\Framework\App\ResponseInterface|Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $orderId = $order->getIncrementId();

        try {

            $storeManager = ObjectManager::getInstance()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            );
            $url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

            $transaction = $this->_gatewayClient->transactions()->create(
                $this->_gatewayClient->getSiteId(),
                (int)round($order->getGrandTotal() * 100),
                $order->getOrderCurrencyCode()
            );

            $code = $order->getPayment()->getMethodInstance()->getCode();
            $paymentMethod = substr($code, 9);
            $transaction->setPaymentMethod($this->_gatewayClient->methods()->get($paymentMethod));

            $transaction->setCallbackUrl($url.'cardgate/payment/callback');
            $transaction->setRedirectUrl($url.'cardgate/payment/redirect');
            $transaction->setReference($orderId);
            $transaction->setDescription(
                str_replace(
                    '%id%',
                    $orderId,
                    $this->_cardgateConfig->getGlobal('order_description')
                )
            );

            // Add the consumer data to the transaction.
            $consumer = $transaction->getConsumer();
            $billingAddress = $order->getBillingAddress();
            if (!$billingAddress) {
                throw new StartException('missing or invalid billing address');
            }
            $consumer->setEmail($billingAddress->getEmail());
            $telephone =$billingAddress->getTelephone();
            if (!(($telephone === null) || empty($telephone))) {
                $consumer->setPhone($billingAddress->getTelephone());
            }
            self::_convertAddress($billingAddress, $consumer, 'address');
            $shippingAddress = $order->getShippingAddress();
            if (!$shippingAddress) {
                $shippingAddress = &$billingAddress;
            }
            self::_convertAddress($shippingAddress, $consumer, 'shippingAddress');

            // Add the cart items to the transaction.
            $calculatedGrandTotal = 0.00;
            $calculatedVatTotal = 0.00;
            $cart = $transaction->getCart();
            $stock = ObjectManager::getInstance()->get(
                \Magento\CatalogInventory\Model\Stock\StockItemRepository::class
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $itemQty = (int)( $item->getQtyOrdered() ? $item->getQtyOrdered() : $item->getQty() );
                $product = $item->getProduct();
                $url = $product->getUrlModel()->getUrl($product);
                $cartItem = $cart->addItem(
                    \Cardgate\Payment\Model\CardgateClient\Item::TYPE_PRODUCT,
                    $item->getSku(),
                    $item->getName(),
                    $itemQty,
                    round($item->getPrice() * 100, 0),
                    $url
                );
                $cartItem->setVat(round($item->getTaxPercent(), 0));
                $cartItem->setVatIncluded(false);
                $cartItem->setVatAmount(round(( $item->getTaxAmount() * 100 ) / $itemQty, 0));

                // Include stock in cart items will disable auto-capture on CardGate gateway if item
                // is backordered.
                $oStock = null;
                try {
                    $oStock = $this->stockRegistryInterface->getStockItem($product->getId());

                } catch (StartException $e) {
                    /* ignore */
                }
                if (is_object($oStock) && $oStock->getManageStock()
                ) {
                    if ($oStock->getQty() <= -1) { // happens when backorders are allowed
                        $cartItem->setStock(0);
                    } else {
                        // The stock qty has already been lowered with the purchased quantity.
                        $cartItem->setStock($itemQty + $oStock->getQty());
                    }
                }
                $calculatedGrandTotal += $item->getPrice() * $itemQty + round($item->getTaxAmount(), 2);
                $calculatedVatTotal += $item->getTaxAmount();
            }

            $shippingAmount = $order->getShippingAmount();
            if ($shippingAmount > 0) {
                $cartItem = $cart->addItem(
                    \Cardgate\Payment\Model\CardgateClient\Item::TYPE_SHIPPING,
                    'shipping',
                    'Shipping Costs',
                    1,
                    round($order->getShippingInclTax() * 100, 0)
                );
                $cartItem->setVat(ceil(( ( $order->getShippingInclTax() / $shippingAmount ) - 1 ) * 1000) / 10);
                $cartItem->setVatIncluded(true);
                $cartItem->setVatAmount(round($order->getShippingTaxAmount() * 100, 0));
                $calculatedGrandTotal += $order->getShippingInclTax();
                $calculatedVatTotal += $order->getShippingTaxAmount();
            }

            $discountAmount = $order->getDiscountAmount();
            if ($discountAmount < 0) {
                $cartItem = $cart->addItem(
                    \Cardgate\Payment\Model\CardgateClient\Item::TYPE_DISCOUNT,
                    'discount',
                    'Discount',
                    1,
                    round($discountAmount * 100, 0)
                );
                $cartItem->setVat(
                    ceil((($discountAmount / ($discountAmount - $order->getDiscountTaxCompensationAmount()))-1)
                    * 1000)/10
                );
                $cartItem->setVatIncluded(true);
                $cartItem->setVatAmount(round($order->getDiscountTaxCompensationAmount() * 100, 0));
                $calculatedGrandTotal += $discountAmount;
                $calculatedVatTotal -= $order->getDiscountTaxCompensationAmount();
            }

            $cardGateFeeAmount = $order->getCardgatefeeAmount();
            if ($cardGateFeeAmount > 0) {
                $cartItem = $cart->addItem(
                    \Cardgate\Payment\Model\CardgateClient\Item::TYPE_HANDLING,
                    'cardgatefee',
                    'Payment Fee',
                    1,
                    round($order->getCardgatefeeInclTax() * 100, 0)
                );
                $cartItem->setVat(ceil(( ( $order->getCardgatefeeInclTax() / $cardGateFeeAmount ) - 1 ) * 1000) / 10);
                $cartItem->setVatIncluded(true);
                $cartItem->setVatAmount(round($order->getCardgatefeeTaxAmount() * 100, 0));
                $calculatedGrandTotal += $order->getCardgatefeeInclTax();
                $calculatedVatTotal += $order->getCardgatefeeTaxAmount();
            }

            // Failsafe; correct VAT if needed.
            if (abs($calculatedVatTotal - $order->getTaxAmount()) >= 0.01) {
                $vatCorrection = $order->getTaxAmount() - $calculatedVatTotal;
                $cartItem = $cart->addItem(
                    \Cardgate\Payment\Model\CardgateClient\Item::TYPE_VAT_CORRECTION,
                    'cg-vatcorrection',
                    'VAT Correction',
                    1,
                    round($vatCorrection * 100, 0)
                );
                $cartItem->setVat(100);
                $cartItem->setVatIncluded(true);
                $cartItem->setVatAmount(round($vatCorrection * 100, 0));

                $calculatedGrandTotal += $vatCorrection;
            }

            // Failsafe; correct grandtotal if needed.
            $grandTotalCorrection = round(( $order->getGrandTotal() - $calculatedGrandTotal ) * 100, 0);
            if (abs($grandTotalCorrection) > 0) {
                $cartItem = $cart->addItem(
                    \Cardgate\Payment\Model\CardgateClient\Item::TYPE_CORRECTION,
                    'cg-correction',
                    'Correction',
                    1,
                    $grandTotalCorrection
                );
                $cartItem->setVat(0);
                $cartItem->setVatIncluded(true);
                $cartItem->setVatAmount(0);
            }

            // If there was an issuer present (most likely iDeal), configure the transaction with this issuer. The
            // issuer is stored as additional data in the assignData method from Model/PaymentMethod.php.
            $payment = $order->getPayment();
            $instructions = $this->_cardgateConfig->getValue('instructions', $payment->getOrder()->getStoreId());
            $payment->setAdditionalInformation('instructions', $instructions);

            // Register the transaction and finish up.
            $transaction->register();
            $payment->setCardgateTestmode($this->_gatewayClient->getTestmode());
            $payment->setCardgatePaymentmethod($paymentMethod);
            $payment->setCardgateTransaction($transaction->getId());
            $payment->save();

            $order->addCommentToStatusHistory(__("Transaction registered. Transaction ID %1", $transaction->getId()));
            $this->orderRepository->save($order);

            $actionUrl = $transaction->getActionUrl();
            if (null !== $actionUrl) {
                // Redirect the consumer to the CardGate payment gatew
                return $this->redirect->setUrl($actionUrl);
            } else {
                // Payment methods without user interaction are not yet supported.
                throw new StartException('unsupported payment action');
            }

        } catch (StartException $e) {
            $this->messageManager->addErrorMessage(
                __('Error occurred while registering the transaction') . ' (' . $e->getMessage() . ')'
            );
            $order->registerCancellation(
                __('Error occurred while registering the transaction') . ' (' . $e->getMessage() . ')'
            );
            $this->orderRepository->save($order);
            $this->checkoutSession->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Converts a Magento address object to a cardgate consumer address.
     *
     * @param Address $oAddress_
     * @param \Cardgate\Payment\Model\CardgateClient\Consumer $oConsumer_
     * @param string $sMethod_
     *
     * @return void
     */
    private function _convertAddress(Address &$oAddress_, \Cardgate\Payment\Model\CardgateClient\Consumer &$oConsumer_, $sMethod_)
    {
        $oConsumer_->$sMethod_()->setFirstName($oAddress_->getFirstname());
        $oConsumer_->$sMethod_()->setLastName($oAddress_->getLastname());
        if (!!( $sCompany = $oAddress_->getCompany() )) {
            $oConsumer_->$sMethod_()->setCompany($sCompany);
        }
        $oConsumer_->$sMethod_()->setAddress(implode(PHP_EOL, $oAddress_->getStreet()));
        $oConsumer_->$sMethod_()->setCity($oAddress_->getCity());
        if (!!( $sState = $oAddress_->getRegion() )) {
            $oConsumer_->$sMethod_()->setState($sState);
        }
        $oConsumer_->$sMethod_()->setZipCode($oAddress_->getPostcode());
        $oConsumer_->$sMethod_()->setCountry($oAddress_->getCountryId());
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuote()
    {
        if (! $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     *
     * @return array
     */
    public function getActionFlagList()
    {
        return [];
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     *
     * @return null
     */
    public function getCustomerBeforeAuthUrl()
    {
        return null;
    }

    /**
     * Returns login url parameter for redirect
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     *
     * @return string
     */
    public function getRedirectActionName()
    {
        return 'start';
    }
}
