<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\Config;

use Cardgate\Payment\Model\Config\Master as MasterConfig;

/**
 * Config Structure plugin
 *
 * @author DBS B.V.
 * @package Magento2
 */
class StructurePlugin {

	/**
	 *
	 * @var \Magento\Config\Model\Config\ScopeDefiner
	 */
	protected $_scopeDefiner;

	/**
	 *
	 * @var MasterConfig
	 */
	protected $_masterConfig;

	/**
	 *
	 * @var \Cardgate\Payment\Model\Config
	 */
	protected $_cgconfig;

	/**
	 *
	 * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
	 * @param MasterConfig $cardgateConfig
	 * @param \Cardgate\Payment\Model\Config $config
	 */
	public function __construct ( \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner, MasterConfig $cardgateConfig, \Cardgate\Payment\Model\Config $config ) {
		$this->_scopeDefiner = $scopeDefiner;
		$this->_masterConfig = $cardgateConfig;
		$this->_cgconfig = $config;
	}

	/**
	 * Substitute payment section with CardGate configs
	 *
	 * @param \Magento\Config\Model\Config\Structure $subject
	 * @param \Closure $proceed
	 * @param array $pathParts
	 * @return \Magento\Config\Model\Config\Structure\ElementInterface
	 *         @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function aroundGetElementByPathParts ( \Magento\Config\Model\Config\Structure $subject, \Closure $proceed, array $pathParts ) {
		/** @var \Magento\Config\Model\Config\Structure\Element\Section $result **/
		$result = $proceed( $pathParts );

		if ( $pathParts[0] == 'cardgate' && count( $pathParts ) == 1 ) {
			// get all methods
			$allPaymentMethods = $this->_masterConfig->getCardgateMethods();

			// get all active methods
			$activePms = unserialize( $this->_cgconfig->getGlobal( 'active_pm' ) );
			if ( ! is_array( $activePms ) ) {
				$activePms = [];
			}
			$activePmIds = [];
			foreach ( $activePms as $pmRecord ) {
				$activePmIds[$pmRecord['id']] = $pmRecord['name'];
			}
			asort( $activePmIds, SORT_STRING | SORT_FLAG_CASE );
			asort( $allPaymentMethods, SORT_STRING | SORT_FLAG_CASE );
			$paymentMethods = $activePmIds;
			foreach ( $allPaymentMethods as $pm => $pmname ) {
				if ( ! isset( $paymentMethods[$pm] ) ) {
					$paymentMethods[$pm] = $pmname;
				}
			}

			$newData = $result->getData();
			$newPath = $pathParts[0];
			foreach ( $paymentMethods as $paymentMethod => $paymentMethodName ) {
				$paymentMethodResult = $proceed( [
					'cardgate_pm_skelleton_section'
				] );
				if ( isset( $paymentMethodResult ) && $paymentMethodResult instanceof \Magento\Config\Model\Config\Structure\Element\Section ) {
					$newChildren = [];
					foreach ( $paymentMethodResult->getChildren() as $child ) {
						$childData = array_merge( $child->getData(),
								[
									'id' => "cardgate_{$paymentMethod}",
									'path' => $newPath,
									'label' => sprintf( $child->getLabel(), $paymentMethodName ),
									'sortOrder' => strval( in_array( $paymentMethod, $activePmIds ) ? 10 : 100 ),
									'title' => $paymentMethodName,
									'pmid' => $paymentMethod,
									'pmname' => $paymentMethodName
								] );
						if ( $child instanceof \Magento\Config\Model\Config\Structure\Element\Group ) {
							$childData['children'] = [];
							foreach ( $child->getChildren() as $subchild ) {
								$childData['children'][$subchild->getId()] = array_merge( $subchild->getData(), [
									'path' => $newPath . '/' . $childData['id'],
									'label' => sprintf( $subchild->getLabel(), $paymentMethod )
								] );
							}
						}
						$newChildren[$childData['id']] = $childData;
					}
					$newData['children'] = array_merge( $newData['children'], $newChildren );
				}
			}

			$result->setData( $newData, $this->_scopeDefiner->getScope() );
		}
		return $result;
	}
}
