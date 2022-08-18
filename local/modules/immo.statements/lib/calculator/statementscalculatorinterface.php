<?php

namespace Immo\Statements\Calculator;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

interface StatementsCalculatorInterface
{
    public function __construct(array $element);

    /**
     * Возвращает данные HL-элемента
     *
     * @return array
     */
    public function getElement(): array;


}