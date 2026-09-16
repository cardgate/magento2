<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Test\Unit\Subscription;

use Cardgate\Payment\Model\Subscription;
use Cardgate\Payment\Model\Subscription\OrderCreator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_stubs/magento.php';

/**
 * Regression test for the subscription renewal order creation.
 *
 * The renewal order used to fail with a fatal error:
 *   "Call to a member function getStoreId() on null"
 * because the payment was imported on a quote that was not persisted yet. Magento
 * only links a payment back to its quote once the quote has an id, so the payment
 * had no quote and Quote\Payment::getMethodInstance() fataled.
 */
class OrderCreatorTest extends TestCase
{

    /**
     * Build an OrderCreator with stubbed Magento collaborators.
     *
     * @param \Cardgate\Payment\Test\Unit\Stub\QuoteStub $quote
     * @return OrderCreator
     */
    private function createOrderCreator($quote)
    {
        return new OrderCreator(
            new \Cardgate\Payment\Test\Unit\Stub\OrderRepositoryStub(),
            new \Cardgate\Payment\Test\Unit\Stub\ProductRepositoryStub(),
            new \Cardgate\Payment\Test\Unit\Stub\CustomerRepositoryStub(),
            new \Cardgate\Payment\Test\Unit\Stub\QuoteFactoryStub($quote),
            new \Cardgate\Payment\Test\Unit\Stub\CartRepositoryStub(),
            new \Cardgate\Payment\Test\Unit\Stub\QuoteManagementStub(),
            new \Cardgate\Payment\Test\Unit\Stub\StoreManagerStub(),
            new \Cardgate\Payment\Test\Unit\Stub\LoggerStub()
        );
    }

    /**
     * Build the subscription of the reported incident (order 000000061).
     *
     * @return Subscription
     */
    private function createSubscription()
    {
        $subscription = new Subscription();
        $subscription->setData([
            'entity_id'              => 5,
            'customer_id'            => 2,
            'store_id'               => 1,
            'order_id'               => 60,
            'increment_id'           => '000000061',
            'product_id'             => 14,
            'sku'                    => '24-WB04',
            'name'                   => 'Push It Messenger Bag',
            'qty'                    => 1,
            'mandate_transaction_id' => 'T26912218491',
            'payment_method'         => 'cardgate_creditcard',
            'amount'                 => 4500,
            'currency'               => 'EUR',
            'interval_count'         => 1,
            'interval_unit'          => Subscription::UNIT_DAY,
            'status'                 => Subscription::STATUS_ACTIVE,
            'cycles_billed'          => 0,
        ]);
        return $subscription;
    }

    /**
     * A renewal order is created and the payment method is imported successfully.
     *
     * @return void
     */
    public function testRenewalOrderIsCreated()
    {
        $quote = new \Cardgate\Payment\Test\Unit\Stub\QuoteStub();
        $order = $this->createOrderCreator($quote)->create($this->createSubscription());

        $this->assertSame('000000062', $order->getIncrementId());
        $this->assertSame('cardgate_creditcard', $quote->getPayment()->getMethod());
    }

    /**
     * The quote must be persisted (and thus have an id) before the payment is imported,
     * otherwise Magento hands out a payment that is not linked to the quote.
     *
     * @return void
     */
    public function testPaymentIsImportedOnAPersistedQuote()
    {
        $quote = new \Cardgate\Payment\Test\Unit\Stub\QuoteStub();
        $this->createOrderCreator($quote)->create($this->createSubscription());

        $this->assertNotNull($quote->getPayment()->getQuote(), 'payment must be linked to the quote');
        $this->assertTrue($quote->wasSavedBeforePaymentImport(), 'quote must be saved before importData()');
    }
}
