<?php

namespace Immo\Statements\Calculator;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\SystemException;
use Immo\Statements\ModuleInterface;
use Immo\Statements\Traits\ModuleTrait;
use Immo\Statements\UserType\UserField;

Loader::includeModule('highloadblock');

/**
 * Класс изменяет поля элемента в процессе редактирования других полей
 */
class SalaryStatementCalculator extends StatementsCalculator
{
    /**
     * Рассчитывает сумму значения поля "Итого на руки"
     *
     * @return string
     */
    public function calculateTotalSumBySalaryAccountant(): string
    {
        $value = ($this->element['UF_1C_SUM'] - $this->element['UF_FSS_SUM']);

        return $this->prepareValueFormat($value);
    }

    /**
     * Рассчитывает значение поля "Итого (на руки)" для представления "Директор по БЕ"
     *
     * Идет пересчет – сложение значений полей Оклад 1С и Переработки,
     * если выбран тип расчета «по большей сумме», то сложение значений полей Оклад к выплате на руки и Переработки
     * На этапе согласования с Директором, Директор БЕ может вручную отредактировать итоговую сумму
     * (сумма не может быть меньше Переработок, так как к итоговой введенной сумме добавятся Переработки при их наличии)
     *
     * @return string
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function calculateFinalFinanceSumByBeDirector(): string
    {
        $totalSumCalcTypeXmlId = UserField::getUfEnumValueById($this->element['UF_TOTAL_SUM_CALCULATION_TYPE'])['XML_ID'];

        $overSum = explode('|', $this->element['UF_OVER_SUM']);
        $finalSum = explode('|', $this->element[$totalSumCalcTypeXmlId]);

        $sum = $finalSum[0] + $overSum[0];
        $value = (!empty($this->element['UF_FINAL_SUM']) && (int) $this->element['UF_FINAL_SUM'] > 0) ? $this->element['UF_FINAL_SUM'] : $sum;

        return $this->prepareValueFormat($value);
    }

    /**
     * Сумма Оклада к выплате на руки, Переработки, Доп Оклад
     */
    public function calculateTotalSumFyFinancialDirection(): string
    {
        // UF_FINAL_SUM, UF_ADD_SUM, UF_OVER_SUM

        $finalSum = explode('|', $this->element['UF_FINAL_SUM']);
        $addSum = explode('|', $this->element['UF_ADD_SUM']);
        $overSum = explode('|', $this->element['UF_OVER_SUM']);

        $value = $finalSum[0] + $addSum[0] + $overSum[0];

        return $this->prepareValueFormat($value);
    }

    /**
     * Расчёт поля "Оклад к выплате на руки" для представления "Директор по БЕ"
     *
     * По умолчанию-равно окладу по Оферу, может быть скорректировано на усмотрение Директора БЕ
     */
    public function calculateFinalSumByBeDirector()
    {
        // UF_FINAL_SUM - оклад к выплате на руки:
        // По умолчанию-равно окладу по Оферу, может быть скорректировано на усмотрение Директора БЕ
    }

    /**
     * Меняет значение поля "Оклад к выплате на руки"
     *
     * @param Event $event
     * @return EventResult
     */
    public static function updateFinalSum(Event $event): EventResult
    {
        $result = new EventResult();
        $entity = $event->getEntity();

        if(self::checkHlEntityInEventHandler($entity, self::HL_ENTITY_STATEMENTS_APPROVAL)) {
            $fields = $event->getParameter('fields');

            if(!is_null($fields['UF_OFFER_SUM']) && is_null($fields['UF_FINAL_SUM'])) {
                $fields['UF_FINAL_SUM'] = $fields['UF_OFFER_SUM'];
                $result->modifyFields($fields);
            }
        }

        return $result;
    }
}