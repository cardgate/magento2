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
namespace Cardgate\Payment\Api {

	/**
	 * Subscription instance.
	 */
	final class Subscription extends Transaction {

		/**
		 * The subscription id.
		 * @var string
		 * @access private
		 */
		private $_sId;

		/**
		 * The length of the subscription period.
		 * @var int
		 * @access private
		 */
		private $_iPeriod;

		/**
		 * The type of period (ie day, week, month, year)
		 * @var string
		 * @access private
		 */
		private $_sPeriodType;

		/**
		 * The price per period in cents
		 * @var int
		 * @access private
		 */
		private $_iPeriodPrice;

		/**
		 * The amount of the initial (first) payment, used only when the first payment is different from the monthly costs.
		 * @var int
		 * @access private
		 */
		private $_iInitialPayment;

		/**
		 * The length of the trial period.
		 * @var int
		 * @access private
		 */
		private $_iTrialPeriod;

		/**
		 * The type of trial period (ie day, week, month, year)
		 * @var string
		 * @access private
		 */
		private $_sTrialPeriodType;

		/**
		 * The price for the trial period in cents
		 * @var int
		 * @access private
		 */
		private $_iTrialPeriodPrice;

		/**
		 * The start date (UTC) of the subscription in YYYY-MM-DD hh:mm:ss format.
		 * If none is given the current date will be used.
		 * @var string
		 * @access private
		 */
		private $_sStartDate;

		/**
		 * The end date (UTC) of the subscription in YYYY-MM-DD hh:mm:ss format.
		 * If none is given the subscription will never end.
		 * @var string
		 * @access private
		 */
		private $_sEndDate;

		/**
		 * The status of the subscription.
		 * @var string
		 * @access private
		 */
		private $_sStatus;

		/**
		 * The constructor.
		 * @param Client $oClient_ The client associated with this subscription.
		 * @param int $iSiteId_ Site id to create the subscription for.
		 * @param int $iPeriod_ The period length of the subscription.
		 * @param string $sPeriodType_ The period type of the subscription (e.g. day, week, month, year).
		 * @param int $iPeriodAmount_ The period amount of the subscription in cents.
		 * @param string $sCurrency_ Currency (ISO 4217)
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function __construct( Client $oClient_, $iSiteId_, $iPeriod_, $sPeriodType_, $iPeriodAmount_, $sCurrency_ = 'EUR' ) {
			$this->_oClient = $oClient_;
			$this->setSiteId( $iSiteId_ )
			     ->setPeriod( $iPeriod_ )
			     ->setPeriodType( $sPeriodType_ )
			     ->setPeriodPrice( $iPeriodAmount_ )
			     ->setCurrency( $sCurrency_ );
		}

		/**
		 * Set the subscription id.
		 * @param string $sId_ Subscription id to set.
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
				throw new Exception( 'Subscription.Id.Invalid', 'invalid id: ' . $sId_ );
			}
			$this->_sId = $sId_;
			return $this;
		}

		/**
		 * Get the subscription id associated with this subscription.
		 * @return string The subscription id associated with this subscription.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function getId() {
			if ( empty( $this->_sId ) ) {
				throw new Exception( 'Subscription.Not.Initialized', 'invalid subscription state' );
			}
			return $this->_sId;
		}

		/**
		 * Configure the subscription object with a period.
		 * @param int $iPeriod_ Period length to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setPeriod( $iPeriod_ ) {
			if ( ! is_integer( $iPeriod_ ) ) {
				throw new Exception( 'Subscription.Period.Invalid', 'invalid period: ' . $iPeriod_ );
			}
			$this->_iPeriod = $iPeriod_;
			return $this;
		}

		/**
		 * Get the period of the subscription.
		 * @return int The period of the subscription.
		 * @access public
		 * @api
		 */
		public function getPeriod() {
			return $this->_iPeriod;
		}

		/**
		 * Configure the subscription object with a period type.
		 * @param string $sPeriodType_ Period type to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setPeriodType( $sPeriodType_ ) {
			if (
				! is_string( $sPeriodType_ )
				|| ! in_array( $sPeriodType_, [ 'day', 'week', 'month', 'year' ] )
			) {
				throw new Exception( 'Subscription.Period.Type.Invalid', 'invalid period type: ' . $sPeriodType_ );
			}
			$this->_sPeriodType = $sPeriodType_;
			return $this;
		}

		/**
		 * Get the period type of the subscription.
		 * @return string The period type of the subscription.
		 * @access public
		 * @api
		 */
		public function getPeriodType() {
			return $this->_sPeriodType;
		}

		/**
		 * Configure the subscription object with a period price.
		 * @param int $iPeriod_ Period price to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setPeriodPrice( $iPeriodPrice_ ) {
			if ( ! is_integer( $iPeriodPrice_ ) ) {
				throw new Exception( 'Subscription.Period.Price.Invalid', 'invalid period price: ' . $iPeriodPrice_ );
			}
			$this->_iPeriodPrice = $iPeriodPrice_;
			return $this;
		}

		/**
		 * Get the period price of the subscription.
		 * @return int The period price of the subscription.
		 * @access public
		 * @api
		 */
		public function getPeriodPrice() {
			return $this->_iPeriodPrice;
		}

		/**
		 * Configure the subscription initial payment amount.
		 * @param int $iInitialPayment_ The initial payment amount to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setInitialPayment( $iAmount_ ) {
			if ( ! is_integer( $iAmount_ ) ) {
				throw new Exception( 'Subscription.Initial.Payment.Invalid', 'invalid initial payment amount: ' . $iAmount_ );
			}
			$this->_iInitialPayment = $iAmount_;
			return $this;
		}


		/**
		 * Configure the subscription object with a trial period.
		 * @param int $iPeriod_ Trial period length to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setTrialPeriod( $iTrialPeriod_ ) {
			if ( ! is_integer( $iTrialPeriod_ ) ) {
				throw new Exception( 'Subscription.Period.Invalid', 'invalid trial period: ' . $iTrialPeriod_ );
			}
			$this->_iTrialPeriod = $iTrialPeriod_;
			return $this;
		}

		/**
		 * Get the trial period of the subscription.
		 * @return int The trial period of the subscription.
		 * @access public
		 * @api
		 */
		public function getTrialPeriod() {
			return $this->_iTrialPeriod;
		}

		/**
		 * Configure the subscription object with a trial period type.
		 * @param string $sTrialPeriodType_ Trial Period type to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setTrialPeriodType( $sTrialPeriodType_ ) {
			if (
				! is_string( $sTrialPeriodType_ )
				|| ! in_array( $sTrialPeriodType_, [ 'day', 'week', 'month', 'year' ] )
			) {
				throw new Exception( 'Subscription.Period.Type.Invalid', 'invalid trial period type: ' . $sTrialPeriodType_ );
			}
			$this->_sTrialPeriodType = $sTrialPeriodType_;
			return $this;
		}

		/**
		 * Get the trial period type of the subscription.
		 * @return string The trial period type of the subscription.
		 * @access public
		 * @api
		 */
		public function getTrialPeriodType() {
			return $this->_sTrialPeriodType;
		}

		/**
		 * Configure the subscription object with a trial period price.
		 * @param int $iPeriod_ Trial period price to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setTrialPeriodPrice( $iTrialPeriodPrice_ ) {
			if ( ! is_integer( $iTrialPeriodPrice_ ) ) {
				throw new Exception( 'Subscription.Period.Price.Invalid', 'invalid trial period price: ' . $iTrialPeriodPrice_ );
			}
			$this->_iTrialPeriodPrice = $iTrialPeriodPrice_;
			return $this;
		}

		/**
		 * Get the period price of the subscription.
		 * @return int The period price of the subscription.
		 * @access public
		 * @api
		 */
		public function getTrialPeriodPrice() {
			return $this->_iPeriodPrice;
		}

		/**
		 * Configure the subscription date on which it should start.
		 * @param string $sStartDate_ The start date to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setStartDate( $sStartDate_ ) {
			if ( ! is_string( $sStartDate_ ) ) {
				throw new Exception( 'Subscription.Date.Start.Invalid', 'invalid start date: ' . $sStartDate_ );
			}
			$this->_sStartDate = $sStartDate_;
			return $this;
		}

		/**
		 * Get the start date of the subscription.
		 * @return string The start date of the subscription.
		 * @access public
		 * @api
		 */
		public function getStartDate() {
			return $this->_sStartDate;
		}

		/**
		 * Configure the date on which the subscription should end.
		 * @param string $sEndDate_ The end date to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setEndDate( $sEndDate_ ) {
			if ( ! is_string( $sEndDate_ ) ) {
				throw new Exception( 'Subscription.Date.End.Invalid', 'invalid end date: ' . $sEndDate_ );
			}
			$this->_sEndDate = $sEndDate_;
			return $this;
		}

		/**
		 * Get the end date of the subscription.
		 * @return string The end date of the subscription.
		 * @access public
		 * @api
		 */
		public function getEndDate() {
			return $this->_sEndDate;
		}

		/**
		 * Get the status of the subscription.
		 * @return string The end date of the subscription.
		 * @access public
		 * @api
		 */
		public function getStatus() {
			return $this->_sStatus;
		}

		/**
		 * Registers the subscription with the CardGate payment gateway.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function register() {
			$aData = [
				'site_id' 				=> $this->_iSiteId,
				'currency_id'			=> $this->_sCurrency,
				'url_callback'			=> $this->_sCallbackUrl,
				'url_success'			=> $this->_sSuccessUrl,
				'url_failure'			=> $this->_sFailureUrl,
				'url_pending'			=> $this->_sPendingUrl,
				'description'			=> $this->_sDescription,
				'reference'				=> $this->_sReference,
				'recurring'				=> true,
				'period'				=> $this->_iPeriod,
				'period_type'			=> $this->_sPeriodType,
				'period_price'			=> $this->_iPeriodPrice,
				'initial_payment'		=> $this->_iInitialPayment,
				'trial_period'			=> $this->_iTrialPeriod,
				'trial_period_type'		=> $this->_sTrialPeriodType,
				'trial_period_price'	=> $this->_iTrialPeriodPrice,
				'start_date'			=> $this->_sStartDate,
				'end_date'				=> $this->_sEndDate,
			];
			if ( ! is_null( $this->_oConsumer ) ) {
				$aData['email'] = $this->_oConsumer->getEmail();
				$aData['phone'] = $this->_oConsumer->getPhone();
				$aData['consumer'] = array_merge(
					$this->_oConsumer->address()->getData(),
					$this->_oConsumer->shippingAddress()->getData( 'shipto_' )
				);
				$aData['country_id'] = $this->_oConsumer->address()->getCountry();
			}
			if ( ! is_null( $this->_oCart ) ) {
				$aData['cartitems'] = $this->_oCart->getData();
			}

			$sResource = 'subscription/register/';

			if ( ! empty( $this->_oPaymentMethod ) ) {
				$aData['pt'] = $this->_oPaymentMethod->getId();
				$aData['issuer'] = $this->_sIssuer;
			}

			$aData = array_filter( $aData ); // remove NULL values
			$aResult = $this->_oClient->doRequest( $sResource, $aData, 'POST' );

			if (
				empty( $aResult )
				|| empty( $aResult['subscription'] )
			) {
				throw new Exception( 'Subscription.Request.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo( TRUE, FALSE ) );
			}
			$this->_sId = $aResult['subscription'];
			if (
				isset( $aResult['subscription']['action'] )
				&& 'redirect' == $aResult['subscription']['action']
			) {
				$this->_sActionUrl = $aResult['subscription']['url'];
			}

			return $this;
		}

		/**
		 * Change the subscription status.
		 * @return bool Whether the status change succeeded.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function changeStatus( $sStatus_ ) {

			if ( empty( $this->_sId ) ) {
				throw new Exception( 'Subscription.Request.Invalid', 'invalid subscription id' );
			}

			if ( ! in_array( $sStatus_, [ 'reactivate' , 'suspend', 'cancel', 'deactivate' ] ) ) {
				throw new Exception( 'Subscription.Status.Invalid', 'invalid subscription status provided' );
			}

			$aData = [
				'subscription_id'		=> $this->_sId,
				'description'			=> $this->_sDescription,
			];

			$sResource = 'subscription/' . $sStatus_ . '/';

			$aData = array_filter( $aData ); // remove NULL values
			$aResult = $this->_oClient->doRequest( $sResource, $aData, 'POST' );

			if ( FALSE == $aResult['success'] ) {
				// oClient will have thrown an error if there was a proper error from the API, so this is weird!
				throw new Exception( 'Subscription.Request.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo( TRUE, FALSE ) );
			}

			return TRUE;
		}

	}

}
