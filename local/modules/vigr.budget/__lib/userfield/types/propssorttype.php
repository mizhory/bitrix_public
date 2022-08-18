<?php

namespace Vigr\Budget\UserField\Types;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use CUserTypeManager;
use Bitrix\Main\UserField\Types\DateType;

class PropsSortType extends BaseType
{
    public const
        USER_TYPE_ID = 'propssortype',
        RENDER_COMPONENT = 'bitrix:main.field.datetime';

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'USER_TYPE'=>'DateTimeOne',
            'PROPERTY_TYPE'=>'S',
            'USER_TYPE_ID'=>self::USER_TYPE_ID,
            'DESCRIPTION' => '3123',
            'GetPublicEditHTML'=>[__CLASS__,'GetPublicEditHTML'],
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
        ];
    }

    /**
     * @param array $userField
     * @param array $additionalParameters
     * @return mixed
     */
    public static function getDefaultValue(array $userField, array $additionalParameters = [])
    {
        $value = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '');
        return ($userField['MULTIPLE'] === 'Y' ? [$value] : $value);
    }

    public static function GetPublicEditHTML($arFields,$ar1,$ar2){
        ob_start();
        echo "<tr>
            <td>Привязка к полю:</td>
            <td>             
                <input type='text' name = 'NAME1' id = 'bx-lists-field-name1'>                               
            </td>
            </tr>";
        $s = ob_get_contents();
        ob_end_clean();
        return  $s;

    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        return 'string';
    }

}

