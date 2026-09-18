<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 *
 * PHPUnit bootstrap for the CardGate Magento 2 module.
 *
 * Registers a minimal PSR-4 autoloader for the module's own classes so that the
 * standalone unit tests can run on PHP 8.3 without a full Magento installation.
 */

$sModuleRoot = dirname(__DIR__);

spl_autoload_register(static function ($sClass) use ($sModuleRoot) {
    $sPrefix = 'Cardgate\\Payment\\';
    if (0 !== strpos($sClass, $sPrefix)) {
        return;
    }
    $sRelative = substr($sClass, strlen($sPrefix));
    $sFile = $sModuleRoot . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $sRelative) . '.php';
    if (is_file($sFile)) {
        require_once $sFile;
    }
});
