<?php
/**
 * Copyright (c) 2016, DBS B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      DBS B.V. <info@curopayments.com>
 * @copyright   DBS B.V.
 * @link        https://www.curopayments.com/
 */

namespace curopayments\api\Payment\Request\iDeal;

use curopayments\util\Singleton;
use curopayments\api\Exception;

class Issuers implements \Iterator {

	use Singleton;

	const SORT_NONE = 0;
	const SORT_NAME = 1;

	private $_iIndex = -1;
	
	/**
	 * Issuers; as received from the API call
	 * @var \stdClass[id,name,list]
	 */
	private $_aIssuers;

	/**
	 * @param array $aIssuers_ array of issuers to initialize with
	 */
	public function __construct( $aIssuers_ = NULL ) {
		if ( ! is_null( $aIssuers_ ) ) {
			$this->setIssuers( $aIssuers_ );
		}
	}

	/**
	 * @param array $aIssuers_ array of issuers
	 */
	public function setIssuers( $aIssuers_ ) {

		if ( ! is_array( $aIssuers_ ) ) {
			throw new Exception( 'Payment.Request.Ideal.SetIssuers.Invalid', 'invalid issuers supplied' );
		}
		$this->_aIssuers = $aIssuers_;
		return $this;
	}

	/**
	 * Get the list of issuers iDeal supports
	 * @param integer $iSort_
	 * @return 
	 */
	public function getList( $iSort_ = self::SORT_NONE ) {
		switch( $iSort_ ) {
			case self::SORT_NAME:
				$aTmp = $this->_aIssuers;

				usort( $aTmp, function( $oLhs_, $oRhs_ ) {
					if ( $oLhs_->name == $oRhs_->name) {
						return 0;
					}
					return ( $oLhs_->name < $oRhs_->name ) ? -1 : 1;
				} );

				return $aTmp;

			default:
				return $this->_aIssuers;
		}
	}

	/**#@+
	 * @ignore
	 */
	public function key() {
		return $this->_iIndex;
	}
	public function current() {
		return $this->_aIssuers[ $this->_iIndex ];
	}
	public function valid() {
		return isset( $this->_aIssuers[ $this->_iIndex ] );
	}
	public function rewind() {
		$this->_iIndex = 0;
	}
	public function next() {
		++$this->_iIndex;
	}
	/**#@-*/
}
