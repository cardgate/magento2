/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
define(
	[
		'Magento_Checkout/js/view/summary/abstract-total',
		'jquery',
		'Magento_Checkout/js/model/url-builder',
		'mage/storage',
		'Magento_Checkout/js/model/error-processor',
		'Magento_Customer/js/model/customer',
		'Magento_Checkout/js/model/quote',
		'Magento_Catalog/js/price-utils',
		'Magento_Checkout/js/model/totals',
		'Magento_Checkout/js/model/full-screen-loader',
		'Cardgate_Payment/js/action/set-payment-information',
		'Magento_Checkout/js/model/resource-url-manager'
	],
	function (
			Component,
			$,
			urlBuilder,
			storage,
			errorProcessor,
			customer,
			quote,
			priceUtils,
			totals,
			fullScreenLoader,
			setPaymentInformation,
			resourceUrlManager
		) {

		quote.getPaymentMethod().subscribe(
			function( selectedPM ){
				setPaymentInformation( this.messageContainer, { 'method':selectedPM.method } );
			}
		);

		var displayMode = window.checkoutConfig.reviewShippingDisplayMode;

		return Component.extend({
			defaults: {
				isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
				displayMode: displayMode,
				template: 'Cardgate_Payment/checkout/summary/fee'
			},
			quoteIsVirtual: quote.isVirtual(),
			totals: quote.getTotals(),
			isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
			isBothPricesDisplayed: function() {
				return 'both' == this.displayMode;
			},
			isIncludingDisplayed: function() {
				return 'including' == this.displayMode;
			},
			isExcludingDisplayed: function() {
				return 'excluding' == this.displayMode;
			},
			isDisplayed: function() {
				console.log(this);
				return this.isFullMode();
			},
			hasValue: function() {
				var price = 0;
				if (this.totals()) {
					price = totals.getSegment('cardgatefee').value;
				}
				return ( price > 0 );
			},
			getValue: function() {
				var price = 0;
				if (this.totals()) {
					price = totals.getSegment('cardgatefee').value;
				}
				return this.getFormattedPrice(price);
			},
			getIncludingValue: function() {
				var price = 0;
				if (this.totals()) {
					//price = this.totals().cardgatefee_incl_tax;
					price = totals.getSegment('cardgatefee').value;
				}
				return this.getFormattedPrice(price);
			}/*,
			getBaseValue: function() {
				var price = 0;
				if (this.totals()) {
					price = this.totals().base_fee;
				}
				return priceUtils.formatPrice(price, quote.getBasePriceFormat());
			}*/
		});
	}
);
