<?php

namespace Rikudou\CzechIncomeTax\TaxCredit;

class TaxpayerTaxCredit extends AbstractTaxCredit
{
    private const AMOUNT = 24840;

    /**
     * Returns the amount that will be subtracted from the tax
     *
     * @return float
     */
    public function getAmount(): float
    {
        return self::AMOUNT;
    }
}
