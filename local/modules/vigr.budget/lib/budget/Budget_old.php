<?php


namespace Vigr\Budget;

use \Exception;
use Bitrix\Main\Entity\ExpressionField;
use Vigr\Budget\Internals\BudgetTable;

class BudgetOld
{
    protected $needTransaction;

    /**
    *@var History $history
     */
    protected $history;

    public function __construct($needTransaction = true)
    {
        $this->history = new History();
        $this->needTransaction = $needTransaction;
    }

    public function setTransaction($needTransaction){
        $this->needTransaction = $needTransaction;
    }

    public function work($action, $arAdditionalParams, $subAction = 'set')
    {
        try {
            if($this->needTransaction){
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
                case 'getBudgetByBE':
                    $this->getBudgetByBE($arAdditionalParams);
                    break;

            }
            if($this->needTransaction){
                $DB->commit();
            }

            return [
                'status' => 'ok'
            ];
        } catch (\Exception $exception) {
            if($this->needTransaction){
                $DB->Rollback();
            }
            $this->history->addMessage(0,$exception->getMessage(),'reserve');
            throw new Exception($exception->getMessage());
        }

    }

    public function getBudgetByBE($arData)
    {

    }


    public function recalculateByEdit($arData)
    {
        if ($arData['reArticleId'] > 0) {
            $arUpdateFromRecalculate = BudgetTable::getList(
                [
                    'filter' => [
                        'article' => $arData['reArticleId'],
                        'month' => $arData['reArticleMonth'],
                        'biznesUnit' => $arData['beId']
                    ]
                ]
            )->fetch();

            if (!$arUpdateFromRecalculate) {
                throw new Exception();
            }

            $arUpdateFromRecalculate['plan'] -= $arData['deleteSum'];

            if ($arUpdateFromRecalculate['plan'] < 0) {
                throw new Exception();
            } else {
                BudgetTable::update($arUpdateFromRecalculate['id'], ['plan' => $arUpdateFromRecalculate['plan']]);
            }
        }

        $dbBudgetTable = BudgetTable::getList(
            [
                'filter' => [
                    'article' => $arData['nowArticleId'],
                    'biznesUnit' => $arData['beId'],
                    'year'=>$arData['nowYear']
                ]
            ]
        );

        $arIds = [];

        while ($arBudget = $dbBudgetTable->fetch()) {
            $arIds[$arBudget['month']] = $arBudget['id'];
        }

        foreach ($arData['months'] as $keyM => $value) {
            if ($arData['nowArticleMonth'] == $keyM) {
                $value += $arData['deleteSum'];
            }
            BudgetTable::update($arIds[$keyM], ['plan' => $value]);
        }

        if ($arData['reArticleId'] > 0) {
            $arIds[] = $arUpdateFromRecalculate['id'];
        }

        $this->recalculate(['id' => $arIds]);
    }

    protected function setReserve($arData)
    {
        $this->history->addMessage(0,
            'Успешное резервирование -  '.implode(',',$arData),
            'reserve');
        $arBudget = BudgetTable::getList(
            [
                'filter' => ['unicHash' => $arData['hash']]
            ]
        )->fetch();

        BudgetTable::update($arBudget['id'], ['inReserve' => $arBudget['inReserve'] + $arData['sum']]);
    }

    public function buildDataByActivity($arData){
        $deal = new Deal();
        $arItems = $deal->getDealProducts($arData['dealId']);
        $arFields = $deal->getFieldsByDealBudget($arData['dealId'], ['year', 'month', 'article']);

        $arHashes = [];

        $arForFilter = [];

        foreach ($arItems as $key => $arItem){
            $hash = md5($arItem['be'] . $arFields['article'] . returnNameMonth($arFields['month']) . $arFields['year']);
            $arForFilter[] = [
                'year'=>2021,
               // '>=month' => returnNameMonth($arFields['month']) - 1,
                'article' => $arFields['article'],
                'biznesUnit' => $arItem['be']
            ];
            $arHashes[$hash] = $arItem['summaInReserve'] - $arItem['payInCurse'];
        }

        return [
            'FILTER'=>$arForFilter,
            'HASHES'=>$arHashes
        ];
    }

    public function workWithReserve($action = 'set', $arData = [])
    {
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



                while($arBudget = $dbBudgets->fetch()){
                    BudgetTable::update(
                        $arBudget['id'],
                        [
                            'inReserve' => $arBudget['inReserve'] - $arHashes[$arBudget['unicHash']]
                        ]
                    );
                }

                $this->history->addMessage(0,
                    'Успешная отмена -  '.implode(',',$arData),
                    'reserve');

                if(count($arForFilter) > 1){
                    $arForFilter['LOGIC'] = 'OR';
                }else{
                    $arForFilter = $arForFilter[0];
                }

                $this->recalculate(
                    $arForFilter
                );

                echo 'ok';
                break;
            case 'set':
                $this->setReserve($arData);
                break;
            case 'PARTIAL_PAY':
                $deal = new Deal();
                $arItems = $deal->getDealProducts($arData['dealId']);

                $arForPays = [];


                $arFields = $deal->getFieldsByDealBudget($arData['dealId'], ['year', 'month', 'article']);

                $arForFilter = [];

                $arHashes = [];

                $arForUpdates = [];

                $arReReserve = [];

                foreach ($arItems as $key => $arItem) {
                    $payInCurseReal = round($arData['value'] / 100 * $arItem['percent'] * $arData['curse'], 2);

                    $hash = md5($arItem['be'] . $arFields['article'] . returnNameMonth($arFields['month']) . $arFields['year']);

                    $arHashes[] = $hash;

                    $arForFilter[] = [
                        'year'=>2021,
                        //'>=month' => returnNameMonth($arFields['month']),
                        'article' => $arFields['article'],
                        'biznesUnit' => $arItem['be']
                    ];

                    $arForUpdates[$key] = [
                        'OPLACHENO_PO_VALYUTE' => round($arItem['payInValute'] + $arData['value'] / 100 * $arItem['percent'],
                            2),
                        'OPLACHENO' => round($arItem['payInCurse'] + $payInCurseReal, 2)
                    ];

                    $arForPays[$hash] = $payInCurseReal;

                    if ($arData['reserve'] === 'Y') {
                        $payInValute = $arItem['summa'] - $arItem['payInValute'];
                        $arReReserve[$hash] = [
                            'delReserve' => $arItem['summaInReserve'] - $arItem['payInCurse'],
                            'addReserve' => round($payInValute * $arData['curse'],2),
                            'hash' => $hash
                        ];
                        $arForUpdates[$key]['SUMMA_V_REZERVE'] = round($payInValute * $arData['curse'],2);

                        if ($payInCurseReal > $arForUpdates[$key]['SUMMA_V_REZERVE']) {
                            throw new Exception('Сумма оплаты не может быть больше остатка всей суммы!');
                        }

                    }else{
                        if ($payInCurseReal > $arItem['summaInReserve'] - $arItem['payInValute']) {
                            throw new Exception('Сумма оплаты не может быть больше остатка всей суммы!');
                        }
                    }
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

                    $fact = $arBudget['fact'] + $arForPays[$arBudget['unicHash']];

                    $inReserve = round($inReserve,2);
                    $fact = round($fact,2);

                    BudgetTable::update(
                        $arBudget['id'],
                        [
                            'fact' => $fact,
                            'inReserve' => $inReserve
                        ]
                    );
                };

                $this->history->addMessage(0,
                    'Успешная оплата -  '.implode(',',$arData),
                    'reserve');

                foreach ($arForUpdates as $key => $arForUpdate) {
                    $deal->updateDealsBe(
                        [
                            'ID' => $key
                        ],
                        $arForUpdate
                    );
                }

                if(count($arForFilter) > 1){
                    $arForFilter['LOGIC'] = 'OR';
                }else{
                    $arForFilter = $arForFilter[0];
                }

                $this->recalculate($arForFilter);

                echo 'ok';


                break;
        }
    }


    public function recalculate($arData = false)
    {
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


        if($arData){
            $arParams['filter'] = $arData;
        }

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

            if ($arBudget['monthNew'] < 2) {
                $arTemp['cumulativeTotal'] = $arTemp['plan'];
            } else {
                $arTemp['cumulativeTotal'] = round($arTemp['plan'] + $arForRecalculate[$biznesUnit][$article][$arBudget['monthNew'] - 1]['total'],
                    2);
            }

            $arTemp['total'] = round($arTemp['cumulativeTotal'] - $arTemp['fact'] - $arTemp['inReserve'], 2);

            if ($arTemp['total'] < 0) {
                $error = 'Ошибка пересчета Месяц : ' . $arTemp['month'] . ' БЕ : ' . $arBudget['BEName'] . ' Статья : ' . $arBudget['ArticleName'];
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

    public function getRateByRub($currency)
    {
        $rate = '';
        $xmlCbr = file_get_contents('https://www.cbr.ru/scripts/XML_daily.asp');

        $arRatesCb = new \SimpleXMLElement($xmlCbr);

        foreach ($arRatesCb->Valute as $valute) {
            if ($valute->CharCode == $currency) {
                $rate = round((string)$valute->Nominal / (string)$valute->Value, 4);
            }
        }

        return $rate;
    }
}














