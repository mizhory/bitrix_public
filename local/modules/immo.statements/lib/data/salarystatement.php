<?php

namespace Immo\Statements\Data;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

class SalaryStatement extends Iblock
{
    public const HL_ENTITY_FIELD_ELEMENT_ID = 'UF_LABELS_SALARY_ELEMENT_ID';
    public const HL_ENTITY_FIELD_COMPANY = 'UF_COMPANY';
    public const HL_ENTITY_FIELD_BE = 'UF_BE';

    private $entity;

    private int $elementId;
    private array $element;

    /**
     * Возвращает элемент ИБ ЗПВ
     * @return array
     */
    public function getElement(): array
    {
        return $this->element;
    }

    public function __construct($elementId)
    {
        $this->elementId = $elementId;
        $this
            ->prepareEntity()
            ->prepareElement();
    }

    /**
     * Формирует элемент ИБ ЗПВ для дальнейшего использования в компонентах
     */
    public function prepareElement(): SalaryStatement
    {
        $element = $this->entity::getByPrimary($this->elementId, $this->prepareOptions())->fetch();

        foreach ($element as $field => &$value) {
            if(ctype_upper(str_ireplace('_', '', $field))) {
                unset($element[$field]);
                $element[strtolower($field)] = $value;
            }

            if(preg_match('/_id/', $field, $matches)) {
                $value = (int) $value;
            }
        }

        $this->element = $element;

        return $this;
    }

    /**
     * Формирует параметры для ORM-запроса к ИБ ЗПВ
     * @param array $options
     * @return array
     */
    protected function prepareOptions(array &$options = []): array
    {
        $options['filter'] = isset($options['filter']) ? array_merge($options['filter'], $this->prepareFilter()) : $this->prepareFilter();
        $options['select'] = isset($options['select']) ? array_merge($options['select'], $this->getSelectFields()) : $this->getSelectFields();

        return $options;
    }

    /**
     * Формирует сущность ИБ ЗПВ
     *
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    protected function prepareEntity()
    {
        $iblockId = self::getIblockId(self::IBLOCK_CODE_LABELS_SALARY, self::IBLOCK_TYPE_BITRIX_PROCESSES);
        $this->entity = Iblock::getEntity($iblockId);
        return $this;
    }

    /**
     * Возвращает id БЕ из элемента
     * @return mixed
     */
    public function getBeId()
    {
        return $this->element['be_id'];
    }

    /**
     * Возвращает id юрлица из элемента
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->element['company_id'];
    }

    /**
     * Возвращает id запущенного БП по элементу
     * @return mixed
     */
    public function getWorkflowId()
    {
        return $this->element['workflow'];
    }

    /**
     * Формирует фильтр для ORM-запроса к ИБ ЗПВ
     * @param array $filter
     * @return array
     */
    protected function prepareFilter(array &$filter = [])
    {
        $filter['ID'] = $this->elementId;
        return $filter;
    }

    /**
     * Возвращает список полей для ORM-запроса к ИБ ЗПВ
     * @param array $select
     * @return array
     */
    protected function getSelectFields(array $select = []): array
    {
        $fields = [
            'element_id' => 'ID',
            'IBLOCK_ID',
            'be_id' => 'SELECTED_BE.VALUE',
            'company_id' => 'SELECTED_UR.VALUE',
            'status_card_value_id' => 'STATUS_CARD.VALUE',
            'total_sum_calculation_type_id' => 'TOTAL_SUM_CALCULATION_TYPE.VALUE',
            'workflow' => 'WORKFLOW_ID.VALUE'
        ];

        return array_merge($fields, $select);
    }

    /**
     * Обновляет элемент ИБ ЗПВ для использования в компонентах (если было выполнено обновление на бэке)
     */
    public function reloadElementData(): SalaryStatement
    {
        return $this->prepareElement();
    }

    /**
     * Возвращает id типа расчёта итоговой суммы для всей ведомости
     * @return mixed
     */
    public function getTotalSumCalculationTypeId()
    {
        return $this->element['total_sum_calculation_type_id'];
    }

    /**
     * Возвращает id статуса согласования ЗПВ
     * @return mixed
     */
    public function getStatusCardValueId()
    {
        return $this->element['status_card_value_id'];
    }

}