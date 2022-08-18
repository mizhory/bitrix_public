<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

/**
 * Class CBudgetEdit
 * Редактирование статей бюджета
 */
class CBudgetEdit extends CBitrixComponent implements Controllerable
{
    protected $status;

    protected $response;

    /**
     * Отдает ответ для реквеста
     */
    public function sendRequest(){
        echo json_encode([
            'status'=>$this->status,
            'response'=>$this->response
        ]);
    }


    public function executeComponent()
    {
        \Bitrix\Main\Loader::includeModule('vigr.budget');
        Extension::load('ui.bootstrap4');
        $this->buildData();
        $this->IncludeComponentTemplate();
    }

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'save' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'getArticleData' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }

    /**
     * Сохранение данных
     */
    public function saveAction(){
        global $DB;
        CModule::includeModule('vigr.budget');
        $context = Bitrix\Main\Application::getInstance()->getContext();

        $arValues = $context->getRequest()->getValues();
        $arValues['noNeedComment'] = true;

        $budget = new \Vigr\Budget\Budget();

        try {
            $DB->StartTransaction();

            $budget->recalculateByEdit($arValues);

            $this->status = 'success';
            $DB->commit();
        } catch (\Exception $exception) {
            $DB->Rollback();
            $this->status = 'error';
            $this->response = ['error' => $exception->getMessage()];
        } finally {
            $this->sendRequest();
        }


        die();
    }

    /**
     * @return
     * Отдает планы по статьям
     */
    public function getArticleDataAction(int $articleId, int $beId, int $year) {
        \Bitrix\Main\Loader::includeModule('vigr.budget');

        $arData = [
            'articleId' => $articleId,
            'itogo' => 0
        ];

        $arMonths = \Immo\Integration\Budget\BudgetHelper::getMonths();
        foreach (array_keys($arMonths) as $monthNum) {
            $arData['months'][$monthNum] = '0';
        }

        if($articleId > 0 && $beId>0){
            $dbBudgets = \Vigr\Budget\Internals\BudgetTable::query()
                ->where([
                    ['biznesUnit', $beId],
                    ['article', $articleId],
                    ['year', $year],
                ])
                ->setSelect(['month', 'plan'])
                ->exec();
            while($arBudget = $dbBudgets->fetch()){
                $arData['months'][$arBudget['month']] = $arBudget['plan'];
                $arData['itogo'] += $arBudget['plan'];
            }
        }

        return $arData;
    }

    protected function fillBudgetData(array $variables): void
    {
        $filter = \Bitrix\Main\ORM\Query\Query::filter();

        if (!empty($variables['beId'])) {
            $filter->where('biznesUnit', $variables['beId']);
        }
        if (!empty($variables['articleId'])) {
            $filter->where('article', $variables['articleId']);
        }
        if (!empty($variables['year'])) {
            $filter->where('year', $variables['year']);
        }

        if (empty($filter->getConditions())) {
            return;
        }

        $rsBudget = \Vigr\Budget\Internals\BudgetTable::query()
            ->where($filter)
            ->setSelect(['month', 'plan'])
            ->exec();

        while ($budgetRow = $rsBudget->fetch()) {
            $this->arResult['DATA']['PLAN'][$budgetRow['month']] = $budgetRow['plan'];
            $this->arResult['DATA']['TOTAL'] += $budgetRow['plan'];
        }
    }

    /**
     * @throws Exception
     * Выборка данных
     */
    public function buildData()
    {
        $this->arResult['DATA'] = [
            'plan' => [],
            'total' => 0,
        ];

        $this->arResult['ARTICLES'] = \Immo\Integration\Budget\BudgetHelper::getActiveArticles();
        $this->arResult['BE'] = Immo\Structure\Organization::getAllBe();
        $this->arResult['YEARS'] = \Immo\Integration\Budget\BudgetHelper::getBudgetYears();
        $this->arResult['MONTHS'] = \Immo\Integration\Budget\BudgetHelper::getMonths();

        if (!empty($this->arParams['variables'])) {
            $this->fillBudgetData($this->arParams['variables'] ?? []);
        }
    }
}