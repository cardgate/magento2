<?php
/**
 * Copyright (c) 2024 CardGate B.V.
 * All rights reserved.
 * See LICENSE for license details.
 */
namespace Cardgate\Payment\Test\Unit\Subscription;

use Cardgate\Payment\Model\Subscription\AllowedMethods;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Standalone unit test verifying the subscription allowed-methods gating.
 *
 * It does not require a Magento installation, so it can be executed directly
 * with PHPUnit on PHP 8.3.
 */
class AllowedMethodsTest extends TestCase
{
    /**
     * @var AllowedMethods
     */
    private $oAllowedMethods;

    protected function setUp(): void
    {
        $this->oAllowedMethods = new AllowedMethods();
    }

    public function testAllAllowedMethodsAreReported(): void
    {
        $aAllowed = $this->oAllowedMethods->getAllowed();

        $this->assertIsArray($aAllowed);
        $this->assertContains('cardgate_ideal', $aAllowed);
        $this->assertContains('cardgate_creditcard', $aAllowed);
        $this->assertContains('cardgate_bancontact', $aAllowed);
        $this->assertContains('cardgate_directdebit', $aAllowed);
        $this->assertContains('cardgate_przelewy24', $aAllowed);
        $this->assertContains('cardgate_onlineueberweisen', $aAllowed);
    }

    #[DataProvider('allowedMethodProvider')]
    public function testIsAllowedReturnsTrueForAllowedMethods(string $sMethod): void
    {
        $this->assertTrue($this->oAllowedMethods->isAllowed($sMethod));
    }

    public static function allowedMethodProvider(): array
    {
        return [
            ['cardgate_bancontact'],
            ['cardgate_mistercash'],
            ['cardgate_creditcard'],
            ['cardgate_directdebit'],
            ['cardgate_ideal'],
            ['cardgate_przelewy24'],
            ['cardgate_onlineueberweisen'],
        ];
    }

    public function testIsAllowedReturnsFalseForDisallowedMethods(): void
    {
        $this->assertFalse($this->oAllowedMethods->isAllowed('cardgate_paypal'));
        $this->assertFalse($this->oAllowedMethods->isAllowed('checkmo'));
        $this->assertFalse($this->oAllowedMethods->isAllowed(''));
        $this->assertFalse($this->oAllowedMethods->isAllowed(null));
    }
}
