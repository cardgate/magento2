<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Model\Subscription;

use Psr\Log\LoggerInterface;

/**
 * Runs a billing cycle while shielding it from non-fatal PHP diagnostics.
 *
 * Magento installs an error handler (Magento\Framework\App\ErrorHandler) that turns
 * every PHP warning or notice into an exception. In a web request that is a sane
 * default, but for unattended background billing it is fatal: a purely cosmetic
 * warning - for example an unwritable var/cache directory when the cron user differs
 * from the web server user, which makes Cm_Cache_Backend_File::_saveTags() emit a
 * "file_put_contents(): Failed to open stream: Permission denied" warning - aborts the
 * renewal order and thereby the recurring payment.
 *
 * Warnings and notices are therefore logged and execution continues; real errors and
 * exceptions are untouched and still propagate to the caller.
 */
class NonFatalErrorGuard
{
    /**
     * Error levels that must never abort a billing cycle.
     */
    private const NON_FATAL_LEVELS = E_WARNING
        | E_NOTICE
        | E_USER_WARNING
        | E_USER_NOTICE
        | E_DEPRECATED
        | E_USER_DEPRECATED;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute the given callback with non-fatal PHP diagnostics demoted to log entries.
     *
     * @param callable $callback
     * @return mixed The return value of the callback.
     * @throws \Throwable Anything the callback throws.
     */
    public function run(callable $callback)
    {
        $logger = $this->logger;
        set_error_handler(
            function ($errorNo, $errorStr, $errorFile = '', $errorLine = 0) use ($logger) {
                $logger->warning(
                    'CardGate subscription: ignored non-fatal error during billing: '
                    . $errorStr . ' in ' . $errorFile . ' on line ' . $errorLine
                );
                return true;
            },
            self::NON_FATAL_LEVELS
        );

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }
}
