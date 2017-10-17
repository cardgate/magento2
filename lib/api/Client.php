<?php
/**
 * This is the client class that handles the communication with the CURO Payments platform.
 * An instance of a curopayments\api\Client is used with every class that needs communication.
 *
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      DBS B.V. <info@curopayments.com>
 * @copyright   DBS B.V.
 * @link        https://www.curopayments.com/
 */

namespace curopayments\api;

use curopayments\util\Singleton;
use curopayments\api\Exception;

class Client {

	const URL_PRODUCTION	= 'https://bob.secure.curopayments.dev/rest/v1/curo/';
	const URL_STAGING		= 'https://bob.secure.curopayments.dev/rest/v1/curo/';

	use Singleton;

	/**
	 * Toggle testmode variable
	 * @var boolean
	 * @access private
	 */
	private $_bTestmode;

	/**
	 * The merchant ID to use for authentication
	 * This or merchant account name should be set in order for the client to function
	 * @var integer
	 * @access private
	 */
	private $_iMerchantId;

	/**
	 * The merchant name to use for authentication
	 * @var string
	 * @access private
	 */
	private $_iMerchantName;

	/**
	 * The secret key to use for authentication
	 * @var string
	 * @access private
	 */
	protected $_sKey;

	/**
	 * The error number returned by Curl
	 * @var integer
	 * @access private
	 */
	private $_iErrorNo;

	/**
	 * The error message returned by Curl
	 * @var string
	 * @access private
	 */
	private $_sErrorMessage;

	/**
	 * The constructor
	 * @param boolean $bTestmode_ Toggle testmode for the client
	 * @return curopayments\api\Client
	 * @access public
	 * @throws curopayments\api\Exception if some parameter is incorrect
	 * @api
	 */
	function __construct( $bTestmode_ = FALSE) {

		$this->setTestmode( $bTestmode_ );
	}

	/**
	 * Toggle testmode for the client connection
	 * @param boolean $bTestmode_
	 * @throws Exception
	 * @access public
	 * @api
	 */
	public function setTestmode( $bTestmode_ ) {
		if ( ! is_bool( $bTestmode_ ) ) {
			throw new Exception( 'Client.Testmode.Invalid', 'Invalid testmode: ' . $bTestmode_ );
		}

		$this->_bTestmode = $bTestmode_;
		return $this;
	}

	/**
	 * Get currenct testmode setting for this client connection
	 * @return boolean The current testmode setting
	 * @access public
	 * @api
	 */
	public function getTestmode() {
		return $this->_bTestmode;
	}

	/**
	 * Set the Merchant ID to use for this transaction
	 * @param integer $iMerchantId_ Merchant ID
	 * @return curopayments\api\Transaction
	 * @access public
	 * @throws curopayments\api\Exception if an invalid Merchant ID is supplied
	 * @api
	 */
	public function setMerchantId( $iMerchantId_ ) {
		if (
			! is_integer( $iMerchantId_ )
			|| $iMerchantId_ < 1
		) {
			throw new Exception( 'Client.Merchant.Invalid', 'Invalid merchant: ' . $iMerchantId_ );
		}

		$this->_iMerchantId = $iMerchantId_;
		return $this;
	}

	/**
	 * Get the merchant ID associated with this client
	 * @return integer The merchant ID associated with this client
	 * @access public
	 */
	public function getMerchantId() {
		return $this->_iMerchantId;
	}

	/**
	 * Set the Merchant name to use for this transaction
	 * @param string $sMerchantId_ Merchant name
	 * @access public
	 * @throws curopayments\api\Exception if an invalid Merchant ID is supplied
	 * @api
	 */
	public function setMerchantName( $sMerchantName_ ) {
		if (
			! empty( $sMerchantName_ )
			&& ! is_string( $sMerchantName_ )
		) {
			throw new Exception( 'Client.Merchant.Invalid', 'Invalid merchant name: ' . $sMerchantName_ );
		}

		$this->_iMerchantName = $sMerchantName_;
		return $this;
	}

	/**
	 * Get the merchant anme associated with this client
	 * @return sting The merchant name associated with this client
	 * @access public
	 */
	public function getMerchantName() {
		return $this->_iMerchantName;
	}

	/**
	 * Set the Merchant API key to authenticate the transaction request with
	 * @param string $sKey_ The merchant API key
	 * @return curopayments\api\Client
	 * @access public
	 * @throws curopayments\api\Exception if an invalid Merchant API key is supplied
	 * @api
	 */
	public function setKey( $sKey_ ) {
		if (
			! is_string( $sKey_ )
			|| empty( $sKey_ )
		) {
			throw new Exception( 'Client.MerchantKey.Invalid', 'Invalid merchant key: ' . $sKey_ );
		}
		$this->_sKey = $sKey_;
		return $this;
	}

	/**
	 * Get the URL to use with this connection, depending on testmode settings among others.
	 * @return string The URL to use for this Client
	 * @access public
	 * @api
	 */
	public function getUrl() {
		return ( $this->getTestmode() ? self::URL_STAGING : self::URL_PRODUCTION );
	}

	/**
	 * Send a POST request to the CURO API
	 * @param string $sResource_
	 * @param array $aData_
	 * @return object JSON object containing the response
	 * @throws curopayments\api\Exception
	 */
	public function postRequest( $sResource_, $aData_ = NULL ) {
		$sUsername = '';
		if ( empty( $this->_iMerchantId ) ) {
			if ( empty( $this->_iMerchantName ) ) {
				throw new Exception( 'Client.Curl.Error', 'Invalid username' );
			} else {
				$sUsername = $this->_iMerchantName;
			}
		} else {
			$sUsername = $this->_iMerchantId;
		}

		if ( empty( $this->_sKey ) ) {
			throw new Exception( 'Client.Curl.Error', 'Invalid key' );
		}

		if ( is_null( $aData_ ) ) {
			$aData = [];
		}

		$rCh = curl_init();
		curl_setopt( $rCh, CURLOPT_URL, $this->getUrl() . $sResource_ );
		curl_setopt( $rCh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $rCh, CURLOPT_USERPWD, $sUsername . ':' . $this->_sKey );
		curl_setopt( $rCh, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $rCh, CURLOPT_TIMEOUT, 60 );
		curl_setopt( $rCh, CURLOPT_HEADER, FALSE );
		curl_setopt( $rCh, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		] );

		curl_setopt( $rCh, CURLOPT_POST, TRUE );
		curl_setopt( $rCh, CURLOPT_POSTFIELDS, json_encode( $aData_ ) );

		// YYY: Temp patch
		curl_setopt( $rCh, CURLOPT_SSL_VERIFYPEER, FALSE ); // verify SSL peer
		curl_setopt( $rCh, CURLOPT_SSL_VERIFYHOST, FALSE );
		//curl_setopt( $rCh, CURLOPT_SSL_VERIFYPEER, TRUE ); // verify SSL peer
		//curl_setopt( $rCh, CURLOPT_SSL_VERIFYHOST, 2 ); // check for valid common name and verify host

		$sResults = curl_exec( $rCh );

		if ( FALSE == $sResults ) {
			$this->_iErrorNo = curl_errno( $rCh );
			$this->_sErrorMessage = curl_error( $rCh );
			throw new Exception( 'Client.Curl.Error.' . $this->_iErrorNo, $this->_sErrorMessage );
		}

		curl_close( $rCh );

		if ( NULL === ( $oResults = json_decode( $sResults ) ) ) {
			throw new Exception( 'Client.postRequest.InvalidJSON', 'Remote gave invalid JSON: ' . $sResults );
		}

		if ( isset( $oResults->error ) ) {
			throw new Exception( 'Client.postRequest.Remote.' . $oResults->error->code, $oResults->error->message );
		}

		return $oResults;
	}

	/**
	 * Send a GET request to the CURO API
	 * @param string $sResource_
	 * @param array $aData_
	 * @return object JSON object containing the response
	 * @throws curopayments\api\Exception
	 */
	public function getRequest( $sResource_, $aData_ = NULL ) {


	    $sUsername = '';
		if ( empty( $this->_iMerchantId ) ) {
			if ( empty( $this->_iMerchantName ) ) {
				throw new Exception( 'Client.Curl.Error', 'Invalid username' );
			} else {
				$sUsername = $this->_iMerchantName;
			}
		} else {
			$sUsername = $this->_iMerchantId;
		}

		$sUrl = $this->getUrl();
		if ( ! is_null( $aData_ ) ) {
			$sDelim = ( FALSE === strchr( $sUrl, '?' ) ? '?' : '&' );
			$sUrl .= $sDelim . http_build_query( $aData_ );
		}

		$rCh = curl_init();
		curl_setopt( $rCh, CURLOPT_URL, $this->getUrl() . $sResource_ );
		curl_setopt( $rCh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $rCh, CURLOPT_USERPWD, $sUsername . ':' . $this->_sKey );
		curl_setopt( $rCh, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $rCh, CURLOPT_TIMEOUT, 60 );
		curl_setopt( $rCh, CURLOPT_HEADER, FALSE );
		curl_setopt( $rCh, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		] );

		// YYY: Temp patch
		curl_setopt( $rCh, CURLOPT_SSL_VERIFYPEER, FALSE ); // verify SSL peer
		curl_setopt( $rCh, CURLOPT_SSL_VERIFYHOST, FALSE ); // check for valid common name and verify host
		//curl_setopt( $rCh, CURLOPT_SSL_VERIFYPEER, TRUE ); // verify SSL peer
		//curl_setopt( $rCh, CURLOPT_SSL_VERIFYHOST, 2 ); // check for valid common name and verify host

		$sResults = curl_exec( $rCh );

		if ( FALSE == $sResults ) {
			$this->_iErrorNo = curl_errno( $rCh );
			$this->_sErrorMessage = curl_error( $rCh );
			throw new Exception( 'Client.Curl.Error.' . $this->_iErrorNo, $this->_sErrorMessage );
		}

		curl_close( $rCh );

		if ( NULL === ( $oResults = json_decode( $sResults ) ) ) {
			throw new Exception( 'Client.getRequest.InvalidJSON', 'Remote gave invalid JSON: ' . $sResults );
		}

		if ( isset( $oResults->error ) ) {
			throw new Exception( 'Client.getRequest.Remote.' . $oResults->error->code, $oResults->error->message );
		}

		return $oResults;
	}

	/**
	 */
	function __destruct() {
	}
}
