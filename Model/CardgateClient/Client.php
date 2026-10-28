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
     * CardGate client object.
     */
    class Client
    {

        /**
         * Client version.
         */
        public const CLIENT_VERSION = "0";

        /**
         * Url to use for production.
         */
        public const URL_PRODUCTION = 'https://secure.curopayments.net/rest/v1/curo/';

        /**
         * Url to use for testing.
         */
        public const URL_STAGING = 'https://secure-staging.curopayments.net/rest/v1/curo/';

        /**
         * Toggle testmode variable.
         * @var bool
         */
        private $_bTestmode;

        /**
         * The merchant id to use for authentication.
         * @var int
         */
        private $_iMerchantId;

        /**
         * The secret key to use for authentication.
         * @var string
         */
        private $_sKey;

        /**
         * The consumer IP address associated with the client.
         * @var string
         */
        private $_sIp = null;

        /**
         * The language to use when communicating with the API.
         * @var string
         */
        private $_sLanguage = null;

        /**
         * The version resource.
         * @var resources\Version
         */
        private $_oVersion = null;

        /**
         * The transactions resource.
         * @var resources\Transactions
         */
        private $_oTransactions = null;

        /**
         * The subscriptions resource.
         * @var resources\Subscriptions
         */
        private $_oSubscriptions = null;

        /**
         * The recurring resource.
         * @var resources\Recurring
         */
        private $_oRecurring = null;

        /**
         * The consumers resource.
         * @var resources\Consumers
         */
        private $_oConsumers = null;

        /**
         * The methods resource.
         * @var resources\Methods
         */
        private $_oMethods = null;

        /**
         * Debug level. 0 = None, 1 = Include result in errors, 2 = Verbose CURL calls.
         * @var int
         */
        public const DEBUG_NONE    = 0;
        public const DEBUG_RESULTS = 1;
        public const DEBUG_VERBOSE = 2;

        private $_iDebugLevel = 0;

        /**
         * Last request and result for debugging.
         * @var string
         */
        private $_sLastRequest = null;
        private $_sLastResult = null;

        /**
         * The constructor.
         * @param int $iMerchantId_ The merchant id for the client.
         * @param string $sKey_ The merchant API key for the client.
         * @param bool $bTestmode_ Toggle testmode for the client.
         * @throws ClientException
         */
        public function __construct($iMerchantId_, $sKey_, $bTestmode_ = false)
        {
            $this->setMerchantId($iMerchantId_)->setKey($sKey_)->setTestmode($bTestmode_);
        }

        /**
         * Prevent leaking info in dumps.
         * @ignore
         */
        public function __debugInfo()
        {
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
         * @throws ClientException
         */
        public function setTestmode($bTestmode_)
        {
            if (! is_bool($bTestmode_)) {
                throw new ClientException('Client.Testmode.Invalid', 'invalid testmode: ' . $bTestmode_);
            }
            $this->_bTestmode = $bTestmode_;
            return $this;
        }

        /**
         * Get currenct testmode setting.
         * @return bool The current testmode setting
         */
        public function getTestmode()
        {
            return $this->_bTestmode;
        }

        /**
         * Set debug level.
         * @param int $iDebugLevel_ Level: 0 = None, 1 = Include request/resule in errors, 2 = Verbose cURL calls.
         * @return $this
         */
        public function setDebugLevel($iLevel_)
        {
            $this->_iDebugLevel = $iLevel_;
            return $this;
        }

        /**
         * Get current debug level setting.
         * @return int The current debug level.
         */
        public function getDebugLevel()
        {
            return $this->_iDebugLevel;
        }

        /**
         * Get debug information according to debug level.
         * @param bool $bStartWithNewLine_ Optional flag to indicate the info should start with a new-line.
         * @param bool $bAddResult_ Optional flag to indicate the result should be included too.
         * @return string Debug info or empty if level = 0.
         */
        public function getDebugInfo($bStartWithNewLine_ = true, $bAddResult_ = true)
        {
            if ($this->getDebugLevel() > self::DEBUG_NONE) {
                $sResult = ( $bStartWithNewLine_ ? PHP_EOL : '' );
                $sResult .= 'Request: ' . $this->getLastRequest();
                if ($bAddResult_) {
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
         * @throws ClientException
         */
        public function setMerchantId($iMerchantId_)
        {
            if (! is_integer($iMerchantId_)) {
                throw new ClientException('Client.MerchantId.Invalid', 'invalid merchant: ' . $iMerchantId_);
            }
            $this->_iMerchantId = $iMerchantId_;
            return $this;
        }

        /**
         * Get the merchant id associated with this client.
         * @return int The merchant id associated with this client
         */
        public function getMerchantId()
        {
            return $this->_iMerchantId;
        }

        /**
         * Set the Merchant API key to authenticate the transaction request with.
         * @param string $sKey_ The merchant API key to set.
         * @return $this
         * @throws ClientException
         */
        public function setKey($sKey_)
        {
            if (! is_string($sKey_)) {
                throw new ClientException('Client.Key.Invalid', 'invalid merchant key: ' . $sKey_);
            }
            $this->_sKey = $sKey_;
            return $this;
        }

        /**
         * Get the Merchant API key to authenticate the transaction request with.
         * @return string The merchant API key.
         */
        public function getKey()
        {
            return $this->_sKey;
        }

        /**
         * Set the IP address.
         * @param string The IP address of the consumer.
         * @return $this
         * @throws ClientException
         */
        public function setIp($sIp_)
        {
            if (! is_string($sIp_)
                || false === filter_var($sIp_, FILTER_VALIDATE_IP) // NOTE ipv6
            ) {
                throw new ClientException('Client.Ip.Invalid', 'invalid IP address: ' . $sIp_);
            }
            $this->_sIp = $sIp_;
            return $this;
        }

        /**
         * Get the IP address.
         * @return string The consumer IP address.
         */
        public function getIp()
        {
            return $this->_sIp;
        }

        /**
         * Configure the language to use.
         * @param string $sLanguage_ The language to set.
         * @return $this
         * @throws ClientException
         */
        public function setLanguage($sLanguage_)
        {
            if (! is_string($sLanguage_)) {
                throw new ClientException('Client.Language.Invalid', 'invalid language: ' . $sLanguage_);
            }
            $this->_sLanguage = $sLanguage_;
            return $this;
        }

        /**
         * Get the language the client is configured with.
         * @return string The language the client is configured with.
         */
        public function getLanguage()
        {
            return $this->_sLanguage;
        }

        /**
         * Get the URL to use with this connection, depending on testmode settings.
         * @return string The URL to use
         */
        public function getUrl()
        {
            if (! empty(getenv('CG_API_URL'))) {
                return getenv('CG_API_URL');
            } else {
                return ( $this->getTestmode() ? self::URL_STAGING : self::URL_PRODUCTION );
            }
        }

        /**
         * Get the last request sent to the API.
         * @return string The request string.
         */
        public function getLastRequest()
        {
            return (string) $this->_sLastRequest;
        }

        /**
         * Get the last result from an API call.
         * @return string The result string.
         */
        public function getLastResult()
        {
            return (string) $this->_sLastResult;
        }

        /**
         * Pull the config from the API using a token provided by the site setup button in the backoffice.
         * @return array Returns an array with settings.
         * @throws ClientException
         */
        public function pullConfig($sToken_)
        {
            if (! is_string($sToken_)) {
                throw new ClientException('Client.Token.Invalid', 'invalid token for settings pull: ' . $sToken_);
            }
            $sResource = "pullconfig/{$sToken_}/";
            return $this->doRequest($sResource);
        }

        /**
         * Accessor for the versioning resource.
         * @return resources\Version
         */
        public function version()
        {
            if (null == $this->_oVersion) {
                $this->_oVersion = new resources\Version();
            }
            return $this->_oVersion;
        }

        /**
         * Accessor for the transactions resource.
         * @return resources\Transactions
         */
        public function transactions()
        {
            if (null == $this->_oTransactions) {
                $this->_oTransactions = new resources\Transactions($this);
            }
            return $this->_oTransactions;
        }

        /**
         * Accessor for the subscriptions resource.
         * @return resources\Subscriptions
         */
        public function subscriptions()
        {
            if (null == $this->_oSubscriptions) {
                $this->_oSubscriptions = new resources\Subscriptions($this);
            }
            return $this->_oSubscriptions;
        }

        /**
         * Accessor for the recurring resource.
         * @return resources\Recurring
         */
        public function recurring()
        {
            if (null == $this->_oRecurring) {
                $this->_oRecurring = new resources\Recurring($this);
            }
            return $this->_oRecurring;
        }

        /**
         * Accessor for the consumers resource.
         * @return resources\Consumers
         */
        public function consumers()
        {
            if (null == $this->_oConsumers) {
                $this->_oConsumers = new resources\Consumers($this);
            }
            return $this->_oConsumers;
        }

        /**
         * Accessor for the payment methods resource.
         * @return resources\Methods
         */
        public function methods()
        {
            if (null == $this->_oMethods) {
                $this->_oMethods = new resources\Methods($this);
            }
            return $this->_oMethods;
        }

        /**
         * Send a request to the CardGate API.
         * @param string $sResource_ The resource to call.
         * @param array $aData_ Optional data to use for the call.
         * @param string $sHttpMethod_ The http method to use (GET or POST, which is the default).
         * @return array An array with request results.
         * @throws ClientException
         */
        public function doRequest($sResource_, $aData_ = null, $sHttpMethod_ = 'POST')
        {

            if (! in_array($sHttpMethod_, [ 'GET', 'POST' ])) {
                throw new ClientException('Client.HttpMethod.Invalid', 'invalid http method: ' . $sHttpMethod_);
            }

            $sUrl = $this->getUrl() . $sResource_;
            if (is_array($aData_)) {
                $aData_['ip'] = $this->getIp();
                $aData_['language_id'] = $this->getLanguage();
            } elseif (($aData_ === null)) {
                $aData_ = [ 'ip' => $this->getIp(), 'language_id' => $this->getLanguage() ];
            } else {
                throw new ClientException('Client.Data.Invalid', 'invalid data: ' . $aData_);
            }

            if (null !== $this->_oVersion) {
                $aData_ = array_merge($aData_, $this->_oVersion->getData());
            }

            $rCh = curl_init();
            curl_setopt($rCh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($rCh, CURLOPT_USERPWD, $this->_iMerchantId . ':' . $this->_sKey);
            curl_setopt($rCh, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($rCh, CURLOPT_TIMEOUT, 60);
            curl_setopt($rCh, CURLOPT_HEADER, false);
            curl_setopt($rCh, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            if ($this->_bTestmode) {
                curl_setopt($rCh, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($rCh, CURLOPT_SSL_VERIFYHOST, 0);
            } else {
                curl_setopt($rCh, CURLOPT_SSL_VERIFYPEER, true); // verify SSL peer
                curl_setopt($rCh, CURLOPT_SSL_VERIFYHOST, 2); // check for valid common name and verify host
            }

            if ('POST' == $sHttpMethod_) {
                $this->_sLastRequest = json_encode($aData_, JSON_PARTIAL_OUTPUT_ON_ERROR);
                curl_setopt($rCh, CURLOPT_URL, $sUrl);
                curl_setopt($rCh, CURLOPT_POST, true);
                curl_setopt($rCh, CURLOPT_POSTFIELDS, $this->_sLastRequest);
                $this->_sLastRequest = "[POST $sUrl] " . $this->_sLastRequest;
            } else {
                $this->_sLastRequest = $sUrl
                    . ( false === strchr($sUrl, '?') ? '?' : '&' )
                    . http_build_query($aData_)
                ;
                curl_setopt($rCh, CURLOPT_URL, $this->_sLastRequest);
            }

            if (self::DEBUG_VERBOSE == $this->_iDebugLevel) {
                curl_setopt($rCh, CURLOPT_VERBOSE, true);
            }

            $this->_sLastResult = curl_exec($rCh);
            if (false == $this->_sLastResult) {
                $sError = curl_error($rCh);
                curl_close($rCh);
                throw new ClientException('Client.Request.Curl.Error', $sError);
            } else {
                curl_close($rCh);
            }
            if (null === ( $aResults = json_decode($this->_sLastResult, true) )) {
                throw new ClientException(
                    'Client.Request.JSON.Invalid',
                    'remote gave invalid JSON: ' . $this->_sLastResult
                );
            }
            if (isset($aResults['error'])) {
                throw new ClientException(
                    'Client.Request.Remote.' . $aResults['error']['code'],
                    $aResults['error']['message'] . $this->getDebugInfo()
                );
            }

            return $aResults;
        }
    }

}
