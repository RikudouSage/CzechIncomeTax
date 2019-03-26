<?php

namespace Rikudou\Tests\CzechIncomeTax;

use PHPUnit\Framework\TestCase;
use Rikudou\CzechIncomeTax\TaxCredit\TaxpayerTaxCredit;

class TaxpayerTaxCreditTest extends TestCase
{

    public function testGetAmount()
    {
        $this->assertEquals(24840, $this->getInstance()->getAmount(), 'The current taxpayer credit amount is 24 840');
    }

    public function testIsSubtractableBelowZero()
    {
        $this->assertFalse($this->getInstance()->isSubtractableBelowZero(), 'Taxpayer credit is not allowed to go below zero');
    }

    public function testGetMaxCount()
    {
        $this->assertEquals(1, $this->getInstance()->getMaxCount(), 'Taxpayer credit can be used only once');
    }

    private function getInstance()
    {
        return new TaxpayerTaxCredit();
    }
}
