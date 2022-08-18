<?php
namespace Vigr\CustomField\Integration\Iblock;


use Vigr\CustomField\UserField\Types\ulAndHeadsType;
use Bitrix\Main\UI\Extension;
class propertyUlAndHeads
{
    public const
        RENDER_COMPONENT = 'vigr:budget.be';

    public static function getIBlockPropertyDescription(){
        return [
            'PROPERTY_TYPE'=>'S',
            'USER_TYPE'=>ulAndHeadsType::USER_TYPE_ID,
            'DESCRIPTION'=>'ЗО: Юрлица и руководители',
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
            "GetAdminListViewHTML" => array(__CLASS__,'GetPublicViewHTML'),
            "GetAdminViewHTML" => array(__CLASS__,'GetPublicViewHTML'),
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

        Extension::load('ui.bootstrap4');
        Extension::load('vigr.usercard');
        global $USER;
        \CJSCore::Init(array('vigr.usercard'));
        $APPLICATION->IncludeComponent(
            'vigr:usertype.ulandheads',
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
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'vigr:usertype.ulandheads',
            'view',
            [
                'userField' => $arProperty,
                'page'=>'view'
            ]
        );

        $s = ob_get_contents();
        ob_end_clean();

        return  $s;
    }

    public static function GetAdminListViewHTML($arProperty,$value){
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'vigr:usertype.ulandheads',
            'view',
            [
                'userField' => $arProperty,
                'page'=>'view'
            ]
        );

        $s = ob_get_contents();
        ob_end_clean();

        return  $s;
    }
}
















