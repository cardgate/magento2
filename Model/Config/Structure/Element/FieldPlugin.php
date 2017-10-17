<?php
/**
 * Copyright © 2016 CardGate.
 * All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Cardgate\Payment\Model\Config\Structure\Element;

/**
 * Config Field plugin for rewriting ConfigPaths
 *
 * @author DBS B.V.
 * @package Magento2
 */
class FieldPlugin {

	/**
	 *
	 * @var \Magento\Framework\App\RequestInterface
	 */
	protected $_request;

	/**
	 *
	 * @param \Magento\Framework\App\RequestInterface $request
	 */
	public function __construct ( \Magento\Framework\App\RequestInterface $request ) {
		$this->_request = $request;
	}

	/**
	 * alter getConfigPath's output
	 *
	 * @param \Magento\Config\Model\Config\Structure\Element\Field $subject
	 * @param \Closure $proceed
	 * @return string|null
	 */
	public function aroundGetConfigPath ( \Magento\Config\Model\Config\Structure\Element\Field $subject, \Closure $proceed ) {
		$configPath = $proceed();
		if ( ! isset( $configPath ) && $this->_request->getParam( 'section' ) == 'cardgate' ) {
			if ( ! preg_match( '@^(cardgate/version)|(cardgate/global)/@', $subject->getPath() ) ) {
				$configPath = preg_replace( '@^cardgate/@', 'payment/', $subject->getPath() );
			}
		}
		return $configPath;
	}
}
