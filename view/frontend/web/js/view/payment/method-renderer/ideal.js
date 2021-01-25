/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
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
				template: 'Cardgate_Payment/payment/ideal',
				issuer_id: ''
			},

			initObservable: function () {
				this._super()
					.observe([
						'issuer_id'
					]);
				return this;
			},

			getCode: function() {
				return this.item.method;
			},

			getImageSrc: function() {
					return "https://cdn.curopayments.net/images/paymentmethods/" + this.item.method.substring(9) + ".svg";
			},

			getDescription: function() {
				return this.item.title;
			},

			getData: function() {
				return {
					'method': this.item.method,
					'additional_data': {
						'issuer_id': this.issuer_id()
					}
				};
			},

			getIDealIssuers: function() {
				return window.checkoutConfig.payment.cardgate_ideal_issuers;
			},
			
			getInstructions: function() {
				return window.checkoutConfig.payment.instructions[this.item.method];
			},

			afterPlaceOrder: function() {
				window.location.replace(url.build('cardgate/payment/start/'));
			}

		});
	}
);
