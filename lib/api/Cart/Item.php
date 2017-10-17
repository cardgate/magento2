<?php
namespace curopayments\api\Cart;

use curopayments\api\Exception;
use curopayments\api\ISerializedData;

class Item implements ISerializedData {
	// TODO - Insert your code here

	protected $_iQuantity = NULL;
	protected $_sSku = NULL;
	protected $_sName = NULL;
	protected $_iPrice = NULL;
	protected $_iVatAmount = NULL;
	protected $_bVatIncluded = NULL;
	protected $_iType = NULL;

	const TYPE_DEFAULT = 0;
	const TYPE_PRODUCT = 1;
	const TYPE_SHIPPING = 2;
	const TYPE_PAYMENT = 3;
	const TYPE_DISCOUNT = 4;
	const TYPE_HANDLING = 5;

	/**
	 */
	public function __construct( ) {
	}

	/**
	 * Set the quantity for the cart item
	 * @param integer $iQuantity_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setQuantity( $iQuantity_ ) {
		if (
			! is_integer( $iQuantity_ ) ||
			$iQuantity_ < 1
		) {
			throw new Exception( 'Cart.Item.Quantity.Invalid', 'Invalid quantity: ' . $iQuantity_ );
		}
		$this->_iQuantity = $iQuantity_;
		return $this;
	}

	/**
	 * Get the item quantity
	 * @return integer Item quantity
	 */
	public function getQuantity() {
		return $this->_iQuantity;
	}
	
	/**
	 * Set the stock keeping unit (SKU) for the cart item
	 * @param string $sSku_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setSku( $sSku_ ) {
		if ( ! is_string( $sSku_ ) ) {
				throw new Exception( 'Cart.Item.Sku.Invalid', 'Invalid SKU: ' . $sSku_ );
		}
		$this->_sSku = $sSku_;
		return $this;	
	}

	/**
	 * Get the item stock keeping unit (SKU)
	 * @return string Item SKU
	 */
	public function getSku() {
		return $this->_sSku;
	}

	/**
	 * Set the name for the cart item
	 * @param string $sName_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setName( $sName_ ) {
		if ( ! is_string( $sName_ ) ) {
			throw new Exception( 'Cart.Item.Name.Invalid', 'Invalid name: ' . $sName_ );
		}
		$this->_sName = $sName_;
		return $this;
	}

	/**
	 * Get the item name
	 * @return string Item name
	 */
	public function getName() {
		return $this->_sName;
	}

	/**
	 * Set the price (in cents) for the cart item
	 * @param integer $iPrice_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setPrice( $iPrice_ ) {
		if (
			! is_integer( $iPrice_ ) ||
			$iPrice_ < 1
		) {
			throw new Exception( 'Cart.Item.Price.Invalid', 'Invalid price: ' . $iPrice_ );
		}
		$this->_iQuantity = $iPrice_;
		return $this;
	}

	/**
	 * Get the item price
	 * @return integer Item price (in cents)
	 */
	public function getPrice() {
		return $this->_iQuantity;
	}	

	/**
	 * Set the amount of VAT to use for the cart item
	 * @param integer $iVatAmount_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setVatAmount( $iVatAmount_ ) {
		if (
			! is_integer( $iVatAmount_ )
			|| $iVatAmount_ < 1
			) {
				throw new Exception( 'Cart.Item.VatAmount.Invalid', 'Invalid VAT amount: ' . $iVatAmount_ );
			}
			$this->_iVatAmount = $iVatAmount_;
			return $this;
	}

	/**
	 * Get the item VAT amount
	 * @return integer Item VAT amount (in cents)
	 */
	public function getVatAmount() {
		return $this->_iVatAmount;
	}

	/**
	 * Specify whether VAT is included in the price
	 * @param boolean $bVatIncluded_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setVatIncluded( $bVatIncluded_ ) {
		 if ( ! is_bool( $bVatIncluded_ ) ) {
			throw new Exception( 'Cart.Item.VatIncluded.Invalid', 'Invalid value for flag: ' . $bVatIncluded_ );
		}
		$this->_bVatIncluded = $bVatIncluded_;
		return $this;
	}

	/**
	 * Get whether VAT is included
	 * @return boolean
	 */
	public function getVatIncluded() {
		return $this->_bVatIncluded;
	}

	/**
	 * Set the amount of VAT to use for the cart item
	 * @param integer $iVatAmount_
	 * @return cart\Item The reference to the current cart item
	 */
	public function setType( $iType_ ) {
		if (
			! is_integer( $iType_ )
			|| ( $iType_ < 1 && $iType_ > 5 ) // XXX: change for new types
		) {
				throw new Exception( 'Cart.Item.VatAmount.Invalid', 'Invalid VAT amount: ' . $iType_ );
			}
			$this->_iType = $iType_;
			return $this;
	}

	/**
	 * Get the item type
	 * @return integer Item type
	 */
	public function getType() {
		return $this->_iType;
	}

	/**
	 * Generate an interpretable array of the cart item for the API
	 * {@inheritDoc}
	 * @see \curopayments\api\ISerializedData::getArray()
	 */
	public function getArray() {
		$aData = [
			'sku'			=> $this->getSku(),
			'name'			=> $this->getName(),
			'quantity'		=> $this->getQuantity(),
			'price'			=> $this->getPrice(),
			'vat_inc'		=> (integer) $this->getVatIncluded(),
			'vat_amount'	=> $this->getVatAmount(),
			'type'			=> $this->getType()
		];
		return array_filter( $aData ); // filter out NULL values
	}

	/**
	 * Dereference all variables
	 */
	function __destruct() {
		$this->_iQuantity = NULL;
		$this->_sSku = NULL;
		$this->_sName = NULL;
		$this->_iPrice = NULL;
	 	$this->_iVatAmount = NULL;
		$this->_bVatIncluded = NULL;
		$this->_iType = NULL;
	}
}

