<?php
/**
 * This class handles payment requests.
 *
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      DBS B.V. <info@curopayments.com>
 * @copyright   DBS B.V.
 * @link        https://www.curopayments.com/
 */

namespace curopayments\api\Payment;

use curopayments\util\Singleton;
use curopayments\api\Exception;
use curopayments\api\Client;
use curopayments\api\Payment\Response;
use curopayments\api\Cart;

/**
 * The base class where the functional transaction classes derive from
 * @abstract
 */
abstract class Request {

	use Singleton;

	/**
	 * The site ID on which the transaction should be registered
	 * @var integer
	 * @access protected
	 */
	protected $_iSiteId = NULL;

	/**
	 * The amount of the transaction in cents
	 * @var integer
	 * @access protected
	 */
	protected $_iAmount = NULL;

	/**
	 * The currency of the transaction in (ISO 4217)
	 * @var string
	 * @access protected
	 */
	protected $_sCurrency = NULL;

	/**
	 * The payment type for the transaction (eg. ideal, mistercash)
	 * @var string Set by extending classes, default set to NULL
	 * @access protected
	 */
	protected $_sPaymentType = NULL;

	/**
	 * The consumer IP address associated with the transaction
	 * @var string
	 * @access protected
	 */
	protected $_sIp = NULL;

	/**
	 * The URL to send payment callback updates to
	 * @var string
	 * @access protected
	 */
	protected $_sCallbackUrl = NULL;

	/**
	 * The URL to redirect to on success
	 * @var string
	 * @access protected
	 */
	protected $_sSuccessUrl = NULL;

	/**
	 * The URL to redirect to on failre
	 * @var string
	 * @access protected
	 */
	protected $_sFailureUrl = NULL;

	/**
	 * The URL to redirect to on pending
	 * @var string
	 * @access protected
	 */
	protected $_sPendingUrl = NULL;

	/**
	 * Whether or not this is going to be a recurrable transaction
	 * @var boolean
	 * @access protected
	 */
	protected $_bRecurring = FALSE;

	/**
	 * The client used for communication
	 * @access protected
	 */
	protected $_oClient;

	/**
	 * The customer data
	 * @access protected
	 */
	protected $_oCustomer = NULL;

	/**
	 * The shopping cart
	 * @access protected
	 */
	protected $_oCart = NULL;

	/**
	 * The constructor
	 * @param curopayments\api\Client $oClient_ The client to use
	 * @param integer $iSite_ (optional) Site ID
	 * @param integer $iAmount_ (optional) Amount in cents
	 * @param string $sCurrency_ (optional) Currency (ISO 4217)
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @throws curopayments\api\Exception if some parameter is incorrect
	 * @api
	 */
	public function __construct( Client $oClient_, $iSite_ = NULL, $iAmount_ = NULL, $sCurrency_ = NULL ) {

		$this->_oClient = $oClient_;

		if ( ! is_null( $iSite_ ) ) {
			$this->setSiteId( $iSite_ );
		}
		if ( ! is_null( $iAmount_ ) ) {
			$this->setAmount( $iAmount_ );
		}
		if ( ! is_null( $sCurrency_ ) ) {
			$this->setCurrency( $sCurrency_ );
		}
	}

	/**
	 * Validates the set data before sending it
	 * @return boolean TRUE on success
	 * @throws Exception
	 * @access protected
	 */
	protected function _validate() {
		foreach( [ '_iSiteId', '_iAmount', '_sCurrency' ] as $sMandatory ) {
			if ( is_null( $this->$sMandatory ) ) {
				$sVar = substr( $sMandatory, 2 );
				throw new Exception( 'Payment.Request' . $sVar . '.Missing', 'missing mandatory: ' . $sVar );
			}
		}
		return TRUE;
	}

	/**
	 * Get the payment type for this transaction
	 * @return string The payment type of this Transaction
	 * @access public
	 * @api
	 */
	public function getPaymentType() {
		$aClass = array_reverse( explode( '\\', get_called_class() ) );
		return strtolower( $aClass[0] );
	}

	/**
	 * Add the oppurtunity to add parameters to a register call
	 * @return array An array containing extra arguments to a register call
	 * @access protected
	 */
	protected function _addRegisterParams() {
		return [];
	}

	/**
	 * Set the Site ID to use for this transaction
	 * @param integer $iSite_ The Site ID
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @throws curopayments\api\Exception if an invalid Site ID is supplied
	 * @api
	 */
	public function setSiteId( $iSiteId_ ) {
		if (
			! is_integer( $iSiteId_ )
			|| $iSiteId_ < 1
		) {
			throw new Exception( 'Payment.Invalid.Invalid.SiteId', 'Invalid site ID: ' . $iSiteId_ );
		}

		$this->_iSiteId = $iSiteId_;
		return $this;
	}

	/**
	 * Get the Site ID
	 * @return integer The site ID associated with the transaction
	 * @access public
	 * @api
	 */
	public function getSiteId() {
		return $this->_iSiteId;
	}

	/**
	 * Set the amount for the transaction
	 * @param integer $iSite_ The Site ID
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @throws curopayments\api\Exception if an invalid amount is supplied
	 * @api
	 */
	public function setAmount( $iAmount_ ) {
		if (
			! is_integer( $iAmount_ )
			|| $iAmount_ < 1
		) {
			throw new Exception( 'Payment.Invalid.Amount', 'Invalid amount: ' . $iAmount_ );
		}

		$this->_iAmount = $iAmount_;
		return $this;
	}

	/**
	 * Get the amount
	 * @return integer The amount in cents for this transaction
	 * @access public
	 * @api
	 */
	public function getAmount() {
		return $this->_iAmount;
	}

	/**
	 * Set the amount for the transaction
	 * @param integer $iSite_ The Site ID
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @throws curopayments\api\Exception if an invalid amount is supplied
	 * @api
	 */
	public function setCurrency( $sCurrency_ ) {
		if (
			! is_string( $sCurrency_ )
			|| strlen( $sCurrency_ ) != 3
		) {
			throw new Exception( 'Payment.Invalid.Currency', 'Invalid currency: ' . $sCurrency_ );
		}

		$this->_sCurrency = $sCurrency_;
		return $this;
	}

	/**
	 * Get the currency
	 * @return integer The amount in cents for this transaction
	 * @access public
	 * @api
	 */
	public function getCurrency() {
		return $this->_sCurrency;
	}

	/**
	 * Set the IP address
	 * @param string The IP address of the consumer doing the transaction
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @api
	 * @throws curopayments\api\Exception if an invalid IP address is supplied
	 */
	public function setIp( $sIp_ ) {
		if (
			! is_string( $sIp_ )
			|| FALSE === filter_var( $sIp_, FILTER_VALIDATE_IP )
		) {
			throw new Exception( 'Payment.Invalid.Ip', 'Invalid IP address: ' . $sIp_ );
		}

		$this->_sIp = $sIp_;
		return $this;
	}

	/**
	 * Get the IP address
	 * @return string The consumer IP address
	 * @access public
	 * @api
	 */
	public function getIp() {
		return $this->_sIp;
	}

	/**
	 * Set the description
	 * @param string $sDescription_ The description
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @api
	 * @throws curopayments\api\Exception if an invalid description is supplied
	 */
	public function setDescription( $sDescription_ ) {
		if ( ! is_string( $sDescription_ ) ) {
			throw new Exception( 'Payment.Invalid.Description', 'Invalid description: ' . $sDescription_ );
		}
		$this->_sDescription = $sDescription_;
		return $this;
	}

	/**
	 * Get the description
	 * @return string The description
	 * @access public
	 * @api
	 */
	public function getDescription() {
		return $this->_sDescription;
	}

	/**
	 * Set the reference
	 * @param string $sReference_ The reference
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @api
	 * @throws curopayments\api\Exception if an invalid reference is supplied
	 */
	public function setReference( $sReference_ ) {
		if ( ! is_string( $sReference_ ) ) {
			throw new Exception( 'Payment.Invalid.Reference', 'Invalid description: ' . $sReference_ );
		}
		$this->_sReference = $sReference_;
		return $this;
	}

	/**
	 * Get the description
	 * @return string The description
	 * @access public
	 * @api
	 */
	public function getReference() {
		return $this->_sReference;
	}

	/**
	 * Set the callback URL
	 * @param string The URL to send callbacks to
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @api
	 * @throws curopayments\api\Exception if an invalid callback URL is supplied
	 */
	public function setCallbackUrl( $sUrl_ ) {
		if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
			throw new Exception( 'Payment.Invalid.CallbackUrl', 'Invalid URL: ' . $sUrl_ );
		}

		$this->_sCallbackUrl = $sUrl_;
		return $this;
	}

	/**
	 * Set the success URL
	 * @param string The URL to send successful transaction redirects
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @api
	 * @throws curopayments\api\Exception if an invalid success URL is supplied
	 */
	public function setSuccessUrl( $sUrl_ ) {
		if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
			throw new Exception( 'Payment.Invalid.SuccessUrl', 'Invalid URL: ' . $sUrl_ );
		}

		$this->_sSuccessUrl = $sUrl_;
		return $this;
	}

	public function getSuccessUrl() {
		return $this->_sSuccessUrl;
	}

	/**
	 * Set the failure URL
	 * @param string The URL to send failed transaction redirects
	 * @return curopayments\api\Payment\Request
	 * @access public
	 * @api
	 * @throws curopayments\api\Exception if an invalid failure URL is supplied
	 */
	public function setFailureUrl( $sUrl_ ) {
		if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
			throw new Exception( 'Payment.Invalid.FailureUrl', 'Invalid URL: ' . $sUrl_ );
		}

		$this->_sFailureUrl = $sUrl_;
		return $this;
	}

	/**
	 * Sets whether this is a recurrable transactions
	 * @param boolean $bRecurring_
	 * @return curopayments\api\Payment\Request
	 * @throws curopayments\api\Exception when value is incorrect
	 * @api
	 */
	public function setRecurring( $bRecurring_ = TRUE ) {
		if ( ! is_bool( $bRecurring_ ) ) {
			throw new Exception( 'Payment.Invalid.Recurring', 'Not a boolean: ' . $bRecurring_ );
		}
		$this->_bRecurring = boolval( $bRecurring_ );
		return $this;
	}

	/**
	 * Get recurring flag for this transaction
	 * @return boolean Recurring flag
	 */
	public function getRecurring() {
		return $this->_bRecurring;
	}

	/**
	 * Add customer information to the payment request
	 * @param Customer $oCustomer_ The customer object
	 * @api
	 * @access public
	 */
	public function setCustomer( Customer $oCustomer_ ) {
		$this->_oCustomer = $oCustomer_;
	}

}