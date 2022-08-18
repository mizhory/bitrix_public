<?php

namespace Vigr\CustomField\Integration\Iblock;

use Vigr\CustomField\UserField\Types\headAndSuccessors;
use \Bitrix\Main\UI\Extension;
class propertyHeadAndSuccessors{
    public const
        RENDER_COMPONENT = 'usertype.headsandsuccessors';

    public static function getIBlockPropertyDescription(){
        return [
            'PROPERTY_TYPE'=>'S',
            'USER_TYPE'=>headAndSuccessors::USER_TYPE_ID,
            'DESCRIPTION'=>'ЗО: Юрлица, руководители и преемники',
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
            "GetAdminEditHTML" => array(__CLASS__,'GetAdminEditHTML'),
            'VIEW_CALLBACK' => [static::class, 'renderEdit'],
            'EDIT_CALLBACK' => [static::class, 'renderView'],
            'PrepareSettings' => array(__CLASS__, 'prepareSettings'),
            'GetSettingsHTML' => array(__CLASS__, 'getSettingsHTML'),
            'USE_FIELD_COMPONENT' => false
        ];
    }

    public static function GetPublicEditHTML($arProperty,$value){
        global $APPLICATION;
        ob_start();

        Extension::load('vigr.usercard');
        \CJSCore::Init(['vigr.usercard']);
        $APPLICATION->IncludeComponent(
            'vigr:usertype.headsandsuccessors',
            'edit',
            [
                'userField' => $arProperty,
                'page'=>'edit'
            ]
        );

        $s = ob_get_contents();
        ob_end_clean();

        return  $s;
    }

    public static function GetPublicViewHTML($arProperty,$value){
        return '2';
    }

    public static function GetAdminEditHTML($arProperty,$value){
        return '2';
    }
}