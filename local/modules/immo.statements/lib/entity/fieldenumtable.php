<?php

namespace Immo\Statements\Entity;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;

class FieldEnumTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_user_field_enum';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_ID_FIELD')
                ]
            ),
            new IntegerField(
                'USER_FIELD_ID',
                [
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_USER_FIELD_ID_FIELD')
                ]
            ),
            new StringField(
                'VALUE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateValue'],
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_VALUE_FIELD')
                ]
            ),
            new BooleanField(
                'DEF',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_DEF_FIELD')
                ]
            ),
            new IntegerField(
                'SORT',
                [
                    'default' => 500,
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_SORT_FIELD')
                ]
            ),
            new StringField(
                'XML_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateXmlId'],
                    'title' => Loc::getMessage('FIELD_ENUM_ENTITY_XML_ID_FIELD')
                ]
            ),
            new Reference(
                'UF',
                UserFieldTable::class,
                Join::on('this.USER_FIELD_ID', 'ref.ID')
            )
        ];
    }

    /**
     * Returns validators for VALUE field.
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public static function validateValue(): array
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for XML_ID field.
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public static function validateXmlId(): array
    {
        return [
            new LengthValidator(null, 255),
        ];
    }
}