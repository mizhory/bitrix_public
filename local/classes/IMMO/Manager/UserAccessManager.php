<?php

namespace IMMO\Manager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserTable;

/**
 * Класс-Менеджер содержащий в себе методы для получения статуса допуска для того или иного пользователя
 * в разных интерпретациях и ситуация независящие от модулей или файлов и могут быть вызванны как нативный
 * независимый код в любом месте где нам это необходимо для проверки
 *
 */
class UserAccessManager
{
    /**
     * Символьный код поля который отвечает за пул идентификаторов сотрудников имеющий статус допуска
     * "Согласующие HR"
     */
    const FILED_ACCEPT_HR_CODENAME = "UF_SOGLASUYCHIE_HR";
    /**
     * Символьный код поля который отвечает за пул идентификаторов сотрудников имеющий статус допуска
     * "Контроллирующие руководители"
     */
    const FIELD_ACCEPT_SUPERVISING_MANAGERS = "UF_SUPERVISING_MANAGERS";
    /**
     * Символьный код поля который отвечает за пол идентификаторов сотрудников имеющий статус допуска
     * "Пользователи, которые могут редактировать переплату"
     */
    const FIELD_ACCEPT_OVER_SALARY = "UF_ACCEPT_OVER_SALARY";

    /**
     * Метод для проверки пользователя по идентификатору - относится ли пользователь к группе ХР или юл-бе
     * @param $user_id идентификатор пользователя для проверки
     * @return bool
     */
    public static function getAccessByUserID($user_id = false)
    {
        if (!intval($user_id) || !$user_id || is_bool($user_id)) return false;

        $arUserAccess = \Bitrix\Main\Config\Option::get("askaron.settings", static::FILED_ACCEPT_HR_CODENAME);

        foreach ($arUserAccess as $k => $r) {
            $a = (new \Immo\Structure\Organization(intval($r)))->findUserActing([]);
            if (intval($a['userId']))
                $arUserAccess[] = intval($a['userId']);
        }

        if (in_array($user_id, $arUserAccess))
            return true;

        $arUserAccess = \Bitrix\Main\Config\Option::get("askaron.settings", static::FIELD_ACCEPT_SUPERVISING_MANAGERS);

        foreach ($arUserAccess as $k => $r) {
            $arUserAccess[] = intval($r);
        }

        if (in_array($user_id, $arUserAccess))
            return true;

        $arUserAccess = \Bitrix\Main\Config\Option::get("askaron.settings", static::FIELD_ACCEPT_OVER_SALARY);

        foreach ($arUserAccess as $k => $r) {
            $arUserAccess[] = intval($r);
        }

        if (in_array($user_id, $arUserAccess))
            return true;

        return false;
    }

    /**
     * Метод возвращает развернутый ответ по доступу к Карточке Сотрудника
     *
     * @param $user_id - идентификатор пользователя к которому нужно проверить доступ
     * @return array|false - значение при отсутствии доступа или массив по допуску в виде Ключ - параметр. Ключ будет присутствовать только в случае положительного допуска
     */
    public static function expandedAcceptResponse($user_id = false)
    {
        if (!intval($user_id) || is_bool($user_id) || !$user_id) return false;

        $arAccessReturn = false;

        $arUserAccess = \Bitrix\Main\Config\Option::get(
            "askaron.settings",
            static::FILED_ACCEPT_HR_CODENAME
        );

        foreach ($arUserAccess as $k => $r) {
            $a = (new \Immo\Structure\Organization(intval($r)))->findUserActing([]);
            if (intval($a['userId']))
                $arUserAccess[] = intval($a['userId']);
        }

        if (in_array($user_id, $arUserAccess))
            $arAccessReturn[static::FILED_ACCEPT_HR_CODENAME] = true;

        $arUserAccess = \Bitrix\Main\Config\Option::get(
            "askaron.settings",
            static::FIELD_ACCEPT_SUPERVISING_MANAGERS
        );

        foreach ($arUserAccess as $k => $r) {
            $arUserAccess[] = intval($r);
        }

        if (in_array($user_id, $arUserAccess))
            $arAccessReturn[static::FIELD_ACCEPT_SUPERVISING_MANAGERS] = true;

        $arUserAccess = \Bitrix\Main\Config\Option::get(
            "askaron.settings",
            static::FIELD_ACCEPT_OVER_SALARY
        );

        foreach ($arUserAccess as $k => $r) {
            $arUserAccess[] = intval($r);
        }

        if (in_array($user_id, $arUserAccess))
            $arAccessReturn[static::FIELD_ACCEPT_OVER_SALARY] = true;

        if (is_array($arAccessReturn) && count($arAccessReturn) > 0)
            return $arAccessReturn;

        return false;
    }
}