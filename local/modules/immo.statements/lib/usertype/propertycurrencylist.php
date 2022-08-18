<?php

namespace Immo\Statements\UserType;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Renderer;
use Bitrix\Main\UserField\Types\StringType;

Loc::loadMessages(__FILE__);
Loc::loadMessages(__DIR__ . '/usertypecurrencylist.php');

class PropertyCurrencyList
{
    const RENDER_COMPONENT = 'immo:property.currency.list';
    const RENDER_COMPONENT_TEMPLATE_EDIT_FORM = 'edit_form';

    public static function getUserTypeDescription()
    {
        $ufTypeDescription = UserTypeCurrencyList::getUserTypeDescription();

        return [
            'PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
            'USER_TYPE' => UserTypeCurrencyList::USER_TYPE_ID,
            'DESCRIPTION' => $ufTypeDescription['DESCRIPTION'],
            'GetPropertyFieldHtml' => [__CLASS__, 'getPropertyFieldHtml'],
            'GetPublicEditHTMLMulty' => [__CLASS__, 'getPublicEditHTMLMulty'],
        ];
    }

    public static function getPropertyFieldHtml($property, $value, $htmlControl): string
    {
        $params = [
            'property' => $property,
            'current_value' => $value['VALUE'],
            'select_name' => $htmlControl['VALUE']
        ];

        return self::renderComponent($params, self::RENDER_COMPONENT_TEMPLATE_EDIT_FORM);
    }

    public static function getPublicViewHtml($property, $value, $htmlControl)
    {
        return self::renderComponent([
            'property' => $property,
            'current_value' => $value['VALUE']
        ]);
    }

    protected static function renderComponent($params = [],$template = '')
    {
        ob_start();

        global $APPLICATION;
        $APPLICATION->IncludeComponent(self::RENDER_COMPONENT, $template, $params);

        return ob_get_clean();
    }


}