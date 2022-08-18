<?php

namespace IMMO\Manager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\UserTable;

/**
 * Класс-Менеджер для работы с пользователями и Карточкой Сотрудника
 */
class UserCardManager
{
    /**
     * символьный код поля содержащий информацию о полях
     * - "БЕ по зарплате"
     * - "Оклад по офферу"
     * - "Дополнительный оклад"
     * - "Переплата"
     * - "Остаток до 15 процентов"
     */
    const USER_FIELD_CODENAME = 'UF_CS_BE';
    /**
     * символьный идентификатор инфоблока содержащий в себе информацию о структуре организации и список БЕ
     */
    const IBLOCK_DEP_CODENAME = 'departments';

    /**
     * Метод поиска по названию департамента в оргструктуре связанных с пользователем
     *
     * @param $USER_ID идентификатор пользователя
     * @param $arF дополнительные параметры фильтрации
     * @param $src строка поиска
     * @return array найденные элементы
     * @throws \Exception
     */
    public static function getListNSearchByName($USER_ID, $arF = [], $src = '')
    {
        $IBlockID = getIblockIdByCode(static::$IBLOCK_DEP_CODENAME);
        $arF = [
            'IBLOCK_ID' => $IBlockID
        ];
        if (strlen($src) >= 1) {
            $arF['NAME'] = "%" . $src . "%";
        }
        $obRes = \CIBlockSection::GetList(
            $arSort = ['NAME' => 'asc'],
            $arF,
            false,
            ["ID", "NAME"]
        );
        while ($d = $obRes->fetch()) {
            $arReturn[] = $d;
        }

        $filter = ['ID' => $USER_ID];
        $filter = ['filter' => $filter, 'select' => ['UF_CS_BE', 'ID']];
        $uRet = \Bitrix\Main\UserTable::getList($filter)->fetch();
        return [$arReturn, $uRet];
    }

    public static function updateUserSalary($USER_ID, $salaryBESelect)
    {
        $IBlockID = getIblockIdByCode(static::$IBLOCK_DEP_CODENAME);
        $arF = [
            'IBLOCK_ID' => $IBlockID
        ];

        $e = ['ID' => $USER_ID];
        $e = ['filter' => $e, 'select' => ['UF_CS_BE', 'ID']];
        $e = \Bitrix\Main\UserTable::getList($e)->fetch();
        if ($e['ID']) {
            $cs_be = json_decode($e['UF_CS_BE'], 1);
            $cs_be['salaryBeSelect'] = $salaryBESelect;
            $arField = [
                'UF_CS_BE' => json_encode($cs_be)
            ];
            $obRes = new \CUser(false);
            $e = $obRes->Update($USER_ID, $arField);
            if ($e == true) {
                return true;
            } elseif ($obRes->LAST_ERROR) {
                return ('Exception error: ' . $obRes->LAST_ERROR);
            } else {
                return ('Error! Error is undefined!');
            }
        } else {
            return ('Error response - not found user in database');
        }

        return true;
    }

    /**
     * Метод возвращается значение и контекста запроса с предварительным парсингом
     * в связи с тем что данные приходят в виде строки, так же обязательным наличие информации в запросе о
     * идентификаторе пользователя - ключ - USER_ID
     *
     * @return array
     */
    public static function getAjaxDataForUpdateUserInContext()
    {
        $data = file_get_contents('php://input');
        $data = explode('&', $data);
        $USER_ID = $UF_USER_TYPE = $arFlds = false;

        foreach ($data as $k => $r) {
            list($key, $val) = explode('=', $r);

            if ($key == 'USER_ID')
                $USER_ID = $val;
            elseif ($key == 'UF_USER_TYPE')
                $UF_USER_TYPE = $val;
            else {
                $val = str_replace('+', '', $val);
                $arFlds[$key] = $val;
            }
        }

        return [$USER_ID, $UF_USER_TYPE, $arFlds];
    }

    /**
     * Функция выполняет вывод пользовательского поля с предварительным парсингом данных для разбора
     *
     * @param $array - входящие данные для правильного функционирование метода. Обязательные ключи: USER_ID - идентификатор пользователя, USER_FIELD_CODENAME - название пользовательского свойства которое необхоидмо получить, arFlds - массив полей с данным для сопоставления
     * @return array
     */
    public static function getDataFromDB($array = [])
    {
        $USER_ID = $array['USER_ID'];
        $USER_FIELD_CODENAME = static::USER_FIELD_CODENAME;
        $arFlds = $array['arFlds'];

        $arFilter = [
            'ID' => $USER_ID
        ];
        $arSelect = [static::USER_FIELD_CODENAME];

        $userKSBeData = UserTable::getList([
            'filter' => $arFilter,
            'select' => $arSelect
        ])->fetch();

        $userKSBeData = json_decode($userKSBeData[static::USER_FIELD_CODENAME], 1);
        $arFields = [];

        foreach ($userKSBeData as $key => $val) {
            if (array_key_exists($key, $arFlds))
                $arFields[$key] = $arFlds[$key];
            else
                $arFields[$key] = $val;
        }
        return $arFields;
    }

    /**
     * Метод вызывает событие сохранения данный о пользователе описанное в UserHandler классе
     *
     * @param $arFields - данные для сохранения
     * @param $USER_ID - идентификатор пользователя
     * @param $arOtherFields - иные данные для сохранения
     * @return mixed - возвращает успех или поражение логическое при вызове события сохранения
     */
    public static function updateComplete($arFields = [], $USER_ID, $arOtherFields = [])
    {
        $obUser = new \CUser(false);
        $arUpdate = array(
            static::USER_FIELD_CODENAME => $arFields,
        );

        $arUpdate = array_merge($arUpdate, $arOtherFields);

        return $obUser->Update($USER_ID, $arUpdate);
    }
}