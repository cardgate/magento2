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
	 * CardGate client object.
	 */
	final class Client {

		/**
		 * Client version.
		 */
		const CLIENT_VERSION = "1.1.22";

		/**
		 * Url to use for production.
		 */
		const URL_PRODUCTION = 'https://secure.curopayments.net/rest/v1/curo/';

		/**
		 * Url to use for testing.
		 */
		const URL_STAGING = 'https://secure-staging.curopayments.net/rest/v1/curo/';

		/**
		 * Toggle testmode variable.
		 * @var bool
		 * @access private
		 */
		private $_bTestmode;

		/**
		 * The merchant id to use for authentication.
		 * @var int
		 * @access private
		 */
		private $_iMerchantId;

		/**
		 * The secret key to use for authentication.
		 * @var string
		 * @access private
		 */
		private $_sKey;

		/**
		 * The consumer IP address associated with the client.
		 * @var string
		 * @access private
		 */
		private $_sIp = NULL;

		/**
		 * The language to use when communicating with the API.
		 * @var string
		 * @access private
		 */
		private $_sLanguage = NULL;

		/**
		 * The version resource.
		 * @var resource\Version
		 * @access private
		 */
		private $_oVersion = NULL;

		/**
		 * The transactions resource.
		 * @var resource\Transactions
		 * @access private
		 */
		private $_oTransactions = NULL;

		/**
		 * The subscriptions resource.
		 * @var resource\Subscriptions
		 * @access private
		 */
		private $_oSubscriptions = NULL;

		/**
		 * The consumers resource.
		 * @var resource\Consumers
		 * @access private
		 */
		private $_oConsumers = NULL;

		/**
		 * The methods resource.
		 * @var resource\Methods
		 * @access private
		 */
		private $_oMethods = NULL;

		/**
		 * Debug level. 0 = None, 1 = Include result in errors, 2 = Verbose CURL calls.
		 * @var int
		 * @access private
		 */
		const DEBUG_NONE    = 0;
		const DEBUG_RESULTS = 1;
		const DEBUG_VERBOSE = 2;

		private $_iDebugLevel = 0;

		/**
		 * Last request and result for debugging.
		 * @var string
		 * @access private
		 */
		private $_sLastRequest = NULL;
		private $_sLastResult = NULL;

		/**
		 * The constructor.
		 * @param int $iMerchantId_ The merchant id for the client.
		 * @param string $sKey_ The merchant API key for the client.
		 * @param bool $bTestmode_ Toggle testmode for the client.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function __construct( $iMerchantId_, $sKey_, $bTestmode_ = FALSE ) {
			$this->setMerchantId( $iMerchantId_ )->setKey( $sKey_ )->setTestmode( $bTestmode_ );
		}

		/**
		 * Prevent leaking info in dumps.
		 * @ignore
		 */
		public function __debugInfo() {
			return [
				'Version'       => $this->_oVersion,
				'Testmode'    => $this->_bTestmode,
				'DebugLevel'  => $this->_iDebugLevel,
				'iMerchantId' => $this->_iMerchantId,
				'API_URL'     => $this->getUrl(),
				'LastRequest' => $this->_sLastRequest,
				'LastResult'  => $this->_sLastResult
			];
		}

		/**
		 * Toggle testmode.
		 * @param bool $bTestmode_ Enable or disable testmode for this client.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setTestmode( $bTestmode_ ) {
			if ( ! is_bool( $bTestmode_ ) ) {
				throw new Exception( 'Client.Testmode.Invalid', 'invalid testmode: ' . $bTestmode_ );
			}
			$this->_bTestmode = $bTestmode_;
			return $this;
		}

		/**
		 * Get currenct testmode setting.
		 * @return bool The current testmode setting
		 * @access public
		 * @api
		 */
		public function getTestmode() {
			return $this->_bTestmode;
		}

		/**
		 * Set debug level.
		 * @param int $iDebugLevel_ Level: 0 = None, 1 = Include request/resule in errors, 2 = Verbose cURL calls.
		 * @return $this
		 * @access public
		 * @api
		 */
		public function setDebugLevel( $iLevel_ ) {
			$this->_iDebugLevel = $iLevel_;
			return $this;
		}

		/**
		 * Get current debug level setting.
		 * @return int The current debug level.
		 * @access public
		 * @api
		 */
		public function getDebugLevel() {
			return $this->_iDebugLevel;
		}

		/**
		 * Get debug information according to debug level.
		 * @param bool $bStartWithNewLine_ Optional flag to indicate the info should start with a new-line.
		 * @param bool $bAddResult_ Optional flag to indicate the result should be included too.
		 * @return string Debug info or empty if level = 0.
		 * @access public
		 * @api
		 */
		public function getDebugInfo( $bStartWithNewLine_ = TRUE, $bAddResult_ = TRUE ) {
			if ( $this->getDebugLevel() > self::DEBUG_NONE ) {
				$sResult = ( $bStartWithNewLine_ ? PHP_EOL : '' );
				$sResult .= 'Request: ' . $this->getLastRequest();
				if ( $bAddResult_ ) {
					$sResult .= PHP_EOL . 'Result: ' . $this->getLastResult();
				}
				return $sResult;
			} else {
				return '';
			}
		}

		/**
		 * Configure the client object with a merchant id.
		 * @param int $iMerchantId_ Merchant id to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setMerchantId( $iMerchantId_ ) {
			if ( ! is_integer( $iMerchantId_ ) ) {
				throw new Exception( 'Client.MerchantId.Invalid', 'invalid merchant: ' . $iMerchantId_ );
			}
			$this->_iMerchantId = $iMerchantId_;
			return $this;
		}

		/**
		 * Get the merchant id associated with this client.
		 * @return int The merchant id associated with this client
		 * @access public
		 * @api
		 */
		public function getMerchantId() {
			return $this->_iMerchantId;
		}

		/**
		 * Set the Merchant API key to authenticate the transaction request with.
		 * @param string $sKey_ The merchant API key to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setKey( $sKey_ ) {
			if ( ! is_string( $sKey_ ) ) {
				throw new Exception( 'Client.Key.Invalid', 'invalid merchant key: ' . $sKey_ );
			}
			$this->_sKey = $sKey_;
			return $this;
		}

		/**
		 * Get the Merchant API key to authenticate the transaction request with.
		 * @return string The merchant API key.
		 * @access public
		 * @api
		 */
		public function getKey() {
			return $this->_sKey;
		}

		/**
		 * Set the IP address.
		 * @param string The IP address of the consumer.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setIp( $sIp_ ) {
			if (
				! is_string( $sIp_ )
				|| FALSE === filter_var( $sIp_, FILTER_VALIDATE_IP ) // NOTE ipv6
			) {
				throw new Exception( 'Client.Ip.Invalid', 'invalid IP address: ' . $sIp_ );
			}
			$this->_sIp = $sIp_;
			return $this;
		}

		/**
		 * Get the IP address.
		 * @return string The consumer IP address.
		 * @access public
		 * @api
		 */
		public function getIp() {
			return $this->_sIp;
		}

		/**
		 * Configure the language to use.
		 * @param string $sLanguage_ The language to set.
		 * @return $this
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setLanguage( $sLanguage_ ) {
			if ( ! is_string( $sLanguage_ ) ) {
				throw new Exception( 'Client.Language.Invalid', 'invalid language: ' . $sLanguage_ );
			}
			$this->_sLanguage = $sLanguage_;
			return $this;
		}

		/**
		 * Get the language the client is configured with.
		 * @return string The language the client is configured with.
		 * @access public
		 * @api
		 */
		public function getLanguage() {
			return $this->_sLanguage;
		}

		/**
		 * Get the URL to use with this connection, depending on testmode settings.
		 * @return string The URL to use
		 * @access public
		 * @api
		 */
		public function getUrl() {
			if ( ! empty( $_SERVER['CG_API_URL'] ) ) {
				return $_SERVER['CG_API_URL'];
			} else {
				return ( $this->getTestmode() ? self::URL_STAGING : self::URL_PRODUCTION );
			}
		}

		/**
		 * Get the last request sent to the API.
		 * @return string The request string.
		 * @access public
		 * @api
		 */
		public function getLastRequest() {
			return (string) $this->_sLastRequest;
		}

		/**
		 * Get the last result from an API call.
		 * @return string The result string.
		 * @access public
		 * @api
		 */
		public function getLastResult() {
			return (string) $this->_sLastResult;
		}

		/**
		 * Pull the config from the API using a token provided by the site setup button in the backoffice.
		 * @return array Returns an array with settings.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function pullConfig( $sToken_ ) {
			if ( ! is_string( $sToken_ ) ) {
				throw new Exception( 'Client.Token.Invalid', 'invalid token for settings pull: ' . $sToken_ );
			}
			$sResource = "pullconfig/{$sToken_}/";
			return $this->doRequest($sResource);
		}

		/**
		 * Accessor for the versioning resource.
		 * @return resource\Version
		 * @access public
		 * @api
		 */
		public function version() {
			if ( NULL == $this->_oVersion ) {
				$this->_oVersion = new resource\Version();
			}
			return $this->_oVersion;
		}

		/**
		 * Accessor for the transactions resource.
		 * @return resource\Transactions
		 * @access public
		 * @api
		 */
		public function transactions() {
			if ( NULL == $this->_oTransactions ) {
				$this->_oTransactions = new resource\Transactions( $this );
			}
			return $this->_oTransactions;
		}

		/**
		 * Accessor for the subscriptions resource.
		 * @return resource\Subscriptions
		 * @access public
		 * @api
		 */
		public function subscriptions() {
			if ( NULL == $this->_oSubscriptions ) {
				$this->_oSubscriptions = new resource\Subscriptions( $this );
			}
			return $this->_oSubscriptions;
		}

		/**
		 * Accessor for the consumers resource.
		 * @return resource\Consumers
		 * @access public
		 * @api
		 */
		public function consumers() {
			if ( NULL == $this->_oConsumers ) {
				$this->_oConsumers = new resource\Consumers( $this );
			}
			return $this->_oConsumers;
		}

		/**
		 * Accessor for the payment methods resource.
		 * @return resource\Methods
		 * @access public
		 * @api
		 */
		public function methods() {
			if ( NULL == $this->_oMethods ) {
				$this->_oMethods = new resource\Methods( $this );
			}
			return $this->_oMethods;
		}

		/**
		 * Send a request to the CardGate API.
		 * @param string $sResource_ The resource to call.
		 * @param array $aData_ Optional data to use for the call.
		 * @param string $sHttpMethod_ The http method to use (GET or POST, which is the default).
		 * @return array An array with request results.
		 * @throws Exception
		 */
		public function doRequest( $sResource_, $aData_ = NULL, $sHttpMethod_ = 'POST' ) {

			if ( ! in_array( $sHttpMethod_, [ 'GET', 'POST' ] ) ) {
				throw new Exception( 'Client.HttpMethod.Invalid', 'invalid http method: ' . $sHttpMethod_ );
			}

			$sUrl = $this->getUrl() . $sResource_;
			if ( is_array( $aData_ ) ) {
				$aData_['ip'] = $this->getIp();
				$aData_['language_id'] = $this->getLanguage();
			} elseif ( is_null( $aData_ ) ) {
				$aData_ = [ 'ip' => $this->getIp(), 'language_id' => $this->getLanguage() ];
			} else {
				throw new Exception( 'Client.Data.Invalid', 'invalid data: ' . $aData_ );
			}

			if ( NULL !== $this->_oVersion ) {
				$aData_ = array_merge( $aData_, $this->_oVersion->getData() );
			}

			$rCh = curl_init();
			curl_setopt( $rCh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
			curl_setopt( $rCh, CURLOPT_USERPWD, $this->_iMerchantId . ':' . $this->_sKey );
			curl_setopt( $rCh, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $rCh, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $rCh, CURLOPT_HEADER, FALSE );
			curl_setopt( $rCh, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Accept: application/json'
			] );
			if ( $this->_bTestmode ) {
				curl_setopt( $rCh, CURLOPT_SSL_VERIFYPEER, FALSE );
				curl_setopt( $rCh, CURLOPT_SSL_VERIFYHOST, 0 );
			} else {
				curl_setopt( $rCh, CURLOPT_SSL_VERIFYPEER, TRUE ); // verify SSL peer
				curl_setopt( $rCh, CURLOPT_SSL_VERIFYHOST, 2 ); // check for valid common name and verify host
			}

			if ( 'POST' == $sHttpMethod_ ) {
				$this->_sLastRequest = json_encode( $aData_, JSON_PARTIAL_OUTPUT_ON_ERROR );
				curl_setopt( $rCh, CURLOPT_URL, $sUrl );
				curl_setopt( $rCh, CURLOPT_POST, TRUE );
				curl_setopt( $rCh, CURLOPT_POSTFIELDS, $this->_sLastRequest );
				$this->_sLastRequest = "[POST $sUrl] " . $this->_sLastRequest;
			} else {
				$this->_sLastRequest = $sUrl
					. ( FALSE === strchr( $sUrl, '?' ) ? '?' : '&' )
					. http_build_query( $aData_ )
				;
				curl_setopt( $rCh, CURLOPT_URL, $this->_sLastRequest );
			}

			if ( self::DEBUG_VERBOSE == $this->_iDebugLevel ) {
				curl_setopt( $rCh, CURLOPT_VERBOSE, TRUE );
			}

			$this->_sLastResult = curl_exec( $rCh );
			if ( FALSE == $this->_sLastResult ) {
				$sError = curl_error( $rCh );
				curl_close( $rCh );
				throw new Exception( 'Client.Request.Curl.Error', $sError );
			} else {
				curl_close( $rCh );
			}
			if ( NULL === ( $aResults = json_decode( $this->_sLastResult, TRUE ) ) ) {
				throw new Exception( 'Client.Request.JSON.Invalid', 'remote gave invalid JSON: ' . $this->_sLastResult );
			}
			if ( isset( $aResults['error'] ) ) {
				throw new Exception( 'Client.Request.Remote.' . $aResults['error']['code'], $aResults['error']['message']	. $this->getDebugInfo()	);
			}

			return $aResults;
		}

	}

}
