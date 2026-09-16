<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Setup\Patch\Data;

use Cardgate\Payment\Model\Subscription\SubscriptionManager;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Adds the product attributes used to flag a product as a subscription and to configure
 * its billing interval. The attributes are added to all product types (the "is
 * subscription" flag has no apply_to restriction) so simple, configurable, bundle,
 * grouped and virtual products can all be sold as subscriptions.
 */
class AddSubscriptionProductAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return AddSubscriptionProductAttributes|void
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $group = 'CardGate Subscription';

        $eavSetup->addAttribute(
            Product::ENTITY,
            SubscriptionManager::ATTR_IS_SUBSCRIPTION,
            [
                'type'                    => 'int',
                'label'                   => 'Sell as Subscription',
                'input'                   => 'boolean',
                'source'                  => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required'                => false,
                'default'                 => 0,
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'user_defined'            => false,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true,
                'group'                   => $group,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            SubscriptionManager::ATTR_INTERVAL,
            [
                'type'                    => 'int',
                'label'                   => 'Subscription Interval Count',
                'input'                   => 'text',
                'required'                => false,
                'default'                 => 1,
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'user_defined'            => false,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => false,
                'group'                   => $group,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            SubscriptionManager::ATTR_INTERVAL_UNIT,
            [
                'type'                    => 'varchar',
                'label'                   => 'Subscription Interval Unit',
                'input'                   => 'select',
                'source'                  => \Cardgate\Payment\Model\Config\Source\IntervalUnit::class,
                'required'                => false,
                'default'                 => 'month',
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'user_defined'            => false,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => false,
                'group'                   => $group,
            ]
        );
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
