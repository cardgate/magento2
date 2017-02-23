/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
define(
	[
		'Cardgate_Payment/js/view/checkout/summary/fee'
	],
	function (Component) {
		'use strict';

		return Component.extend({

			/**
			 * @override
			 */
			isDisplayed: function () {
				return true;
			}
		});
	}
);