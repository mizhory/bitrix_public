<?php

namespace Immo\Statements\Access;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Immo\Statements\Data\Department;
use Immo\Statements\Data\SalaryStatement;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;

Loader::includeModule('intranet');

class User implements ModuleInterface
{
    use ModuleTrait;

    /**
     * Список соответствий статусов ЗПВ и ролей пользователя
     *
     * @var array
     */
    public static array $statesByRole = [
        self::PROPERTY_STATUS_CARD_SALARY_ACCOUNTANT_COMPANY_XML_ID => self::ROLE_SALARY_ACCOUNTANT_COMPANY,
        self::PROPERTY_STATUS_CARD_MAIN_ACCOUNTANT_XML_ID => self::ROLE_MAIN_ACCOUNTANT,
        self::PROPERTY_STATUS_CARD_BE_DIRECTOR_XML_ID => self::ROLE_BE_DIRECTOR,
        self::PROPERTY_STATUS_CARD_FINANCIAL_DIRECTION_XML_ID => self::ROLE_FINANCIAL_DIRECTION,
        self::PROPERTY_STATUS_CARD_SALARY_ACCOUNTANT_BE_XML_ID => self::ROLE_SALARY_ACCOUNTANT_BE,
    ];

    /**
     * Массив с параметрами отображения БЕ/ЮЛ и HL-сущности в зависимости от роли пользователя
     *
     * @var array|array[]
     */
    public static array $viewsByRole = [
        self::ROLE_SALARY_ACCOUNTANT_COMPANY => [
            'iblock_property' => self::PROPERTY_SELECTED_UR_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_COMPANY_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVAL,
        ],
        self::ROLE_SALARY_ACCOUNTANT_BE => [
            'iblock_property' => self::PROPERTY_SELECTED_BE_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_BE_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVAL,
        ],
        self::ROLE_MAIN_ACCOUNTANT => [
            'iblock_property' => self::PROPERTY_SELECTED_BE_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_BE_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVAL,
        ],
        self::ROLE_BE_DIRECTOR => [
            'iblock_property' => self::PROPERTY_SELECTED_BE_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_BE_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVAL,
        ],
        self::ROLE_FINANCIAL_DIRECTION => [
            'iblock_property' => self::PROPERTY_SELECTED_BE_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_BE_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVAL,
        ],
        self::ROLE_ACCOUNTANT_CASHIER => [
            'iblock_property' => self::PROPERTY_SELECTED_BE_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_BE_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVED
        ],
        self::ROLE_MATCHING_HR => [
            'iblock_property' => self::PROPERTY_SELECTED_UR_CODE,
            'details_view_option' => self::DETAILS_VIEW_OPTION_COMPANY_ID,
            'hl_entity' => self::HL_ENTITY_STATEMENTS_APPROVED
        ],
    ];

    /**
     * Массив с полями для фильтрации элементов HL-блока ЗПВ
     *
     * @var array|array[]
     */
    public static array $fieldsForFilter = [
        [
            'fields' => [
                self::DETAILS_VIEW_OPTION_ELEMENT_ID => SalaryStatement::HL_ENTITY_FIELD_ELEMENT_ID,
                self::DETAILS_VIEW_OPTION_COMPANY_ID => SalaryStatement::HL_ENTITY_FIELD_COMPANY,
            ],
            'roles' => [
                self::ROLE_SALARY_ACCOUNTANT_COMPANY,
            ]
        ],
        [
            'fields' => [
                self::DETAILS_VIEW_OPTION_ELEMENT_ID => SalaryStatement::HL_ENTITY_FIELD_ELEMENT_ID,
                self::DETAILS_VIEW_OPTION_BE_ID => SalaryStatement::HL_ENTITY_FIELD_BE,
            ],
            'roles' => [
                self::ROLE_SALARY_ACCOUNTANT_BE,
                self::ROLE_MAIN_ACCOUNTANT,
                self::ROLE_BE_DIRECTOR,
                self::ROLE_FINANCIAL_DIRECTION
            ]
        ]
    ];

    /**
     * Возвращает ID текущего пользователя
     *
     * @return int
     */
    public function getCurrentUserId(): int
    {
        return (int) self::getCurrentUser()->getId();
    }

    /**
     * Формирует фильтр для получения списка ролей пользователя
     *
     * @return string[]
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function prepareFilter(): array
    {
        $currentUserId = $this->getCurrentUserId();
        $fields = Department::getSalaryStatementRolesSelectFields();
        $filter = [
            'LOGIC' => 'OR'
        ];

        foreach ($fields as $field) {
            if($field !== 'ID') {
                $filter[] = [
                    $field => $currentUserId
                ];
            }
        }

        return $filter;
    }

    /**
     * Возвращает роли пользователя для ЗПВ
     *
     * @return array
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getSalaryStatementsRoles(): array
    {
        $userId = $this->getCurrentUserId();
        $results = [
            'user_id' => $userId,
            'is_admin' => self::getCurrentUser()->isAdmin(),
            'structure' => []
        ];
        $options = [
            'filter' => $this->prepareFilter(),
            'select' => Department::getSalaryStatementRolesSelectFields()
        ];


        $list = Department::getEntity()::getList($options)->fetchAll();

        foreach ($list as $item) {

            $departmentId = (int) $item['ID'];
            foreach ($item as $field => $value) {
                if($field !== 'ID' && !is_null($value) && (in_array($userId, $value) || $userId == $value)) {
                    $results['roles'][] = $field;

                    if(!in_array($departmentId, $results['department_id'], true)) {
                        $results['department_id'][] = $departmentId;
                    }

                    $beId = (int)$item['IBLOCK_SECTION_ID'];
                    if ($departmentId > 0 && $beId) {
                        $results['structure'][$beId][$departmentId] = $departmentId;
                    }
                }
            }
        }

        foreach (self::GLOBAL_USER_ROLES as $globalRole) {
            $roleValue = Option::get('askaron.settings', $globalRole);

            if(
                (is_array($roleValue) && in_array($userId, $roleValue)) ||
                (is_string($roleValue) && $userId === (int) $roleValue)
            ) {
                $results['roles'][] = self::ROLE_MAIN_ACCOUNTANT;
            }
        }



        if(!empty($results['roles']) || $results['is_admin']) {
            return $results;
        }

        throw new AccessDeniedException('Access denied');
    }

    /**
     * Возвращает ID группы пользователя по строковому идентификатору
     *
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getGroupId($stringId): int
    {
        return (int) GroupTable::getList([
            'filter' => ['STRING_ID' => $stringId],
            'select' => ['ID']
        ])->fetchObject()->getId();
    }

    /**
     * Выбирает тип отображения (БЕ или юрлица) по роли пользователя на странице списка ведомостей
     * @param string $role
     * @return string
     */
    public function getListViewModeByRole(string $role): string
    {
        $viewsByRole = self::$viewsByRole[$role];
        return str_ireplace('SELECTED_', '', $viewsByRole['iblock_property']);
    }
}