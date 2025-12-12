<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class AddSalesOrderStatus implements DataPatchInterface
{
    /**
     * Connection to Resource
     *
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Save CardGate order status
     *
     * @return AddSalesOrderStatus|void
     */
    public function apply()
    {
        $tablename  = $this->resourceConnection->getTableName('sales_order_status');
        $query      = "DELETE FROM {$tablename} WHERE status LIKE 'cardgate%'";
        $data     = [];
        $statuses = [
            'cardgate_payment_pending' => __('CardGate Payment Pending'),
            'cardgate_payment_success' => __('CardGate Payment Success'),
            'cardgate_payment_failure' => __('CardGate Payment Failure')];

        foreach ($statuses as $code => $info) {
            $data[] = [
                'status' => $code,
                'label'  => $info
            ];
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->startSetup();
        $connection->query($query);
        $connection->insertArray($tablename, [
            'status',
            'label'
        ], $data);
        $connection->endSetup();
    }

    /**
     * Fetch dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return[];
    }

    /**
     * Fetch aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return[];
    }
}
