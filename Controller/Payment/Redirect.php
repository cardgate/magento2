<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\App\RequestInterface as Request;
use \Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Cardgate\Payment\Model\Config as CardgateConfig;

/**
 * Client redirect after payment action
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Redirect implements ActionInterface
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

    /**
     *
     * @var ResultRedirect
     */
    protected $resultRedirect;

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var Manager
     */
    protected $messageManager;

    public function __construct(
        ResultRedirect $resultRedirect,
        Request $request,
        Session $checkoutSession,
        CardgateConfig $cardgateConfig,
        Manager $messageManager
    ) {
        $this->resultRedirect = $resultRedirect;
        $this->request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_cardgateConfig = $cardgateConfig;
        $this->messageManager = $messageManager;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Magento\Framework\App\ActionInterface::execute()
     */
    public function execute()
    {
        $orderId = $this->request->getParam('reference');
        $status = $this->request->getParam('status');
        $code = $this->request->getParam('code');
        $transactionId = $this->request->getParam('transaction');

        try {
            if (empty($orderId)
                || empty($status)
                || empty($code)
                || empty($transactionId)
            ) {
                throw new \Exception(__('Wrong parameters supplied.'));
            }

            // If the callback hasn't been received (yet) the most recent status is fetched from the gateway instead
            // of relying on the provided status in the url.
            $order = ObjectManager::getInstance()->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($orderId);
            if (\Magento\Sales\Model\Order::STATE_NEW == $order->getState()) {
                $gatewayClient = ObjectManager::getInstance()->get(\Cardgate\Payment\Model\GatewayClient::class);
                $status = $gatewayClient->transactions()->status($transactionId);
            }
            if ('success' == $status
                || 'pending' == $status
            ) {
                $this->_checkoutSession->start();
                $this->resultRedirect->setPath('checkout/onepage/success');
            } elseif ((int)$code == 309) {
                if (!!$this->_cardgateConfig->getGlobal('return_to_checkout')) {
                    $this->_checkoutSession->restoreQuote();
                    $this->resultRedirect->setPath('checkout');
                } else {
                    throw new \Exception(__('Transaction canceled.'));
                }
            } else {
                throw new \Exception(__('Payment not completed.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            if (!!$this->_cardgateConfig->getGlobal('always_show_success_page')) {
                $this->_checkoutSession->start();
                $this->resultRedirect->setPath('checkout/onepage/success');
            } else {
                $this->_checkoutSession->restoreQuote();
                $this->resultRedirect->setPath('checkout/cart');
            }
        }

        return $this->resultRedirect;
    }
}
