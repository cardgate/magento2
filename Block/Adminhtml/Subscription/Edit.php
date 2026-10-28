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
     * @var Registry
     */
    private $registry;

    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * @return Subscription|null
     */
    public function getSubscription()
    {
        return $this->registry->registry(EditController::REGISTRY_KEY);
    }

    /**
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
     * @param int $cents
     * @return string
     */
    public function centsToDecimal($cents)
    {
        return number_format(((int) $cents) / 100, 2, '.', '');
    }

    /**
     * @return string
     */
    public function getNextRunValue()
    {
        $next = $this->getSubscription()->getNextRunAt();
        return $next ? date('Y-m-d\TH:i', strtotime($next . ' UTC')) : '';
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * @param int|null $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $orderId ? $this->getUrl('sales/order/view', ['order_id' => $orderId]) : '';
    }
}
