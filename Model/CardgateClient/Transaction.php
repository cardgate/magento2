<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @license     The MIT License (MIT) https://opensource.org/licenses/MIT
 * @author      CardGate B.V.
 * @copyright   CardGate B.V.
 * @link        https://www.cardgate.com
 */
namespace Cardgate\Payment\Model\CardgateClient {

    /**
     * Transaction instance.
     */
    class Transaction
    {

        /**
         * The client associated with this transaction.
         * @var Client
         * @access protected
         */
        protected $_oClient;

        /**
         * The transaction id.
         * @var string
         * @access private
         */
        private $_sId;

        /**
         * The site id to use for payments.
         * @var int
         * @access protected
         */
        protected $_iSiteId;

        /**
         * The site key to use for payments.
         * @var string
         * @access protected
         */
        protected $_sSiteKey;

        /**
         * The transaction amount in cents.
         * @var int
         * @access protected
         */
        protected $_iAmount;

        /**
         * The transaction currency (ISO 4217).
         * @var string
         * @access protected
         */
        protected $_sCurrency;

        /**
         * The description for the transaction.
         * @var string
         * @access protected
         */
        protected $_sDescription;

        /**
         * A reference for the transaction.
         * @var string
         * @access protected
         */
        protected $_sReference;

        /**
         * The payment method for the transaction.
         * @var Method
         * @access protected
         */
        protected $_oPaymentMethod = null;

        /**
         * The payment method issuer for the transaction.
         * @var string
         * @access protected
         */
        protected $_sIssuer = null;

        /**
         * The recurring flag
         * @var bool
         * @access private
         */
        private $_bRecurring = false;

        /**
         * The consumer for the transaction.
         * @var Consumer
         * @access protected
         */
        protected $_oConsumer = null;

        /**
         * The cart for the transaction.
         * @var Cart
         * @access protected
         */
        protected $_oCart = null;

        /**
         * The URL to send payment callback updates to.
         * @var string
         * @access protected
         */
        protected $_sCallbackUrl = null;

        /**
         * The URL to redirect to on success.
         * @var string
         * @access protected
         */
        protected $_sSuccessUrl = null;

        /**
         * The URL to redirect to on failre.
         * @var string
         * @access protected
         */
        protected $_sFailureUrl = null;

        /**
         * The URL to redirect to on pending.
         * @var string
         * @access protected
         */
        protected $_sPendingUrl = null;

        /**
         * The URL to redirect to after initial transaction register.
         * @var string
         * @access protected
         */
        protected $_sActionUrl = null;

        /**
         * The constructor.
         * @param Client $oClient_ The client associated with this transaction.
         * @param int $iSiteId_ Site id to create transaction for.
         * @param int $iAmount_ The amount of the transaction in cents.
         * @param string $sCurrency_ Currency (ISO 4217)
         * @throws Exception
         * @access public
         * @api
         */
        function __construct(Client $oClient_, $iSiteId_, $iAmount_, $sCurrency_ = 'EUR')
        {
            $this->_oClient = $oClient_;
            $this->setSiteId($iSiteId_)->setAmount($iAmount_)->setCurrency($sCurrency_);
        }

        /**
         * Set the transaction id.
         * @param string $sId_ Transaction id to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setId($sId_)
        {
            if (! is_string($sId_)
                || empty($sId_)
            ) {
                throw new Exception('Transaction.Id.Invalid', 'invalid id: ' . $sId_);
            }
            $this->_sId = $sId_;
            return $this;
        }

        /**
         * Get the transaction id associated with this transaction.
         * @return string The transaction id associated with this transaction.
         * @throws Exception
         * @access public
         * @api
         */
        public function getId()
        {
            if (empty($this->_sId)) {
                throw new Exception('Transaction.Not.Initialized', 'invalid transaction state');
            }
            return $this->_sId;
        }

        /**
         * Configure the transaction object with a site id.
         * @param int $iSiteId_ Site id to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setSiteId($iSiteId_)
        {
            if (! is_integer($iSiteId_)) {
                throw new Exception('Transaction.SiteId.Invalid', 'invalid site: ' . $iSiteId_);
            }
            $this->_iSiteId = $iSiteId_;
            return $this;
        }

        /**
         * Get the site id associated with this transaction.
         * @return int The site id associated with this transaction.
         * @access public
         * @api
         */
        public function getSiteId()
        {
            return $this->_iSiteId;
        }

        /**
         * Set the Site key to authenticate the hash in the request.
         * @param string $sSiteKey_ The site key to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setSiteKey($sSiteKey_)
        {
            if (! is_string($sSiteKey_)) {
                throw new Exception('Client.SiteKey.Invalid', 'invalid site key: ' . $sSiteKey_);
            }
            $this->_sSiteKey = $sSiteKey_;
            return $this;
        }

        /**
         * Get the Merchant API key to authenticate the transaction request with.
         * @return string The merchant API key.
         * @access public
         * @api
         */
        public function getSiteKey()
        {
            return $this->_sSiteKey;
        }

        /**
         * Configure the transaction object with an amount.
         * @param int $iAmount_ Amount in cents to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setAmount($iAmount_)
        {
            if (! is_integer($iAmount_)) {
                throw new Exception('Transaction.Amount.Invalid', 'invalid amount: ' . $iAmount_);
            }
            $this->_iAmount = $iAmount_;
            return $this;
        }

        /**
         * Get the amount of the transaction.
         * @return int The amount of the transaction.
         * @access public
         * @api
         */
        public function getAmount()
        {
            return $this->_iAmount;
        }

        /**
         * Configure the transaction currency.
         * @param string $sCurrency_ The currency to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setCurrency($sCurrency_)
        {
            if (! is_string($sCurrency_)) {
                throw new Exception('Transaction.Currency.Invalid', 'invalid currency: ' . $sCurrency_);
            }
            $this->_sCurrency = $sCurrency_;
            return $this;
        }

        /**
         * Get the currency of the transaction.
         * @return string The currency of the transaction.
         * @access public
         * @api
         */
        public function getCurrency()
        {
            return $this->_sCurrency;
        }

        /**
         * Configure the description for the transaction.
         * @param string $sDescription_ The description to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setDescription($sDescription_)
        {
            if (! is_string($sDescription_)) {
                throw new Exception('Transaction.Description.Invalid', 'invalid description: ' . $sDescription_);
            }
            $this->_sDescription = $sDescription_;
            return $this;
        }

        /**
         * Get the description for the transaction.
         * @return string The description of the transaction.
         * @access public
         * @api
         */
        public function getDescription()
        {
            return $this->_sDescription;
        }

        /**
         * Configure the reference for the transaction.
         * @param string $sReference_ The reference to set.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setReference($sReference_)
        {
            if (! is_string($sReference_)) {
                throw new Exception('Transaction.Reference.Invalid', 'invalid reference: ' . $sReference_);
            }
            $this->_sReference = $sReference_;
            return $this;
        }

        /**
         * Get the reference for the transaction.
         * @return string The reference of the transaction.
         * @access public
         * @api
         */
        public function getReference()
        {
            return $this->_sReference;
        }

        /**
         * Set the payment method to use for the transaction.
         * @param Mixed $mPaymentMethod_ The payment method to use for the transaction. Can be one of the
         * consts defined in {@link Method} or a {@link Method} instance.
         * @return $this
         * @throws Exception|\ReflectionException
         * @access public
         * @api
         */
        public function setPaymentMethod($mPaymentMethod_)
        {
            if ($mPaymentMethod_ instanceof Method) {
                $this->_oPaymentMethod = $mPaymentMethod_;
            } elseif (is_string($mPaymentMethod_)) {
                $this->_oPaymentMethod = new Method($this->_oClient, $mPaymentMethod_, $mPaymentMethod_);
            } else {
                throw new Exception('Transaction.PaymentMethod.Invalid', 'invalid payment method: ' . $mPaymentMethod_);
            }
            return $this;
        }

        /**
         * Get the payment method that will be used for the transaction.
         * @return Method The payment method that will be used for the transaction.
         * @access public
         * @api
         */
        public function getPaymentMethod()
        {
            return $this->_oPaymentMethod;
        }

        /**
         * Set the optional payment method issuer to use for the transaction.
         * @param string $sIssuer_ The payment method issuer to use for the transaction.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setIssuer($sIssuer_)
        {
            if (empty($this->_oPaymentMethod)
                || ! is_string($sIssuer_)
            ) {
                throw new Exception('Transaction.Issuer.Invalid', 'invalid issuer: ' . $sIssuer_);
            }
            $this->_sIssuer = $sIssuer_;
            return $this;
        }

        /**
         * Get the optional payment method issuer that will be used for the transaction.
         * @return string The payment method issuer that will be used for the transaction.
         * @access public
         * @api
         */
        public function getIssuer()
        {
            return $this->_sIssuer;
        }

        /**
         * Set the recurring flag on the transaction.
         * @param bool $bRecurring_ Wether or not this transaction can be used for recurring.
         * @return $this
         * @access public
         * @api
         */
        public function setRecurring($bRecurring_)
        {
            $this->_bRecurring = (bool) $bRecurring_;
            return $this;
        }

        /**
         * Get the recurring flag of the transaction.
         * @return bool Returns wether or not this transaction can be used for recurring.
         * @access public
         * @api
         */
        public function getRecurring()
        {
            return $this->_bRecurring;
        }

        /**
         * Set the consumer for the transaction.
         * @param Consumer $oConsumer_ The consumer for the transaction.
         * @return $this
         * @access public
         * @api
         */
        public function setConsumer(Consumer $oConsumer_)
        {
            $this->_oConsumer = $oConsumer_;
            return $this;
        }

        /**
         * Get the consumer for the transaction.
         * @return Consumer The consumer for the transaction.
         * @access public
         * @api
         */
        public function getConsumer()
        {
            if (empty($this->_oConsumer)) {
                $this->_oConsumer = new Consumer();
            }
            return $this->_oConsumer;
        }

        /**
         * Get the consumer for the transaction.
         * @return Consumer The consumer for the transaction.
         * @access public
         * @api
         * @deprecated Will be removed in v2.0.0.
         */
        public function getCustomer()
        {
            return $this->getConsumer();
        }

        /**
         * Set the cart for the transaction.
         * @param Cart $oCart_ The cart for the transaction.
         * @return $this
         * @access public
         * @api
         */
        public function setCart(Cart $oCart_)
        {
            $this->_oCart = $oCart_;
            return $this;
        }

        /**
         * Get the cart for the transaction.
         * @return Cart The cart for the transaction.
         * @access public
         * @api
         */
        public function getCart()
        {
            if (empty($this->_oCart)) {
                $this->_oCart = new Cart();
            }
            return $this->_oCart;
        }

        /**
         * Set the callback URL.
         * @param string $sUrl_ The URL to send callbacks to.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setCallbackUrl($sUrl_)
        {
            if (false === filter_var($sUrl_, FILTER_VALIDATE_URL)) {
                throw new Exception('Transaction.CallbackUrl.Invalid', 'invalid url: ' . $sUrl_);
            }
            $this->_sCallbackUrl = $sUrl_;
            return $this;
        }

        /**
         * Get the callbacl URL.
         * @return string The URL callbacks are being sent to.
         * @access public
         * @api
         */
        public function getCallbackUrl()
        {
            return $this->_sCallbackUrl;
        }

        /**
         * Set the success URL.
         * @param string $sUrl_ The URL to send successful transaction redirects.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setSuccessUrl($sUrl_)
        {
            if (false === filter_var($sUrl_, FILTER_VALIDATE_URL)) {
                throw new Exception('Transaction.SuccessUrl.Invalid', 'invalid url: ' . $sUrl_);
            }
            $this->_sSuccessUrl = $sUrl_;
            return $this;
        }

        /**
         * Get the success URL.
         * @return string The URL successful transactions are being redirected to.
         * @access public
         * @api
         */
        public function getSuccessUrl()
        {
            return $this->_sSuccessUrl;
        }

        /**
         * Set the failure URL.
         * @param string $sUrl_ The URL to send failed transaction redirects.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setFailureUrl($sUrl_)
        {
            if (false === filter_var($sUrl_, FILTER_VALIDATE_URL)) {
                throw new Exception('Transaction.FailureUrl.Invalid', 'invalid url: ' . $sUrl_);
            }
            $this->_sFailureUrl = $sUrl_;
            return $this;
        }

        /**
         * Get the failure URL.
         * @return string The URL failed transactions are being redirected to.
         * @access public
         * @api
         */
        public function getFailureUrl()
        {
            return $this->_sFailureUrl;
        }

        /**
         * Set the failure URL.
         * @param string $sUrl_ The URL to send failed transaction redirects.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setPendingUrl($sUrl_)
        {
            if (false === filter_var($sUrl_, FILTER_VALIDATE_URL)) {
                throw new Exception('Transaction.PendingUrl.Invalid', 'invalid url: ' . $sUrl_);
            }
            $this->_sPendingUrl = $sUrl_;
            return $this;
        }

        /**
         * Use this method to set the url for success, failure and pending all at once.
         * @param string $sUrl_ The URL to use for success, failure and pending.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function setRedirectUrl($sUrl_)
        {
            $this->setSuccessUrl($sUrl_)->setFailureUrl($sUrl_)->setPendingUrl($sUrl_);
            return $this;
        }

        /**
         * Get the pending URL.
         * @return string The URL pending transactions are being redirected to.
         * @access public
         * @api
         */
        public function getPendingUrl()
        {
            return $this->_sPendingUrl;
        }

        /**
         * Get the redirect URL after transaction register.
         * @return string The URL to redirect to after register.
         * @access public
         * @api
         */
        public function getActionUrl()
        {
            return $this->_sActionUrl;
        }

        /**
         * Registers the transaction with the cardgate payment gateway.
         * @return $this
         * @throws Exception
         * @access public
         * @api
         */
        public function register()
        {
            $aData = [
                'site_id'         => $this->_iSiteId,
                'amount'        => $this->_iAmount,
                'currency_id'    => $this->_sCurrency,
                'url_callback'    => $this->_sCallbackUrl,
                'url_success'    => $this->_sSuccessUrl,
                'url_failure'    => $this->_sFailureUrl,
                'url_pending'    => $this->_sPendingUrl,
                'description'    => $this->_sDescription,
                'reference'        => $this->_sReference,
                'recurring'        => $this->_bRecurring ? '1' : '0'
            ];
            if (! is_null($this->_oConsumer)) {
                $aData['email'] = $this->_oConsumer->getEmail();
                $aData['phone'] = $this->_oConsumer->getPhone();
                $aData['consumer'] = array_merge(
                    $this->_oConsumer->address()->getData(),
                    $this->_oConsumer->shippingAddress()->getData('shipto_')
                );
                $aData['country_id'] = $this->_oConsumer->address()->getCountry();
            }
            if (! is_null($this->_oCart)) {
                $aData['cartitems'] = $this->_oCart->getData();
            }

            $sResource = 'payment/';
            if (! empty($this->_oPaymentMethod)) {
                $sResource .= $this->_oPaymentMethod->getId() . '/';
                $aData['issuer'] = $this->_sIssuer;
            }

            $aData = array_filter($aData); // remove NULL values
            $aResult = $this->_oClient->doRequest($sResource, $aData, 'POST');

            if (empty($aResult['payment'])
                || empty($aResult['payment']['transaction'])
            ) {
                throw new Exception('Transaction.Request.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo(true, false));
            }
            $this->_sId = $aResult['payment']['transaction'];
            if (isset($aResult['payment']['action'])
                && 'redirect' == $aResult['payment']['action']
            ) {
                $this->_sActionUrl = $aResult['payment']['url'];
            }

            return $this;
        }

        /**
         * This method can be used to determine if this transaction can be refunded.
         * @param bool $iRemainder_ Will be set to the amount that can be refunded.
         * refunds are supported.
         * @return bool
         * @throws Exception
         * @access public
         */
        public function canRefund(&$iRemainder_ = null)
        {
            $sResource = "transaction/{$this->_sId}/";

            $aResult = $this->_oClient->doRequest($sResource, null, 'GET');

            if (empty($aResult['transaction'])) {
                throw new Exception('Transaction.CanRefund.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo(true, false));
            }

            $iRemainder_ = (int) ($aResult['transaction']['refund_remainder'] ?? 0);

            return !empty($aResult['transaction']['can_refund']);
        }

        /**
         * This method can be used to (partially) refund a transaction.
         * @param int $iAmount_
         * @return Transaction The new (refund) transaction.
         * @throws Exception
         * @access public
         * @api
         */
        public function refund($iAmount_ = null, $sDescription_ = null)
        {
            if (! is_null($iAmount_)
                && ! is_integer($iAmount_)
            ) {
                throw new Exception('Transaction.Amount.Invalid', 'invalid amount: ' . $iAmount_);
            }

            $aData = [
                'amount'        => is_null($iAmount_) ? $this->_iAmount : $iAmount_,
                'currency_id'    => $this->_sCurrency,
                'description'    => $sDescription_
            ];

            $sResource = "refund/{$this->_sId}/";

            $aData = array_filter($aData); // remove NULL values
            $aResult = $this->_oClient->doRequest($sResource, $aData, 'POST');

            if (empty($aResult['refund'])
                || empty($aResult['refund']['transaction'])
            ) {
                throw new Exception('Transaction.Refund.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo(true, false));
            }

            // This is a bit unlogical! Why not leave this to the callee?
            return $this->_oClient->transactions()->get($aResult['refund']['transaction']);
        }

        /**
         * This method can be used to recur a transaction.
         * @param int $iAmount_
         * @param string $sReference_ Optional reference for the recurring transaction.
         * @param string $sDescription_ Optional description for the recurring transaction.
         * @return Transaction The new (recurred) transaction.
         * @throws Exception
         * @access public
         * @api
         */
        public function recur($iAmount_, $sReference_ = null, $sDescription_ = null)
        {
            if (! is_integer($iAmount_)) {
                throw new Exception('Transaction.Amount.Invalid', 'invalid amount: ' . $iAmount_);
            }

            $aData = [
                'amount'        => $iAmount_,
                'currency_id'    => $this->_sCurrency,
                'reference'        => $sReference_,
                'description'    => $sDescription_
            ];

            $sResource = "recurring/{$this->_sId}/";

            $aData = array_filter($aData); // remove NULL values
            $aResult = $this->_oClient->doRequest($sResource, $aData, 'POST');

            if (empty($aResult['recurring'])
                || empty($aResult['recurring']['transaction_id'])
            ) {
                throw new Exception('Transaction.Recur.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo(true, false));
            }

            // Same unlogical stuff as method above! Why not leave this to the callee?
            return $this->_oClient->transactions()->get($aResult['recurring']['transaction_id']);
        }
    }

}
