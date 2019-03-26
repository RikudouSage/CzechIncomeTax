<?php

namespace Rikudou\Tests\CzechIncomeTax;

use PHPUnit\Framework\TestCase;
use Rikudou\CzechIncomeTax\Enums\PercentExpense;
use Rikudou\CzechIncomeTax\Exception\TaxCalculatorLogicException;
use Rikudou\CzechIncomeTax\TaxCalculator;
use Rikudou\CzechIncomeTax\TaxCredit\AbstractTaxCredit;
use Rikudou\CzechIncomeTax\TaxCredit\TaxCreditInterface;
use Rikudou\CzechIncomeTax\TaxCredit\TaxpayerTaxCredit;

class TaxCalculatorTest extends TestCase
{
    public function testGetExpenses()
    {
        $instance = $this->getInstance();
        $this->assertEquals(0, $instance->getExpenses(), 'The default value should be zero');

        $instance->setExpenses(500);
        $this->assertEquals(500, $instance->getExpenses());

        $instance->setIncome(100);

        $instance->setExpenses(PercentExpense::PERCENT_80);
        $this->assertEquals(100 * 0.8, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_60);
        $this->assertEquals(100 * 0.6, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_40);
        $this->assertEquals(100 * 0.4, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_30);
        $this->assertEquals(100 * 0.3, $instance->getExpenses());
    }

    public function testGetAdvanceTax()
    {
        $instance = $this->getInstance();

        $this->assertEquals(0, $instance->getAdvanceTax(), 'The default value should be zero');

        $instance->setAdvanceTax(500);
        $this->assertEquals(500, $instance->getAdvanceTax());
    }

    public function testGetTaxCredits()
    {
        $instance = $this->getInstance();

        $this->assertIsArray($instance->getTaxCredits(), 'The default value should be an empty array');
        $this->assertEmpty($instance->getTaxCredits(), 'The default value should be an empty array');

        $instance->addTaxCredit(new TaxpayerTaxCredit());
        $this->assertCount(1, $instance->getTaxCredits());

        $fakeCredit = new class extends AbstractTaxCredit {
            public function getAmount(): float
            {
                return 0;
            }

            public function getMaxCount(): float
            {
                return INF;
            }
        };

        $instance->addTaxCredit($fakeCredit);
        $this->assertCount(2, $instance->getTaxCredits());

        $instance->addTaxCredit($fakeCredit);
        $this->assertCount(3, $instance->getTaxCredits());
    }

    public function testGetIncome()
    {
        $instance = $this->getInstance();

        $this->assertEquals(0, $instance->getIncome(), 'The initial income should be zero');

        $instance->setIncome(500);
        $this->assertEquals(500, $instance->getIncome());
    }

    public function testAddTaxCredit()
    {
        $instance = $this->getInstance();

        $instance->addTaxCredit(new TaxpayerTaxCredit());
        $this->assertCount(1, $instance->getTaxCredits());

        $this->expectException(\TypeError::class);
        $instance->addTaxCredit('a string');
    }

    public function testAddExpense()
    {
        $instance = $this->getInstance();

        $instance->addExpense(500);
        $this->assertEquals(500, $instance->getExpenses());

        $instance->addExpense(300);
        $this->assertEquals(800, $instance->getExpenses());

        $instance->addExpense(-100);
        $this->assertEquals(700, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_60);
        $this->expectException(TaxCalculatorLogicException::class);
        $instance->addExpense(100);
    }

    public function testAddIncome()
    {
        $instance = $this->getInstance();

        $instance->addIncome(500);
        $this->assertEquals(500, $instance->getIncome());

        $instance->addIncome(300);
        $this->assertEquals(800, $instance->getIncome());

        $instance->addIncome(-100);
        $this->assertEquals(700, $instance->getIncome());
    }

    public function testAddAdvanceTax()
    {
        $instance = $this->getInstance();

        $instance->addAdvanceTax(500);
        $this->assertEquals(500, $instance->getAdvanceTax());

        $instance->addAdvanceTax(300);
        $this->assertEquals(800, $instance->getAdvanceTax());

        $instance->addAdvanceTax(-100);
        $this->assertEquals(700, $instance->getAdvanceTax());
    }

    public function testSetExpenses()
    {
        $instance = $this->getInstance();

        $instance->setExpenses(500);
        $this->assertEquals(500, $instance->getExpenses());

        $instance->setExpenses(300);
        $this->assertEquals(300, $instance->getExpenses());

        $instance->setIncome(100);

        $instance->setExpenses(PercentExpense::PERCENT_80);
        $this->assertEquals(100 * 0.8, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_60);
        $this->assertEquals(100 * 0.6, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_40);
        $this->assertEquals(100 * 0.4, $instance->getExpenses());

        $instance->setExpenses(PercentExpense::PERCENT_30);
        $this->assertEquals(100 * 0.3, $instance->getExpenses());

        $this->expectException(TaxCalculatorLogicException::class);
        $instance->setExpenses('non-numeric-string');
    }

    public function testSetIncome()
    {
        $instance = $this->getInstance();

        $instance->setIncome(500);
        $this->assertEquals(500, $instance->getIncome());

        $instance->setIncome(150);
        $this->assertEquals(150, $instance->getIncome());
    }

    public function testSetAdvanceTax()
    {
        $instance = $this->getInstance();

        $instance->setAdvanceTax(150);
        $this->assertEquals(150, $instance->getAdvanceTax());

        $instance->setAdvanceTax(300);
        $this->assertEquals(300, $instance->getAdvanceTax());
    }

    public function testSetTaxCredits()
    {
        $instance = $this->getInstance();

        $instance->setTaxCredits([
            new TaxpayerTaxCredit(),
        ]);
        $this->assertCount(1, $instance->getTaxCredits());

        $instance->setTaxCredits([]);
        $this->assertCount(0, $instance->getTaxCredits());
    }

    public function testGetCalculatedTax()
    {
        $instance = $this->getInstance();

        $instance->setIncome(10000);
        $this->assertEquals(1500, $instance->getCalculatedTax());

        $instance->setExpenses(3000);
        $this->assertEquals(1050, $instance->getCalculatedTax());

        $instance->addExpense(500);
        $this->assertEquals(975, $instance->getCalculatedTax());

        $instance->setExpenses(PercentExpense::PERCENT_80);
        $this->assertEquals(300, $instance->getCalculatedTax());

        $instance->setExpenses(PercentExpense::PERCENT_60);
        $this->assertEquals(600, $instance->getCalculatedTax());

        $instance->setExpenses(PercentExpense::PERCENT_40);
        $this->assertEquals(900, $instance->getCalculatedTax());

        $instance->setExpenses(PercentExpense::PERCENT_30);
        $this->assertEquals(1050, $instance->getCalculatedTax());

        $instance->addTaxCredit(new TaxpayerTaxCredit());
        $this->assertEquals(0, $instance->getCalculatedTax());

        $instance->addAdvanceTax(5000);
        $this->assertEquals(-5000, $instance->getCalculatedTax());

        $instance->addTaxCredit(new class implements TaxCreditInterface {
            public function getAmount(): float
            {
                return 1000;
            }

            /**
             * Whether the amount can be subtracted even if the tax is already zero
             *
             * @return bool
             */
            public function isSubtractableBelowZero(): bool
            {
                return true;
            }

            /**
             * Returns the max count of instances of this class that can be present in one instance of tax calculator.
             * Should be an integer but is typehinted float so that you can return INF
             *
             * @return int|float
             */
            public function getMaxCount(): float
            {
                return INF;
            }
        });
        $this->assertEquals(-6000, $instance->getCalculatedTax());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetExpensesNonNumeric()
    {
        $instance = $this->getInstance();
        $reflection = new \ReflectionObject($instance);

        $expenses = $reflection->getProperty('expenses');
        $expenses->setAccessible(true);
        $expenses->setValue($instance, 'non-numeric-string');

        $this->expectException(TaxCalculatorLogicException::class);
        $instance->getExpenses();
    }

    public function testAddTaxCreditWithInvalidCount()
    {
        $taxCredit = new class implements TaxCreditInterface {
            public function getAmount(): float
            {
                return 1;
            }

            public function isSubtractableBelowZero(): bool
            {
                return false;
            }

            public function getMaxCount(): float
            {
                return 1;
            }
        };

        $instance = $this->getInstance();
        $instance->addTaxCredit($taxCredit);
        $this->expectException(TaxCalculatorLogicException::class);
        $instance->addTaxCredit($taxCredit);
    }

    public function testSetTaxCreditWithInvalidCount()
    {
        $taxCredit = new class implements TaxCreditInterface {
            public function getAmount(): float
            {
                return 1;
            }

            public function isSubtractableBelowZero(): bool
            {
                return false;
            }

            public function getMaxCount(): float
            {
                return 1;
            }
        };

        $this->expectException(TaxCalculatorLogicException::class);
        $this->getInstance()->setTaxCredits([
            $taxCredit,
            $taxCredit,
        ]);
    }

    public function testSetTaxCreditsInvalidType()
    {
        $instance = $this->getInstance();

        $this->expectException(\InvalidArgumentException::class);
        $instance->setTaxCredits([
            new TaxpayerTaxCredit(),
            'a string',
        ]);
    }

    private function getInstance()
    {
        return new TaxCalculator();
    }
}
