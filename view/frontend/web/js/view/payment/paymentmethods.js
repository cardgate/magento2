/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
define(
	[
		'uiComponent',
		'Magento_Checkout/js/model/payment/renderer-list'
	],
	function (
		Component,
		rendererList
	) {
		'use strict';
		for ( var i in window.checkoutConfig.payment ) {
			if (
				i.substr( 0, 9 ) == "cardgate_" &&
				window.checkoutConfig.payment[i] != undefined
			) {
				rendererList.push({
						type: i,
						component: 'Cardgate_Payment/js/view/payment/method-renderer/' + window.checkoutConfig.payment[i].renderer
					}
				);
			}
		}
		/** Add view logic here if needed */
		return Component.extend({});
	}
);
