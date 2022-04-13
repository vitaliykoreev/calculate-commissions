<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    public function testGetCountryByBinReturnsCorrectValue(): void
    {
        require '../app.php';

        $this->assertEquals('UA', getCountryCodeByBin('44411114'));
        $this->assertEquals('ID', getCountryCodeByBin('54441223'));
    }

    public function testGetExchangeRateByCurrency(): void
    {
        require '../app.php';

        $this->assertEqualsWithDelta(1.0, getExchangeRateByCurrency('USD'), .1);
    }

    public function testIsEu(): void
    {
        require '../app.php';

        $this->assertTrue(isEu('IT'));
        $this->assertNotTrue(isEu('UK'));
    }

    public function testCalculateCommissionByCountry(): void
    {
        require '../app.php';

        $this->assertEquals(1, calculateCommissionByCountry(100, 'EUR', 'IT'));
        $this->assertEqualsWithDelta(.46, calculateCommissionByCountry(50, 'USD', 'IT'), .1);
        $this->assertEqualsWithDelta(1.65, calculateCommissionByCountry(10000, 'JPY', 'JP'), .3);
    }
}