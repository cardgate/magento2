/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
/*browser:true*/
/*global define*/
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
			if ( i.substr( 0, 9 ) == "cardgate_" ) {
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
