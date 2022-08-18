<?php

namespace Immo\Statements\Details;

use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Immo\Statements\Calculator\Role\SalaryAccountantByBeCalculator;
use Immo\Statements\Calculator\SalaryStatementCalculator;
use Immo\Statements\Data\HLBlock;
use Immo\Statements\ModuleInterface;
use ReflectionClass;
use ReflectionException;

Loader::includeModule('currency');
Loc::loadMessages(__FILE__);

class Fields implements ModuleInterface
{
    public const FIELD_TYPE_TEXT = 'text';
    public const FIELD_TYPE_NUMBER = 'number';
    public const FIELD_TYPE_SELECT = 'select';
    public const FIELD_TYPE_TEXTAREA = 'textarea';

    /**
     * Возвращает список полей для бухгалтера по юр. лицу и главного бухгалтера
     *
     * @param $entityFields
     *
     * @return array[]
     */
    protected static function getFieldsByCompanyAccountant($entityFields): array
    {
        return [
            'ID' => [
                'label' => $entityFields['ID'],
            ],
            'UF_USER' => [
                'type' => $entityFields['UF_USER']['type'],
                'label' => $entityFields['UF_USER']['label']
            ],
            'UF_1C_SUM' => [
                'type' => $entityFields['UF_1C_SUM']['type'],
                'label' =>$entityFields['UF_1C_SUM']['label']
            ],
            'UF_FSS_SUM' => [
                'type' => $entityFields['UF_FSS_SUM']['type'],
                'label' => $entityFields['UF_FSS_SUM']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'TOTAL_ON_HANDS' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage("GRID_COLUMN_TOTAL_ON_HANDS_LABEL"),
                'calculated' => true,
                'callback_class' => SalaryStatementCalculator::class,
                'callback_method' => 'calculateTotalSumBySalaryAccountant'
            ]
        ];
    }

    /**
     * Возвращает список полей для директора по БЕ
     *
     * @param $entityFields
     *
     * @return array[]
     */
    protected static function getFieldsByBeDirector($entityFields): array
    {
        return [
            'ID' => [
                'label' => $entityFields['ID'],
            ],
            'UF_USER' => [
                'type' => $entityFields['UF_USER']['type'],
                'label' => $entityFields['UF_USER']['label']
            ],
            'UF_OFFER_SUM' => [
                'type' => $entityFields['UF_OFFER_SUM']['type'],
                'label' => $entityFields['UF_OFFER_SUM']['label'],
            ],
            'UF_1C_SUM' => [
                'type' => $entityFields['UF_1C_SUM']['type'],
                'label' =>$entityFields['UF_1C_SUM']['label']
            ],
            'UF_ADD_SUM' => [
                'type' => $entityFields['UF_ADD_SUM']['type'],
                'label' => $entityFields['UF_ADD_SUM']['label']
            ],
            'UF_OVER_SUM' => [
                'type' => $entityFields['UF_OVER_SUM']['type'],
                'label' => $entityFields['UF_OVER_SUM']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'UF_NAL_SUM' => [
                'type' => $entityFields['UF_NAL_SUM']['type'],
                'label' => $entityFields['UF_NAL_SUM']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'UF_FINAL_SUM' => [
                'type' => $entityFields['UF_FINAL_SUM']['type'],
                'label' => $entityFields['UF_FINAL_SUM']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER,
                'default_is_empty' => 'UF_OFFER_SUM'
            ],
            'UF_FINAL_FINANCE_SUM' => [
                'type' => $entityFields['UF_FINAL_FINANCE_SUM']['type'],
                'label' => $entityFields['UF_FINAL_FINANCE_SUM']['label'],
                'calculated' => true,
                'callback_class' => SalaryStatementCalculator::class,
                'callback_method' => 'calculateFinalFinanceSumByBeDirector',
                'not_less_than' => 'UF_OVER_SUM',

            ],
            'UF_COMMENT' => [
                'type' => $entityFields['UF_COMMENT']['type'],
                'label' => $entityFields['UF_COMMENT']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_TEXTAREA
            ],
            'UF_TOTAL_SUM_CALCULATION_TYPE' => [
                'type' => $entityFields['UF_TOTAL_SUM_CALCULATION_TYPE']['type'],
                'label' => $entityFields['UF_TOTAL_SUM_CALCULATION_TYPE']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_SELECT,
                'items' => $entityFields['UF_TOTAL_SUM_CALCULATION_TYPE']['items']
            ]
        ];
    }

    /**
     * Возвращает список полей для финдирекции
     *
     * @param array $entityFields
     * @return array[]
     */
    protected static function getFieldsByFinancialBlock(array $entityFields): array
    {
        return [
            'ID' => [
                'label' => $entityFields['ID'],
            ],
            'UF_USER' => [
                'type' => $entityFields['UF_USER']['type'],
                'label' => $entityFields['UF_USER']['label']
            ],
            'UF_1C_SUM' => [
                'type' => $entityFields['UF_1C_SUM']['type'],
                'label' =>$entityFields['UF_1C_SUM']['label']
            ],
            'UF_OFFER_SUM' => [
                'type' => $entityFields['UF_OFFER_SUM']['type'],
                'label' => $entityFields['UF_OFFER_SUM']['label'],
            ],
            'UF_ADD_SUM' => [
                'type' => $entityFields['UF_ADD_SUM']['type'],
                'label' => $entityFields['UF_ADD_SUM']['label'],
            ],
            'UF_FINAL_SUM' => [
                'type' => $entityFields['UF_FINAL_SUM']['type'],
                'label' => $entityFields['UF_FINAL_SUM']['label']
            ],
            'TOTAL_ON_HANDS' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage('GRID_COLUMN_TOTAL_ON_HANDS_LABEL'),
                'calculated' => true,
                'callback_class' => SalaryStatementCalculator::class,
                'callback_method' => 'calculateTotalSumFyFinancialDirection'
            ],
            'UF_OVER_SUM' => [
                'type' => $entityFields['UF_OVER_SUM']['type'],
                'label' => $entityFields['UF_OVER_SUM']['label']
            ],
            'UF_NAL_SUM' => [
                'type' => $entityFields['UF_NAL_SUM']['type'],
                'label' => $entityFields['UF_NAL_SUM']['label']
            ],
            'UF_PREM_VOZ' => [
                'type' => $entityFields['UF_PREM_VOZ']['type'],
                'label' => $entityFields['UF_PREM_VOZ']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER,
            ],
            'UF_PREM_RETURN_TO_CASHBOX' => [
                'type' => $entityFields['UF_PREM_RETURN_TO_CASHBOX']['type'],
                'label' => $entityFields['UF_PREM_RETURN_TO_CASHBOX']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER,
            ],
            'UF_PREM_MOTIVATION' => [
                'type' => $entityFields['UF_PREM_MOTIVATION']['type'],
                'label' => $entityFields['UF_PREM_MOTIVATION']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'UF_PREM_VIP' => [
                'type' => $entityFields['UF_PREM_VIP']['type'],
                'label' => $entityFields['UF_PREM_VIP']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'UF_PREM_COSH' => [
                'type' => $entityFields['UF_PREM_COSH']['type'],
                'label' => $entityFields['UF_PREM_COSH']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'UF_PREM_OTHER_SALARY' => [
                'type' => $entityFields['UF_PREM_OTHER_SALARY']['type'],
                'label' => $entityFields['UF_PREM_OTHER_SALARY']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'UF_CASH_RETURN' => [
                'type' => $entityFields['UF_CASH_RETURN']['type'],
                'label' => $entityFields['UF_CASH_RETURN']['label'],
                'editable' => true,
                'field_type' => self::FIELD_TYPE_NUMBER
            ]
        ];
    }

    /**
     * Список полей для представления “Бухгалтер по ЗП Финал”
     *
     * @param array $entityFields
     * @return array[]
     */
    protected static function getFieldsByFinalViewOfBeSalaryAccountant(array $entityFields): array
    {
        return [
            'ID' => [
                'label' => $entityFields['ID'],
            ],
            'UF_USER' => [
                'type' => $entityFields['UF_USER']['type'],
                'label' => $entityFields['UF_USER']['label']
            ],
            'UF_1C_SUM' => [
                'type' => $entityFields['UF_1C_SUM']['type'],
                'label' =>$entityFields['UF_1C_SUM']['label']
            ],
            'UF_OFFER_SUM' => [
                'type' => $entityFields['UF_OFFER_SUM']['type'],
                'label' => $entityFields['UF_OFFER_SUM']['label'],
            ],
            'UF_FINAL_SUM' => [
                'type' => $entityFields['UF_FINAL_SUM']['type'],
                'label' => $entityFields['UF_FINAL_SUM']['label']
            ],
            'UF_ADD_SUM' => [
                'type' => $entityFields['UF_ADD_SUM']['type'],
                'label' => $entityFields['UF_ADD_SUM']['label']
            ],
            'UF_1C_SALARY_SUM' => [
                'type' => $entityFields['UF_1C_SALARY_SUM']['type'],
                'label' => $entityFields['UF_1C_SALARY_SUM']['label']
            ],
            'UF_FSS_SUM' => [
                'type' => $entityFields['UF_FSS_SUM']['type'],
                'label' => $entityFields['UF_FSS_SUM']['label'],
            ],
            'ADDITIONAL_PAYMENT' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage("GRID_COLUMN_ADDITIONAL_PAYMENT_LABEL"),
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateAdditionalPaymentSum'
            ],
            'OVERPAYMENT' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage("GRID_COLUMN_OVERPAYMENT_LABEL"),
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateOverpaymentSum'
            ],
            'UF_OVERPAY_PERIOD' => [
                'type' => $entityFields['UF_OVERPAY_PERIOD']['type'],
                'label' => $entityFields['UF_OVERPAY_PERIOD']['label'],
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateOverPayPeriodSum'
            ],
            'DEDUCTION_FROM_OVERPAYMENT_1' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage("GRID_COLUMN_DEDUCTION_FROM_OVERPAYMENT_1_LABEL"),
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateDeductionFromOverpayment1Sum'
            ],
            'SUPPLEMENT_TO_1C_SUM' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage("GRID_COLUMN_SUPPLEMENT_TO_1C_SUM_LABEL"),
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateSupplementTo1CSum'
            ],
            'BONUS_IN_SALARY_EXCESS' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => Loc::getMessage("GRID_COLUMN_BONUS_IN_SALARY_EXCESS_LABEL"),
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateBonusInSalaryExcessSum'
            ],
            'UF_DEDUCTION_FROM_OVERPAYMENT_2' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => 'Списание из переплаты 2',
                'calculated_editable' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateDeductionFromOverPayment2Sum',
                'field_type' => self::FIELD_TYPE_NUMBER
            ],
            'BONUSES_SPECIAL_TO_CARD' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => 'Премии/ специальные на карту',
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateBonusSpecialToCardSum'
            ],
            'PREMIUM_TO_SURCHARGE' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => 'Премия к доплате (с НДФЛ)',
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculatePremiumToSurchargeSum'
            ],
            'TOTAL_SALARY_ON_HANDS' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => 'Итого ЗП (на руки)',
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateTotalSalaryOnHandsSum'
            ],
            'TOTAL_SALARY_TO_CARD' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => 'Итого ЗП (на карту)',
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateTotalSalaryToCardSum'
            ],
            'TOTAL_SALARY_CASH' => [
                'type' => MoneyType::USER_TYPE_ID,
                'label' => 'Итого ЗП (наличные)',
                'calculated' => true,
                'callback_class' => SalaryAccountantByBeCalculator::class,
                'callback_method' => 'calculateTotalSalaryCashSum'
            ]
        ];
    }

    /**
     * Возвращает список полей для представления "Бухгалтер-кассир"
     *
     * @param array $entityFields
     * @return array[]
     */
    protected static function getFieldsByAccountantCashier(array $entityFields): array
    {
        return [
            'ID' => [
                'label' => $entityFields['ID']
            ],
            'UF_COMPANY' => [
                'type' => $entityFields['UF_COMPANY']['type'],
                'label' => $entityFields['UF_COMPANY']['label']
            ],
            'UF_SALARY_NAL' => [
                'type' => $entityFields['UF_SALARY_NAL']['type'],
                'label' => $entityFields['UF_SALARY_NAL']['label']
            ]
        ];
    }

    /**
     * Возвращает список полей для представления "Сотрудник кадровой службы (по ЮЛ)"
     *
     * @param array $entityFields
     *
     * @return array[]
     */
    protected static function getFieldsByMatchingHr(array $entityFields): array
    {
        return [
            'UF_USER' => [
                'type' => $entityFields['UF_USER']['type'],
                'label' => $entityFields['UF_USER']['label']
            ],
            'UF_SALARY_1C_OVERPAY' => [
                'type' => $entityFields['UF_SALARY_1C_OVERPAY']['type'],
                'label' => $entityFields['UF_SALARY_1C_OVERPAY']['label'],
            ],
            'UF_SALARY_PREM_OVERPAY' => [
                'type' => $entityFields['UF_SALARY_PREM_OVERPAY']['type'],
                'label' => $entityFields['UF_SALARY_PREM_OVERPAY']['label']
            ]
        ];
    }

    /**
     * Хранит общий список полей, разделённый на представления по ролям
     *
     * @param HLBlock $entity
     * @return string[][]
     *
     */
    public static function getViewList(HLBlock $entity): array
    {
        $entityFields = $entity->getFields();

        return [
            self::ROLE_SALARY_ACCOUNTANT_COMPANY => self::getFieldsByCompanyAccountant($entityFields),
            self::ROLE_MAIN_ACCOUNTANT => self::getFieldsByCompanyAccountant($entityFields),
            self::ROLE_BE_DIRECTOR => self::getFieldsByBeDirector($entityFields),
            self::ROLE_FINANCIAL_DIRECTION => self::getFieldsByFinancialBlock($entityFields),
            self::ROLE_SALARY_ACCOUNTANT_BE => self::getFieldsByFinalViewOfBeSalaryAccountant($entityFields),
            self::ROLE_ACCOUNTANT_CASHIER => self::getFieldsByAccountantCashier($entityFields),
            self::ROLE_MATCHING_HR => self::getFieldsByMatchingHr($entityFields),
        ];
    }

    /**
     * Выводит список полей в соответствии с ролью пользователя
     *
     * @param $role
     * @param HLBlock $entity
     * @return array[]|string[]
     *
     */
    public static function getFieldsByRole($role, HLBlock $entity): array
    {
        $fields = self::getViewList($entity);
        return $fields[$role];
    }

    /**
     * Возвращает callback-массив
     * @param $class
     * @param $method
     * @param null $args
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public static function getCallback($class, $method, $args = null): array
    {
        $class = new ReflectionClass($class);
        $instance = $class->newInstance($args);

        return [$instance, $method];
    }
}