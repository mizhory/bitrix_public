<?php
namespace Vigr\CustomField\UserField\Types;

class headAndSuccessors extends \Bitrix\Main\UserField\Types\StringType {
    public const
        USER_TYPE_ID = 'headandsuccessors',
        RENDER_COMPONENT = 'bitrix:main.field.datetime';

    public static function getDescription(): array
    {

        return [
            'DESCRIPTION'=>'ЗО: Юрлица, руководители и преемники',
            'BaseType'=>\CUserTypeManager::BASE_TYPE_STRING
        ];
    }


    public static function getUserTypeDescription(): array
    {
        return array_merge(
            [
                "USER_TYPE_ID" => self::USER_TYPE_ID,
                "DESCRIPTION" => 'ЗО: Юрлица, руководители и преемники',
                "GetPublicFilterHTML" => array(__CLASS__,'GetPublicFilterHTML'),
                "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
                'VIEW_CALLBACK' => [static::class, 'renderEdit'],
                'EDIT_CALLBACK' => [static::class, 'renderView'],
                'USE_FIELD_COMPONENT' => true
            ],
            parent::getUserTypeDescription()
        );
    }
}