<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model;

use curopayments\api\Client;
use Cardgate\Payment\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order\Address;

/**
 * Gateway Client wrapper for CUROPayments RESTful API library
 *
 * @author DBS B.V.
 * @package Magento2
 */
class GatewayClient extends Client {

	/**
	 *
	 * @var \Cardgate\Payment\Model\Config
	 */
	private $config;

	/**
	 *
	 * @var \Magento\Framework\Encryption\EncryptorInterface
	 */
	private $encryptor;

	/**
	 *
	 * @param Config $config
	 * @param EncryptorInterface $encryptor
	 */
	public function __construct ( Config $config, EncryptorInterface $encryptor ) {
		$this->config = $config;
		$this->encryptor = $encryptor;

		parent::__construct( boolval( $this->config->getGlobal( 'testmode' ) ) );
		$this->setMerchantName( $this->config->getGlobal( 'api_username' ) );
		$this->setKey( $this->encryptor->decrypt( $this->config->getGlobal( 'api_password' ) ) );
	}

	/**
	 * Get Global-config site ID
	 *
	 * @return mixed
	 */
	public function getSiteId () {
		return $this->config->getGlobal( 'site_id' );
	}

	/**
	 * Get RESTful API entry point
	 *
	 * {@inheritdoc}
	 *
	 * @see \curopayments\api\Client::getUrl()
	 */
	public function getUrl () {
		if ( isset( $_SERVER['CGP_API_URL'] ) && $_SERVER['CGP_API_URL'] != '' ) {
			return $_SERVER['CGP_API_URL'];
		} else {
			return parent::getUrl();
		}
	}

	/**
	 * Generate validation hash.
	 * Returns generated MD5 hash.
	 *
	 * @param boolean $testmode
	 * @param string $transactionId
	 * @param string $currency
	 * @param int $amount
	 * @param string $reference
	 * @param int $code
	 * @return string
	 */
	public function generateHash ( $testmode, $transactionId, $currency, $amount, $reference, $code ) {
		$key = $this->_sKey;
		return md5( ( $testmode ? 'TEST' : '' ) . "{$transactionId}{$currency}{$amount}{$reference}{$code}{$key}" );
	}

	/**
	 * Validate given hash with generated hash.
	 * Returns true if hash validates given values.
	 *
	 * @param string $hash
	 * @param boolean $testmode
	 * @param string $transactionId
	 * @param string $currency
	 * @param int $amount
	 * @param string $reference
	 * @param int $code
	 * @return boolean
	 */
	public function validateHash ( $hash, $testmode, $transactionId, $currency, $amount, $reference, $code ) {
		$hash2 = $this->generateHash( $testmode, $transactionId, $currency, $amount, $reference, $code );
		return ( $hash2 == $hash );
	}

	/**
	 * Retrieve the Ip Address of the Customer for this payment
	 *
	 * @return string The Ip Address of the Customer
	 * @throws curopayments\api\Exception
	 */
	public static function determineIp () {

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// check ip from share internet
			$sIp = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// to check ip is pass from proxy
			$sIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$sIp = $_SERVER['REMOTE_ADDR'];
		} else {
			$sIp = '0.0.0.0';
		}

		return $sIp;
	}

	/**
	 * Convert Magento2 Address to CUROPayments Consumer
	 *
	 * @param Address $address
	 * @param string $isShipping
	 * @return string[]
	 */
	public static function convertAddressToConsumer ( Address $address, $isShipping = false ) {
		$prefix = ( $isShipping ? 'shipto_' : '' );
		return [
			$prefix . 'firstname' => $address->getFirstname(),
			$prefix . 'lastname' => $address->getLastname(),
			$prefix . 'company' => $address->getCompany(),
			$prefix . 'address' => implode( PHP_EOL, $address->getStreet() ),
			$prefix . 'city' => $address->getCity(),
			$prefix . 'state' => $address->getRegion(),
			$prefix . 'zipcode' => $address->getPostcode(),
			$prefix . 'country_id' => $address->getCountryId(),
			$prefix . 'phone' => $address->getTelephone(),
			$prefix . 'email' => $address->getEmail()
		];
	}

}