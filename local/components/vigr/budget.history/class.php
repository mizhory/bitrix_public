<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Class CBudgetHistory
 * История бюджета
 */
class CBudgetHistory extends CBitrixComponent
{
    /**
     * @description Возвращает типы истории
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getTypes(): array
    {
        foreach (\Immo\Financial\App\FinancialApp::getFinancialIblocks() as $iblockId) {
            $type = \Immo\Integration\Budget\BudgetIblock::defineTypeBudget($iblockId);
            $arTypes[$type] = $type;
        }

        foreach (\Vigr\Budget\History::TYPES as $typeKey => $name) {
            $arTypes[$typeKey] = $name;
        }

        return $arTypes ?? [];
    }

    /**
     *Постороение параметров таблицы  + фильтр
     */
    public function buildTableParams()
    {
        $this->arResult['filterFields'] = [
            [
                'id' => 'DEAL_TYPE',
                'name' => 'Тип заявки',
                'type' => 'list',
                'items' => $this->getTypes(),
                'params' => ['multiple' => 'Y'],
                'default' => true
            ],
            [
                'id' => 'type',
                'name' => 'Тип события',
                'type' => 'list',
                'items' =>
                    [
                        \Vigr\Budget\History::START_BUDGET => 'Первичная загрузка бюджет',
                        \Vigr\Budget\History::UPDATE_BUDGET => 'Обновление бюджета',
                        \Vigr\Budget\History::RESERVE => 'Резервирование средств',
                        \Vigr\Budget\History::RERESERVE => 'Перерезервирование средств',
                        \Vigr\Budget\History::FACT => 'Списание средств',
                        \Vigr\Budget\History::CANCEL => 'Отмена резервирования средств',
                        \Vigr\Budget\History::RE_BUDGET => 'Перераспределение средств бюджета',
                    ],
                'params' => ['multiple' => 'Y'],
                'default' => true
            ],
            [
                'id' => 'USER_ID',
                'name' => 'Пользователь',
                'type' => 'entity_selector',
                'params' => [
                    'multiple' => 'Y',
                    'dialogOptions' => [
                        'height' => 240,
                        'context' => 'filter',
                        'entities' => [
                            [
                                'id' => 'user',
                                'options' => [
                                    'inviteEmployeeLink' => false
                                ],
                            ],
                            [
                                'id' => 'department',
                            ]
                        ]
                    ],
                ],
                'default' => true
            ],
            [
                'id' => 'DATE_HISTORY',
                'name' => 'Дата/время действия',
                'type' => 'date',
                'default' => true
            ],
        ];

        $articleIblockId = \Immo\Iblock\Manager::getArticleIblockId();
        if ($articleIblockId > 0) {
            $rsArticles = \CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $articleIblockId, 'ACTIVE' => 'Y'],
                false,
                false,
                ['NAME', 'ID']
            );
            while($article = $rsArticles->Fetch()) {
                $arArticles[$article['ID']] = $article['NAME'];
            }

            if (!empty($arArticles)) {
                $this->arResult['filterFields'][] = [
                    'id' => 'ARTICLE_ID',
                    'name' => 'Статья расходов',
                    'type' => 'list',
                    'items' => $arArticles,
                    'params' => ['multiple' => 'Y']
                ];
            }
        }

        $beIblockId = \Immo\Iblock\Manager::getIblockId('be');
        if ($beIblockId > 0) {
            $rsBe = \CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $beIblockId, 'ACTIVE' => 'Y'],
                false,
                false,
                ['NAME', 'ID']
            );
            while($be = $rsBe->Fetch()) {
                $arBes[$be['ID']] = $be['NAME'];
            }

            if (!empty($arBes)) {
                $this->arResult['filterFields'][] = [
                    'id' => 'BE_ID',
                    'name' => 'БЕ',
                    'type' => 'list',
                    'items' => $arBes,
                    'params' => ['multiple' => 'Y']
                ];
            }
        }

        global $USER;

        $this->arResult['filterId'] = 'history_' . $USER->getId();
        $filterOption = new Bitrix\Main\UI\Filter\Options('history_' . $USER->getId());
        $this->filterData = $filterOption->getFilter([]);
    }


    /**
     * Получение данных
     */
    public function initData()
    {
        $arHistors = [];

        $arParams = [
            'order' => [
                'date' => 'desc'
            ],
            'filter'=>[

            ]
        ];

        if ($this->filterData['type']) {
            $arParams['filter']['action'] = $this->filterData['type'];
        }

        if ($this->filterData['USER_ID']) {
            $arParams['filter']['user'] = $this->filterData['USER_ID'];
        }

        if (!empty($this->filterData['ARTICLE_ID'])) {
            $arParams['filter']['@id_article'] = $this->filterData['ARTICLE_ID'];
        }

        if (!empty($this->filterData['BE_ID'])) {
            $arParams['filter']['@id_be'] = $this->filterData['BE_ID'];
        }

        $arParams['order'] = [
            'id'=>'desc'
        ];

        if ($this->filterData['DEAL_TYPE']) {
            $arParams['filter']['@DEAL_TYPE'] = $this->filterData['DEAL_TYPE'];
        }

        if (!empty($this->filterData['DATE_HISTORY_from'])) {
            $arParams['filter']['>=date'] = $this->filterData['DATE_HISTORY_from'];
        }
        if (!empty($this->filterData['DATE_HISTORY_to'])) {
            $arParams['filter']['<=date'] = $this->filterData['DATE_HISTORY_to'];
        }

        $dbHistory = \Vigr\Budget\Internals\HistoryTable::getList(
            $arParams
        );

        $aUsersIds = [];
        while ($arHistory = $dbHistory->fetch()) {
            switch ($arHistory['action']){
                case '0':
                    $arHistory['action'] = 'Загрузка файла(ов) бюджета';
                    break;
                case '1':
                    $arHistory['action'] = 'Первичная загрузка бюджет';
                    break;
                case '2':
                    $arHistory['action'] = 'Обновление бюджета';
                    break;
                case '3':
                    $arHistory['action'] = 'Резервирование средств';
                    break;
                case '4':
                    $arHistory['action'] = 'Перерезервирование средств';
                    break;
                case '5':
                    $arHistory['action'] = 'Списание средств';
                    break;
                case '6':
                    $arHistory['action'] = 'Отмена резервирования средств';
                    break;
                case '7':
                    $arHistory['action'] = 'Перераспределение средств бюджета';
                    break;
            }
            $aUsersIds[] = $arHistory['user'];
            $arHistors[] = $arHistory;
        }
        $by="personal_country";
        $order="desc";
        $dbUsers = CUser::getList($by,$order,['ID'=>implode('|',$aUsersIds)]);
        $arUsers = [];

        while($arUser = $dbUsers->fetch()){
            $arUsers[$arUser['ID']] = '['.$arUser['ID'].'] '.$arUser['EMAIL'].' '.$arUser['NAME'].' '.$arUser['LAST_NAME'];
        }

        foreach ($arHistors as &$arHistor){
            $arHistor['user'] = $arUsers[$arHistor['user']];
            unset($arHistor);
        }

        $this->arResult['data'] = $arHistors;
    }

    public function executeComponent()
    {
        $this->buildTableParams();
        $this->initData();
        $this->IncludeComponentTemplate();
    }
}
