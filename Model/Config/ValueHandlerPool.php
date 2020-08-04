<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;

/**
 * Handler to provide payment gateway configuration values.
 */
class ValueHandlerPool implements ValueHandlerPoolInterface
{

	/**
	 *
	 * @var ValueHandlerInterface
	 */
	private $handler;

	/**
	 *
	 * @param ValueHandlerInterface $handler
	 */
	public function __construct(ValueHandlerInterface $handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Retrieves the configuration value handler
	 *
	 * @param string $field
	 * @return ValueHandlerInterface
	 */
	public function get($field)
	{
		return $this->handler;
	}
}