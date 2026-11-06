<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Subscription;

use Cardgate\Payment\Controller\Adminhtml\Subscription\Edit as EditController;
use Cardgate\Payment\Model\Subscription;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

/**
 * Admin edit form for a single subscription.
 */
class Edit extends Template
{
    /**
     * The registry.
     *
     * @var Registry
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Get subscription.
     *
     * @return Subscription|null
     */
    public function getSubscription()
    {
        return $this->registry->registry(EditController::REGISTRY_KEY);
    }

    /**
     * Get status options.
     *
     * @return string[]
     */
    public function getStatusOptions()
    {
        $options = [
            Subscription::STATUS_ACTIVE    => (string) __('Active'),
            Subscription::STATUS_PAUSED    => (string) __('Paused'),
            Subscription::STATUS_CANCELLED => (string) __('Cancelled'),
        ];
        // A cancelled subscription is final and cannot be reactivated.
        if ($this->getSubscription() && $this->getSubscription()->isCancelled()) {
            return [Subscription::STATUS_CANCELLED => $options[Subscription::STATUS_CANCELLED]];
        }
        return $options;
    }

    /**
     * Get unit options.
     *
     * @return string[]
     */
    public function getUnitOptions()
    {
        return [
            Subscription::UNIT_DAY   => (string) __('Day(s)'),
            Subscription::UNIT_WEEK  => (string) __('Week(s)'),
            Subscription::UNIT_MONTH => (string) __('Month(s)'),
            Subscription::UNIT_YEAR  => (string) __('Year(s)'),
        ];
    }

    /**
     * Cents to decimal.
     *
     * @param int $cents
     * @return string
     */
    public function centsToDecimal($cents)
    {
        return number_format(((int) $cents) / 100, 2, '.', '');
    }

    /**
     * Get next run value.
     *
     * @return string
     */
    public function getNextRunValue()
    {
        $next = $this->getSubscription()->getNextRunAt();
        return $next ? date('Y-m-d\TH:i', strtotime($next . ' UTC')) : '';
    }

    /**
     * Get save url.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Get back url.
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * Get order url.
     *
     * @param int|null $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $orderId ? $this->getUrl('sales/order/view', ['order_id' => $orderId]) : '';
    }
}
