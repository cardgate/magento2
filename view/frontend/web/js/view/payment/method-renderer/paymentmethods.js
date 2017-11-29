/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
	[
		'Magento_Checkout/js/view/payment/default',
		'Magento_Checkout/js/action/select-payment-method',
		'Magento_Checkout/js/checkout-data',
		'Magento_Checkout/js/model/quote',
		'mage/url'
	],
	function (Component,
			selectPaymentMethodAction,
			checkoutData,
			quote,
			url) {
		'use strict';

		return Component.extend({

			redirectAfterPlaceOrder: false,

			defaults: {
				template: 'Cardgate_Payment/payment/form',
				transactionResult: ''
			},

			initObservable: function () {
				this._super()
					.observe([
						'transactionResult'
					]);
				return this;
			},

			getCode: function() {
				//console.log(this);
				return this.item.method;
			},

			getImageSrc: function(type) {
				if ( type == "small" ) {
					return "https://cdn.curopayments.net/thumb/100/20/paymentmethods/" + this.item.method.substring(9) + ".png";
				} else {
					return "https://cdn.curopayments.net/thumb/300/50/paymentmethods/" + this.item.method.substring(9) + ".png";
				}
			},

			getDescription: function() {
				return this.item.title;
			},

			getData: function() {
				return {
					'method': this.item.method
				};
			},

			afterPlaceOrder: function() {
				window.location.replace(url.build('cardgate/payment/start/'));
			}

		});
	}
);
