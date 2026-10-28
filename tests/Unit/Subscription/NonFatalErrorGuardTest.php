<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Test\Unit\Subscription;

use Cardgate\Payment\Model\Subscription\NonFatalErrorGuard;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_stubs/magento.php';

/**
 * Regression test for the billing cycle error guard.
 *
 * Magento's error handler turns every PHP warning into an exception. A warning from
 * an unrelated subsystem (an unwritable var/cache directory) therefore aborted the
 * renewal order and with it the recurring payment.
 */
class NonFatalErrorGuardTest extends TestCase
{

    /**
     * A warning raised inside the callback does not abort it.
     *
     * @return void
     */
    public function testWarningDoesNotAbortTheCallback()
    {
        $logger = new \Cardgate\Payment\Test\Unit\Stub\LoggerStub();
        $guard = new NonFatalErrorGuard($logger);

        $result = $guard->run(function () {
            trigger_error('cache directory not writable', E_USER_WARNING);
            return 'charged';
        });

        $this->assertSame('charged', $result);
    }

    /**
     * Reproduces the production situation: Magento's error handler is active and would
     * turn the cache warning into an exception, killing the billing cycle.
     *
     * @return void
     */
    public function testMagentoErrorHandlerIsNeutralisedDuringBilling()
    {
        // Magento\Framework\App\ErrorHandler::handler() in a nutshell.
        set_error_handler(function ($errorNo, $errorStr) {
            throw new \Exception('Warning: ' . $errorStr);
        });

        try {
            $guard = new NonFatalErrorGuard(new \Cardgate\Payment\Test\Unit\Stub\LoggerStub());
            $charged = $guard->run(function () {
                trigger_error(
                    'file_put_contents(var/cache//mage-tags/mage---c67_DB_PDO_MYSQL_DDL):'
                    . ' Failed to open stream: Permission denied',
                    E_USER_WARNING
                );
                return true;
            });
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($charged, 'a cache warning must not abort the billing cycle');
    }

    /**
     * Real exceptions still propagate and the previous error handler is restored.
     *
     * @return void
     */
    public function testExceptionsPropagateAndHandlerIsRestored()
    {
        $guard = new NonFatalErrorGuard(new \Cardgate\Payment\Test\Unit\Stub\LoggerStub());

        $handlerBefore = set_error_handler(null);
        restore_error_handler();

        try {
            $guard->run(function () {
                throw new \RuntimeException('gateway declined');
            });
            $this->fail('exception was swallowed');
        } catch (\RuntimeException $e) {
            $this->assertSame('gateway declined', $e->getMessage());
        }

        $handlerAfter = set_error_handler(null);
        restore_error_handler();
        $this->assertSame($handlerBefore, $handlerAfter);
    }
}
