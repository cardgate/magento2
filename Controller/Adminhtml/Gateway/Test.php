<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Controller\Adminhtml\Gateway;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Cardgate\Payment\Model\GatewayClient;
use Magento\Framework\App\ObjectManager;

/**
 * Test gateway connectivity Adminhtml action
 *
 * @author DBS B.V.
 * @package Magento2
 */
class Test extends Action {

	/**
	 *
	 * @var GatewayClient
	 */
	private $gatewayclient;

	/**
	 *
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct ( \Magento\Backend\App\Action\Context $context ) {
		parent::__construct( $context );
		$this->gatewayclient = ObjectManager::getInstance()->get( GatewayClient::class );
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Magento\Framework\App\ActionInterface::execute()
	 */
	public function execute () {
		$status = $this->getRequest()->getParam( 'section' );
		$result = $this->resultFactory->create( ResultFactory::TYPE_RAW );
		$testResult = "Testing Cardgate gateway communication...\n\n";
		try {
			$pmResult = $this->gatewayclient->postRequest( 'options/' . $this->gatewayclient->getSiteId() );
			$testResult .= "Gateway request for site #" . $this->gatewayclient->getSiteId() . " completed...\n\nFound paymentmethods:\n";
			foreach ( $pmResult->options as $pmId => $pmRecord ) {
				$testResult .= "  {$pmRecord->name}\n";
			}
		} catch ( \Exception $e ) {
			$testResult .= "Error occurred : " . $e->getMessage();
		}
		$result->setContents( "<pre>" . $testResult . "\n\nCompleted.<pre>" );
		return $result;
	}
}
