var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Cardgate_Payment/js/action/set-shipping-information': true
            }
        }
    }
};