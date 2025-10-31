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
namespace Cardgate\Payment\Model\CardgateClient\resources {

	/**
	 * CardGate resource object.
	 */
	final class Methods extends Base {

		/**
		 * This method can be used to receive a {@link \Cardgate\Payment\Model\CardgateClient\Method} instance.
		 * @param string $sId_ Method id to receive method instance for.
		 * @return \Cardgate\Payment\Model\CardgateClient\Method
		 * @throws \Cardgate\Payment\Model\CardgateClient\Exception|\ReflectionException
		 * @access public
		 * @api
		 */
		public function get( $sId_ ) {
			return new \Cardgate\Payment\Model\CardgateClient\Method( $this->_oClient, $sId_, $sId_ );
		}

		/**
		 * This method can be used to retrieve a list of all available payment methods for a site.
		 * @param int $iSiteId_ The site to retrieve payment methods for.
		 * @return array
		 * @throws \Cardgate\Payment\Model\CardgateClient\Exception|\ReflectionException
		 * @access public
		 * @api
		 */
		public function all( $iSiteId_ ) {
			if ( ! is_integer( $iSiteId_ ) ) {
				throw new \Cardgate\Payment\Model\CardgateClient\Exception( 'Methods.SiteId.Invalid', 'invalid site id: ' . $iSiteId_ );
			}

			$sResource = "options/{$iSiteId_}/";

			$aResult = $this->_oClient->doRequest( $sResource, NULL, 'GET' );

			if ( empty( $aResult['options'] ) ) {
				throw new \Cardgate\Payment\Model\CardgateClient\Exception( 'Method.Options.Invalid', 'unexpected result: ' . $this->_oClient->getLastResult() . $this->_oClient->getDebugInfo( TRUE, FALSE )	);
			}

            $aValidMethods  = ( new \ReflectionClass( '\Cardgate\Payment\Model\CardgateClient\Method' ) )->getConstants();
			$aMethods = [];
			foreach( $aResult['options'] as $aOption ) {

                if (!in_array($aOption['id'],$aValidMethods)) {
                    continue;
                }

				try {
					$aMethods[] = new \Cardgate\Payment\Model\CardgateClient\Method( $this->_oClient, $aOption['id'], $aOption['name'] );
				} catch ( \Cardgate\Payment\Model\CardgateClient\Exception $oException_ ) {
					trigger_error( $oException_->getMessage() . '. Please update this SDK to the latest version.', E_USER_WARNING );
				}
			}
			return $aMethods;
		}
	}

}
