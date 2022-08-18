<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @description Активити для работы с бюджетом
 * Class CBPBudgetActivityIblock
 */
class CBPBudgetActivityIblock extends CBPActivity
{
    use \Immo\Tools\CommonActivity;

    /**
     * CBPBudgetActivityIblock constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'action' => '',
            'iblockId' => '',
            'sum' => '',
            'dealId' => '',
            'curse' => '',
            'reReserve' => '',
            'status' => '',
            'errors' => '',
            'adjective' => ''
        ];
    }

    /**
     * @description Производит действие с заявкой
     * @return int
     */
    public function Execute()
    {
		$type = $this->action;
        $reReserve = $this->reReserve;

        if ($type === 'PAY') {
            $type = 'PARTIAL_PAY';
        }

		$arData = [
		    'dealId' => $this->dealId,
            'value' => $this->sum,
            'curse' => $this->curse,
            'adjective' => $this->adjective,
            'type' => 'iblock',
            'iblockId' => $this->iblockId,

            'month' => (new \Bitrix\Main\Type\Date())->format('n'),
            'year' => (new \Bitrix\Main\Type\Date())->format('Y'),
            'rateCurse' => 1,
            'rate' => 'RUB'
        ];

		if ($reReserve > 1 || $reReserve == 'Y') {
            $arData['reserve'] = 'Y';
        }

        try {
		    \Bitrix\Main\Loader::includeModule('vigr.budget');

            /**
             * Объект для работы с бюджетом
             */
            $budget = new \Immo\Integration\Budget\BudgetIblock();

            /**
             * Если текущая заявка - финансовая, тогда дополняем некоторые данные в массив
             * Дополняются:
             * Год резерва
             * Месяц разерва
             * Курс валюты
             * Валюта
             */
            $budget->fillByFinancialData((int)$arData['dealId'], (int)$arData['iblockId'], $arData);

            /**
             * Запуск работы бюджета
             */
            $budget->work('workWithReserve', $arData, $type);

            $arDataProcessed = $budget->buildDataByActivity($arData);
            $budget->recalculate($arDataProcessed['FILTER']);

            $this->status = 'ok';
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->errors = $e->getMessage();
        }

        return CBPActivityExecutionStatus::Closed;
    }
}