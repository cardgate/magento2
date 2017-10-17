<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Block\Info;

/**
 * Default Checkout template
 *
 * @author DBS B.V.
 * @package Magento2
 */
class DefaultInfo extends \Magento\Payment\Block\Info {

	/**
	 * Checkout template
	 *
	 * @var string
	 */
	protected $_template = 'Cardgate_Payment::info/defaultinfo.phtml';
}