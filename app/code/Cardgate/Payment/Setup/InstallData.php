<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

/**
 * Install Data class.
 * Executed at first installation of this plugin.
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class InstallData implements InstallDataInterface {

	/**
	 *
	 * @var SalesSetupFactory
	 */
	protected $salesSetupFactory;

	/**
	 *
	 * @var QuoteSetupFactory
	 */
	protected $quoteSetupFactory;

	/**
	 *
	 * @param SalesSetupFactory $salesSetupFactory
	 * @param QuoteSetupFactory $quoteSetupFactory
	 */
	public function __construct ( SalesSetupFactory $salesSetupFactory, QuoteSetupFactory $quoteSetupFactory ) {
		$this->salesSetupFactory = $salesSetupFactory;
		$this->quoteSetupFactory = $quoteSetupFactory;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function install ( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
		/**
		 * Prepare database for install
		 */
		$setup->startSetup();

		$data = [];
		$statuses = [
			'cardgate_waitconf' => __( 'Waiting Confirmation CardGate' ),
			'cardgate_authorized' => __( 'Authorized CardGate' ),
			'cardgate_refund' => __( 'Refund CardGate' )
		];
		foreach ( $statuses as $code => $info ) {
			$data[] = [
				'status' => $code,
				'label' => $info
			];
		}
		$setup->getConnection()->insertArray( $setup->getTable( 'sales_order_status' ), [
			'status',
			'label'
		], $data );

		/**
		 * Prepare database after install
		 */
		$setup->endSetup();
	}
}
