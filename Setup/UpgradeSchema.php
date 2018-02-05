<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade Schema class.
 * Executed every time this module is upgraded.
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class UpgradeSchema implements UpgradeSchemaInterface {

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Framework\Setup\UpgradeSchemaInterface::upgrade()
	 */
	public function upgrade ( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();
		$setup->endSetup();
	}
}
