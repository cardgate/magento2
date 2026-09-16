<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 *
 * Minimal Magento stubs, so the subscription classes can be unit tested without a
 * full Magento installation. The stubs mimic the behaviour of the real classes as
 * far as the subscription engine relies on it; most importantly:
 *
 *   Magento\Quote\Model\Quote::getPayment() only links the payment back to the
 *   quote when the quote already has an id, and Quote\Payment::getMethodInstance()
 *   dereferences that quote - which is exactly what made the renewal order fail.
 */

namespace Magento\Framework\Model {

    if (! class_exists(AbstractModel::class, false)) {
        /**
         * Data object / model stub with Magento's magic getters and setters.
         */
        class AbstractModel
        {
            /**
             * @var array
             */
            protected $_data = [];

            /**
             * Constructor.
             */
            public function __construct()
            {
                if (method_exists($this, '_construct')) {
                    $this->_construct();
                }
            }

            /**
             * Set data on the object.
             *
             * @param array|string $key
             * @param mixed $value
             * @return $this
             */
            public function setData($key, $value = null)
            {
                if (is_array($key)) {
                    $this->_data = $key;
                } else {
                    $this->_data[$key] = $value;
                }
                return $this;
            }

            /**
             * Add data to the object.
             *
             * @param array $data
             * @return $this
             */
            public function addData(array $data)
            {
                foreach ($data as $key => $value) {
                    $this->_data[$key] = $value;
                }
                return $this;
            }

            /**
             * Read data from the object.
             *
             * @param string $key
             * @return mixed
             */
            public function getData($key = '')
            {
                if ('' === $key) {
                    return $this->_data;
                }
                return isset($this->_data[$key]) ? $this->_data[$key] : null;
            }

            /**
             * Entity id accessor.
             *
             * @return mixed
             */
            public function getId()
            {
                return $this->getData('entity_id');
            }

            /**
             * Entity id mutator.
             *
             * @param mixed $id
             * @return $this
             */
            public function setId($id)
            {
                return $this->setData('entity_id', $id);
            }

            /**
             * Set the resource model.
             *
             * @param string $resourceModel
             * @return void
             */
            protected function _init($resourceModel)
            {
            }

            /**
             * Magic getters and setters (getFooBar()/setFooBar()).
             *
             * @param string $method
             * @param array $args
             * @return mixed
             */
            public function __call($method, $args)
            {
                $key = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', substr($method, 3)));
                if (0 === strpos($method, 'get')) {
                    return $this->getData($key);
                }
                if (0 === strpos($method, 'set')) {
                    return $this->setData($key, isset($args[0]) ? $args[0] : null);
                }
                if (0 === strpos($method, 'has')) {
                    return null !== $this->getData($key);
                }
                return null;
            }
        }
    }
}

namespace Magento\Sales\Api\Data {

    if (! interface_exists(OrderInterface::class, false)) {
        interface OrderInterface
        {
        }
    }
}

namespace Magento\Sales\Api {

    if (! interface_exists(OrderRepositoryInterface::class, false)) {
        interface OrderRepositoryInterface
        {
        }
    }
}

namespace Magento\Catalog\Api {

    if (! interface_exists(ProductRepositoryInterface::class, false)) {
        interface ProductRepositoryInterface
        {
        }
    }
}

namespace Magento\Customer\Api {

    if (! interface_exists(CustomerRepositoryInterface::class, false)) {
        interface CustomerRepositoryInterface
        {
        }
    }
}

namespace Magento\Quote\Api {

    if (! interface_exists(CartRepositoryInterface::class, false)) {
        interface CartRepositoryInterface
        {
        }
    }
}

namespace Magento\Quote\Model {

    if (! class_exists(QuoteFactory::class, false)) {
        class QuoteFactory
        {
        }
    }
    if (! class_exists(QuoteManagement::class, false)) {
        class QuoteManagement
        {
        }
    }
}

namespace Magento\Store\Model {

    if (! interface_exists(StoreManagerInterface::class, false)) {
        interface StoreManagerInterface
        {
        }
    }
}

namespace Psr\Log {

    if (! interface_exists(LoggerInterface::class, false)) {
        interface LoggerInterface
        {
        }
    }
}

namespace Cardgate\Payment\Test\Unit\Stub {

    /**
     * Payment stub mimicking Magento\Quote\Model\Quote\Payment.
     */
    class PaymentStub
    {
        /**
         * @var QuoteStub|null
         */
        private $quote = null;

        /**
         * @var string
         */
        private $method = '';

        /**
         * Link the payment to its quote.
         *
         * @param QuoteStub $quote
         * @return $this
         */
        public function setQuote($quote)
        {
            $this->quote = $quote;
            return $this;
        }

        /**
         * @return QuoteStub|null
         */
        public function getQuote()
        {
            return $this->quote;
        }

        /**
         * @var array
         */
        private $additionalInformation = [];

        /**
         * @return string
         */
        public function getMethod()
        {
            return $this->method;
        }

        /**
         * @param string $key
         * @param mixed $value
         * @return $this
         */
        public function setAdditionalInformation($key, $value = null)
        {
            $this->additionalInformation[$key] = $value;
            return $this;
        }

        /**
         * @return array
         */
        public function getAdditionalInformation()
        {
            return $this->additionalInformation;
        }

        /**
         * Import the payment data, exactly as Magento does: the method instance is
         * resolved through the quote, which fatals when the quote is missing.
         *
         * @param array $data
         * @return $this
         */
        public function importData(array $data)
        {
            $this->method = isset($data['method']) ? (string) $data['method'] : '';
            // Mimics Magento\Quote\Model\Quote\Payment::getMethodInstance().
            $storeId = $this->quote->getStoreId();
            $this->quote->onPaymentImported();
            return $this;
        }
    }

    /**
     * Quote stub mimicking the relevant Magento\Quote\Model\Quote behaviour.
     */
    class QuoteStub extends \Magento\Framework\Model\AbstractModel
    {
        /**
         * @var PaymentStub|null
         */
        private $payment = null;

        /**
         * @var bool
         */
        private $savedBeforePaymentImport = false;

        /**
         * @var bool
         */
        private $saved = false;

        /**
         * Magento only attaches the quote when the quote is already persisted.
         *
         * @return PaymentStub
         */
        public function getPayment()
        {
            if (null === $this->payment) {
                $this->payment = new PaymentStub();
            }
            if ($this->getId()) {
                $this->payment->setQuote($this);
            }
            return $this->payment;
        }

        /**
         * Persist the quote (assigns an id, like the quote repository does).
         *
         * @return void
         */
        public function markSaved()
        {
            $this->saved = true;
            if (! $this->getId()) {
                $this->setId(101);
            }
        }

        /**
         * Record that the payment was imported on a persisted quote.
         *
         * @return void
         */
        public function onPaymentImported()
        {
            $this->savedBeforePaymentImport = $this->saved;
        }

        /**
         * @return bool
         */
        public function wasSavedBeforePaymentImport()
        {
            return $this->savedBeforePaymentImport;
        }

        /**
         * @return bool
         */
        public function isVirtual()
        {
            return false;
        }

        /**
         * @param mixed $customer
         * @return $this
         */
        public function assignCustomer($customer)
        {
            return $this;
        }

        /**
         * @param mixed $product
         * @param float $qty
         * @return $this
         */
        public function addProduct($product, $qty = 1)
        {
            $this->setData('items_qty', $qty);
            return $this;
        }

        /**
         * @return AddressStub
         */
        public function getBillingAddress()
        {
            return new AddressStub();
        }

        /**
         * @return AddressStub
         */
        public function getShippingAddress()
        {
            return new AddressStub();
        }

        /**
         * @return $this
         */
        public function collectTotals()
        {
            return $this;
        }
    }

    /**
     * Quote address stub.
     */
    class AddressStub extends \Magento\Framework\Model\AbstractModel
    {
        /**
         * @return $this
         */
        public function collectShippingRates()
        {
            return $this;
        }
    }

    /**
     * Order stub.
     */
    class OrderStub extends \Magento\Framework\Model\AbstractModel implements
        \Magento\Sales\Api\Data\OrderInterface
    {
        /**
         * @param mixed $comment
         * @return $this
         */
        public function addCommentToStatusHistory($comment)
        {
            return $this;
        }

        /**
         * @return PaymentStub
         */
        public function getPayment()
        {
            return new PaymentStub();
        }

        /**
         * @return AddressStub
         */
        public function getBillingAddress()
        {
            return new AddressStub();
        }

        /**
         * @return AddressStub
         */
        public function getShippingAddress()
        {
            return new AddressStub();
        }
    }

    /**
     * Order repository stub returning the originating order.
     */
    class OrderRepositoryStub implements \Magento\Sales\Api\OrderRepositoryInterface
    {
        /**
         * @param int $orderId
         * @return OrderStub
         */
        public function get($orderId)
        {
            $order = new OrderStub();
            $order->setData([
                'entity_id'          => $orderId,
                'increment_id'       => '000000061',
                'store_id'           => 1,
                'customer_email'     => 'roni_cost@example.com',
                'customer_firstname' => 'Veronica',
                'customer_lastname'  => 'Costello',
                'shipping_method'    => 'flatrate_flatrate',
                'grand_total'        => 45.00,
            ]);
            return $order;
        }

        /**
         * @param mixed $entity
         * @return mixed
         */
        public function save($entity)
        {
            return $entity;
        }
    }

    /**
     * Product repository stub.
     */
    class ProductRepositoryStub implements \Magento\Catalog\Api\ProductRepositoryInterface
    {
        /**
         * @param int $productId
         * @param bool $editMode
         * @param int|null $storeId
         * @param bool $forceReload
         * @return \Magento\Framework\Model\AbstractModel
         */
        public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
        {
            $product = new \Magento\Framework\Model\AbstractModel();
            $product->setData(['entity_id' => $productId, 'sku' => '24-WB04']);
            return $product;
        }
    }

    /**
     * Customer repository stub.
     */
    class CustomerRepositoryStub implements \Magento\Customer\Api\CustomerRepositoryInterface
    {
        /**
         * @param int $customerId
         * @return \Magento\Framework\Model\AbstractModel
         */
        public function getById($customerId)
        {
            $customer = new \Magento\Framework\Model\AbstractModel();
            $customer->setData(['entity_id' => $customerId, 'group_id' => 1]);
            return $customer;
        }
    }

    /**
     * Quote repository stub that persists (ids) the quote.
     */
    class CartRepositoryStub implements \Magento\Quote\Api\CartRepositoryInterface
    {
        /**
         * @param QuoteStub $quote
         * @return void
         */
        public function save($quote)
        {
            $quote->markSaved();
        }
    }

    /**
     * Quote factory stub handing out the quote under test.
     */
    class QuoteFactoryStub extends \Magento\Quote\Model\QuoteFactory
    {
        /**
         * @var QuoteStub
         */
        private $quote;

        /**
         * @param QuoteStub $quote
         */
        public function __construct($quote)
        {
            $this->quote = $quote;
        }

        /**
         * @param array $data
         * @return QuoteStub
         */
        public function create(array $data = [])
        {
            return $this->quote;
        }
    }

    /**
     * Quote management stub creating the renewal order.
     */
    class QuoteManagementStub extends \Magento\Quote\Model\QuoteManagement
    {
        /**
         * @param QuoteStub $quote
         * @param array $orderData
         * @return OrderStub
         */
        public function submit($quote, $orderData = [])
        {
            $order = new OrderStub();
            $order->setData([
                'entity_id'    => 62,
                'increment_id' => '000000062',
                'grand_total'  => 45.00,
            ]);
            return $order;
        }
    }

    /**
     * Store manager stub.
     */
    class StoreManagerStub implements \Magento\Store\Model\StoreManagerInterface
    {
        /**
         * @param int|null $storeId
         * @return \Magento\Framework\Model\AbstractModel
         */
        public function getStore($storeId = null)
        {
            $store = new \Magento\Framework\Model\AbstractModel();
            $store->setData(['store_id' => $storeId, 'code' => 'default']);
            return $store;
        }
    }

    /**
     * Logger stub.
     */
    class LoggerStub implements \Psr\Log\LoggerInterface
    {
        /**
         * @param string $message
         * @param array $context
         * @return void
         */
        public function info($message, array $context = []): void
        {
        }

        /**
         * @param string $message
         * @param array $context
         * @return void
         */
        public function debug($message, array $context = []): void
        {
        }

        /**
         * @param string $message
         * @param array $context
         * @return void
         */
        public function error($message, array $context = []): void
        {
        }

        /**
         * @param string $message
         * @param array $context
         * @return void
         */
        public function warning($message, array $context = []): void
        {
        }
    }
}

namespace {

    if (! function_exists('__')) {
        /**
         * Translation stub.
         *
         * @param string $text
         * @param mixed ...$args
         * @return string
         */
        function __($text, ...$args)
        {
            return vsprintf(str_replace(['%1', '%2', '%3'], '%s', $text), $args);
        }
    }
}
