<?php

namespace Vigr\Budget\Integration\Iblock;

use Bitrix\Main\UserField\Types\BaseType;


/**
 * Class biznesUnitsField
 * @package Vigr\Budget\Integration\Iblock
 * Поле БЕ
 */
class biznesUnitsField extends BaseType{

    public const
        USER_TYPE_ID = 'propertybiznesuntis',
        RENDER_COMPONENT = 'vigr:budget.be';

    public static function getUserTypeDescription() : array
    {
        return array_merge(
            [
                'PROPERTY_TYPE'=>'S',
                'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
                "USER_TYPE_ID" => self::USER_TYPE_ID,
                "DESCRIPTION" => 'Тестовое свойство2',
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
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => 'Тестовое свойство2',
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING
        ];
    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        return 'text';
    }

}