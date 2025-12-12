<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Block\Sales\Order;

/**
 * Fee block in "Totals"
 *
 *
 */
class Fee extends \Magento\Framework\View\Element\Template
{

    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check if we need display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Retrieve application store object
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Retrieve Order object
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Retrieve Label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Retrieve value properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {

        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $value = $this->_order->getCardgatefeeAmount();
        if ((float)$value > 0) {
            $fee = new \Magento\Framework\DataObject(
                [
                    'code'       => 'cardgatefee',
                    'strong'     => false,
                    'value'      => $value,
                    'base_value' => $this->_order->getBaseCardgatefeeAmount(),
                    'label'      => __('Checkout fee')
                ]
            );

            $parent->addTotal($fee, 'subtotal');
        }
        return $this;
    }
}
