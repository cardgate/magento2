<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Controller\Adminhtml\Gateway;

use \Magento\Framework\App\ObjectManager;

/**
 * Test gateway connectivity Adminhtml action.
 */
class Test extends \Cardgate\Payment\Controller\Adminhtml\Gateway\FetchPM {

	/**
	 * @return \Magento\Framework\Controller\Result\Raw\Interceptor
	 */
	public function execute() {
		$sTestResult = "Testing CardGate gateway communication...\n";
		self::_fetch( $sTestResult );
		$sResult = $this->resultFactory->create( \Magento\Framework\Controller\ResultFactory::TYPE_RAW );
		$sResult->setContents( '<pre>' . $sTestResult . "Completed.<pre>" );
		return $sResult;
	}

}
