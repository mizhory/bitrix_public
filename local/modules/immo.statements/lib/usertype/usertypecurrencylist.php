<?php

namespace Immo\Statements\UserType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\StringType;

Loc::loadMessages(__FILE__);

class UserTypeCurrencyList extends BaseType
{
    public const USER_TYPE_ID = 'CURRENCY_LIST';
    public const RENDER_COMPONENT = 'immo:immo.field.currencylist';

    public static function getUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => static::USER_TYPE_ID,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => Loc::getMessage("UF_CURRENCY_LIST_DESCRIPTION"),
            'BASE_TYPE' => StringType::USER_TYPE_ID
        ];
    }

    public static function getDbColumnType(): string
    {
        return StringType::getDbColumnType();
    }
}