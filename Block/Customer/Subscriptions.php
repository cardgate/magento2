<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Customer;

use Cardgate\Payment\Model\ResourceModel\Subscription\Collection;
use Cardgate\Payment\Model\ResourceModel\Subscription\CollectionFactory;
use Cardgate\Payment\Model\Config\Source\IntervalUnit;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Renders the customer's subscriptions in the "My Account" dashboard.
 */
class Subscriptions extends Template
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var IntervalUnit
     */
    private $intervalUnit;

    /**
     * @var Collection|null
     */
    private $subscriptions;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param CustomerSession $customerSession
     * @param IntervalUnit $intervalUnit
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CustomerSession $customerSession,
        IntervalUnit $intervalUnit,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->intervalUnit = $intervalUnit;
    }

    /**
     * Get the current customer's subscriptions.
     *
     * @return Collection
     */
    public function getSubscriptions()
    {
        if (null === $this->subscriptions) {
            $this->subscriptions = $this->collectionFactory->create();
            $this->subscriptions->addFieldToFilter(
                'customer_id',
                (int) $this->customerSession->getCustomerId()
            );
            $this->subscriptions->setOrder('created_at', 'DESC');
        }
        return $this->subscriptions;
    }

    /**
     * Format a cents amount using the store currency.
     *
     * @param int $cents
     * @param string $currency
     * @return string
     */
    public function formatAmount($cents, $currency)
    {
        return number_format(((int) $cents) / 100, 2) . ' ' . $currency;
    }

    /**
     * Available interval unit options for the alter form.
     *
     * @return array
     */
    public function getIntervalUnitOptions()
    {
        return $this->intervalUnit->toOptionArray();
    }

    /**
     * Build a posting URL for a self-service action.
     *
     * @param string $action
     * @param int $id
     * @return string
     */
    public function getActionUrl($action, $id)
    {
        return $this->getUrl('cardgate/subscription/' . $action, ['id' => $id]);
    }
}
