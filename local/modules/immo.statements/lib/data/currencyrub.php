<?php

namespace Immo\Statements\Data;

use CCurrencyLang;

/**
 * Форматирует число в формат валюты
 */
class CurrencyRub
{
    private array $currencyFormat;

    public function __construct(string $sCurrencyCode = 'RUB')
    {
        $this->currencyFormat = CCurrencyLang::GetFormatDescription($sCurrencyCode);
        $this->currencyFormat['DEC_POINT'] = ',';
        $this->currencyFormat['THOUSANDS_SEP'] = ' ';
        $this->currencyFormat['HIDE_ZERO'] = 'N';
    }

    /**
     * Преобразования числа в формат валюыт
     * @param float $price
     * @param $bUserTemplate
     * @return mixed
     */
    public function format(float $price, $bUserTemplate = false)
    {
        return CCurrencyLang::formatValue($price, $this->currencyFormat, $bUserTemplate);
    }
}