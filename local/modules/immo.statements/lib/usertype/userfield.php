<?php

namespace Immo\Statements\UserType;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\UserFieldTable;
use Immo\Statements\Entity\FieldEnumTable;

class UserField
{
    /**
     * Возвращает тип пользовательского поля
     *
     * @param $fieldName
     * @param $entityId
     *
     * @return string
     *
     */
    public static function getType($fieldName, $entityId): string
    {
        $options = [
            'select' => ['FIELD_NAME', 'USER_TYPE_ID']
        ];

        $uf = self::getRow($fieldName, $entityId, $options);

        return $uf['USER_TYPE_ID'];
    }

    /**
     * Возвращает ID пользовательского поля по коду
     *
     * @param $fieldName
     * @param $entityId
     *
     * @return int
     */
    public static function getId($fieldName, $entityId): int
    {
        $row = self::getRow($fieldName, $entityId);
        return (int) $row['ID'];
    }

    /**
     * Возвращает название пользовательского поля
     *
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getLabel($fieldName, $entityId)
    {
        if(!self::isExists($fieldName, $entityId)) {
            throw new SystemException(sprintf('Field %s for entity %s is not exists', $fieldName, $entityId));
        }

        $options = [
            'filter' => [
                'LANGUAGE_ID' => LANGUAGE_ID
            ],
            'select' => UserFieldTable::getLabelsSelect(),
            'runtime' => [UserFieldTable::getLabelsReference()]
        ];

        $uf = self::getRow($fieldName, $entityId, $options);



        return $uf['EDIT_FORM_LABEL'] ?? $uf['LIST_COLUMN_LABEL'] ?? $uf['LIST_FILTER_LABEL'];
    }

    /**
     * Проверяет пользовальское поле на существование
     * @param $fieldName
     * @param $entityId
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function isExists($fieldName, $entityId)
    {
        return !empty(self::getRow($fieldName, $entityId));
    }

    /**
     * Возвращает отдельную строку из таблицы
     *
     * @param $fieldName
     * @param $entityId
     * @param array $options
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected static function getRow($fieldName, $entityId, &$options = []): array
    {
        $filter = [
            'FIELD_NAME' => $fieldName,
            'ENTITY_ID' => $entityId
        ];

        $options['filter'] = isset($options['filter']) ? array_merge($filter, $options['filter']) : $filter;

        return UserFieldTable::getList($options)->fetch();
    }

    /**
     * Возвращает форматированный список пользовательских полей
     *
     * @param array $options
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function list(array &$options = []): array
    {
        $fields = [];

        $select = [
            'ID',
            'FIELD_NAME',
            'USER_TYPE_ID',
            'ENUM_VALUE_ID' => 'ENUM.ID',
            'ENUM_VALUE' => 'ENUM.VALUE',
            'ENUM_XML_ID' => 'ENUM.XML_ID',
        ];

        $select = array_merge($select, UserFieldTable::getLabelsSelect());

        $runtime = [
            new Reference('ENUM', FieldEnumTable::class, Join::on('this.ID', 'ref.USER_FIELD_ID')),
            UserFieldTable::getLabelsReference()
        ];

        $options['select'] = isset($options['select']) ? array_merge($options['select'], $select) : $select;
        $options['runtime']  = isset($options['runtime']) ? array_merge($options['runtime'], $runtime) : $runtime;


        $uf = UserFieldTable::getList($options);

        foreach ($uf->fetchAll() as $field) {
            $fieldName = $field['FIELD_NAME'];
            $fieldTitle = $field['EDIT_FORM_LABEL'] ?? $field['LIST_COLUMN_LABEL'] ?? $field['LIST_FILTER_LABEL'];

            $fieldType = $field['USER_TYPE_ID'];

            $fields[$fieldName]['field_name'] = $fieldName;
            $fields[$fieldName]['type'] = $fieldType;
            $fields[$fieldName]['label'] = $fieldTitle;

            $enumItems = [];

            if($fieldType === EnumType::USER_TYPE_ID) {
                $enumId = $field['ENUM_VALUE_ID'];
                $enumXmlId = $field['ENUM_XML_ID'];
                $enumValue = $field['ENUM_VALUE'];

                $enumItems = [
                    'id' => $enumId,
                    'xml_id' => $enumXmlId,
                    'value' => $enumValue
                ];
            }

            if(!empty($enumItems)) {
                $fields[$fieldName]['items'][$enumItems['id']] = $enumItems;
            }
        }

        return $fields;
    }

    /**
     * Возвращает значение пользовательского поля по XML ID
     *
     * @param $fieldName
     * @param $valueXmlId
     *
     * @return array|null
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getUfEnumValue($fieldName, $valueXmlId, $entityId = null): ?array
    {
        $params = [
            'filter' => [
                'UF.FIELD_NAME' => $fieldName,
                'XML_ID' => $valueXmlId
            ],
            'select' => self::getUfEnumSelect()
        ];

        if(!is_null($entityId)) {
            $params['filter']['UF.ENTITY_ID'] = $entityId;
        }

        return FieldEnumTable::getRow($params);
    }

    /**
     * Возвращает значение пользовательского поля по id
     * @param $id
     * @param null $entityId
     *
     * @return array|null
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getUfEnumValueById($id, $entityId = null)
    {
        $params = [
            'filter' => [
                'ID' => $id
            ],
            'select' => self::getUfEnumSelect()
        ];

        if(!is_null($entityId)) {
            $params['filter']['UF.ENTITY_ID'] = $entityId;
        }

        return FieldEnumTable::getRow($params);
    }

    protected static function getUfEnumSelect(): array
    {
        return [
            'ID',
            'FIELD_NAME' => 'UF.FIELD_NAME',
            'ENTITY_ID' => 'UF.ENTITY_ID',
            'XML_ID',
            'VALUE'
        ];
    }
}