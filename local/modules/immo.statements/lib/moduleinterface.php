<?php

namespace Immo\Statements;

interface ModuleInterface
{
    public const MODULE_ID = 'immo.statements';

    public const IBLOCK_CODE_LABELS_SALARY = 'labels_salary';
    public const IBLOCK_CODE_COMPANIES = 'companies';
    public const IBLOCK_TYPE_BITRIX_PROCESSES = 'bitrix_processes';

    public const HL_ENTITY_COMPANY_EMPLOYEES_DATA = 'CompanyEmplyeesData';
    public const HL_ENTITY_ROUTES = 'Routes';
    public const HL_ENTITY_STATEMENTS_APPROVAL = 'StatementsApproval';
    public const HL_ENTITY_STATEMENTS_APPROVED = 'StatementsApproved';

    /**
     * Код группы "Сотрудники финансовой дирекции"
     */
    public const GROUP_CODE_FINANCIAL_DIRECTION = 'FD';

    /**
     * Код роли бухглатера юрлица
     * @deprecated
     */
    public const ROLE_COMPANY_ACCOUNTANT = 'UF_ROLE_COMPANY_ACCOUNTANT';

    /**
     * @deprecated
     */
    public const ROLE_SALARY_ACCOUNTANT = 'UF_ROLE_SALARY_ACCOUNTANT';

    /**
     * Код роли бухгалтера по ЗП (по БЕ)
     */
    public const ROLE_SALARY_ACCOUNTANT_BE = 'UF_ROLE_SALARY_ACCOUNTANT_BE';

    /**
     * Код роли бухгалтера по ЗП (по юрлицу)
     */
    public const ROLE_SALARY_ACCOUNTANT_COMPANY = 'UF_ROLE_SALARY_ACCOUNTANT_COMPANY';

    /**
     * Код роли Бухгалтер-кассир
     */
    const ROLE_ACCOUNTANT_CASHIER = 'UF_ROLE_ACCOUNTANT_CASHIER';

    /**
     * Код роли директора по БЕ
     */
    public const ROLE_BE_DIRECTOR = 'UF_ROLE_BE_DIRECTOR';

    /**
     * Код роли главного бухгалтера
     */
    public const ROLE_MAIN_ACCOUNTANT = 'UF_GLAV_BUH';

    /**
     * Код роли Список согласующих HR
     */
    public const ROLE_MATCHING_HR = 'UF_SOGLASUYCHIE_HR';

    /**
     * Массив глобальных ролей
     */
    public const GLOBAL_USER_ROLES = [
        self::ROLE_MAIN_ACCOUNTANT,
        self::ROLE_MATCHING_HR
    ];

    /**
     * Код роли финдирекции
     */
    public const ROLE_FINANCIAL_DIRECTION = 'UF_ROLE_FINANCIAL_DIRECTION';

    /*
     * XML_ID статусов согласования. См. свойство "Статус согласования" инфоблока "Ярлыки зарплатных ведомостей"
     */

    public const PROPERTY_STATUS_CARD_SALARY_ACCOUNTANT_COMPANY_XML_ID = 'buhCompany';
    public const PROPERTY_STATUS_CARD_MAIN_ACCOUNTANT_XML_ID = 'mainBuh';
    public const PROPERTY_STATUS_CARD_BE_DIRECTOR_XML_ID = 'direcBe';
    public const PROPERTY_STATUS_CARD_FINANCIAL_DIRECTION_XML_ID = 'fd';
    public const PROPERTY_STATUS_CARD_SALARY_ACCOUNTANT_BE_XML_ID = 'buhZp';
    public const PROPERTY_STATUS_CARD_SUCCESS_XML_ID = 'success';
    public const PROPERTY_STATUS_CARD_FAIL_XML_ID = 'fail';

    const PROPERTY_SELECTED_BE_CODE = 'SELECTED_BE';
    const PROPERTY_SELECTED_UR_CODE = 'SELECTED_UR';

    const DETAILS_VIEW_OPTION_ELEMENT_ID = 'element_id';
    const DETAILS_VIEW_OPTION_COMPANY_ID = 'company_id';
    const DETAILS_VIEW_OPTION_BE_ID = 'be_id';
}