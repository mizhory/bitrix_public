<?php

namespace Immo\Statements\Traits;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\Query;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

trait IblockTrait
{
    /**
     * Возвращает id инфоблока по символьному коду
     *
     * @param string $code
     * @param string $type
     *
     * @return int
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getIblockId(string $code, string $type): int
    {
        $params = [
            'filter' => [
                'CODE' => $code,
                'IBLOCK_TYPE_ID' => $type
            ],
            'select' => ['ID']
        ];
        return (int) IblockTable::getList($params)->fetchObject()->getId();
    }

    /**
     * Возвращает свойства ИБ
     *
     * @param $iblockId
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getProperties($iblockId)
    {
        $rows = (new Query(PropertyTable::class))
            ->where('IBLOCK_ID', $iblockId)
            ->setSelect([
                'ID', 'NAME', 'CODE', 'SORT',
                'PROPERTY_TYPE', 'DEFAULT_VALUE',
                'MULTIPLE', 'IS_REQUIRED', 'LINK_IBLOCK_ID',
                'ELEMENT_ID' => 'E.ID',
                'ELEMENT_NAME' => 'E.NAME',
                'LIST_ID' => 'L.ID',
                'LIST_VALUE' => 'L.VALUE'
            ])
            ->registerRuntimeField('E', [
                'data_type' => ElementTable::class,
                'reference' => [
                    'this.LINK_IBLOCK_ID' => 'ref.IBLOCK_ID'
                ]
            ])
            ->registerRuntimeField('L', [
                'data_type' => PropertyEnumerationTable::class,
                'reference' => [
                    'this.ID' => 'ref.PROPERTY_ID'
                ]
            ])
            ->fetchAll();

        foreach ($rows as $row) {
            $code = $row['CODE'];
            $name = $row['NAME'];
        }
    }

    /**
     * Возвращает значения списочных свойств
     *
     * @param $propertyCode
     * @param $iblockId
     * @param null $valueId
     * @param false $withXmlId
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getPropertiesListValues($propertyCode, $iblockId, $valueId = null, $withXmlId = false): array
    {
        $values = [];

        $params = [
            'filter' => [
                'P.CODE' => $propertyCode,
                'P.IBLOCK_ID' => $iblockId,
            ],
            'select' => ['ID', 'XML_ID', 'VALUE'],
            'runtime' => [
                new Reference('P', PropertyTable::class, Join::on('this.PROPERTY_ID', 'ref.ID'))
            ]
        ];

        if(!is_null($valueId)) {
            $params['filter']['ID'] = $valueId;
        }

        $properties = PropertyEnumerationTable::getList($params);
        foreach ($properties->fetchCollection() as $property) {
            $id = $property->getId();
            $xmlId = $property->getXmlId();
            $value = $property->getValue();

            $values[$id] = $withXmlId ? [
                'id' => $id,
                'xml_id' => $xmlId,
                'value' => $value
            ] : $value;
        }

        return $values;
    }

    /**
     * Возвращает название свойства ИБ
     *
     * @param $code
     * @param $iblockId
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getPropertyName($code, $iblockId): string
    {
        return PropertyTable::getList([
            'filter' => [
                'CODE' => $code,
                'IBLOCK_ID' => $iblockId
            ],
            'select' => ['NAME']
        ])->fetchObject()->getName();
    }

    /**
     * Возвращает ORM-сущность ИБ
     *
     * @param $iblockId
     *
     * @return mixed
     */
    public static function getEntity($iblockId)
    {
        return Iblock::wakeUp($iblockId)->getEntityDataClass();
    }

    public static function getIblockIdByElementId($elementId): int
    {
        return (int) ElementTable::query()
            ->where('ID', $elementId)
            ->setSelect(['IBLOCK_ID'])
            ->fetchObject()
            ->getIblockId();
    }


}