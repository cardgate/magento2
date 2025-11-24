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
	 * Item instance.
	 *
	 * @method Item setSKU( \string $sSKU_ ) Sets the sku.
	 * @method string getSKU() Returns the sku.
	 * @method bool hasSKU() Checks for existence of sku.
	 * @method Item unsetSKU() Unsets the sku.
	 *
	 * @method Item setName( \string $sName_ ) Sets the name.
	 * @method string getName() Returns the name.
	 * @method bool hasName() Checks for existence of name.
	 * @method Item unsetName() Unsets the name.
	 *
	 * @method Item setLink( \string $sLink_ ) Sets the link.
	 * @method string getLink() Returns the link.
	 * @method bool hasLink() Checks for existence of link.
	 * @method Item unsetLink() Unsets the link.
	 *
	 * @method Item setQuantity( \string $sQuantity_ ) Sets the quantity.
	 * @method string getQuantity() Returns the quantity.
	 * @method bool hasQuantity() Checks for existence of quantity.
	 * @method Item unsetQuantity() Unsets the quantity.
	 *
	 * @method Item setPrice( \int $iPrice_ ) Sets the price.
	 * @method int getPrice() Returns the price.
	 * @method bool hasPrice() Checks for existence of price.
	 * @method Item unsetPrice() Unsets the price.
	 *
	 * @method string getType() Returns the type.
	 * @method bool hasType() Checks for existence of type.
	 * @method Item unsetType() Unsets the type.
	 *
	 * @method float getVat() Returns the vat.
	 * @method bool hasVat() Checks for existence of vat.
	 * @method Item unsetVat() Unsets the vat.
	 *
	 * @method bool getVatIncluded() Returns the vat included flag.
	 * @method bool hasVatIncluded() Checks for existence of vat included flag.
	 * @method Item unsetVatIncluded() Unsets the vat included flag.
	 *
	 * @method float getVatAmount() Returns the vat amount.
	 * @method bool hasVatAmount() Checks for existence of vat amount.
	 * @method Item unsetVatAmount() Unsets the vat amount.
	 *
	 * @method float getStock() Returns the stock.
	 * @method bool hasStock() Checks for existence of stock.
	 * @method Item unsetStock() Unsets the stock.
	 */
	class Item extends Entity {

		/**
		 * Product.
		 */
		const TYPE_PRODUCT = 1;

		/**
		 * Shipping Costs
		 */
		const TYPE_SHIPPING = 2;

		/**
		 * Payment Costs
		 */
		const TYPE_PAYMENT = 3;

		/**
		 * Discount
		 */
		const TYPE_DISCOUNT = 4;

		/**
		 * Handling fees
		 */
		const TYPE_HANDLING = 5;

		/**
		 * Correction
		 */
		const TYPE_CORRECTION = 6;

		/**
		 * VAT Correction
		 */
		const TYPE_VAT_CORRECTION = 7;

		/**
		 * @ignore
		 * @internal The methods these fields expose are configured in the class phpdoc.
		 */
		static $_aFields = [
			'SKU'			=> 'sku',
			'Name'			=> 'name',
			'Link'			=> 'link',
			'Quantity'		=> 'quantity',
			'Price'			=> 'price',
			'Type'			=> 'type',
			'Vat'			=> 'vat',
			'VatIncluded'	=> 'vat_inc',
			'VatAmount'		=> 'vat_amount',
			'Stock'			=> 'stock'
		];

		/**
		 * The constructor.
		 * @param int $iType_ The cart item type.
		 * @param string $sSKU_ The SKU of the cart item.
		 * @param string $sName_ The name of the cart item (productname).
		 * @param $iQuantity_
		 * @param int $iPrice_ The price of the cart item.
		 * @param string $sLink_ An optional link to the product.
		 * @throws Exception|\ReflectionException
		 * @access public
		 * @api
		 */
		function __construct( $iType_, $sSKU_, $sName_, $iQuantity_, $iPrice_, $sLink_ = NULL ) {
			$this->setType( $iType_ )->setSKU( $sSKU_ )->setName( $sName_ )->setQuantity( $iQuantity_ )->setPrice( $iPrice_ );
			if ( ! is_null( $sLink_ ) ) {
				$this->setLink( $sLink_ );
			}
		}

		/**
		 * Sets the type (must be one of the {@link \Cardgate\Payment\Model\CardgateClient\Item::TYPE_*}} constants.
		 * @param int $iType_ The cart item type to set.
		 * @return Item Returns this, makes the call chainable.
		 * @throws Exception|\ReflectionException
		 * @access public
		 * @api
		 */
		function setType( $iType_ ) {
			if (
				! is_integer( $iType_ )
				|| ! in_array( $iType_, ( new \ReflectionClass( '\Cardgate\Payment\Model\CardgateClient\Item' ) )->getConstants() )
			) {
				throw new Exception( 'Item.Type.Invalid', 'invalid cart item type: ' . $iType_ );
			}
			return parent::setType( $iType_ );
		}

		/**
		 * Sets the vat.
		 * @param float $fVat_ The vat to set.
		 * @return Item Returns this, makes the call chainable.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function setVat( $fVat_ ) {
			if ( ! is_numeric( $fVat_ ) ) {
				throw new Exception( 'Item.Vat.Invalid', 'invalid vat: ' . $fVat_ );
			}
			return parent::setVat( $fVat_ );
		}

		/**
		 * Sets the vat included flag.
		 * @param bool $bVatIncluded_ The vat included flag to set.
		 * @return Item Returns this, makes the call chainable.
		 * @access public
		 * @api
		 */
		function setVatIncluded( $bVatIncluded_ ) {
			return parent::setVatIncluded( !!$bVatIncluded_ );
		}

		/**
		 * Sets the vat amount.
		 * @param float $fVatAmount_ The vat amount to set.
		 * @return Item Returns this, makes the call chainable.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function setVatAmount( $fVatAmount_ ) {
			if ( ! is_numeric( $fVatAmount_ ) ) {
				throw new Exception( 'Item.Vat.Amount.Invalid', 'invalid vat amount: ' . $fVatAmount_ );
			}
			return parent::setVatAmount( $fVatAmount_ );
		}

		/**
		 * Sets the stock.
		 * @param float $fStock_ The stock to set.
		 * @return Item Returns this, makes the call chainable.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function setStock( $fStock_ ) {
			if ( ! is_numeric( $fStock_ ) ) {
				throw new Exception( 'Item.Stock.Invalid', 'invalid stock: ' . $fStock_ );
			}
			return parent::setStock( $fStock_ );
		}

	}

}
