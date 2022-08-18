<?php
namespace Vigr\Budget\Integration\Iblock;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Vigr\Budget\UserField\Types\PropertySortFieldsType;
use Bitrix\Main\Page\Asset;

/**
 * Class SortFields
 * @package Vigr\Budget\Integration\Iblock
 * Поле выбора юзеров без сортировки
 */
class SortFields
{
    public const
        USER_TYPE_ID = 'propertysortfields';

    public static function getIBlockPropertyDescription(){

        return [
            'PROPERTY_TYPE'=>'S',
            'USER_TYPE'=>PropertySortFieldsType::USER_TYPE_ID,
            'DESCRIPTION'=>'Тестовое свойство',
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML'),
            'VIEW_CALLBACK' => [static::class, 'renderEdit'],
            'EDIT_CALLBACK' => [static::class, 'renderView'],
            'PrepareSettings' => array(__CLASS__, 'prepareSettings'),
            'GetSettingsHTML' => array(__CLASS__, 'getSettingsHTML'),
            'USE_FIELD_COMPONENT' => true
        ];
    }


    public static function getUserTypeDescription(){
        return PropertySortFieldsType::getUserTypeDescription();
    }

    public static function GetPublicEditHTML($property, $value, $controlSettings){
        global $APPLICATION;
        if($value['VALUE'] && is_array($value)){
            $valueField = $value['VALUE'];
        }else{
            $valueField = '';
        }

        if($valueField === 'A'){
            $valueField = '';
        }
        $signer = new \Bitrix\Main\Security\Sign\Signer;

        $html = "<input name = 'sortField' type='hidden' value = ".$property['USER_TYPE_SETTINGS']['FIELD'].">";
        $html .= "<input value = '".$valueField."' class = 'propName' name = ".$property['FIELD_ID']." type='hidden' value = ".$property['USER_TYPE_SETTINGS']['FIELD'].">";
        $html .= "<div class = 'res'>";

        Asset::getInstance()->addJs('/local/modules/vigr.budget/js/prop.js');

        return $html;
    }

    public static function GetPublicViewHTML($property, $value, $controlSettings){
        global $APPLICATION;
        $html = '';

        if($property['VALUE']){
            $arUsers = json_decode($property['VALUE'],1)['users'];

            foreach ($arUsers as $arUser){
                $html.= $arUser['name'].'<br>';
            }
        }else{
            $html = 'Нет данных';
        }

        return $html;
    }

    public static function prepareSettings($property)
    {
        if(!is_array($property['USER_TYPE_SETTINGS']))
            $property['USER_TYPE_SETTINGS'] = array();

        return $property;
    }

    public static function getSettingsHTML($property, $controlSettings, &$propertyFields){
        global $APPLICATION;
        if(!is_array($property['USER_TYPE_SETTINGS']))
            $property['USER_TYPE_SETTINGS'] = ['TEST'=>'1'];

        $arUrls = explode('/',$APPLICATION->GetCurDir());

        $arProps = self::getPropsByEntity($arUrls[3]);
        $html = '<tr>';
        $html .= '<td> Выбрать свойство для привязки';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<select name = "USER_TYPE_SETTINGS[FIELD]">';

        foreach ($arProps as $key=>$propName){
            $selected = '';
            if($key == $property['USER_TYPE_SETTINGS']['FIELD']){
                $selected = 'selected';
            }
            $html.="<option ".$selected." value = ".$key.">".$propName."</option>";
        }

        $html .= '</select>';
        $html .= '</td>';
        $html .= '</tr>';
        return $html;
    }


    /**
     * @param $iblockId
     * @return array
     * Получить свойства сущности
     */
    public static function getPropsByEntity($iblockId){
        $arProps = [];
        $dbProps = \CIBlockProperty::GetList(
            [

            ],
            [
                'IBLOCK_ID'=>$iblockId,
                'USER_TYPE'=>'employee'
            ]
        );

        while($arProp = $dbProps->fetch()){
            $arProps[$arProp['ID']] = $arProp['NAME'];
        }

        return $arProps;
    }
}