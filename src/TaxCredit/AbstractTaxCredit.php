<?php

namespace Rikudou\CzechIncomeTax\TaxCredit;

abstract class AbstractTaxCredit implements TaxCreditInterface
{
    /**
     * Whether the amount can be subtracted even if the tax is already zero
     *
     * @return bool
     */
    public function isSubtractableBelowZero(): bool
    {
        return false;
    }

    /**
     * Returns the max count of instances of this class that can be present in one instance of tax calculator.
     * Should be an integer but is typehinted float so that you can return INF
     *
     * @return int|float
     */
    public function getMaxCount(): float
    {
        return 1;
    }
}
