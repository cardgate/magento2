/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
define(
	[
		'Magento_Checkout/js/model/quote',
		'mage/url',
		'mage/storage',
		'Magento_Checkout/js/model/error-processor',
		'Magento_Customer/js/model/customer',
		'Magento_Checkout/js/action/get-totals',
		'Magento_Checkout/js/model/resource-url-manager',
		'Magento_Checkout/js/model/totals'
	],
	function (quote, urlBuilder, storage, errorProcessor, customer, getTotalsAction, resourceUrlManager, totals) {
		'use strict';

		return function (messageContainer, paymentData) {
			totals.isLoading( true );
			return storage.get( urlBuilder.build( 'cardgate/payment/updatepm?pm=' + paymentData.method ) )
				.fail(
					function( response ) {
						totals.isLoading( false );
					}
				).done(
					function() {
						getTotalsAction( [] );
					}
				)
			;

			/*
			var serviceUrl,
				payload;

			if (!customer.isLoggedIn()) {
				serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/set-payment-information', {
					cartId: quote.getQuoteId()
				});
				payload = {
					cartId: quote.getQuoteId(),
					email: quote.guestEmail,
					paymentMethod: paymentData,
					billingAddress: quote.billingAddress()
				};
			} else {
				serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
				payload = {
					cartId: quote.getQuoteId(),
					paymentMethod: paymentData,
					billingAddress: quote.billingAddress()
				};
			}

			totals.isLoading( true );

			return storage.post(
				serviceUrl, JSON.stringify(payload)
			).fail(
				function (response) {
					totals.isLoading( false );
					errorProcessor.process(response, messageContainer);
				}
			).done(
				function () {
					getTotalsAction([]);
				}
			);
			*/
		};
	}
);
