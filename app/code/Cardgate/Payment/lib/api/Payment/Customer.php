<?php
/**
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      DBS B.V. <info@curopayments.com>
 * @copyright   DBS B.V.
 * @link        https://www.curopayments.com/
 */
namespace curopayments\api\Payment;

use curopayments\util\Singleton;
use curopayments\api\Exception;
use curopayments\api\ISerializedData;

class Customer implements ISerializedData {

	use Singleton;

	/**
	 * The customer's first name
	 * @var string
	 * @access protected
	 */
	protected $_sFirstName = NULL;

	/**
	 * The customer's last name
	 * @var string
	 * @access protected
	 */
	protected $_sLastName = NULL;

	/**
	 * The customer's address
	 * @var string
	 * @access protected
	 */
	protected $_sAddress = NULL;

	/**
	 * The customer's postal code
	 * @var string
	 * @access protected
	 */
	protected $_sPostalCode = NULL;

	/**
	 * The customer's city
	 * @var string
	 * @access protected
	 */
	protected $_sCity = NULL;

	/**
	 * The customer's country
	 * @var string
	 * @access protected
	 */
	protected $_sCountry = NULL;

	/**
	 * The customer's emailaddress
	 * @var string
	 * @access protected
	 */
	protected $_sEmail = NULL;

	/**
	 * The customer's phone number
	 * @var string
	 * @access protected
	 */
	protected $_sPhoneNumber = NULL;

	/**
	 * The customer's ip address
	 * @var string
	 * @access protected
	 */
	protected $_sIpAddress = NULL;


	/**
	 * The constructor
	 * @access public
	 */
	public function __construct( $sIpAddress_ = NULL ) {
		if ( is_null( $sIpAddress_ ) ) {
			$this->_sIpAddress = $this->getIpAddress();
		} else {
			$this->_sIpAddress = $sIpAddress_;
		}
	}


	/**
	 * Retrieve all available customer data for a transsaction
	 * @return A customer object containing available data
	 * @throws curopayments\api\Exception 
	 */
	public function getData() {
		return $this;
	}


	/**
	 * Retrieve the Ip Address of the Customer for this payment
	 * @return string The Ip Address of the Customer
	 * @throws curopayments\api\Exception 
	 */
	public function getIpAddress() {

		if ( ! empty( $this->_sIpAddress ) ) {
			return $this->_sIpAddress;
		} 

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$sIp = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$sIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$sIp = $_SERVER['REMOTE_ADDR'];
		}

		if ( empty( $sIp ) ) {
			throw new Exception( 'Payment.Customer.IpAddress', 'Unable to retrieve customer\'s Ip address' );
		}	

		return $sIp;
	}

	/**
	 * Set the First Name to use for this payment
	 * @param string $sFirstName_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid first name is supplied
	 */
	public function setFirstName( $sFirstName_ ) {

		if ( ! is_string( $sFirstName_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid first name: ' . $sFirstName_ );
		}

		$this->_sFirstName = $sFirstName_;
	}

	/**
	 * Get the First Name to use for this payment
	 * @param string $sFirstName_
	 * @access public
	 */
	public function getFirstName( $sFirstName_ ) {
		return $this->_sFirstName;
	}

	/**
	 * Set the Last Name to use for this payment
	 * @param string $sLastName_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid last name is supplied
	 */
	public function setLastName( $sLastName_ ) {

		if ( ! is_string( $sLastName_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid last name: ' . $sLastName_ );
		}

		$this->_sLastName = $sLastName_;
	}

	/**
	 * Get the Last Name to use for this payment
	 * @param string $sLastName_
	 * @access public
	 */
	public function getLastName( $sLastName_ ) {
		return $this->_sLastName;
	}

	/**
	 * Set the Address to use for this payment
	 * @param string $sAddress_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid address is supplied
	 */
	public function setAddress( $sAddress_ ) {

		if ( ! is_string( $sAddress_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid address: ' . $sAddress_ );
		}

		$this->_sAddress = $sAddress_;
	}

	/**
	 * Get the Address to use for this payment
	 * @param string $sAddress_
	 * @access public
	 */
	public function getAddress() {
		return $this->_sAddress;
	}

	/**
	 * Set the postal code to use for this payment
	 * @param string $sPostalCode_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid postal code is supplied
	 */
	public function setPostalCode( $sPostalCode_ ) {

		if ( ! is_string( $sPostalCode_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid postal code: ' . $sPostalCode_ );
		}

		$this->_sPostalCode = $sPostalCode_;
	}

	/**
	 * Get the Postal code to use for this payment
	 * @param string $sPostalCode_
	 * @access public
	 */
	public function getPostalCode() {
		return $this->_sPostalCode;
	}

	/**
	 * Set the City to use for this payment
	 * @param string $sCity_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid city is supplied
	 */
	public function setCity( $sCity_ ) {

		if ( ! is_string( $sCity_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid city: ' . $sCity_ );
		}

		$this->_sCity = $sCity_;
	}

	/**
	 * Get the City to use for this payment
	 * @param string $sCity_
	 * @access public
	 */
	public function getCity() {
		return $this->_sCity;
	}

	/**
	 * Set the Country to use for this payment
	 * @param string $sCountry_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid country is supplied
	 */
	public function setCountry( $sCountry_ ) {
		//RB: Maybe check for length 2??
		if ( ! is_string( $sCountry_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid country: ' . $sCountry_ );
		}

		$this->_sCountry = $sCountry_;
	}

	/**
	 * Get the Country to use for this payment
	 * @param string $sCountry_
	 * @access public
	 */
	public function getCountry() {
		return $this->_sCountry;
	}

	/**
	 * Set the emailaddress to use for this payment
	 * @param string $sEmail_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid emailaddress is supplied
	 */
	public function setEmail( $sEmail_ ) {

		if ( ! is_string( $sEmail_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid emailaddress: ' . $sEmail_ );
		}

		$this->_sEmail = $sEmail_;
	}

	/**
	 * Get the Email to use for this payment
	 * @param string $sEmail_
	 * @access public
	 */
	public function getEmail() {
		return $this->_sEmail;
	}

	/**
	 * Set the phone number to use for this payment
	 * @param string $sPhoneNumber_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid phone number is supplied
	 */
	public function setPhoneNumber( $sPhoneNumber_ ) {

		if ( ! is_string( $sPhoneNumber_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid phone number: ' . $sPhoneNumber_ );
		}

		$this->_sPhoneNumber = $sPhoneNumber_;
	}

	/**
	 * Get the PhoneNumber to use for this payment
	 * @param string $sPhoneNumber_
	 * @access public
	 */
	public function getPhoneNumber() {
		return $this->_sPhoneNumber;
	}


	/**
	 * Set the IP address to use for this payment
	 * @param string $sIpAddress_
	 * @access public
	 * @throws curopayments\api\Exception if an invalid IP address is supplied
	 */
	public function setIpAddress( $sIpAddress_ ) {

		if ( ! is_string( $sIpAddress_ ) ) {
			throw new Exception( 'Payment.Customer.Invalid', 'Invalid IP address: ' . $sIpAddress_ );
		}

		$this->_sIpAddress = $sIpAddress_;
	}
	/**
	 * {@inheritDoc}
	 * @see \curopayments\api\ISerializedData::getArray()
	 */
	public function getArray () {
		// TODO Auto-generated method stub
		
	}



}