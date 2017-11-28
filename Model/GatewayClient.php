<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model;

use Cardgate\Payment\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Cardgate client wrapper, proxies all calls to a fully configured CardGate client instance.
 * The CardGate client is installed as a dependency by Composer.
 * @see https://github.com/cardgate/cardgate-clientlib-php
 */
class GatewayClient {

	/**
	 * @var \cardgate\api\Client
	 */
	private $_oClient;

	/**
	 * @var int
	 */
	private $_iSiteId;

	/**
	 * @var string
	 */
	private $_sSiteKey;

	/**
	 * @param \Cardgate\Payment\Model\Config
	 * @param \Magento\Framework\Encryption\EncryptorInterface
	 * @param \Magento\Framework\Locale\Resolver
	 * @param \Magento\Framework\App\ProductMetadata
	 * @param \Magento\Framework\Module\ModuleListInterface
	 */
	public function __construct( Config $oConfig_, EncryptorInterface $oEncryptor_, Resolver $oLocaleResolver_, ProductMetadata $oMetaData_, ModuleListInterface $oModuleList_ ) {
		$this->_iSiteId = (int)$oConfig_->getGlobal( 'site_id' );
		$this->_sSiteKey = $oConfig_->getGlobal( 'site_key' );

		$sMerchantId = (int)$oConfig_->getGlobal( 'api_username' );
		$sApiKey = $oEncryptor_->decrypt( $oConfig_->getGlobal( 'api_password' ) );
		$bTestMode = !!$oConfig_->getGlobal( 'testmode' );
		if ( ! class_exists( '\cardgate\api\Client' ) ) {
			throw new \Exception( "cardgate client library not installed" );
		}
		$this->_oClient = new \cardgate\api\Client( $sMerchantId, $sApiKey, $bTestMode );

		$this->_oClient->setIp( self::_determineIp() );
		@list( $sLanguage, $sCountry ) = explode( '_', $oLocaleResolver_->getLocale() );
		if ( ! empty( $sLanguage ) ) {
			$this->_oClient->setLanguage( $sLanguage );
		}
		$this->_oClient->version()->setPlatformName( 'PHP' );
		$this->_oClient->version()->setPlatformVersion( phpversion() );
		$this->_oClient->version()->setPluginName( 'Magento/cardgate-clientlib-php' );
		$this->_oClient->version()->setPluginVersion( $oMetaData_->getVersion() . '/' . $oModuleList_->getOne( 'Cardgate_Payment' )['setup_version'] );
	}

	/**
	 * Magic function proxying all calls to the client lib instance.
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call( $sMethod_, $aArgs_ ) {
		if ( is_callable( array( $this->_oClient, $sMethod_ ) ) ) {
			return call_user_func_array( array( $this->_oClient, $sMethod_ ), $aArgs_ );
		} else {
			throw new \Exception( "invalid call to {$sMethod_}" );
		}
	}

	/**
	 * Returns the configured site id.
	 * @return int
	 */
	public function getSiteId() {
		return $this->_iSiteId;
	}

	/**
	 * Returns the configured site key.
	 * @return string
	 */
	public function getSiteKey() {
		return $this->_sSiteKey;
	}

	/**
	 * Get the ip address of the client.
	 * @return string
	 */
	private static function _determineIp() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return $_SERVER['REMOTE_ADDR'];
		} else {
			return '0.0.0.0';
		}
	}

}
