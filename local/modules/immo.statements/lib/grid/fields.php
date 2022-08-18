<?php

namespace Immo\Statements\Grid;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\Web\Json;
use Immo\Statements\Entity\GridFieldsTable;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\IblockTrait;
use Immo\Statements\Traits\ModuleTrait;


class Fields implements ModuleInterface
{
    use IblockTrait,
        ModuleTrait;

    public const GLOBAL_ROLE_CHIEF_ACCOUNTANT = 'chief_accountant';
    public const GLOBAL_ROLE_FINANCIAL_DIRECTORATE = 'financial_directorate';
    public const GLOBAL_ROLE_ADMIN = 'admin';

    public const FIELD_TYPE_STRING = 'string';
    public const FIELD_TYPE_LIST = 'list';
    public const FIELD_TYPE_NUMBER = 'number';
    public const FIELD_TYPE_DATE = 'date';
    public const FIELD_TYPE_CUSTOM_DATE = 'custom_date';
    public const FIELD_TYPE_CHECKBOX = 'checkbox';
    public const FIELD_TYPE_CUSTOM_ENTITY = 'custom_entity';
    public const FIELD_TYPE_ENTITY_SELECTOR = 'entity_selector';
    public const FIELD_TYPE_DEST_SELECTOR = 'dest_selector';

    public const GRID_FIELDS_BASE_LIST = [
        'STATUS_CARD',
        'F_MONTH',
        'F_YEAR',
        'STATEMENT_SUM',
        'PAYMENT_DATE'
    ];

    public const FILTER_BASE_FIELDS_TYPES = [
        'SELECTED_BE' => self::FIELD_TYPE_LIST,
        'SELECTED_UR' => self::FIELD_TYPE_LIST,
        'STATUS_CARD' => self::FIELD_TYPE_LIST,
        'F_MONTH' => self::FIELD_TYPE_LIST,
        'F_YEAR' => self::FIELD_TYPE_LIST,
        'STATEMENT_SUM' => self::FIELD_TYPE_NUMBER,
        'PAYMENT_DATE' => self::FIELD_TYPE_DATE
    ];

    /**
     * Массив, в котором хранятся пользовательские поля значений глобальных ролей
     *
     * @var array|string[]
     */
    public static array $globalRolesUserFields = [
        self::GLOBAL_ROLE_CHIEF_ACCOUNTANT => 'UF_GLAV_BUH',
        self::GLOBAL_ROLE_FINANCIAL_DIRECTORATE => 'UF_FIN_DIRECTION'
    ];

    /**
     * Возвращает список полей грида для глобальных ролей
     *
     * @param string $role Код роли
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getFieldsByGlobalRole(string $role): array
    {
        $entity = GridFieldsTable::getList([
            'filter' => ['GLOBAL_ROLE_CODE' => $role],
            'select' => ['FIELDS']
        ]);

        $fields = $entity->fetchObject()->getFields();

        return Json::decode($fields);
    }

    /**
     * Возвращает название поля
     *
     * @param string $fieldName
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getFieldTitle(string $fieldName): string
    {
        $params = [
            'filter' => ['FIELD_NAME' => $fieldName],
            'select' => [
                'ID',
                'FIELD_NAME',
                'TITLE' => 'UF_LANG.EDIT_FORM_LABEL'
            ],
            'runtime' => [
                new Reference('UF_LANG', UserFieldLangTable::class, Join::on('this.ID', 'ref.USER_FIELD_ID'))
            ]
        ];

        $uf = UserFieldTable::getList($params)->fetchAll();

        foreach ($uf as $field) {
            if(!empty($field['TITLE'])) {
                return  $field['TITLE'];
            }
        }

        throw new SystemException(sprintf('Field title of field %s is empty', $fieldName));
    }

    /**
     * Возвращает список полей в зависимости от режима просмотра
     *
     * @param string $viewMode Код режима просмотра: BE = бизнес-единица, UR - юрлицо
     *
     * @return string[]
     */
    public static function getFieldsByViewMode(string $viewMode): array
    {
        $fields = self::GRID_FIELDS_BASE_LIST;
        array_unshift($fields, sprintf('SELECTED_%s', $viewMode));

        return $fields;
    }
}