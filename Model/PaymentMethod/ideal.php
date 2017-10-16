<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\PaymentMethod;

/**
 * iDeal exception class because we want another renderer template
 *
 * @author DBS B.V.
 * @package Magento2
 */
class ideal extends \Cardgate\Payment\Model\PaymentMethods {

	/**
	 * Renderer template name
	 *
	 * @var string
	 */
	public static $renderer = 'ideal';

}