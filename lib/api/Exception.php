<?php
/**
 * curopayments\api\Exception adds a string-code to the original Exception class
 * 
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      DBS B.V. <info@curopayments.com>
 * @copyright   DBS B.V.
 * @link        https://www.curopayments.com/
 */

namespace curopayments\api;

/**
 * An extension for the base \Exception class, extended with a getCode() method
 */
class Exception extends \Exception {

	/**
	 * The exception code for use in the API
	 * @var string
	 * @access private
	 * @final
	 */
	private $_sCode;

	/**
	 * 
	 * @param unknown $sCode_
	 * @param string $sMessage_ (optional) The exception message
	 * @param number $iCode_
	 * @param \Exception $oPrevious_
	 * @api
	 */
	public function __construct( $sCode_, $sMessage_ = '', $iCode_ = 0, \Exception $oPrevious_ = NULL ) {
		parent::__construct( $sMessage_, $iCode_, $oPrevious_ );
		$this->_sCode = $sCode_;
	}

	/**
	 * Get the code associated with this exception, used to generalize the way in which we (can) handle caught exceptions
	 * @final
	 * @access public
	 * @api
	 * @return string The exception code
	 */
	final public function getStrCode() {
		return $this->_sCode;
	}
}
