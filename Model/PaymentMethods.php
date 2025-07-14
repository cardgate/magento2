<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model;

use Cardgate\Exception\RefundException;
use Cardgate\Payment\Model;
use Cardgate\Payment\Model\Config\ValueHandlerPool;
use Cardgate\Payment\Block\Info\DefaultInfo;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\Config\ConfigValueHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Block\Form;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Tax\Model\Calculation;

/**
 * Base Payment class from which all payment methods extend
 *
 * Magento\Payment\Model\Method\Adapter
 *
 * @author DBS B.V.
 */
class PaymentMethods extends \Magento\Payment\Model\Method\Adapter
{

    /**
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * See /web/js/view/payment/method-renderer
     *
     * @var string
     */
    public static $renderer = 'paymentmethods';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'cardgate_unknown';

    /**
     * @var boolean
     */
    protected $_canRefund = true;

    /**
     * @var boolean
     */
    protected $_canRefundInvoicePartial = true;

    /**
     *
     * @var Calculation
     */
    protected $taxCalculation;

    /**
     *
     * @var \Cardgate\Payment\Model\Config
     */
    protected $config;

    /**
     *
     * @param Calculation $taxCalculation
     * @param \Cardgate\Payment\Model\Config $config
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $eventManager
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Calculation $taxCalculation,
        Config $config,
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager,
        PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
        $this->config = $config;
        $this->config->setMethodCode($this->code);
        $this->taxCalculation = $taxCalculation;
        $this->objectManager = $objectManager;
        $valueHandlerPool = $this->getValueHandlerPool($this->code);
        $commandPool = $this->getCardgateCommandPool();
        $validatorPool = $this->getCardgateValidatorPool();
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $this->code,
            Form::class,
            DefaultInfo::class,
            $commandPool,
            $validatorPool
        );
    }
    /**
     *  Check if the currency is allowed for this payment method.
     *
     * @param $currency
     * @param $payment_method
     *
     * @return bool
     */
    public function checkPaymentCurrency($currency,$payment_method):bool {
        $strictly_euro = in_array($payment_method,['cardgateideal',
            'cardgateidealqr',
            'cardgatebancontact',
            'cardgatebanktransfer',
            'cardgatebillink',
            'cardgatesofortbanking',
            'cardgatedirectdebit',
            'cardgateonlineueberweisen',
            'cardgatespraypay']);
        if ($strictly_euro && $currency != 'EUR') return false;

        $strictly_pln = in_array($payment_method,['cardgateprzelewy24']);
        if ($strictly_pln && $currency != 'PLN') return false;

        return true;
    }

    /**
     * Check payment method availability
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return boolean
     */
    public function isAvailable(?\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (! parent::isAvailable($quote)) {
            return false;
        };

        if (!is_null($quote) && $quote->getData("quote_currency_code")){
            $sCurrencyCode = $quote->getData("quote_currency_code");
            $paymentMethod = 'cardgate'.substr($this->code,9 );
            if (!$this->checkPaymentCurrency($sCurrencyCode,$paymentMethod)) {
                return false;
            }
        }
        $customerGroups = $this->config->getValue('specific_customer_groups', $quote->getStoreId());
        $aCustomerGroups = isset($customerGroups) ? str_getcsv($customerGroups, ',') : [];
        $groupId         = $quote->getCustomer()->getGroupId();
        $loggedInIsGroup = $this->config->loggedInIsGroup($quote->getStoreId());
        $isLoggedIn      = ($quote->getCustomer()->getId() < 1 ? false : true);
        if ($isLoggedIn) {
            if ($groupId > 0 && count($aCustomerGroups) > 0 && ! in_array($groupId, $aCustomerGroups)){
                return false;
            }
        } else {
            if ($loggedInIsGroup) {
                if (in_array('-1', $aCustomerGroups)){
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * Assign Data to Payment method
     *
     * @inheritdoc
     *
     * @see \Magento\Payment\Model\Method\AbstractMethod::assignData()
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $additional = $data->getAdditionalData();
        if (! is_array($additional)) {
            return $this;
        }
        $info = $this->getInfoInstance();
        foreach ($additional as $key => $value) {
            if (is_scalar($value)) {
                $info->setAdditionalInformation($key, $value);
            }
        }
        return $this;
    }

    /**
     * Get payable to data
     *
     * @return string
     */
    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    /**
     * Get mailing address
     *
     * @return string
     */
    public function getMailingAddress()
    {
        return $this->getConfigData('mailing_address');
    }

    /**
     * Get fee for quote
     *
     * @param Quote $quote
     * @param Total $total
     *
     * @return FeeData
     */
    public function getFeeForQuote(Quote $quote, ?Total $total = null)
    {
        $this->config->setMethodCode($this->code);
        $storeId = $quote->getStoreId();
        if (! ($total === null)) {
            $calculatedTotal = array_sum($total->getAllBaseTotalAmounts());
            foreach ($total->getAllBaseTotalAmounts() as $k => $v) {
                $debug[] = "{$k} = {$v}";
            }
        } else {
            $calculatedTotal = 0 - $quote->getPayment()->getBaseCardgatefeeInclTax();
            foreach ($quote->getAllAddresses() as $address) {
                $calculatedTotal += $address->getBaseGrandTotal();
                $debug[]         = $address->getBaseGrandTotal();
            }
        }
        $debug[] = 'total: ' . $calculatedTotal;

        $taxClassId = $this->config->getGlobal('paymentfee_tax_class', $storeId);
        $request               = new DataObject(
            [
                'country_id'        => $quote->getBillingAddress()->getCountryId(),
                'region_id'         => $quote->getBillingAddress()->getRegionId(),
                'postcode'          => $quote->getBillingAddress()->getPostcode(),
                'customer_class_id' => $quote->getCustomerTaxClassId(),
                'product_class_id'  => $taxClassId
            ]
        );
        $taxRate = $this->taxCalculation->getRate($request);
        $baseFeeFixed      = floatval($this->config->getValue('paymentfee_fixed', $storeId));
        $baseFeePercentage = floatval($this->config->getValue('paymentfee_percentage', $storeId));
        $baseFee           = round(( $calculatedTotal * ( $baseFeePercentage / 100 ) ) + $baseFeeFixed, 4);

        $paymentFeeIncludesTax = $this->config->getValue('paymentfee_includes_tax', $storeId);
        if ($paymentFeeIncludesTax) {
            $baseTaxAmount = $baseFee - round($baseFee/((100 + $taxRate)/100), 4);
            $basePriceExcl = $baseFee - $baseTaxAmount;
        } else {
            $baseTaxAmount = round($baseFee * (1+($taxRate/100)), 4) - $baseFee;
            $basePriceExcl = $baseFee;
        }

        $aFee = [
            'amount'             => $basePriceExcl,
            'tax_amount'         => $baseTaxAmount,
            'tax_class'          => $taxClassId,
            'fee_includes_tax'   => $paymentFeeIncludesTax,
            'currency_converter' => $quote->getBaseToQuoteRate()
        ] ;

        $amount = ( $paymentFeeIncludesTax == 1 ? $basePriceExcl:($basePriceExcl + $baseTaxAmount));
        return $this->objectManager->create(
            Model\Total\FeeData::class,
            [
                'amount'             => $basePriceExcl,
                'tax_amount'         => $baseTaxAmount,
                'tax_class'          => $taxClassId,
                'fee_includes_tax'   => $paymentFeeIncludesTax,
                'currency_converter' => $quote->getBaseToQuoteRate()
            ]
        );
    }

    /**
     * Refund a transaction
     * @param InfoInterface $payment
     * @param $amount
     *
     * @return $this|PaymentMethods|\Magento\Payment\Model\Method\Adapter
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $baseToOrderRate = $order->getData()['base_to_order_rate'];
        $amount = round(($amount * $baseToOrderRate), 2);

        try {
            $gatewayClient = $this->objectManager->get(GatewayClient::class);
            $transaction = $gatewayClient->transactions()->get($payment->getCardgateTransaction());

            if ($transaction->canRefund()) {
                $transaction->refund((int)( $amount * 100 ));
            } else {
                throw new RefundException('refund not allowed');
            }
        } catch (RefundException $e) {
            $order->addStatusHistoryComment(__('Error occurred while registering the refund (%1)', $e->getMessage()));
            throw new RefundException($e);
        }
        return $this;
    }

    /**
     * Get instructions
     *
     * @return string
     */
    public function getInstructions()
    {
        $instructions = $this->config->getValue('instructions');
        return nl2br(''.$instructions);
    }

    /**
     * Get Value handler pool
     *
     * @param mixed $methodCode
     *
     * @return mixed
     */
    private function getValueHandlerPool($methodCode)
    {
        $configInterface = $this->objectManager->create(
            Config::class,
            [
                'methodCode' => $methodCode
            ]
        );
        $valueHandler = $this->objectManager->create(
            ConfigValueHandler::class,
            [
                'configInterface' => $configInterface
            ]
        );
        return $this->objectManager->create(ValueHandlerPool::class, [ 'handler' => $valueHandler ]);
    }

    /**
     * Get CardGate validator pool
     *
     * @return mixed
     */
    public function getCardgateValidatorPool()
    {
        return $this->objectManager->get('CardgateValidatorPool');
    }

    /**
     * Get CardGate command pool
     *
     * @return mixed
     */
    public function getCardgateCommandPool()
    {
        return $this->objectManager->get('CardgateCommandPool');
    }
}
