<?php
namespace Vigr\Budget;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Exception;
use Bitrix\Main\Entity\ExpressionField;
use Immo\Iblock\Manager;
use Immo\Integration\Budget\BudgetHelper;
use Immo\Integration\Budget\BudgetIblock;
use Immo\Integration\Budget\Iblock;
use Vigr\Budget\Internals\BudgetTable;

/**
 * Class Budget
 * @package Vigr\Budget
 */
class Budget
{
    /**
     * @var History $history
     */
    public $history;

    /**
     * Budget constructor.
     * @param bool $needTransaction
     */
    public function __construct($needTransaction = true)
    {
        $this->history = new History();
        $this->needTransaction = $needTransaction;
    }

    /**
     * @param $needTransaction
     * Set need or not need transaction
     */
    public function setTransaction($needTransaction): void
    {
        $this->needTransaction = $needTransaction;
    }

    /**
     * @param $action
     * recalculateByEdit - пересчет между статьями
     * recalculate - пересчет после стандартных действий
     * workWithReserve - работа с резервом (резерв, отмена, оплата)
     * @param $arAdditionalParams
     * тут передаются параметры заявки например
     * @param string $subAction
     * требуется для метода работы с бюджетом (тип действия)
     * @return string[]
     * @throws Exception
     * Основной роутер для работы
     */
    public function work($action, $arAdditionalParams, $subAction = 'set')
    {
        try {
            if ($this->needTransaction) {
                global $DB;
                $DB->StartTransaction();
            }

            switch ($action) {
                case 'recalculateByEdit':
                    $this->recalculateByEdit($arAdditionalParams);
                    break;
                case 'recalculate':
                    $this->recalculate($arAdditionalParams);
                    break;
                case 'workWithReserve':
                    $this->workWithReserve($subAction, $arAdditionalParams);
                    break;
            }
            if ($this->needTransaction) {
                $DB->commit();
            }

            return [
                'status' => 'ok'
            ];
        } catch (\Exception $exception) {
            if ($this->needTransaction) {
                $DB->Rollback();
            }

            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @param false $arFilter
     * @param false $arYears
     * @param bool $edit
     * @throws Exception Пересчет всех полей бюджета
     */
    function recalculate($arFilter = false, $arYears = false, bool $edit = false) : void
    {
        $pId = getPropIdByCodeIb(getIblockIdByCode('articles'),
            'UCHITYVAETSYA_BALANS_NI');

        $dbArticles = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('articles')
            ],
            false,
            false,
            ['PROPERTY_'.$pId['ID'],'ID']
        );

        $arArticles = [];

        while ($arArticle = $dbArticles->fetch()){
            $arArticles[$arArticle['ID']] = $arArticle['PROPERTY_'.$pId.'_VALUE'];
        }

        $this->arArticles = $arArticles;

        unset($arFilter['>=month']);
        $arParams = [
            'select' => [
                '*',
                'monthNew'
            ],
            'order' => [
                'monthNew' => 'asc',
                'biznesUnit' => 'asc',
                'article' => 'asc'
            ],
            'runtime' => [
                new ExpressionField(
                    'monthNew',
                    '(CASE WHEN month>4 THEN month-4 ELSE month+8 END)',
                    'month'
                )
            ]
        ];

        if (!$arYears) {
            $arYears = [
                date('Y')-1,
                date('Y'),
                date('Y')+1
            ];
        }

        if (!$arFilter) {
            $arFilter = [];
        }

        foreach ($arYears as $year) {
            $arFilterT = $arFilter;
            $arFilterT['year'] = $year;
            $arParams['filter'] = $arFilterT;
            $this->recalculateByYear($arParams, $edit);
        }
    }

    /**
     * @param $arParams
     * @param bool $edit
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    function recalculateByYear($arParams, bool $edit = false) : void
    {
        $dbBudgets = BudgetTable::getList(
            $arParams
        );

        $arForRecalculate = [];

        $arErrors = [];

        while ($arBudget = $dbBudgets->fetch()) {
            $biznesUnit = $arBudget['biznesUnit'];
            $article = $arBudget['article'];

            if (!array_key_exists($biznesUnit, $arForRecalculate)) {
                $arForRecalculate[$biznesUnit] = [];
            }

            if (!array_key_exists($article, $arForRecalculate[$biznesUnit])) {
                $arForRecalculate[$biznesUnit][$article] = [];
            }

            $arForRecalculate[$biznesUnit][$article][$arBudget['monthNew']] = $arBudget;

            $arTemp = &$arForRecalculate[$biznesUnit][$article][$arBudget['monthNew']];

            $arForRecalculate[$biznesUnit][$article][$arBudget['monthNew']]['total'] = $arBudget['plan'];

            $arTemp['saldo'] = round($arTemp['plan'] - $arTemp['fact'], 2);

            $arTemp['total'] = round($arTemp['plan'] - $arTemp['fact'] - $arTemp['inReserve'], 2);

            if($this->arArticles[$article] !== 'Нет'){
                if ($arBudget['monthNew'] < 2) {
                    $arTemp['cumulativeTotal'] = $arTemp['total'];
                } else {
                    $arTemp['cumulativeTotal'] = round($arTemp['total'] +
                        $arForRecalculate[$biznesUnit][$article][$arBudget['monthNew'] - 1]['cumulativeTotal'],
                        2);
                }
            }else{

                $arTemp['cumulativeTotal'] = $arTemp['total'];
            }

            if ($arTemp['cumulativeTotal'] < 0 and !$edit) {
                $error = 'Недостаточно бюджета для перерезервирования средств . Месяц : ' . $arTemp['month'] . ' БЕ : ' . $arBudget['BEName'] . ' Статья : ' . $arBudget['ArticleName'] . 'Год : ' . $arBudget['year'];
                unset($arTemp);
                throw new Exception($error, 300);
            }

            unset($arTemp);
        }

        foreach ($arForRecalculate as $arBe) {
            foreach ($arBe as $arArticle) {
                foreach ($arArticle as $arMonth) {
                    BudgetTable::update($arMonth['id'], $arMonth);
                }
            }
        }
    }

    /**
     * @param $arData
     * @throws Exception
     * перенос бюджета между статьями
     */
    function recalculateByEdit($arData) : void
    {
        $arData['deleteSum'] = floatval(str_replace([',', ' '],['.', ''],$arData['deleteSum']));

        $arYears = [
            $arData['nowYear']
        ];

        $arFilters = [];

        $dbBudgetTable = BudgetTable::getList(
            [
                'filter' => [
                    'article' => $arData['nowArticleId'],
                    'biznesUnit' => $arData['beId'],
                    'year' => $arData['nowYear']
                ]
            ]
        );

        $arIds = [];

        $arPlansVsMonthsStart = [];

        while ($arBudget = $dbBudgetTable->fetch()) {
            $arIds[$arBudget['month']] = $arBudget['id'];
            $arPlansVsMonthsStart[$arBudget['month']] = $arBudget['plan'];
        }

        $arDiffsMain = [
            'article' => $arData['nowArticleId'],
            'messageMonths' => '',
            'year' => $arData['nowYear']
        ];

        foreach ($arData['months'] as $keyM => $value) {
            if (
                $arIds[$keyM] <= 0
                or !array_key_exists($keyM, $arPlansVsMonthsStart)
            ) {
                if ($value > 0) {
                    $arDiffsMain['messageMonths'] .=
                        'Месяц: ' . returnNameMonth($keyM, true)
                        . ', Год: ' . $arData['nowYear']
                        . ', старое значение: ' .
                        number_format(0,2,',',' ')
                        . ', новое значение: ' . $value . '<br>';
                }
            } elseif ($value != $arPlansVsMonthsStart[$keyM]) {
                switch ($arData['type']) {
                    case 'iblock':
                        $arDiffsMain['messageMonths'] .=
                            'Месяц: ' . returnNameMonth($keyM)
                            . ', Год: ' . $arData['nowYear']
                            . ', старое значение: ' .
                            number_format($arPlansVsMonthsStart[$keyM],2,',',' ')
                            . ', новое значение: ' . $value . '<br>';
                        break;

                    case 'crm':
                    default:
                        $arDiffsMain['messageMonths'] .=
                            'Месяц: ' . returnNameMonth($keyM, true)
                            . ', Год: ' . $arData['nowYear']
                            . ', старое значение: ' .
                            number_format($arPlansVsMonthsStart[$keyM],2,',',' ')
                            . ', новое значение: ' . $value . '<br>';
                        break;
                }

                BudgetTable::update($arIds[$keyM], ['plan' => $value]);
            }
        }

        $arFilters[] = [
            'article' => $arData['nowArticleId'],
            'biznesUnit' => $arData['beId'],
            'year' => $arData['nowYear']
        ];

        $arDiffsSub = [];

        if ($arData['reArticleId'] > 0 && $arData['deleteSum'] > 0) {
            $arYears = [
                $arData['nowYear'],
                $arData['reYear']
            ];
            $arDiffsSub['article'] = $arData['reArticleId'];
            $arUpdateFromRecalculate = BudgetTable::getList(
                [
                    'filter' => [
                        'article' => $arData['reArticleId'],
                        'month' => $arData['reArticleMonth'],
                        'biznesUnit' => $arData['beId'],
                        'year' => $arData['reYear']
                    ]
                ]
            )->fetch();

            $arFilters[] = [
                'article' => $arData['reArticleId'],
                'biznesUnit' => $arData['beId'],
                'year' => $arData['reYear']
            ];


            $arIds[] = $arUpdateFromRecalculate['id'];

            if (!$arUpdateFromRecalculate) {
                throw new Exception('Не загружен бюджет для статьи на данный месяц!');
            }

            $arDiffsSub['originPlanDec'] = $arUpdateFromRecalculate['plan'];
            $arUpdateFromRecalculate['plan'] -= str_replace(',','.',$arData['deleteSum']);

            if ($arUpdateFromRecalculate['plan'] < 0) {
                throw new Exception('Не хватает средств для списания со статьи в выбранном месяце!');
            } else {
                BudgetTable::update($arUpdateFromRecalculate['id'], ['plan' => $arUpdateFromRecalculate['plan']]);
            }

            $arUpdateFromRecalculate = BudgetTable::getList(
                [
                    'filter' => [
                        'article' => $arData['nowArticleId'],
                        'month' => $arData['nowArticleMonth'],
                        'biznesUnit' => $arData['beId'],
                        'year' => $arData['nowYear']
                    ]
                ]
            )->fetch();

            if (!$arUpdateFromRecalculate) {
                throw new Exception('Не загружен бюджет для статьи на данный месяц!');
            }
            $arDiffsSub['del'] = $arData['deleteSum'];
            $arDiffsSub['nMonth'] = returnNameMonth($arData['nowArticleMonth'], true); // зачисление
            $arDiffsSub['rMonth'] = returnNameMonth($arData['reArticleMonth'], true); // списание

            $arDiffsSub['originPlanInc'] = $arUpdateFromRecalculate['plan'];
            $arUpdateFromRecalculate['plan'] += str_replace(',','.',$arData['deleteSum']);

            BudgetTable::update($arUpdateFromRecalculate['id'], ['plan' => $arUpdateFromRecalculate['plan']]);
        }


        if ($arData['reArticleId'] > 0) {
            $arIds[] = $arUpdateFromRecalculate['id'];
        }


        if (count($arFilters) > 1) {
            foreach ($arFilters as $arFilter) {
                $this->recalculate($arFilter, $arYears, true);
            }
        } else {
            $arTFilter = [];
            foreach (array_keys($arData['months']) as $month){
                $arTFilter[] = array_merge($arFilters[0],['month'=>$month]);
            }
            $arTFilter['LOGIC'] = 'OR';

            $dbForRec = BudgetTable::getList(
                [
                    'filter' => $arTFilter
                ]
            );

            $arTMonths = $arData['months'];

            while($arForRec = $dbForRec->fetch()){
                unset($arTMonths[$arForRec['month']]);
            }



            if (count($arTMonths) > 0) {
                $arBes = [];
                $arAs = [];

                $dbAs = \CIBlockElement::GetList(
                    [],
                    [
                        'ID' => array_keys($arAs)
                    ]
                );

                $arANames = [];

                while ($arAs1 = $dbAs->fetch()) {
                    $arANames[$arAs1['ID']] = $arAs1['NAME'];
                }

                $dbBs = \CIBlockElement::GetList(
                    [],
                    [
                        'ID' => array_keys($arBes)
                    ]
                );

                $arBNames = [];

                while ($arBes1 = $dbBs->fetch()) {
                    $arBNames[$arBes1['ID']] = $arBes1['NAME'];

                }
                foreach ($arTMonths as $key=>$month){
                    $hash = BudgetIblock::generateHash(
                        $arData['beId'],
                        $arData['nowArticleId'],
                        $key,
                        $arData['nowYear']
                    );

                    $arLoadArray = [
                        'biznesUnit' => $arData['beId'],
                        'article' => $arData['nowArticleId'],
                        'year' => $arData['nowYear'],
                        'month' => $key,
                        'plan' => $month,
                        'fact' => 0,
                        'inReserve' => 0,
                        'saldo' => 0,
                        'cumulativeTotal' => 0,
                        'total' => 0,
                        'unicHash' => $hash,
                        'BEName' => $arBNames[$arData['beId']],
                        'ArticleName' => $arANames[$arData['nowArticleId']]
                    ];

                    if (!$arData['noNeedComment']) {
                        $this->history->addMessage(
                            $key,
                            'Загрузка плана бюджета' .
                            ' БЕ - ' . $arBNames[$arData['beId']] .
                            ' Статья - ' . $arANames[$arData['nowArticleId']] .
                            ' Месяц - ' . returnMonthInfo('nameByMonth',$key) .
                            ' Год - ' . $arData['nowYear'] .
                            ' Новый план - ' . $month,
                            History::START_BUDGET,
                            History::TYPES['BUDGET'],
                            $arData['beId'],
                            $arData['nowArticleId']
                        );
                    }

                    $id = BudgetTable::add($arLoadArray);
                }
            }

            $this->recalculate($arFilters[0], $arYears);
        }

        $mainMessage = '';

        if ($arDiffsMain['messageMonths'] || $arDiffsSub) {
            $articles = [
                $arDiffsMain['article'],
                $arDiffsSub['article'],
            ];

            $arANames = getsNames($articles);
        }

        if ($arDiffsMain['messageMonths']) {
            $mainMessage .= 'Перераспределение в рамках  статьи ' . $arANames[$arDiffsMain['article']] . ': <br>' . $arDiffsMain['messageMonths'] . '<br>';
        }

        if ($arDiffsSub) {
            $mainMessage .=
                'Перераспределение между статьями: ' . '<br>' . ' Сумма: '
                . BudgetHelper::formatNumber($arDiffsSub['del'])
                . '<br>';

            $mainMessage .=
                'Статья - ' . $arANames[$arDiffsSub['article']]
                . ' Месяц списания: ' . $arDiffsSub['rMonth']
                . ', Год списания: ' . $arData['nowYear']
                . ', Старое значение: ' . $arDiffsSub['originPlanDec']
                . ', Новое значение: '
                . ($arDiffsSub['originPlanDec'] -= str_replace(',','.', $arData['deleteSum']))
                . '<br>';

            $mainMessage .=
                'Статья - ' . $arANames[$arDiffsMain['article']]
                . ' Месяц зачисления: ' . $arDiffsSub['nMonth']
                . ', Год списания: ' . $arData['nowYear']
                . ', Старое значение: ' . $arDiffsSub['originPlanInc']
                . ', Новое значение: '
                . ($arDiffsSub['originPlanInc'] += str_replace(',','.', $arData['deleteSum']))
                . '<br><br>';
        }

        if ($mainMessage !== '') {
            $arBe = Manager::getElementFields(
                [$arData['beId']],
                Manager::getIblockId('be'),
                ['NAME']
            )[$arData['beId']];

            $mainMessage .= 'Комментарий: ' . $arData['comment'];
            $history = new History();

            $type = History::TYPES['RE_BUDGET'];
            $type .= (!empty($arBe['NAME'])) ? ". БЕ: {$arBe['NAME']}" : '';
            $history->addMessage(0, $mainMessage, History::RE_BUDGET, $type, $arData['beId'], $arDiffsMain['article']);
        }
    }

    /**
     * @param string $action
     * CANCEL - отмена заявки
     * set - установка резерва
     * PARTIAL_PAY - частичная оплата
     * @param array $arData
     * @throws Exception
     * работа с резервом  (1й резерв идет через php код в БП)
     *
     */
    public function workWithReserve($action = 'set', $arData = []) : void
    {
        $arData['type'] = (!array_key_exists('type', $arData)) ? 'crm' : $arData['type'];
        $arData['adjective'] = (!array_key_exists('adjective', $arData)) ? 'безналичный' : $arData['adjective'];

        switch ($arData['type']) {
            case 'crm':
                $type = History::TYPES['BEZNAL'];
                break;
            case 'iblock':
                $type = BudgetIblock::defineTypeBudget($arData['iblockId']);
                break;
        }

        switch ($action) {
            case 'CANCEL':
                $arDataProcessed = $this->buildDataByActivity($arData);

                $arHashes = $arDataProcessed['HASHES'];
                $arForFilter = $arDataProcessed['FILTER'];

                $dbBudgets = BudgetTable::getList(
                    [
                        'filter' => [
                            'unicHash' => array_keys($arHashes)
                        ]
                    ]
                );

                while ($arBudget = $dbBudgets->fetch()) {
                    BudgetTable::update(
                        $arBudget['id'],
                        [
                            'inReserve' => $arBudget['inReserve'] - round($arHashes[$arBudget['unicHash']], 2)
                        ]
                    );
                }

                $this->history->addMessage(0,
                    'Успешная отмена - ID ' . $arData['dealId'],
                    History::CANCEL, $type ?? '', );

                if (count($arForFilter) > 1) {
                    $arForFilter['LOGIC'] = 'OR';
                } else {
                    $arForFilter = $arForFilter[0];
                }

                $this->recalculate(
                    $arForFilter
                );


                break;
            case 'set':
                $this->setReserve($arData);
                break;
            case 'PARTIAL_PAY':
                switch ($arData['type']) {
                    case 'crm':
                        $deal = new Deal();
                        break;

                    case 'iblock':
                        $deal = new Iblock();
                        break;
                }
                $arItems = $deal->getDealProducts($arData['dealId']);

                switch ($arData['type']) {
                    case 'crm':
                        $arFields = $deal->getFieldsByDealBudget($arData['dealId'], ['year', 'month', 'article']);
                        break;

                    case 'iblock':
                        $arFields = $deal->getFieldsByDealBudget(
                            $arData['dealId'],
                            ['year', 'month', 'article'],
                            $arData['iblockId']
                        );
                        break;
                }

                $arForPays = [];


                $arForPays = [];

                $arForFilter = [];

                $arHashes = [];

                $arForUpdates = [];

                $arReReserve = [];
                $arHashesVsIds = [];

                $nowMonth = date('n');
                $nowYear = date('Y');

                $nowFinYear = $nowYear;
                if ($nowMonth < 5) {
                    $nowFinYear--;
                }
                $needRe = false;


                foreach ($arItems as $key => $arItem) {
                    $payInCurseReal = round($arData['value'] / 100 * $arItem['percent'] * $arData['curse'], 2);
                    $valuePercent = $arData['value'] / 100 * $arItem['percent'];

                    $valueRound = round($arItem['summa'] - $arItem['payInValute'], 2);

                    if ($valuePercent > $valueRound && (string)$valuePercent != (string)$valueRound){
                        throw new Exception('Средств для списания недостаточно. 
                        После пересчета по актуальному курсу сумма списания превысила зарезервированные средства. 
                        Просьба обратиться в фин дирекцию!');
                    }

                    switch ($arData['type']) {
                        case 'iblock':
                            $hash = md5($arItem['be'] . $arFields['article'] . $arFields['month'] . $arFields['year']);
                            break;

                        case 'crm':
                        default:
                            $hash = md5($arItem['be'] . $arFields['article'] . returnNameMonth($arFields['month']) . $arFields['year']);
                            break;
                    }
                    $realHash = md5($arItem['be'] . $arFields['article'] . date('n') . $nowFinYear);
                    $arHashes[] = $hash;

                    $arHashesReal[] = $realHash;

                    $arHashesVsIds[$hash] = $key;
                    $arForFilter[] = [
                        'year' => (new \DateTime())->format('Y'),
                        'article' => $arFields['article'],
                        'biznesUnit' => $arItem['be']
                    ];

                    $lastKeyPay = count($arItem['payInCurse']) - 1;
                    $lastValutePay = $arItem['payInCurse'][$lastKeyPay];

                    $arForUpdates[$key] = [];

                    $arForUpdates[$key]['OPLACHENO_PO_VALYUTE'] = round($arItem['payInValute'] + $arData['value'] / 100 * $arItem['percent'],
                        2);

                    if ($arData['reserve'] === 'Y') {
                        $payOstInValute = $arItem['summa'] - $arItem['payInValute'];

                        $arReReserve[$hash] = [
                            'delReserve' => round($arItem['summaInReserve'][$lastKeyPay] - $lastValutePay, 2),
                            'addReserve' => round($payOstInValute * $arData['curse'], 2) + 0.01,
                            'hash' => $hash
                        ];
                        $lastKeyPay++;
                        $arForUpdates[$key]['SUMMA_V_REZERVE'] = $arItem['summaInReserve'];
                        $arForUpdates[$key]['SUMMA_V_REZERVE'][$lastKeyPay] = $arReReserve[$hash]['addReserve'];

                        $arForUpdates[$key]['OPLACHENO'] = $arItem['payInCurse'];
                        $arForUpdates[$key]['OPLACHENO'][$lastKeyPay] = $payInCurseReal;
                        $needRe = true;

                    } else {
                        $arForUpdates[$key]['SUMMA_V_REZERVE'] = $arItem['summaInReserve'];
                        $arForUpdates[$key]['OPLACHENO'] = $arItem['payInCurse'];
                        $arForUpdates[$key]['OPLACHENO'][$lastKeyPay] = round($lastValutePay + $payInCurseReal, 2);

                        if ($payInCurseReal > round($arItem['summaInReserve'][$lastKeyPay] - $lastValutePay, 2)) {
                            throw new Exception('Сумма оплаты по курсу не может быть больше резерва (требуется перерезервирование)!');
                        }
                    }

                    $arForPays[$realHash] = $payInCurseReal;
                    $arForPays[$hash] = $payInCurseReal;
                }

                foreach ($arForUpdates as $key => $arForUpdate) {
                    $deal->updateDealsBe(
                        [
                            'ID' => $key
                        ],
                        $arForUpdate
                    );
                }


                $dbBudgets = BudgetTable::getList(
                    [
                        'filter' => [
                            'unicHash' => $arHashes
                        ]
                    ]
                );

                while ($arBudget = $dbBudgets->fetch()) {

                    $unicHash = $arBudget['unicHash'];
                    if ($arData['reserve'] === 'Y') {
                        $inReserve = $arBudget['inReserve'] - $arReReserve[$unicHash]['delReserve'] + $arReReserve[$unicHash]['addReserve'] - $arForPays[$unicHash];
                    } else {
                        $inReserve = $arBudget['inReserve'] - $arForPays[$unicHash];
                    }


                    $id = $arHashesVsIds[$unicHash];
                    if ($arForUpdates[$id]['OPLACHENO_PO_VALYUTE'] == $arItems[$id]['summa']) {
                        $inReserve = $inReserve - ($arForUpdates[$id]['SUMMA_V_REZERVE'][$lastKeyPay] - $arForUpdates[$id]['OPLACHENO'][$lastKeyPay]);
                    }

                    $inReserve = round($inReserve, 2);


                    BudgetTable::update(
                        $arBudget['id'],
                        [
                            'inReserve' => $inReserve
                        ]
                    );

                };

                $dbBudgets = BudgetTable::getList(
                    [
                        'filter' => [
                            'unicHash' => $arHashesReal
                        ]
                    ]
                );
                $factAll = 0;
                while ($arBudget = $dbBudgets->fetch()) {
                    $fact = $arBudget['fact'] + $arForPays[$arBudget['unicHash']];
                    $fact = round($fact, 2);
                    $factAll += $arForPays[$arBudget['unicHash']];
                    BudgetTable::update(
                        $arBudget['id'],
                        [
                            'fact' => $fact
                        ]
                    );
                }

                if ($needRe) {
                    $this->history->addMessage(0, 'Перерезервирование, ID заявки на ' . $arData['adjective'] . ' расчет - ' . $arData['dealId'],
                        History::RERESERVE, $type ?? '');
                }

                $this->history->addMessage(0,
                    'Оплата : ID заявки на ' . $arData['adjective'] . ' расчет - ' . $arData['dealId'] . ' сумма : ' . $factAll,
                    History::FACT, $type ?? '');


                if (count($arForFilter) > 1) {
                    $arForFilter['LOGIC'] = 'OR';
                } else {
                    $arForFilter = $arForFilter[0];
                }

                $this->recalculate($arForFilter);

                break;
        }
    }

    /**
     * @param $arData
     * установка резерва
     */
    protected function setReserve($arData) : void
    {
        switch ($arData['type']) {
            case 'crm':
                $type = History::TYPES['BEZNAL'];
                break;
            case 'iblock':
                $type = BudgetIblock::defineTypeBudget($arData['iblockId']);
                break;
        }

        $this->history->addMessage(0,
            'Успешное резервирование -  ' . implode(',', $arData),
            3, $type ?? '');
        $arBudget = BudgetTable::getList(
            [
                'filter' => ['unicHash' => $arData['hash']]
            ]
        )->fetch();

        BudgetTable::update($arBudget['id'], ['inReserve' => $arBudget['inReserve'] + $arData['sum']]);
    }

    /**
     * @param $currency
     * Валюта БЕ
     * @param $currencyPayed
     * Валюта плательщика
     * @return string
     * @throws Exception
     */
    public function getRateByRub($currency, $currencyPayed) : string
    {
        $rate = '';
        $xmlCbr = file_get_contents('https://www.cbr.ru/scripts/XML_daily.asp');

        $arRatesCb = new \SimpleXMLElement($xmlCbr);

        foreach ($arRatesCb->Valute as $valute) {
            if ($valute->CharCode == $currency && $currencyPayed == 'RUB') {
                $rate = round((string)$valute->Nominal / (string)$valute->Value, 4);
            } elseif ($valute->CharCode == $currencyPayed) {
                $rate = (string)$valute->Value;
                $rate = preg_replace('/,/', '.', $rate);
            }
        }

        if ($currency === 'RUB' && $currencyPayed === 'RUB') {
            return 1;
        }elseif ($currency === $currencyPayed){
            return 1;
        }

        return $rate;
    }

    /**
     * @param $arData
     * @return array
     * Возвращает данные по заявке для активити
     */
    public function buildDataByActivity($arData) : array
    {
        switch ($arData['type']) {
            case 'iblock':
                $deal = new Iblock();
                break;

            case 'crm':
            default:
                $deal = new Deal();
                break;
        }
        $arItems = $deal->getDealProducts($arData['dealId']);

        switch ($arData['type']) {
            case 'iblock':
                $arFields = $deal->getFieldsByDealBudget(
                    $arData['dealId'],
                    ['year', 'month', 'article'],
                    $arData['iblockId']
                );
                break;

            case 'crm':
            default:
                $arFields = $deal->getFieldsByDealBudget($arData['dealId'], ['year', 'month', 'article']);
                break;
        }

        $arHashes = [];

        $arForFilter = [];

        foreach ($arItems as $key => $arItem) {
            switch ($arData['type']) {
                case 'iblock':
                    $hash = md5($arItem['be'] . $arFields['article'] . $arFields['month'] . $arFields['year']);
                    break;

                case 'crm':
                default:
                    $hash = md5($arItem['be'] . $arFields['article'] . returnNameMonth($arFields['month']) . $arFields['year']);
                    break;
            }
            $lastKey = count($arItem['summaInReserve']) - 1;
            $arForFilter[] = [
                'year' => date('Y'),
                'article' => $arFields['article'],
                'biznesUnit' => $arItem['be']
            ];
            $arHashes[$hash] = $arItem['summaInReserve'][$lastKey] - $arItem['payInCurse'][$lastKey];
        }

        return [
            'FILTER' => $arForFilter,
            'HASHES' => $arHashes
        ];
    }

    /**
     * @description Генерирует и возвращает хэш записи бюджета
     * @param int $beId
     * @param int $articleId
     * @param int $monthNumber
     * @param int $year
     * @return string
     */
    public static function generateHash(int $beId, int $articleId, int $monthNumber, int $year): string
    {
        return md5("{$beId}{$articleId}{$monthNumber}{$year}");
    }
}
