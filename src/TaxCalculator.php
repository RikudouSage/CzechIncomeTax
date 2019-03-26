<?php

namespace Rikudou\CzechIncomeTax;

use Rikudou\CzechIncomeTax\Enums\PercentExpense;
use Rikudou\CzechIncomeTax\Exception\TaxCalculatorLogicException;
use Rikudou\CzechIncomeTax\TaxCredit\TaxCreditInterface;

final class TaxCalculator
{
    /**
     * @var float
     */
    private $income = 0;

    /**
     * @var float|string
     */
    private $expenses = 0;

    /**
     * @var TaxCreditInterface[][]
     */
    private $taxCredits = [];

    /**
     * @var float[]
     */
    private $advanceTaxes = [];

    /**
     * @return float
     */
    public function getIncome(): float
    {
        return $this->income;
    }

    public function addIncome(float $income): TaxCalculator
    {
        $this->income += $income;

        return $this;
    }

    /**
     * @param float $income
     *
     * @return TaxCalculator
     */
    public function setIncome(float $income): TaxCalculator
    {
        $this->income = $income;

        return $this;
    }

    /**
     * @return float
     */
    public function getExpenses(): float
    {
        if (is_string($this->expenses)) {
            switch ($this->expenses) {
                case PercentExpense::PERCENT_80:
                    return $this->income * 0.8;
                case PercentExpense::PERCENT_60:
                    return $this->income * 0.6;
                case PercentExpense::PERCENT_40:
                    return $this->income * 0.4;
                case PercentExpense::PERCENT_30:
                    return $this->income * 0.3;
            }
        }
        if (!is_numeric($this->expenses)) {
            throw new TaxCalculatorLogicException(sprintf('Expenses must be a float or %s constant', PercentExpense::class));
        }

        return floatval($this->expenses);
    }

    public function addExpense(float $expense): TaxCalculator
    {
        if (is_string($this->expenses)) {
            throw new TaxCalculatorLogicException("Cannot add expense if you're using a percent expenses, please set expenses to a number before calling addExpense()");
        }
        $this->expenses += $expense;

        return $this;
    }

    public function setExpenses($expenses): TaxCalculator
    {
        if (!is_numeric($expenses)) {
            if (!in_array($expenses, [
                PercentExpense::PERCENT_30,
                PercentExpense::PERCENT_40,
                PercentExpense::PERCENT_60,
                PercentExpense::PERCENT_80,
            ], true)) {
                throw new TaxCalculatorLogicException(sprintf('Expenses must be a float or %s constant', PercentExpense::class));
            }
            $this->expenses = $expenses;
        } else {
            $this->expenses = floatval($expenses);
        }

        return $this;
    }

    /**
     * @return TaxCreditInterface[]
     */
    public function getTaxCredits(): array
    {
        $result = [];
        foreach ($this->taxCredits as $taxCredits) {
            foreach ($taxCredits as $taxCredit) {
                $result[] = $taxCredit;
            }
        }

        return $result;
    }

    /**
     * @param TaxCreditInterface $taxCredit
     *
     * @return TaxCalculator
     */
    public function addTaxCredit(TaxCreditInterface $taxCredit): TaxCalculator
    {
        $class = get_class($taxCredit);
        if (!isset($this->taxCredits[$class])) {
            $this->taxCredits[$class] = [];
        }
        if (count($this->taxCredits[$class]) >= $taxCredit->getMaxCount()) {
            throw new TaxCalculatorLogicException(sprintf('There can only be %d instances of %s', $taxCredit->getMaxCount(), $class));
        }
        $this->taxCredits[$class][] = $taxCredit;

        return $this;
    }

    /**
     * @param TaxCreditInterface[] $taxCredits
     *
     * @return TaxCalculator
     */
    public function setTaxCredits(array $taxCredits): TaxCalculator
    {
        $result = [];
        foreach ($taxCredits as $taxCredit) {
            if (!$taxCredit instanceof TaxCreditInterface) {
                throw new \InvalidArgumentException('All tax credits must be instance of ' . TaxCreditInterface::class);
            }
            $maxCount = $taxCredit->getMaxCount();
            $class = get_class($taxCredit);

            if (!isset($result[$class])) {
                $result[$class] = [];
            }

            if (count($result[$class]) >= $maxCount) {
                throw new TaxCalculatorLogicException(sprintf('There can only be %d instances of %s', $maxCount, $class));
            }

            $result[$class][] = $taxCredit;
        }

        $this->taxCredits = $result;

        return $this;
    }

    /**
     * @return float
     */
    public function getAdvanceTax(): float
    {
        return array_sum($this->advanceTaxes);
    }

    /**
     * @param float $advanceTax
     *
     * @return TaxCalculator
     */
    public function addAdvanceTax(float $advanceTax): TaxCalculator
    {
        $this->advanceTaxes[] = $advanceTax;

        return $this;
    }

    /**
     * @param float $advanceTax
     *
     * @return TaxCalculator
     */
    public function setAdvanceTax(float $advanceTax): TaxCalculator
    {
        $this->advanceTaxes = [];
        $this->advanceTaxes[] = $advanceTax;

        return $this;
    }

    public function getCalculatedTax(): float
    {
        $income = $this->getIncome();
        $expenses = $this->getExpenses();

        $taxesBase = $income - $expenses;

        $taxes = intval($taxesBase / 100) * 100;
        $taxes = ceil($taxes * 0.15);
        foreach ($this->getTaxCredits() as $taxCredit) {
            if (!$taxCredit->isSubtractableBelowZero()) {
                $taxes -= $taxCredit->getAmount();
            }
        }
        if ($taxes < 0) {
            $taxes = 0;
        }
        foreach ($this->getTaxCredits() as $taxCredit) {
            if ($taxCredit->isSubtractableBelowZero()) {
                $taxes -= $taxCredit->getAmount();
            }
        }
        $taxes -= $this->getAdvanceTax();

        return $taxes;
    }
}
