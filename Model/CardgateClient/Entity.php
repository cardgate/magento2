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
	 * CardGate client object.
	 */
	abstract class Entity {

		/**
		 * @ignore
		 * @internal The _aData property holds the data of the entity.
		 */
		protected $_aData = [];

		/**
		 * @ignore
		 * @internal To make the data in an Entity available through magic functions (setName, getAmount, unsetName,
		 * hasCart) populate the _aFields property below. To make autocompletion work in Zend, use the @method phpdoc
		 * directive like this: @method null setId( int $iId ).
		 * Example: [ 'MerchantId' => 'merchant_id', 'Name' => 'name' ]
		 */
		static protected $_aFields = [];

		/**
		 * This method can be used to retrieve all the data of the instance.
		 * @param string $sPrefix_ Optionally prefix all the data entries.
		 * @return array Returns an array with the data in the entity.
		 */
		public function getData( $sPrefix_ = NULL ) {
			if ( is_string( $sPrefix_ ) ) {
				$aResult = [];
				foreach( $this->_aData as $sKey => $mValue ) {
					$aResult[$sPrefix_ . $sKey] = $mValue;
				}
				return $aResult;
			} else {
				return $this->_aData;
			}
		}

		/**
		 * @ignore
		 * @internal The __call method translates get-, set-, unset- and has-methods to their configured fields.
		 * @return $this|mixed|bool Return $this on set and unset, mixed on get and bool on has
		 * @throws Exception|\ReflectionException
		 */
		public function __call( $sMethod_, $aArgs_ ) {
			$sClassName = ( new \ReflectionClass( $this ) )->getShortName();
			switch ( substr( $sMethod_, 0, 3 ) ) {
				case 'get':
					$sKey = substr( $sMethod_, 3 );
					if ( isset( static::$_aFields[$sKey] ) ) {
						return isset( $this->_aData[static::$_aFields[$sKey]] ) ? $this->_aData[static::$_aFields[$sKey]]  : NULL;
					}
					break;
				case 'set':
					$sKey = substr( $sMethod_, 3 );
					if ( isset( static::$_aFields[$sKey] ) ) {
						if ( isset( $aArgs_[0] ) ) {
							if (
								is_scalar( $aArgs_[0] )
								&& (
									! is_string( $aArgs_[0] )
									|| strlen( $aArgs_[0] ) > 0
								)
							) {
								$this->_aData[static::$_aFields[$sKey]] = $aArgs_[0];
								return $this; // makes the call chainable
							} else {
								throw new Exception( "{$sClassName}.Invalid.Method", "invalid value for {$sMethod_}" );
							}
						} else {
							throw new Exception( "{$sClassName}.Invalid.Method", "missing parameter 1 for {$sMethod_}" );
						}
					}
					break;
				case 'uns':
					$sKey = substr( $sMethod_, 5 );
					if ( isset( static::$_aFields[$sKey] ) ) {
						unset( $this->_aData[static::$_aFields[$sKey]] );
						return $this; // makes the call chainable
					}
					break;
				case 'has':
					$sKey = substr( $sMethod_, 3 );
					if ( isset( static::$_aFields[$sKey] ) ) {
						return isset( $this->_aData[static::$_aFields[$sKey]] );
					}
					break;
			}
			throw new Exception( "{$sClassName}.Invalid.Method", "call to undefined method {$sMethod_}" );
		}

	}

}
