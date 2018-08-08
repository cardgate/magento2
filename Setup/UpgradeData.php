<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data class.
 * Executed at first installation or upgrade of this plugin.
 *
 * @author DBS B.V.
 * @package Magento2
 *
 */
class UpgradeData implements UpgradeDataInterface {

	/**
	 * @var Magento\Framework\Setup\ModuleDataSetupInterface
	 * @var Magento\Framework\Setup\ModuleContextInterface
	 */
	public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
		$newInstall = !$context->getVersion();
		if (
			$newInstall
			|| version_compare( $context->getVersion(), '2.0.13', '<' )
		) {
			$setup->startSetup();

			$data = [];
			$statuses = [
				'cardgate_payment_pending' => __( 'CardGate Payment Pending' ),
				'cardgate_payment_success' => __( 'CardGate Payment Success' ),
				'cardgate_payment_failure' => __( 'CardGate Payment Failure' )
			];
			foreach ( $statuses as $code => $info ) {
				$data[] = [
					'status' => $code,
					'label' => $info
				];
			}
			$tablename = $setup->getTable( 'sales_order_status' );
			$setup->getConnection()->query( "DELETE FROM {$tablename} WHERE status LIKE 'cardgate%'" );
			$setup->getConnection()->insertArray( $setup->getTable( 'sales_order_status' ), [
				'status',
				'label'
			], $data );

			$setup->endSetup();
		}
	}

}
