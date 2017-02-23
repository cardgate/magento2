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

namespace curopayments\api\Payment\Request;

use curopayments\api\Payment\Request;
use curopayments\api\Payment\Request\iDeal\Issuers;
use curopayments\api\Exception;

class iDeal extends Request {

	/**
	 * The issuer to use with the iDeal transaction
	 * @var string
	 * @access protected
	 */
	protected $_sIssuer = NULL;

	/**
	 * @see \curopayments\api\Payment::_addRegisterParams()
	 */
	protected function _addRegisterParams() {
		$aData = [];
		if ( ! is_null( $this->_sIssuer ) ) {
			$aData['issuer'] = $this->_sIssuer;
		}
		return $aData;
	}

	/**
	 * Set the issuer to use for this iDeal transaction
	 * @param string $sIssuer_ The Issuer ID for this iDeal transaction
	 * @return curopayments\api\Payment\iDeal
	 * @access public
	 * @throws curopayments\api\Exception if an invalid amount is supplied
	 * @api
	 */
	public function setIssuer( $sIssuer_ ) {
		if ( ! is_string( $sIssuer_ ) ) {
			throw new Exception( 'Payment.iDeal.Issuer.Invalid', 'Invalid issuer: ' . $sIssuer_ );
		}
		$this->_sIssuer = $sIssuer_;
		return $this;
	}

	/**
	 * Return the (optional) issuer provided
	 * @return string The issuer associated
	 */
	public function getIssuer() {
		return $this->_sIssuer;
	}

	/**
	 *
	 * @throws Exception
	 * @return iDeal\Issuers Singleton instance
	 */
	public function getIssuers() {
		$oResults = $this->_oClient->getRequest( 'ideal/issuers/' );
		if ( ! is_array( $oResults->issuers ) ) {
			throw new Exception( 'Payment.iDeal.getIssuers.Invalid', 'Issuerlist unavailable' );
		}
		return Issuers::getInstance()->setIssuers( $oResults->issuers );
	}
}


