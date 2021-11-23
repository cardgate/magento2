<?php
/**
 * Copyright (c) 2018 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Controller\Adminhtml\Gateway;

use \Magento\Framework\App\ObjectManager;

/**
 * Test gateway connectivity Adminhtml action.
 */
class FetchPM extends \Magento\Backend\App\Action
{

    /**
     * @return \Magento\Framework\Controller\Result\Raw\Interceptor
     */
    public function execute()
    {
        $sFetchResult = "Fetching CardGate payment methods...\n";
        $aMethods = self::_fetch($sFetchResult);
        if (is_array($aMethods)) {
            $aActiveMethods = [];
            foreach ($aMethods as $oMethod) {
                $aActiveMethods[] = [
                    'id'   => $oMethod->getId(),
                    'name' => $oMethod->getName()
                ];
            }
            $oConfig = ObjectManager::getInstance()->get(\Cardgate\Payment\Model\Config::class);
            $oConfig->setGlobal('active_pm', $this->serialize($aActiveMethods));
            $sFetchResult .= "<span style=\"color:blue;font-weight:bold;\">
                               Please go to \"Cache Management\" and refresh cache types.
                               </span>\n";
        }
        $sResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $sResult->setContents('<pre>' . $sFetchResult . "Completed.<pre>");
        return $sResult;
    }

    /**
     * @return array
     */
    protected function _fetch(&$sResult_)
    {
        try {
            $oGatewayClient = ObjectManager::getInstance()->get(\Cardgate\Payment\Model\GatewayClient::class);
            $sResult_ .= 'Using gateway address ' . $oGatewayClient->getUrl() . "\n";
            $aMethods = $oGatewayClient->methods()->all($oGatewayClient->getSiteId());
            $sResult_ .= 'Gateway request for site #' . $oGatewayClient->getSiteId() . " completed.\n";
            if (count($aMethods) > 0) {
                $sResult_ .= "<span style=\"color:green;font-weight:bold;\">Found payment methods: ";
                foreach ($aMethods as $iIndex => $oMethod) {
                    if ($iIndex > 0) {
                        $sResult_ .= ', ';
                    }
                    $sResult_ .= $oMethod->getId();
                }
                $sResult_ .= ".</span>\n";
            } else {
                $sResult_ .= "<span style=\"color:red;font-weight:bold;\">No payment methods found.</span>\n";
            }
            return $aMethods;
        } catch (\Exception $e_) {
            $sResult_ .= "<span style=\"color:red;font-weight:bold;\">Error occurred: " .
                         $e_->getMessage() . "</span>\n";
            return false;
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function serialize($data)
    {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
            return $serializer->serialize($data);
    }
}
