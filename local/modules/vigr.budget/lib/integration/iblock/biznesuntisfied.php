<?php

use Vigr\Budget\UserField\Types\PropertySortFieldsType;

use Bitrix\Main\UserField\Types\BaseType;


class biznesUnitsField extends BaseType{

    public const
        USER_TYPE_ID = 'propertybiznesuntis',
        RENDER_COMPONENT = 'bitrix:main.field.datetime';

    public static function getUserTypeDescription() : array
    {
        return array_merge(
            [
                "USER_TYPE_ID" => self::USER_TYPE_ID,
                "DESCRIPTION" => 'Тестовое свойство',
                
                "GetPublicFilterHTML" => array(__CLASS__,'GetPublicFilterHTML'),
                "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML')
            ],
            parent::getUserTypeDescription()
        );
    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        return 'text';
    }

}