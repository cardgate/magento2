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
use Cardgate\Payment\Model\Config;
use Magento\Framework\App\ObjectManager;

/**
 * Fetch paymentmethods Adminhtml action
 *
 * @author DBS B.V.
 * @package Magento2
 */
class FetchPM extends Action {

	/**
	 *
	 * @var Config
	 */
	private $config;

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
		$this->config = ObjectManager::getInstance()->get( Config::class );
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
		$testResult = [];
		$activePms = [];
		try {
			$pmResult = $this->gatewayclient->postRequest( 'options/' . $this->gatewayclient->getSiteId() );
			foreach ( $pmResult->options as $pmId => $pmRecord ) {
				$activePms[] = [
					'id' => $pmRecord->id,
					'name' => $pmRecord->name
				];
			}
			$testResult['pms'] = $activePms;
			$this->config->setGlobal( 'active_pm', serialize( $activePms ) );
			$testResult['success'] = true;
		} catch ( \Exception $e ) {
			$testResult['success'] = false;
			$testResult['message'] = $e->getMessage();
		}
		// YYY: Should be JSON data + flush cache here automagically?
		//$result = $this->resultFactory->create( ResultFactory::TYPE_JSON );
		//$result->setData( $testResult );
		$result = $this->resultFactory->create( ResultFactory::TYPE_RAW );
		$result->setContents("<html><body><pre>After successful query; close this tab and <b><u>please flush CACHE</u></b> ('System' > 'Tools' > 'Cache Management')." .
				( isset( $testResult['message'] ) ? "\n\n<b>Message : " . $testResult['message'] . '' : '' ) . "</b>\n\nNumber of active paymentmethods found : " . count( $activePms ) .
				"\n\nRaw Result :\n" . var_export($activePms, 1)."</pre></body></html>"
		);
		return $result;
	}
}
