<?php

namespace Immo\Statements\Calculator\Role;

use Bitrix\Main\ArgumentException;
use Immo\Statements\Calculator\StatementsCalculator;

/**
 * Класс для расчёта вычисляемых полей представления "Бухгалтер по ЗП (финал, по БЕ)"
 */
class SalaryAccountantByBeCalculator extends StatementsCalculator
{
    /**
     * Рассчитывает значение поля "Доплата":
     * Оклад по 1С минус Заработная плата по 1С
     *
     * @return float
     */
    public function calculateAdditionalPaymentSum(): float
    {
        $salaryFrom1CSum1 = $this->prepareMoneyValueBeforeCalculation('UF_1C_SUM');
        $salaryFrom1CSum2 = $this->prepareMoneyValueBeforeCalculation('UF_1C_SALARY_SUM');

        $value = $salaryFrom1CSum1 - $salaryFrom1CSum2;

        return $this->prepareValueFormat($value);
    }

    /**
     * Рассчитывает значение поля "Переплата":
     * Данные подтягиваются из карточки сотрудника (поле КС_БЕ)
     *
     * @return float
     *
     * @throws ArgumentException
     */
    public function calculateOverpaymentSum(): float
    {
        return $this->prepareValueFormat($this->getOverpaymentSum());
    }

    /**
     * Переплата на конец периода
     *
     * Поле сохраняет сведения об остатках переплаты после формирования зарплатной ведомости
     * (разница полей Переплата, Списание из переплаты 1, Списание из переплаты 2, Возврат переплаты в кассу).
     * После согласования ведомости данные в карточке сотрудника обновляются на значение данного поля
     *
     * @return float
     *
     * @throws ArgumentException
     */
    public function calculateOverPayPeriodSum(): float
    {
       $overpaySum = $this->getOverpaymentSum();
       $deductionFromOverpayment1Sum = $this->calculateDeductionFromOverpayment1Sum();
       $deductionFromOverpayment2Sum = $this->prepareMoneyValueBeforeCalculation('UF_DEDUCTION_FROM_OVERPAYMENT_2');
       $cashReturnSum = $this->prepareMoneyValueBeforeCalculation('UF_CASH_RETURN');

       $value = $overpaySum - $deductionFromOverpayment1Sum - $deductionFromOverpayment2Sum - $cashReturnSum;

       return $this->prepareValueFormat($value);
    }

    /**
     * Списание из переплаты 1
     *
     * Если Доплата меньше чем Переплата,
     * то присваивается значение Доплаты,
     * в противном случае присваивается значение Переплаты
     *
     * @return float
     *
     * @throws ArgumentException
     */
    public function calculateDeductionFromOverpayment1Sum(): float
    {
        $addPaymentSum = $this->calculateAdditionalPaymentSum();
        $overpaymentSum = $this->calculateOverpaymentSum();

        $value = $addPaymentSum < $overpaymentSum ? $addPaymentSum : $overpaymentSum;

        return $this->prepareValueFormat($value);
    }

    /**
     * Списание из переплаты 2
     *
     * Если разница между Переплатой и Списание из переплаты 1 больше нуля,
     * то присваивается значение разницы Переплаты и Списание из переплаты 1,
     * в противном случае присваивается значение 0.
     * Списание распространяется на доплату спец премий.
     * Возможна ручная корректировка
     *
     * @return float
     * @throws ArgumentException
     */
    public function calculateDeductionFromOverPayment2Sum(): float
    {
        $overpaymentSum = $this->calculateOverpaymentSum();
        $deductionOverpayment1Sum = $this->calculateDeductionFromOverpayment1Sum();

        $diff = $overpaymentSum - $deductionOverpayment1Sum;
        $value = null;

        if(!is_null('UF_DEDUCTION_FROM_OVERPAYMENT_2')) {
            $value = $this->prepareMoneyValueBeforeCalculation('UF_DEDUCTION_FROM_OVERPAYMENT_2');
        } elseif ((int) $diff > 0) {
            $value = $diff;
        } else {
            $value = 0;
        }

        return $this->prepareValueFormat($value);
    }

    /**
     * Премия сверх оклада по договоренности
     *
     * Оклад к выплате на руки минус Оклад по 1С и плюс Доп оклад
     *
     * @return float
     */
    public function calculateBonusInSalaryExcessSum(): float
    {
        $finalSum = $this->prepareMoneyValueBeforeCalculation('UF_FINAL_SUM');
        $oneCSum = $this->prepareMoneyValueBeforeCalculation('UF_1C_SUM');
        $addSum = $this->prepareMoneyValueBeforeCalculation('UF_ADD_SUM');

        $value = $finalSum - $oneCSum - $addSum;

        return $this->prepareValueFormat($value);
    }

    /**
     * Премии/ специальные на карту
     *
     * «Переработки» плюс «Премия/Возмещение расходов» плюс «Премия/Возврат в кассу»
     * плюс «Премия/Проектная мотивация» плюс «Премия/VIP» плюс «Премия/КОШ»
     * плюс «Премия/Чужая ЗП» МИНУС в «т.ч. Наличными»
     *
     * @return float
     */
    public function calculateBonusSpecialToCardSum(): float
    {
        $overSum = $this->prepareMoneyValueBeforeCalculation('UF_OVER_SUM');
        $premiumCompensationSum = $this->prepareMoneyValueBeforeCalculation('UF_PREM_VOZ');
        $returnToCashboxSum = $this->prepareMoneyValueBeforeCalculation('UF_PREM_RETURN_TO_CASHBOX');
        $motivationSum = $this->prepareMoneyValueBeforeCalculation('UF_PREM_MOTIVATION');
        $vipSum = $this->prepareMoneyValueBeforeCalculation('UF_PREM_VIP');
        $coshSum = $this->prepareMoneyValueBeforeCalculation('UF_PREM_COSH');
        $otherSalarySum = $this->prepareMoneyValueBeforeCalculation('UF_PREM_OTHER_SALARY');
        $cashSum = $this->prepareMoneyValueBeforeCalculation('UF_NAL_SUM');

        $value = ($overSum + $premiumCompensationSum + $returnToCashboxSum + $motivationSum + $vipSum + $coshSum + $otherSalarySum) - $cashSum;

        return $this->prepareValueFormat($value);
    }

    //region расчётные поля с НДФЛ

    /**
     * Доплата до оклада 1С (с НДФЛ)
     *
     * Если Списание с переплаты 1 меньше чем Переплата,
     * то присваивается значение 0,
     * в противном случае из Доплаты вычитается Переплата
     * и полученная разность ПРОВЕРЯЕТСЯ и делится на НДФЛ
     * (с округлением без десятичных дробей)
     *
     * @return float
     * @throws ArgumentException
     */
    public function calculateSupplementTo1CSum(): float
    {
        /**
         * Списание с переплаты 1
         */
        $deductionSum = $this->calculateDeductionFromOverpayment1Sum();

        /**
         * переплата
         */
        $overpaySum = $this->getOverpaymentSum();

        if($deductionSum < $overpaySum) {
            return $this->prepareValueFormat(0);
        }

        /**
         * доплата
         */
        $additionalSum = $this->calculateAdditionalPaymentSum();

        $diff = $additionalSum - $overpaySum;

        $taxParameter = $diff < $this->getTaxBorder() ? self::GLOBAL_SETTINGS_PERSONAL_INCOME_TAX_BASED : self::GLOBAL_SETTINGS_PERSONAL_INCOME_TAX_INCREASED;

        $value = $diff / $this->getPercentValue($taxParameter);

        return $this->prepareValueFormat($value);
    }

    /**
     * Премия к доплате (с НДФЛ)
     *
     * Премия сверх оклада по договоренности минус Списание с переплаты 2 плюс Премии/специальные на карту.
     * Полученная сумма делится на НДФЛ
     *
     * @return float
     * @throws ArgumentException
     */
    public function calculatePremiumToSurchargeSum(): float
    {
        $bonusExcessSum = $this->calculateBonusInSalaryExcessSum();
        $deduction2Sum = $this->calculateDeductionFromOverPayment2Sum();
        $bonusSpecialSum = $this->calculateBonusSpecialToCardSum();

        $value = (($bonusExcessSum - $deduction2Sum) + $bonusSpecialSum);

        $tax = (int) $value >= $this->getTaxBorder() ? self::GLOBAL_SETTINGS_PERSONAL_INCOME_TAX_INCREASED : self::GLOBAL_SETTINGS_PERSONAL_INCOME_TAX_BASED;

        return $this->prepareValueFormat(($value / $this->getPercentValue($tax)));


    }

    /**
     * Итого ЗП (на руки)
     *
     * Заработная плата по 1С плюс ФСС плюс Доплата до оклада 1С с НДФЛ (умноженное на НДФЛ)
     * плюс Премия к доплате с НДФЛ (умноженное на НДФЛ) плюс поле «в т.ч. Наличными»
     *
     * @return float
     * @throws ArgumentException
     */
    public function calculateTotalSalaryOnHandsSum(): float
    {

        /**
         * Заработная плата по 1С
         */
        $uf1CSalarySum = $this->prepareMoneyValueBeforeCalculation('UF_1C_SALARY_SUM');

        /**
         * ФСС
         */
        $ufFssSum = $this->prepareMoneyValueBeforeCalculation('UF_FSS_SUM');

        /**
         * Доплата до оклада 1С с НДФЛ (умноженное на НДФЛ)
         */
        $supplementTo1CSum = $this->calculateSupplementTo1CSum();

        /**
         * Премия к доплате с НДФЛ (умноженное на НДФЛ)
         */
        $premiumToSurchargeSum = $this->calculatePremiumToSurchargeSum();

        /**
         * в т.ч. Наличными
         */
        $ufNalSum = $this->prepareMoneyValueBeforeCalculation('UF_NAL_SUM');

        $value = $uf1CSalarySum + $ufFssSum + $supplementTo1CSum + $premiumToSurchargeSum + $ufNalSum;

        return $this->prepareValueFormat($value);

    }

    /**
     * Итого ЗП (на карту)
     *
     * Заработная плата по 1С плюс ФСС плюс Доплата до оклада 1С с НДФЛ (умноженное на НДФЛ)
     * плюс Премия к доплате с НДФЛ (умноженное на НДФЛ)
     *
     * @return float
     *
     * @throws ArgumentException
     */
    public function calculateTotalSalaryToCardSum(): float
    {
        /**
         * Заработная плата по 1С
         */
        $uf1CSalarySum = $this->prepareMoneyValueBeforeCalculation('UF_1C_SALARY_SUM');

        /**
         * ФСС
         */
        $ufFssSum = $this->prepareMoneyValueBeforeCalculation('UF_FSS_SUM');

        /**
         * Доплата до оклада 1С с НДФЛ (умноженное на НДФЛ)
         */
        $supplementSum = $this->calculateSupplementTo1CSum();

        /**
         * Премия к доплате с НДФЛ (умноженное на НДФЛ)
         */
        $premiumSum = $this->calculatePremiumToSurchargeSum();

        $value = $uf1CSalarySum + $ufFssSum + $supplementSum + $premiumSum;

        return $this->prepareValueFormat($value);
    }
    //endregion

    /**
     * Итого ЗП (наличные)
     *
     * Присваивается значение поля «в т.ч. Наличными»
     *
     * @return float
     */
    public function calculateTotalSalaryCashSum(): float
    {
        return $this->prepareValueFormat($this->prepareMoneyValueBeforeCalculation('UF_NAL_SUM'));
    }
}