<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cardgate\Payment;

\Magento\Framework\Component\ComponentRegistrar::register(
	\Magento\Framework\Component\ComponentRegistrar::MODULE,
	'Cardgate_Payment',
	__DIR__
);

/**
 * Dynamic payment method class loader.
 */
class Registration {

	public static function autoload( $sClassName ) {
		$sClassPrefix = 'Cardgate\Payment\Model\PaymentMethod';
		if ( FALSE !== strstr( $sClassName, $sClassPrefix ) ) {
			$sPM = substr( $sClassName, strlen( $sClassPrefix ) + 1 );
			$oFileSystem = \Magento\Framework\App\ObjectManager::getInstance()->get( \Magento\Framework\Filesystem::class );
			$oDirectory = $oFileSystem->getDirectoryWrite( \Magento\Framework\App\Filesystem\DirectoryList::TMP );
			$sVersion = \Magento\Framework\App\ObjectManager::getInstance()->get( \Magento\Framework\Module\ModuleListInterface::class )->getOne( 'Cardgate_Payment' )['setup_version'];
			$sFilename = "paymentmethod_cardgate_{$sVersion}_{$sPM}.php";
			if ( ! $oDirectory->isFile( $sFilename ) ) {
				$oDirectory->writeFile( $sFilename, "<?php\nnamespace Cardgate\\Payment\\Model\\PaymentMethod;\nclass {$sPM} extends \\Cardgate\\Payment\\Model\\PaymentMethod\\nonexistent {\n}\n" );
			}
			require $oDirectory->getAbsolutePath( $sFilename );
		}
	}

}

spl_autoload_register( [ '\Cardgate\Payment\Registration', 'autoload' ] );

$vendorDir = require BP . '/app/etc/vendor_path.php';
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";
/** @var \Composer\Autoload\ClassLoader $composerAutoloader */
$composerAutoloader = include $vendorAutoload;
$composerAutoloader->addPsr4('curopayments\\', array(__DIR__ . '/lib'));
