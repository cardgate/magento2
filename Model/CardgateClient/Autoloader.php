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
namespace Cardgate\Payment\Api {

	/**
	 * Autoloader for all CardGate client library classes.
	 * Usage: Cardgate\Payment\Api\Autoloader::register();
	 */
	final class Autoloader {

		/**
		 * Load the class.
		 * @param string $sClass_ The class to load
		 * @access private
		 */
		private static function autoload( $sClass_ ) {
			$iLength = strlen( __NAMESPACE__ );
			if ( substr( $sClass_, 0, $iLength ) == __NAMESPACE__ ) {
				require str_replace( '\\', '/', substr( $sClass_, $iLength + 1 ) ) . '.php';
			}
		}

		/**
		 * Register the autoloader.
		 * @access public
		 */
		public static function register() {
			return spl_autoload_register( [ __CLASS__, 'autoload' ] );
		}

	}

}
