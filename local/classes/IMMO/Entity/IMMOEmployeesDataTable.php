<?php

namespace IMMO\Entity;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\{
    Localization\Loc,
    ORM\Data\DataManager,
    ORM\Fields\DateField,
    ORM\Fields\FloatField,
    ORM\Fields\IntegerField,
    ORM\Fields\TextField,
    UserTable
};

Loc::loadMessages(__FILE__);

/**
 * Системный класс таблицы справочника (HL Блока) в виде ORM - данный клас замещает конструкцию с помощью поиска
 * сначала ХЛ блока после получения доступа генерация таблицы и ТД, данный класс ORM-представление таблицы
 * хайлоад блока с имплементирующими параметрами DataTable - с возможностью доступа к стандартным функциям
 * только для данной таблицы (хайлоад блока) GetList, Update, Add, Remove и иные методы работы с таблицой
 *
 * Class IMMOEmployeesDataTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> UF_OBJECT_ID text optional
 * <li> UF_WORK_TYPE text optional
 * <li> UF_START_DATE date optional
 * <li> UF_WORK_DAYS double optional
 * <li> UF_DEPARTMENT text optional
 * <li> UF_SALARY_FIX double optional
 * <li> UF_SALARY_TOTAL double optional
 * <li> UF_PART_KOEF double optional
 * <li> UF_VACATION_NUM double optional
 * <li> UF_VACATION_NUM_FACT double optional
 * <li> UF_FROM_15 double optional
 * <li> UF_SALARY_DIFF double optional
 * <li> UF_COMPANY int optional
 * <li> UF_ACTIVE int optional
 * <li> UF_SNILS text optional
 * </ul>
 *
 * @package Bitrix\Company
 **/
class IMMOEmployeesDataTable extends DataManager
{
    /**
     * Константа опредлеяет идентификатор (символьный код) справочника (хайлоад блока)
     */
    const ENTITY_CODENAME = 'CompanyEmplyeesData';

    /**
     * Returns DB table name for entity.
     * Данная функция возвращает название таблицы с хранимыми элементами для справочника (хайлоад блока)
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'uf_company_employees_data';
    }

    /**
     * Returns entity map definition.
     * Данная функция возвращает маппинг полей таблицы для взаимодействия с ней в виде представления ORM
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_OBJECT_ID',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_OBJECT_ID_FIELD')
                ]
            ),
            new TextField(
                'UF_WORK_TYPE',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_WORK_TYPE_FIELD')
                ]
            ),
            new DateField(
                'UF_START_DATE',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_START_DATE_FIELD')
                ]
            ),
            new FloatField(
                'UF_WORK_DAYS',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_WORK_DAYS_FIELD')
                ]
            ),
            new TextField(
                'UF_DEPARTMENT',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_DEPARTMENT_FIELD')
                ]
            ),
            new FloatField(
                'UF_SALARY_FIX',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_SALARY_FIX_FIELD')
                ]
            ),
            new FloatField(
                'UF_SALARY_TOTAL',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_SALARY_TOTAL_FIELD')
                ]
            ),
            new FloatField(
                'UF_PART_KOEF',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_PART_KOEF_FIELD')
                ]
            ),
            new FloatField(
                'UF_VACATION_NUM',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_VACATION_NUM_FIELD')
                ]
            ),
            new FloatField(
                'UF_VACATION_NUM_FACT',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_VACATION_NUM_FACT_FIELD')
                ]
            ),
            new FloatField(
                'UF_FROM_15',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_FROM_15_FIELD')
                ]
            ),
            new FloatField(
                'UF_SALARY_DIFF',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_SALARY_DIFF_FIELD')
                ]
            ),
            new IntegerField(
                'UF_COMPANY',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_COMPANY_FIELD')
                ]
            ),
            new IntegerField(
                'UF_ACTIVE',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_ACTIVE_FIELD')
                ]
            ),
            new TextField(
                'UF_SNILS',
                [
                    'title' => Loc::getMessage('EMPLOYEES_DATA_ENTITY_UF_SNILS_FIELD')
                ]
            ),
        ];
    }

    /**
     * Метод инициализации событий для HL блока (таблицы/справочника)
     *
     * Инициализация 2х событий - добавление элемента в таблицу, обновление элемента таблицы
     * @return void
     */
    public static function EventInit()
    {
        \Bitrix\Main\EventManager::getInstance()->addEventHandler('', static::ENTITY_CODENAME . 'OnAfterUpdate',
            [__CLASS__, 'onBeforeUpdate']
        );
        \Bitrix\Main\EventManager::getInstance()->addEventHandler('', static::ENTITY_CODENAME . 'OnAfterAdd',
            [__CLASS__, 'onBeforeAdd']
        );
    }

    /**
     * Метод отвечающая за событие добавление нового элемента в таблица (в справочник)
     *
     * @param \Bitrix\Main\ORM\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     */
    public static function onBeforeAdd(\Bitrix\Main\ORM\Event $event)
    {
        $arFields = $event->getParameter("fields");
        $arFields = array_merge($arFields, $event->getParameter('id'));
        $arUpdate = [
            'ID' => $arFields['ID'],
            'UF_SALARY_FIX' => $arFields['UF_SALARY_FIX']['VALUE'],
            'UF_SNILS' => $arFields['UF_SNILS']['VALUE']
        ];
        static::updateLogics($arUpdate);
        return new \Bitrix\Main\Entity\EventResult();
    }

    /**
     * Метод отвечающая за обновление элемента таблицы (справочника)
     *
     * @param \Bitrix\Main\ORM\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     */
    public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
    {
        $arFields = $event->getParameter("fields");
        $arFields = array_merge($arFields, $event->getParameter('id'));
        //var_dump($arFields);die;
        $arUpdate = [
            'ID' => $arFields['ID'],
            'UF_SALARY_FIX' => $arFields['UF_SALARY_FIX']['VALUE'],
            'UF_SNILS' => $arFields['UF_SNILS']['VALUE']
        ];
        static::updateLogics($arUpdate);
        return new \Bitrix\Main\Entity\EventResult();
    }

    const HL_FIELD_SNILS_CODE = 'UF_SNILS';
    const US_FIELD_SNILS_CODE = 'UF_SNILS';

    /**
     * Метод расчета Дополнительного оклада и сохранение обновленных данных в таблице
     *
     * @param $arFields
     * @return mixed
     */
    public static function updateLogics($arFields)
    {
        if (
            empty($arFields[static::HL_FIELD_SNILS_CODE])
            || !isset($arFields[static::HL_FIELD_SNILS_CODE])
        )
            return $arFields;

        global $USER_FIELD_MANAGER;

        $userOb = UserTable::getList([
            'select' => ['ID', "UF_CS_BE"],
            'filter' => [
                static::US_FIELD_SNILS_CODE => $arFields[static::HL_FIELD_SNILS_CODE]
            ]
        ]);
        $arUser = $userOb->fetch();

        if (!intval($arUser['ID'])) return $arFields;

        $UF_CS_BE = json_decode($arUser['UF_CS_BE'], 1);

        $additionalSalary = $UF_CS_BE['additionalSalary'];
        $UF_SALARY_FIX = $arFields['UF_SALARY_FIX'];

        $userSolaryFixSum = 0;

        $employersOb = IMMOEmployeesDataTable::getList([
            'select' => ['UF_SALARY_FIX'],
            'filter' => [
                static::HL_FIELD_SNILS_CODE => $arFields[static::HL_FIELD_SNILS_CODE],
                '!ID' => $arFields['ID']
            ]
        ]);

        while ($emplResult = $employersOb->fetch()) {
            $userSolaryFixSum = $userSolaryFixSum + $emplResult['UF_SALARY_FIX'];
        }

        $userSolaryFixSum = $userSolaryFixSum + $UF_SALARY_FIX;

        if ($additionalSalary != $userSolaryFixSum) {
            $additional_salary = $userSolaryFixSum;
        }

        if ($additional_salary <= 0) {
            $additional_salary = "0,00";
        } elseif ($additional_salary > 0) {
            $exploded_additional_solary = explode(',', $additional_salary);
            if (is_array($exploded_additional_solary) && count($exploded_additional_solary) < 2) {
                $additional_salary = ($exploded_additional_solary[0] - floatval($UF_CS_BE["salary"]));
            }
        }

        if ($additional_salary <= 0) {
            $additional_salary = 0;
        }
        $UF_CS_BE['additionalSalary'] = $additional_salary . ',00';
        $obUser = new \CUser(false);
        $obUser->Update($arUser['ID'], ['UF_CS_BE' => $UF_CS_BE]);

        return $arFields;
    }
}