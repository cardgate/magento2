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
	 * Paymentmethod instance.
	 */
	class Method {

		/**
		 * iDeal.
		 */
		const IDEAL = 'ideal';

		/**
		 * iDeal (legacy).
		 */
		const IDEALPRO = 'idealpro';

		/**
		 * BanContact.
		 */
		const BANCONTACT = 'bancontact';

        /**
         * MisterCash (legacy)
         */
        const MISTERCASH = 'mistercash';

        /**
		 * CreditCard.
		 */
		const CREDITCARD = 'creditcard';

		/**
		 * Afterpay.
		 */
		const AFTERPAY = 'afterpay';

		/**
		 * Giropay.
		 */
		const GIROPAY = 'giropay';

		/**
		 * Giropay.
		 */
		const BANKTRANSFER = 'banktransfer';

		/**
		 * Bitcoins.
		 */
		const BITCOIN = 'bitcoin';

		/**
		 * DirectDebit.
		 */
		const DIRECTDEBIT = 'directdebit';

		/**
		 * Klarna.
		 */
		const KLARNA = 'klarna';

		/**
		 * PayPal.
		 */
		const PAYPAL = 'paypal';

		/**
		 * Przelewy24.
		 */
		const PRZELEWY24 = 'przelewy24';

		/**
		 * SofortBanking.
		 */
		const SOFORTBANKING = 'sofortbanking';

		/**
		 * Paysafecard
		 */
		const PAYSAFECARD = 'paysafecard';

		/**
		 * Billink
		 */
		const BILLINK = 'billink';
		
		/**
		 * IDEALQR
		 */
		const IDEALQR = 'idealqr';

		/**
		 * Paysafecash
		 */
		const PAYSAFECASH = 'paysafecash';
		
		/**
		 * OnlineUberweisen
		 */
		const ONLINEUEBERWEISEN = 'onlineueberweisen';
		
		/**
		 * Gift Card
		 */
		const GIFTCARD = 'giftcard';

		/**
		 * EPS
		 */
		const EPS = 'eps';

		/**
		 * SprayPay
		 */
		const SPRAYPAY = 'spraypay';

        /**
         * Crypto
         */
        const CRYPTO = 'crypto';

		/**
		 * The client associated with this payment method.
		 * @var Client
		 * @access private
		 */
		private $_oClient;

		/**
		 * The payment method.
		 * @var string
		 * @access private
		 */
		private $_sId;

		/**
		 * The payment method name.
		 * @var string
		 * @access private
		 */
		private $_sName;

		/**
		 * The constructor.
		 * @param Client $oClient_ The client associated with this transaction.
		 * @param string $sId_ The payment method identifier to create a method instance for.
		 * @throws Exception|\ReflectionException
		 * @access public
		 * @api
		 * @throws
		 */
		function __construct( Client $oClient_, $sId_, $sName_ ) {
			static $aValidMethods; // use static cache for this

			if ( ! isset( $aValidMethods ) ) {
				$aValidMethods  = ( new \ReflectionClass( '\Cardgate\Payment\Model\CardgateClient\Method' ) )->getConstants();
			}
			$this->_oClient = $oClient_;
            /*
			if ( ! in_array( $sId_, $aValidMethods ) ) {
				throw new Exception('Method.PaymentMethod.Invalid', 'invalid payment method: ' . $sId_);
			}
            */
			$this->setId( $sId_ );
			$this->setName( $sName_ );
		}

		/**
		 * Set the method id.
		 * @param string $sId_ Method id to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setId( $sId_ ) {
			if (
				! is_string( $sId_ )
				|| empty( $sId_ )
			) {
				throw new Exception( 'Method.Id.Invalid', 'invalid id: ' . $sId_ );
			}
			$this->_sId = $sId_;
			return $this;
		}

		/**
		 * Get the payment method id.
		 * @return string The payment method id for this instance.
		 * @access public
		 * @api
		 */
		public function getId() {
			return $this->_sId;
		}

		/**
		 * Set the method name.
		 * @param string $sName_ Method name to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setName( $sName_ ) {
			if (
				! is_string( $sName_ )
				|| empty( $sName_ )
			) {
				throw new Exception( 'Method.Name.Invalid', 'invalid name: ' . $sName_ );
			}
			$this->_sName = $sName_;
			return $this;
		}

		/**
		 * Get the payment method name.
		 * @return string The payment method name for this instance.
		 * @access public
		 * @api
		 */
		public function getName() {
			return $this->_sName;
		}

		/**
		 * This method returns all the issuers available for the current payment method.
		 * @return array An array with issuers
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function getIssuers() {
            $aIssuers   = [0=>["id"=>"ZERO", "name"=>"Deprecated"]];
			return  $aIssuers; //Deprecated since iDEAL2
		}

	}

}
