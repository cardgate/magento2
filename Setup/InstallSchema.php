<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install Schema class.
 * Executed at first installation of this plugin.
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class InstallSchema implements InstallSchemaInterface {

	/**
	 * @var Magento\Framework\Setup\SchemaSetupInterface
	 * @var Magento\Framework\Setup\ModuleContextInterface
	 */
	public function install( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();

			// QUOTE_PAYMENT TABLE
		$quotePaymentTable = $setup->getTable( 'quote_payment' );

		$quotePaymentColumns = [
			'cardgatefee_incl_tax' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Incl Tax'
			],
			'base_cardgatefee_incl_tax' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Incl Tax'
			],

			'base_cardgatefee_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Excl Tax Amount'
			],
			'base_cardgatefee_tax_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Tax Amount'
			],

			'cardgatefee_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Excl Tax Amount'
			],
			'cardgatefee_tax_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Tax Amount'
			]
		];

		foreach ( $quotePaymentColumns as $columnName => $definition ) {
			$setup->getConnection()->addColumn( $quotePaymentTable, $columnName, $definition );
		}

		// ORDER TABLE
		$orderTable = $setup->getTable( 'sales_order' );

		$orderColumns = [
			'cardgatefee_incl_tax' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Incl Tax'
			],
			'base_cardgatefee_incl_tax' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Incl Tax'
			],

			'base_cardgatefee_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Excl Tax Amount'
			],
			'base_cardgatefee_cancelled' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Excl Tax Cancelled'
			],
			'base_cardgatefee_invoiced' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Excl Tax Invoiced'
			],
			'base_cardgatefee_refunded' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Excl Tax Refunded'
			],
			'base_cardgatefee_tax_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Tax Amount'
			],
			'base_cardgatefee_tax_refunded' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Tax Refunded'
			],

			'cardgatefee_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Excl Tax Amount'
			],
			'cardgatefee_cancelled' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Excl Tax Cancelled'
			],
			'cardgatefee_invoiced' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Excl Tax Invoiced'
			],
			'cardgatefee_refunded' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Excl Tax Refunded'
			],
			'cardgatefee_tax_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Tax Amount'
			],
			'cardgatefee_tax_refunded' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Tax Refunded'
			]
		];

		foreach ( $orderColumns as $columnName => $definition ) {
			$setup->getConnection()->addColumn( $orderTable, $columnName, $definition );
		}

		// INVOICE TABLE
		$invoiceTable = $setup->getTable( 'sales_invoice' );

		$invoiceColumns = [
			'cardgatefee_incl_tax' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Incl Tax'
			],
			'base_cardgatefee_incl_tax' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Incl Tax'
			],

			'base_cardgatefee_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Excl Tax Amount'
			],
			'base_cardgatefee_tax_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'Base CardGate Fee Tax Amount'
			],

			'cardgatefee_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Excl Tax Amount'
			],
			'cardgatefee_tax_amount' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'default' => '0.0000',
				'nullable' => true,
				'comment' => 'CardGate Fee Tax Amount'
			]
		];

		foreach ( $invoiceColumns as $columnName => $definition ) {
			$setup->getConnection()->addColumn( $invoiceTable, $columnName, $definition );
		}

		// ORDER PAYMENT TABLE
		$orderPaymentTable = $setup->getTable( 'sales_order_payment' );

		$orderPaymentColumns = [
			'cardgate_paymentmethod' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'length' => 64,
				'default' => '',
				'nullable' => true,
				'comment' => 'CardGate PaymentMethod'
			],
			'cardgate_transaction' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'length' => 64,
				'default' => '',
				'nullable' => true,
				'comment' => 'CardGate TransactionID'
			],
			'cardgate_status' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				'default' => 0,
				'nullable' => true,
				'comment' => 'CardGate StatusCode'
			],
			'cardgate_testmode' => [
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
				'default' => 0,
				'nullable' => true,
				'comment' => 'CardGate TestMode'
			]
		];

		foreach ( $orderPaymentColumns as $columnName => $definition ) {
			$setup->getConnection()->addColumn( $orderPaymentTable, $columnName, $definition );
		}
	}

}
