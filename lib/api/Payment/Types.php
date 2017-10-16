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
use curopayments\api\Client;
use curopayments\api\Response;

class Types implements \Iterator {

	use Singleton;

	private $_iIndex = -1;

	/**
	 * The site ID on which the transaction should be registered
	 * @var integer
	 * @access protected
	 */
	protected $_iSiteId = NULL;

	/**
	 * The payment types for the site (eg. ideal, mistercash)
	 * @var array
	 * @access protected
	 */
	protected $_aPaymentTypes = NULL;

	/**
	 * The client used for communication
	 * @access protected
	 */
	protected $_oClient;

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
	public function __construct( Client $oClient_, $iSite_ = NULL ) {
		
		$this->_oClient = $oClient_;

		if ( ! is_null( $iSite_ ) ) {
			$this->setSiteId( $iSite_ );
		}
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
			throw new Exception( 'Payment.SiteId.Invalid', 'Invalid site ID: ' . $iSiteId_ );
		}

		$this->_iSiteId = $iSiteId_;
		return $this;
	}


	/**
	 * Retrieve all available payment types for a site from the CURO payment gateway (eg. ideal, mistercash)
	 * @return A response object containing available payment types
	 * @throws curopayments\api\Exception 
	 */
	public function get() {

		if ( empty( $this->_iSiteId ) ) {
			throw new Exception( 'Payment.SiteId.Invalid', 'No site ID set!' );
		}

		if ( is_null( $this->_aPaymentTypes ) ) {
			$this->_aPaymentTypes = $this->_oClient->postRequest( 'paymenttypes/' . $this->_iSiteId . '/' );
		}
		return $this->_aPaymentTypes;
		// return new Response( $this->_aPaymentTypes );
	}


	/**
	 * Retrieve all available payment types for a site from the CURO payment gateway (eg. ideal, mistercash)
	 * @return A response object containing available payment types
	 * @throws curopayments\api\Exception 
	 */
	public function containsIdeal() {

		if ( is_null( $this->_aPaymentTypes ) ) {
			$this->get();
		}
		
		foreach( $this->_aPaymentTypes->paymenttypes->options as $iKey => $aValue ) {
			if ( $aValue->id == 'ideal' ) {
				return TRUE;
			} 
		}

		return FALSE;
	}


	/**#@+
	 * @ignore
	 */
	public function key() {
		return $this->_iIndex;
	}
	public function current() {
		return $this->_aPaymentTypes[ $this->_iIndex ];
	}
	public function valid() {
		return isset( $this->_aPaymentTypes[ $this->_iIndex ] );
	}
	public function rewind() {
		$this->_iIndex = 0;
	}
	public function next() {
		++$this->_iIndex;
	}
	/**#@-*/

}
