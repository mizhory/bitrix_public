<?php

namespace Immo\Statements\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\IO\File;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Exception;
use Immo\Statements\Entity\GridCalculatedFieldsTable;
use Immo\Statements\Entity\GridFieldsTable;
use Immo\Statements\Traits\ModuleTrait;

/**
 * Обёртка над orm-сущностью Immo\Statements\Entity\GridFieldsTable
 */
class GridFields
{
    use ModuleTrait;

    /**
     * Добавляет запись в таблицу
     *
     * @param string $role
     * @param array $fields
     *
     * @param bool $isGlobal
     * @param string $hlEntityName
     *
     * @return array|int
     * @throws Exception
     */
    public static function add(string $role, array $fields, bool $isGlobal = false, string $hlEntityName = 'StatementsApproval')
    {
        $params = [
            'ROLE' => $role,
            'FIELDS' => $fields,
            'IS_GLOBAL' => $isGlobal,
            'HL_ENTITY_NAME' => $hlEntityName
        ];

        $add = GridFieldsTable::add($params);
        return $add->isSuccess() ? $add->getId() : $add->getErrorMessages();
    }

    /**
     * Добавляет вычисляемое поле в таблицу
     *
     * @param string $role
     * @param string $fieldName
     * @param string $fieldLabel
     * @param array $sumByFields
     * @return array|int
     * @throws Exception
     */
    public static function addCalculatedField(string $role, string $fieldName, string $fieldLabel, array  $sumByFields)
    {
        $params = [
            'ROLE' => $role,
            'CALCULATED_FIELD_NAME' => $fieldName,
            'CALCULATED_FIELD_LABEL' => $fieldLabel,
            'SUM_BY_FIELDS' => $sumByFields
        ];

        $add = GridCalculatedFieldsTable::add($params);

        return $add->isSuccess() ? $add->getId() : $add->getErrorMessages();
    }

    /**
     * Выводит список строк из таблицы (вместе с вычисляемыми полями)
     *
     * @param array $params
     * @param bool $deleteId
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public static function list(array $params = [], bool $deleteId = false): array
    {
        $select = [
            'ROLE', 'HL_ENTITY_NAME',
            'FIELDS', 'IS_GLOBAL',
            'CALC_FIELD_NAME' => 'CALC.CALCULATED_FIELD_NAME',
            'CALC_FIELD_LABEL' => 'CALC.CALCULATED_FIELD_LABEL',
            'SUM_BY_FIELDS' => 'CALC.SUM_BY_FIELDS',
            'DIFFERENCE_BY_FIELDS' => 'CALC.DIFFERENCE_BY_FIELDS'
        ];

        $params['select'] = !empty($params['select']) ? array_merge($params['select'], $select) : $select;

        $results = [];

        $data = GridFieldsTable::getList($params)->fetchAll();

        foreach ($data as &$item) {
            $role = $item['ROLE'];

            $results[$role] = [
                'role' => $role,
                'hl_entity_name' => $item['HL_ENTITY_NAME'],
                'fields' => Json::decode($item['FIELDS']),
                'is_global' => $item['IS_GLOBAL'],
            ];

            if(!is_null($item['CALC_FIELD_NAME'])) {
                $calcFieldName = $item['CALC_FIELD_NAME'];

                $results[$role]['calculated_fields'][$calcFieldName] = [
                    'field' => $calcFieldName,
                    'label' => $item['CALC_FIELD_LABEL'],
                ];

                if(!empty($item['DIFFERENCE_BY_FIELDS'])) {
                    $results[$role]['calculated_fields'][$calcFieldName]['difference_by_fields'] = Json::decode($item['DIFFERENCE_BY_FIELDS']);
                }

                if(!empty($item['SUM_BY_FIELDS'])) {
                    $results[$role]['calculdated_fields'][$calcFieldName]['sum_fy_fields'] = Json::decode($item['SUM_BY_FIELDS']);
                }
            }
        }

        return $results;
    }

    public static function getFieldsByRole($role = 'admin')
    {
        $params = [
            'filter' => ['ROLE' => $role],
        ];
        $gridFields = GridFields::list($params);
        return $gridFields[strtoupper($role)];
    }

    /**
     * Экспортирует данные из таблицы в файл
     *
     * @throws ArgumentException|SystemException
     */
    public static function export(): void
    {
        $data = self::list([], true);
        $data = Json::encode($data, JSON_UNESCAPED_UNICODE);
        $file = self::getFilePath();

        File::putFileContents($file, $data);
    }

    /**
     * Импортирует данные из файла в таблицу
     *
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function import(): void
    {
        $file = self::getFilePath();

        if(File::isFileExists($file)) {
            $data = File::getFileContents($file);
            $data = Json::decode($data);

            GridFieldsTable::addMulti($data);
        }
    }

    /**
     * Возвращает путь к файлу с экспортированными/импортируемыми данными
     *
     * @return string
     */
    public static function getFilePath(): string
    {
        return sprintf('%s/install/data/%s.json', self::getModulePath(), GridFieldsTable::getTableName());
    }

    /**
     * Проверяет, есть ли у роли вычисляемые поля
     *
     * @param $role
     *
     * @return bool
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public static function hasCalculatedFields($role): bool
    {
        return GridCalculatedFieldsTable::getByPrimary($role)->getCount() > 0;
    }

    /**
     * Возвращает название вычисляемого поля по роли и коду
     *
     * @param $role
     * @param $fieldName
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public static function getCalculatedFieldLabel($role, $fieldName): string
    {
        return GridCalculatedFieldsTable::getList([
            'filter' => [
                'ROLE' => $role,
                'CALCULATED_FIELD_NAME' =>$fieldName
            ],
            'select' => ['CALCULATED_FIELD_LABEL']
        ])->fetchObject()->getCalculatedFieldLabel();
    }
}