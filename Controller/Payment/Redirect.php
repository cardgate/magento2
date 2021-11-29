<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Cardgate\Payment\Model\Config;
use Cardgate\Exception\RedirectException;

/**
 * Client redirect after payment action
 *
 * @author DBS B.V.
 *
 */
class Redirect extends \Magento\Framework\App\Action\Action
{

    /**
     *
     * @var \Cardgate\Payment\Model\Config
     */
    private $_cardgateConfig;

    /**
     *
     * @var Session
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $checkoutSession,
        Config $cardgateConfig
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_cardgateConfig = $cardgateConfig;
        parent::__construct($context);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Magento\Framework\App\ActionInterface::execute()
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('reference');
        $status = $this->getRequest()->getParam('status');
        $code = $this->getRequest()->getParam('code');
        $transactionId = $this->getRequest()->getParam('transaction');

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if (empty($orderId)
                || empty($status)
                || empty($code)
                || empty($transactionId)
            ) {
                throw new RedirectException(__('Wrong parameters supplied.'));
            }

            // If the callback hasn't been received (yet) the most recent status is fetched from the gateway instead
            // of relying on the provided status in the url.
            $order = ObjectManager::getInstance()->create(
                \Magento\Sales\Model\Order::class
            )->loadByIncrementId(
                $orderId
            );
            if (\Magento\Sales\Model\Order::STATE_NEW == $order->getState()) {
                $gatewayClient = ObjectManager::getInstance()->get(\Cardgate\Payment\Model\GatewayClient::class);
                $status = $gatewayClient->transactions()->status($transactionId);
            }
            if ('success' == $status
                || 'pending' == $status
            ) {
                $this->_checkoutSession->start();
                $resultRedirect->setPath('checkout/onepage/success');
            } elseif ((int)$code == 309) {
                if (!!$this->_cardgateConfig->getGlobal('return_to_checkout')) {
                    $this->_checkoutSession->restoreQuote();
                    $resultRedirect->setPath('checkout');
                } else {
                    throw new RedirectException(__('Transaction canceled.'));
                }
            } else {
                throw new RedirectException(__('Payment not completed.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            if (!!$this->_cardgateConfig->getGlobal('always_show_success_page')) {
                $this->_checkoutSession->start();
                $resultRedirect->setPath('checkout/onepage/success');
            } else {
                $this->_checkoutSession->restoreQuote();
                $resultRedirect->setPath('checkout/cart');
            }
        }

        return $resultRedirect;
    }
}
