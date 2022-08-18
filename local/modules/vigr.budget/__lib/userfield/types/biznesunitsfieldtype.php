<?php

namespace Vigr\Budget\UserField\Types;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\StringType;
use CUserTypeManager;
use \Bitrix\Iblock;
use Bitrix\Main\UserField\Types\DateType;

class biznesUnitsFieldType extends BaseType
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
                "GetPublicEditHTML" => array(__CLASS__,'GetPublicEditHTML')
            ],
            parent::getUserTypeDescription()
        );
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

