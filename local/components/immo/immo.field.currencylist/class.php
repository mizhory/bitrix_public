<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Crm\Currency;
use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\Loader;
use Immo\Statements\UserType\UserTypeCurrencyList;

Loader::includeModule('crm');

class CurrencyListUfComponent extends BaseUfComponent
{

    protected static function getUserTypeId(): string
    {
        return UserTypeCurrencyList::USER_TYPE_ID;
    }

    protected function prepareResult(): void
    {
        $currencies = Currency::getCurrencyList();
        $value = $this->arResult['userField']['VALUE'];

        $this->arResult['CURRENCY_LIST'] = $currencies;
        $this->arResult['CURRENCY_VALUE_NAME'] = $value;

    }
}