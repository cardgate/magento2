<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Cardgate_Payment',
    __DIR__
);

$vendorDir = require BP . '/app/etc/vendor_path.php';
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";
/** @var \Composer\Autoload\ClassLoader $composerAutoloader */
$composerAutoloader = include $vendorAutoload;
$composerAutoloader->addPsr4('curopayments\\', array(__DIR__ . '/lib'));