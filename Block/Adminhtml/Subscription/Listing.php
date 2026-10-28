<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Adminhtml\Subscription;

use Cardgate\Payment\Model\ResourceModel\Subscription\Collection;
use Cardgate\Payment\Model\ResourceModel\Subscription\CollectionFactory;
use Cardgate\Payment\Model\Subscription;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Admin subscription overview (filterable, paginated).
 */
class Listing extends Template
{
    public const PAGE_SIZE = 20;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Collection|null
     */
    private $collection;

    public function __construct(Context $context, CollectionFactory $collectionFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return string[]
     */
    public function getStatusOptions()
    {
        return [
            Subscription::STATUS_ACTIVE    => (string) __('Active'),
            Subscription::STATUS_PAUSED    => (string) __('Paused'),
            Subscription::STATUS_CANCELLED => (string) __('Cancelled'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusFilter()
    {
        $status = (string) $this->getRequest()->getParam('status');
        return isset($this->getStatusOptions()[$status]) ? $status : '';
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return trim((string) $this->getRequest()->getParam('q'));
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return max(1, (int) $this->getRequest()->getParam('p', 1));
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $collection = $this->collectionFactory->create();
            if ($this->getStatusFilter() !== '') {
                $collection->addFieldToFilter('status', $this->getStatusFilter());
            }
            if ($this->getSearch() !== '') {
                $like = '%' . addcslashes($this->getSearch(), '%_') . '%';
                $collection->addFieldToFilter(
                    ['increment_id', 'sku', 'name', 'mandate_transaction_id'],
                    [['like' => $like], ['like' => $like], ['like' => $like], ['like' => $like]]
                );
            }
            $collection->setOrder('entity_id', 'DESC');
            $collection->setPageSize(self::PAGE_SIZE);
            $collection->setCurPage($this->getCurrentPage());
            $this->collection = $collection;
        }
        return $this->collection;
    }

    /**
     * @return int
     */
    public function getLastPage()
    {
        return max(1, (int) $this->getCollection()->getLastPageNumber());
    }

    /**
     * @param array $params
     * @return string
     */
    public function getListUrl(array $params = [])
    {
        $query = array_filter(array_merge([
            'status' => $this->getStatusFilter(),
            'q'      => $this->getSearch(),
        ], $params), function ($v) {
            return $v !== '' && $v !== null;
        });
        return $this->getUrl('*/*/index', ['_query' => $query]);
    }

    /**
     * @param Subscription $subscription
     * @return string
     */
    public function getEditUrl(Subscription $subscription)
    {
        return $this->getUrl('*/*/edit', ['id' => $subscription->getId()]);
    }

    /**
     * @param Subscription $subscription
     * @return string
     */
    public function getOrderUrl(Subscription $subscription)
    {
        return $subscription->getOrderId()
            ? $this->getUrl('sales/order/view', ['order_id' => $subscription->getOrderId()])
            : '';
    }

    /**
     * @param int $cents
     * @param string $currency
     * @return string
     */
    public function formatCents($cents, $currency)
    {
        return $currency . ' ' . number_format(((int) $cents) / 100, 2, '.', '');
    }
}
