<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\PaymentMethod;


use Cardgate\Payment\Model\PaymentMethods;

/**
 * Shady exception class because we want to misuse the DI system for nonexistent paymentmethods
 *
 * @author DBS B.V.
 * @package Magento2
 */
class nonexistent extends PaymentMethods {

	public function __construct (
		\Magento\Framework\ObjectManagerInterface $objManager
	) {
		parent::__construct(
			$objManager->get( 'Magento\\Framework\\Model\\Context' ),
			$objManager->get( 'Magento\\Framework\\Registry' ),
			$objManager->get( 'Magento\\Framework\\Api\\ExtensionAttributesFactory' ),
			$objManager->get( 'Magento\\Framework\\Api\\AttributeValueFactory' ),
			$objManager->get( 'Magento\\Payment\\Helper\\Data' ),
			$objManager->get( 'Magento\\Framework\\App\\Config\\ScopeConfigInterface' ),
			$objManager->get( 'Magento\\Payment\\Model\\Method\\Logger' ),
			$objManager->get( 'Cardgate\\Payment\\Model\\Config\\Master' ),
			$objManager->get( 'Cardgate\\Payment\\Model\\Config' ),
			$objManager->get( 'Magento\\Sales\\Model\\Order\\Email\\Sender\\OrderSender' ),
			$objManager->get( 'Magento\\Sales\\Model\\Order\\Email\\Sender\\InvoiceSender' ),
			$objManager->get( 'Magento\\Sales\\Model\\Order\\Payment\\Transaction\\Repository' )
		);
	}

}