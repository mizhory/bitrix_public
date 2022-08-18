<?php
namespace Vigr\CustomField\Integration\Iblock;



use Vigr\CustomField\UserField\Types\vacationDaysType;

class propertyVacationDays
{
    public const
        RENDER_COMPONENT = 'vigr:budget.be';

    public static function getIBlockPropertyDescription(){
        return [
            'PROPERTY_TYPE'=>'S',
            'USER_TYPE'=>vacationDaysType::USER_TYPE_ID,
            'DESCRIPTION'=>'Дни отпуска',
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
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
        $APPLICATION->IncludeComponent(
            'vigr:usertype.vacationdays',
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
}
















