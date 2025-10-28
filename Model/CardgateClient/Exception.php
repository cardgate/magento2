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
	 * Class for all exceptions specific to the CardGate client library.
	 */
	final class Exception extends \Exception {

		/**
		 * The unified string code of the exception.
		 * @var string
		 * @access private
		 */
		private $_sError;

		/**
		 * Constructs the exception.
		 * @param string $sError_ A unified string code of the exception to throw.
		 * @param string $sMessage_ The exception message to throw.
		 * @param int $iCode_ The numeric exception code.
		 * @param \Throwable $oPrevious_ The previous exception used for the exception chaining.
		 * @access public
		 * @api
		 */
		function __construct( $sError_, $sMessage_, $iCode_ = 0, \Throwable $oPrevious_ = NULL ) {
			$this->_sError = $sError_;
			parent::__construct( $sMessage_, $iCode_, $oPrevious_ );
		}

		/**
		 * Get the unified string code associated with this exception.
		 * @return string The unified string code of the exception.
		 * @access public
		 * @api
		 */
		public function getError() {
			return $this->_sError;
		}

	}

}
