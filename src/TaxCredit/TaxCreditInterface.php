<?php

namespace Rikudou\CzechIncomeTax\TaxCredit;

interface TaxCreditInterface
{
    /**
     * Returns the amount that will be subtracted from the tax
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Whether the amount can be subtracted even if the tax is already zero
     *
     * @return bool
     */
    public function isSubtractableBelowZero(): bool;

    /**
     * Returns the max count of instances of this class that can be present in one instance of tax calculator.
     * Should be an integer but is typehinted float so that you can return INF
     *
     * @return int|float
     */
    public function getMaxCount(): float;
}
