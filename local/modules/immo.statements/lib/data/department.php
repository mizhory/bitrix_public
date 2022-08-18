<?php

namespace Immo\Statements\Data;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\Model\Section as IblockSection;
use Bitrix\Iblock\SectionTable;
use Bitrix\Intranet\UserField\Types\EmployeeType;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use Immo\Statements\ModuleInterface;
use Immo\Statements\ModuleTrait;
use Immo\Statements\Traits\IblockTrait;

class Department implements ModuleInterface
{
    use IblockTrait;

    public const FILTER_BUSINESS_UNITS_QUERY = '%"type":"be"%';
    public const FILTER_COMPANIES_QUERY = '%"type":"companies"%';
    public const UF_ROLE_LABEL_PREFIX = '[Зарплатная ведомость]%';

    public static function getUfEntityId()
    {
        $iblockId = Option::get('intranet', 'iblock_structure');
        return sprintf('IBLOCK_%s_SECTION', $iblockId);
    }

    /**
     * Возвращает сущность разделов инфоблока
     *
     * @return SectionTable|string|null
     */
    public static function getEntity()
    {
        $iblockId = (int) Option::get('intranet', 'iblock_structure');
        return IblockSection::compileEntityByIblock($iblockId);
    }

    /**
     * Возвращает список БЕ
     *
     * @return array
     */
    public static function getBusinessUnits($id = null)
    {
        $filter = self::prepareFilter(self::FILTER_BUSINESS_UNITS_QUERY);

        if(!is_null($id)) {
            $filter['ID'] = $id;
        }

        return self::getSectionList($filter);
    }

    /**
     * Возвращает список ЮЛ
     *
     * @return array
     */
    public static function getCompanies($id = null)
    {
        $filter = self::prepareFilter(self::FILTER_COMPANIES_QUERY);

        if(!is_null($id)) {
            $filter['ID'] = $id;
        }

        return self::getSectionList($filter);
    }

    /**
     * Возвращает список разделов инфоблока
     *
     * @param array $filter
     * @param array $select
     * @param false $useSeparator
     * @param string $separator
     * @param array|null $options
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected static function getSectionList($filter, $select = [], $useSeparator = false, $separator = '.', $options = null): array
    {
        $sections = [];
        $defaultSelect = ['ID', 'NAME', 'LEFT_MARGIN', 'DEPTH_LEVEL'];

        if(!is_array($select)) {
            $select = [$select];
        }

        $params = [
            'filter' => $filter,
            'select' => array_merge($defaultSelect, $select),
            'order' => [
                'LEFT_MARGIN' => 'ASC',
                'DEPTH_LEVEL' => 'ASC'
            ],
            'group' => ['DEPTH_LEVEL']
        ];

        if(!is_null($options)) {
            $params = array_merge($params, $options);
        }

        $class = self::getEntity();
        $entity = new $class;
        $list = $entity::getList($params)->fetchAll();

        foreach ($list as $item) {
            $sep = str_repeat($separator, ($item['DEPTH_LEVEL'] - 1));
            $itemId = (int) $item['ID'];
            $itemValue = $useSeparator ? sprintf('%s%s', $sep, $item['NAME']) : $item['NAME'];

            $sections[$itemId] = $itemValue;
        }

        return $sections;
    }

    /**
     * Подготавливает параметры для фильтрации разделов инфоблока
     *
     * @param $value
     * @return array
     */
    protected static function prepareFilter($value)
    {
        return [
            'UF_BE_AND_UL' => $value
        ];
    }

    /**
     * Возвращает название БЕ/ЮЛ по ID
     * @param $id
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getElementNameById($id)
    {
        return self::getEntity()::getById($id)->fetchObject()->getName();
    }

    public static function getDepartmentCurrency($elementId)
    {
        $entity = self::getEntity()::getRow([
            'filter' => ['=ID' => $elementId],
            'select' => ['UF_CURRENCY']
        ]);

        return $entity['UF_CURRENCY'];
    }

    /**
     * Возвращает список полей для получения ролей по ЗПВ
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getSalaryStatementRolesSelectFields()
    {

        $fields = UserFieldTable::getList([
            'filter' => [
                'ENTITY_ID' => Department::getUfEntityId(),
                'USER_TYPE_ID' => EmployeeType::USER_TYPE_ID,
                [
                    'LOGIC' => 'OR',
                    ['EDIT_FORM_LABEL' => self::UF_ROLE_LABEL_PREFIX],
                    ['LIST_COLUMN_LABEL' => self::UF_ROLE_LABEL_PREFIX],
                    ['LIST_FILTER_LABEL' => self::UF_ROLE_LABEL_PREFIX],

                ]
            ],
            'select' => array_merge(['FIELD_NAME'], UserFieldTable::getLabelsSelect()),
            'runtime' => [UserFieldTable::getLabelsReference()]
        ]);

        return array_merge(
            ['ID', 'IBLOCK_SECTION_ID'],
            array_column($fields->fetchAll(), 'FIELD_NAME')
        );
    }
}