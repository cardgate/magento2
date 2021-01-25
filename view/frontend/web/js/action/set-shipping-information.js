/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data'
], function ($, wrapper, quote, checkoutData) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            quote.setPaymentMethod(null);
            checkoutData.setSelectedPaymentMethod(null);
            return originalAction();
        });
    };
});
