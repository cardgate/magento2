<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Observer;

use Cardgate\Payment\Model\Subscription\SubscriptionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;

/**
 * Prevents adding more than one subscription product to the cart.
 */
class CartSubscriptionLimit implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $subscriptionFlags = [];

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getData('quote_item');
        if (! $quoteItem instanceof Item || ! $this->isSubscriptionItem($quoteItem)) {
            return;
        }

        $quote = $quoteItem->getQuote();
        if (! $quote) {
            return;
        }

        $subscriptionCount = 0;
        $currentCounted = false;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($this->isSameItem($item, $quoteItem)) {
                $currentCounted = true;
                $subscriptionCount ++;
                continue;
            }
            if ($this->isSubscriptionItem($item)) {
                $subscriptionCount ++;
            }
        }
        if (! $currentCounted) {
            $subscriptionCount ++;
        }

        if ($subscriptionCount > 1) {
            throw new LocalizedException(
                __('Only one subscription product is allowed per cart. You can add non-subscription products.')
            );
        }
    }

    /**
     * Check whether the quote item refers to a subscription product.
     *
     * The product carried by a quote item that was loaded from the database does not
     * always contain the subscription attribute, so it is resolved from the product
     * repository when it is missing.
     *
     * @param Item $item
     * @return bool
     */
    private function isSubscriptionItem(Item $item)
    {
        $product = $item->getProduct();
        if ($product && $product->hasData(SubscriptionManager::ATTR_IS_SUBSCRIPTION)) {
            return (int) $product->getData(SubscriptionManager::ATTR_IS_SUBSCRIPTION) === 1;
        }

        $productId = (int) $item->getProductId();
        if ($productId < 1) {
            return false;
        }
        if (! array_key_exists($productId, $this->subscriptionFlags)) {
            try {
                $loaded = $this->productRepository->getById($productId);
                $this->subscriptionFlags[$productId] =
                    (int) $loaded->getData(SubscriptionManager::ATTR_IS_SUBSCRIPTION) === 1;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->subscriptionFlags[$productId] = false;
            }
        }

        return $this->subscriptionFlags[$productId];
    }

    /**
     * Determine whether both quote items are the very same item.
     *
     * @param Item $item
     * @param Item $quoteItem
     * @return bool
     */
    private function isSameItem(Item $item, Item $quoteItem)
    {
        if ($item === $quoteItem) {
            return true;
        }
        return $item->getId() && $quoteItem->getId() && $item->getId() == $quoteItem->getId();
    }
}
