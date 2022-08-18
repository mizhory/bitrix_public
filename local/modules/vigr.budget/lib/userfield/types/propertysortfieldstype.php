<?php

namespace Vigr\Budget\UserField\Types;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\StringType;
use CUserTypeManager;
use \Bitrix\Iblock;
use Bitrix\Main\UserField\Types\DateType;

class PropertySortFieldsType extends BaseType
{
    public const
        USER_TYPE_ID = 'propertysortfields',
        RENDER_COMPONENT = 'bitrix:main.field.datetime';


    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => 'Тестовое свойство',
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING
        ];
    }

    public static function getUserTypeDescription():array{
        return array_merge(
            [
                "USER_TYPE_ID" => self::USER_TYPE_ID,
                "DESCRIPTION" => 'Тестовое свойство',
                "GetPublicFilterHTML" => array(__CLASS__,'GetPublicFilterHTML'),
               "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
                'VIEW_CALLBACK' => [static::class, 'renderEdit'],
                'EDIT_CALLBACK' => [static::class, 'renderView'],
                'USE_FIELD_COMPONENT' => true
            ],
            parent::getUserTypeDescription()
        );
    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        return 'string';
    }

}

