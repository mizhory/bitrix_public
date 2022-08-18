<?php

namespace IMMO\Handlers;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \IMMO\ {
    Entity\IMMOEmployeesDataTable
};
use \Bitrix\Main\ {
    UserTable
};

/**
 * Класс-обработчик событий связанный с сущностью "Пользователи(User)"
 */
class UserHandlers
{
    /**
     * Метод инициализации событий
     * @return void
     */
    public static function init()
    {
        AddEventHandler("main", "OnBeforeUserUpdate", array(__CLASS__, "OnBeforeUserUpdateAdditionalSalary"));
    }

    /**
     * Обработчик события Обновление пользователя до записи в базу
     * @param $arFields - параметры формы и полей для обновления
     * @return void
     */
    public static function OnBeforeUserUpdateAdditionalSalary(&$arFields)
    {
        if (
            (
                empty($arFields['ID']) || !isset($arFields['ID'])
            )
            && intval($arFields['USER_ID'])
        )
            $arFields['ID'] = $arFields['USER_ID'];

        $e = ['ID' => $arFields['ID']];
        $e = ['filter' => $e, 'select' => ['UF_CS_BE', 'ID']];

        $arrFields = \Bitrix\Main\UserTable::getList($e)->fetch();

        if (empty($arFields['UF_SNILS']) || !isset($arFields['UF_SNILS'])) {
            $userOb = UserTable::GetList(['select' => ['ID', 'UF_SNILS'], 'filter' => ['ID' => $arFields['ID']]]);
            $userRet = $userOb->fetch();
            if (strlen($userRet['UF_SNILS']) <= 0) return $arFields;
            else {
                $arFields['UF_SNILS'] = $userRet['UF_SNILS'];
            }
        }

        $emploesRes = IMMOEmployeesDataTable::getList([
            'select' => ['ID', 'UF_SALARY_FIX'],
            'filter' => ['UF_SNILS' => $arFields['UF_SNILS']]
        ]);
        $userSolaryFixSum = 0.00;

        while ($elem_emploesRet = $emploesRes->fetch()) {
            $userSolaryFixSum = floatval($userSolaryFixSum) + floatval($elem_emploesRet['UF_SALARY_FIX']);
        }
        $UF_KsBe = $arFields['UF_CS_BE'];
        $additional_salary = floatval($UF_KsBe['additionalSalary']);

        if ($additional_salary != $userSolaryFixSum)
            $additional_salary = $userSolaryFixSum;


        if ($additional_salary <= 0)
            $additional_salary = 0;
        elseif ($additional_salary > 0) {
            $exploded_additional_solary = explode('.', $additional_salary);

            if (is_array($exploded_additional_solary) && count($exploded_additional_solary) < 2)
                $additional_salary = ($exploded_additional_solary[0] - floatval($UF_KsBe["salary"]));

        }
        if ($additional_salary <= 0)
            $additional_salary = 0;


        $kSBE = $arFields['UF_CS_BE'];

        $kSBE['additionalSalary'] = $additional_salary;
        $kSBE['salary'] = html_entity_decode($kSBE['salary']);
        if (count(explode(',', $kSBE['salary'])) <= 0) {
            $kSBE['salary'] = $kSBE['salary'] . ',00';
        }
        $arFields['UF_CS_BE'] = json_encode($kSBE);

        return $arFields;
    }
}
