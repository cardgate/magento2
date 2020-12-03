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
		};
	}
);
