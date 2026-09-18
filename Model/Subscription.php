<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Subscription entity model.
 *
 * Represents a single recurring (CuroPayments) subscription created from an order item.
 */
class Subscription extends AbstractModel
{
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAUSED    = 'paused';
    public const STATUS_CANCELLED = 'cancelled';

    public const UNIT_DAY   = 'day';
    public const UNIT_WEEK  = 'week';
    public const UNIT_MONTH = 'month';
    public const UNIT_YEAR  = 'year';

    /**
     * @var string
     */
    protected $_eventPrefix = 'cardgate_subscription';

    /**
     * Initialize model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Cardgate\Payment\Model\ResourceModel\Subscription::class);
    }

    /**
     * Whether the subscription is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return self::STATUS_ACTIVE === $this->getStatus();
    }

    /**
     * Whether the subscription is paused.
     *
     * @return bool
     */
    public function isPaused()
    {
        return self::STATUS_PAUSED === $this->getStatus();
    }

    /**
     * Whether the subscription is cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return self::STATUS_CANCELLED === $this->getStatus();
    }

    /**
     * Total recurring amount (item + shipping) in cents for one cycle.
     *
     * @return int
     */
    public function getCycleAmount()
    {
        return (int) $this->getAmount() + (int) $this->getShippingAmount();
    }
}
