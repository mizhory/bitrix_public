<?php


namespace Vigr\Budget;

use Vigr\Budget\Internals\BudgetTable;
use Vigr\Budget\Internals\HistoryTable;
use Vigr\Budget\Traits\AjaxTrait;
use Vigr\Budget\Traits\ErrorTrait;

/**
 * Class FileDownloader
 * @package Vigr\Budget
 */
class FileDownloader
{
    use ErrorTrait;
    use AjaxTrait;

    /**
     * @var array
     */
    protected $dataFiles = [];


    /**
     * @var History $history
     */
    protected $history;

    /**
     * @var array
     */
    protected $dataFilesHashes = [];

    /**
     * @var array
     */
    protected $arBudgetElements = [];

    /**
     * @return bool
     * Проверка файлов на корректность
     */
    public function checkFiles(): bool
    {
        $history = new History();
        $this->history = $history;
        global $DB;
        try {
            $DB->StartTransaction();
            $this->checkFilesStepOne();
            $this->checkFilesStepTwo();

            $this->loadFilesInTable();

            $this->status = 'ok';
            //$history->addMessage(0, 'Файлы загружены успешно', 'downloadFile');
            $DB->commit();
        } catch (\Exception $exception) {
            if ($exception->getCode() === 300) {
                $this->pushInErrors('recalculateInFile', $exception->getMessage());
            }
            $DB->Rollback();
            $history->addMessage(0, 'Ошибка при загрузке файлов', 'downloadFile');
            $this->status = 'error';
            $this->response = $this->arErrors;
        } finally {
            $this->sendRequest();
        }

        return true;
    }

    /**
     *Шаг 2 - Проверка наличия уже таких данных в таблице бюджета
     */
    public function checkFilesStepTwo()
    {
        $arBE = [];
        $arArticles = [];

        $arAllData = [];

        foreach ($this->dataFiles as $dataFile) {
            foreach ($dataFile as $arString) {
                $arArticles[$arString[1]] = true;
                $arBE[$arString[0]] = true;
                $arAllData[] = $arString;
            }
        }

        $dbIblocks = \CIBlock::GetList(
            [],
            [
                'CODE' => ['be', 'articles']
            ]
        );

        $iblockBeId = 0;
        $iblockArticleBe = 0;

        while ($arIblock = $dbIblocks->fetch()) {
            if ($arIblock['CODE'] === 'articles') {
                $iblockArticleBe = $arIblock['ID'];
            } elseif ($arIblock['CODE'] === 'be') {
                $iblockBeId = $arIblock['ID'];
            }
        }

        $arArticlesIds = [];
        $arBeIds = [];

        $dbElements = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => [$iblockBeId, $iblockArticleBe],
                'NAME' => array_merge(array_keys($arArticles), array_keys($arBE))
            ]
        );

        while ($arElement = $dbElements->fetch()) {
            if ($arElement['IBLOCK_ID'] == $iblockArticleBe) {
                $arArticlesIds[$arElement['NAME']] = $arElement['ID'];
            } else {
                $arBeIds[$arElement['NAME']] = $arElement['ID'];
            }
        }

        $arHashes = [];

        foreach ($this->dataFiles as $fKey => $dataFile) {
            foreach ($dataFile as $sKey => $arString) {
                $hash = md5($arString[0] . $arString[1] . $arString[2] . $arString[3]);



                $this->dataFiles[$fKey][$sKey]['hash'] = $hash;
                $arHashes[] = $hash;
            }
        }

        $dbBudgetElements = BudgetTable::getList(
            [
                'filter' => [
                    'unicHash' => $arHashes
                ]
            ]
        );

        $arBudgetElements = [];

        while ($arBudgetElement = $dbBudgetElements->fetch()) {
            $arBudgetElements[$arBudgetElement['unicHash']] = $arBudgetElement;
        }

        $this->arBudgetElements = $arBudgetElements;
    }

    /**
     * @throws \Exception
     * Проверка файлов на корректность
     */
    public function checkFilesStepOne(): void
    {
        $arData = [];

        $haveHashes = [];

        foreach ($_FILES as $arFile) {
            $handle = fopen($arFile['tmp_name'], "r");
            $length = 0;
            while (($data = fgetcsv($handle, 0, ";")) !== false) {

                if ($length === 0) {
                    $length++;
                    continue;
                }

                if (!array_key_exists($arFile['name'], $arData)) {
                    $arData[$arFile['name']] = [];
                }

                $arData[$arFile['name']][] = $data;
            }

            fclose($handle);
        }

        $arHashes = [];

        $arIdsBes = [];
        $arIdsArticles = [];

        $nowMonth = date('n');
        $nowYear = date('Y');
        $nowDay = date('d');

        $nowFinYear = $nowYear;
        if($nowMonth < 5){
            $nowFinYear--;
        }

        foreach ($arData as $fileKey => $arDataFile) {

            foreach ($arDataFile as $key => $arDataString) {
                $arIdsBes[$arDataString[0]] = true;
                $arIdsArticles[$arDataString[1]] = true;
                $hash = md5($arDataString[0] . $arDataString[1] . $arDataString[2] . $arDataString[3]);

                $finMonth = returnMonthNumberByBudget($arDataString[2]);

                $nowMinMonth = returnMonthNumberByBudget(date('n'));

                if($nowDay == 13 || $nowDay == 14 || $nowDay == 15){
                    if($arDataString[3] < $nowFinYear || $arDataString[3] > $nowFinYear + 1){
                        $this->pushInErrors('additionalError', [
                            'message' => 'Некорректные данные. Бюджет года '.$arDataString[3].' не был загружен. '
                        ]);
                    }
                    if ($finMonth < $nowMinMonth && $arDataString[3] == $nowFinYear) {
                        $this->pushInErrors('additionalError', [
                            'message' => 'Реальный месяц - ' . $arDataString[2] . ' Фин.месяц -  ' . $finMonth . ' меньше чем текущий финансовый месяц - ' . $nowMinMonth
                        ]);
                    }
                }elseif ($arDataString[3] != $nowFinYear){
                    $this->pushInErrors('additionalError', [
                        'message' => 'Некорректные данные. Бюджет года '.$arDataString[3].' не был загружен. '
                    ]);
                    if ($finMonth < $nowMinMonth) {
                        $this->pushInErrors('additionalError', [
                            'message' => 'Реальный месяц - ' . $arDataString[2] . ' Фин.месяц -  ' . $finMonth . ' меньше чем текущий финансовый месяц - ' . $nowMinMonth
                        ]);
                    }
                }

                $finMonth = returnMonthNumberByBudget($arDataString[2]);

                $nowMinMonth = returnMonthNumberByBudget(date('n'));

                if($arDataString[2] > 12 || $arDataString[2] < 1){
                    $this->pushInErrors('additionalError', [
                        'message' => 'Месяца '.$arDataString[2].' не существует, бюджет не был загружен'
                    ]);
                }



                $arMatches = [];

                if (preg_match('/[^0-9.]/', $arDataString[4])) {
                    $this->pushInErrors('additionalError', [
                        'message' => 'Значение может быть только с цифрами и точкой . Файл : ' . $fileKey . ' , строка : ' . $key
                    ]);
                } elseif (preg_match('/\./', $arDataString[4],)) {
                    $newStr = preg_replace('/[^.]/', '', $arDataString[4]);
                    if (iconv_strlen($newStr) > 1) {
                        $this->pushInErrors('additionalError', [
                            'message' => 'Значение может быть только с цифрами и точкой . Файл : ' . $fileKey . ' , строка : ' . $key
                        ]);
                    }
                }

                if ($arHashes[$hash]) {
                    $this->pushInErrors('stringInFile', [
                        'fileName1' => $fileKey,
                        'string1' => $key,
                        'fileName2' => $arHashes[$hash]['name'],
                        'string2' => $arHashes[$hash]['string']
                    ]);
                } else {
                    $arHashes[$hash] = [
                        'name' => $fileKey,
                        'string' => $key
                    ];
                }
            }
        }

        $arIdsBes = array_keys($arIdsBes);
        $arIdsArticles = array_keys($arIdsArticles);

        $dbBes = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('be'),
                'ID' => $arIdsBes
            ]
        );

        $arBes = [];

        while ($arBe = $dbBes->fetch()) {
            $arBes[] = $arBe['ID'];
        }

        foreach (array_diff($arIdsBes, $arBes) as $beId) {
            $this->pushInErrors('additionalError', [
                'message' => 'БЕ c ИД '.$beId.' не существует, бюджет не был загружен'
            ]);
        }

        $dbArticles = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('articles'),
                'ID' => $arIdsArticles
            ]
        );

        $arArticles = [];

        while ($arArticle = $dbArticles->fetch()) {
            $arArticles[] = $arArticle['ID'];
        }

        foreach (array_diff($arIdsArticles, $arArticles) as $articleId) {
            $this->pushInErrors('additionalError', [
                'message' => 'Статьи '.$articleId.' не существует, бюджет не был загружен'
            ]);
        }

        $this->dataFilesHashes = $arHashes;

        $this->dataFiles = $arData;
        if ($this->checkErrors()) {
            throw new \Exception();
        }
    }

    /**
     * @throws \Exception
     * добавление / обновление данных в таблице
     */
    public function loadFilesInTable()
    {
        $nowMonth = date('n');
        $nowYear = date('Y');
        $nowDay = date('d');

        $nowFinYear = $_POST['year'];
        foreach ($this->dataFiles as $dataFile) {
            foreach ($dataFile as $arString) {
                $arBeAndArticles[$arString[0] . $arString[1]] = [
                    'biznesUnit' => $arString[0],
                    'article' => $arString[1],
                    'year' => $arString[3]
                ];
                if (!array_key_exists($arString['hash'], $this->arBudgetElements)) {
                    if ($arString[0] && $arString[1]) {
                        $arLoadArray = [
                            'biznesUnit' => $arString[0],
                            'article' => $arString[1],
                            'year' => $arString[3],
                            'month' => $arString[2],
                            'plan' => $arString[4],
                            'fact' => 0,
                            'inReserve' => 0,
                            'saldo' => 0,
                            'cumulativeTotal' => 0,
                            'total' => 0,
                            'unicHash' => $arString['hash'],
                        ];
                        $id = BudgetTable::add($arLoadArray);

                        $this->history->addMessage(
                            $arString['hash'],
                            'Добавление плана бюджета' .
                            ' БЕ - ' . $arLoadArray['biznesUnit'] .
                            ' Статья - ' . $arLoadArray['article'] .
                            ' Месяц - ' . $arLoadArray['month'] .
                            ' Год - ' . $nowFinYear .
                            ' Значение плана - ' . $arLoadArray['plan'],
                            History::START_BUDGET,
                            '',
                            $arString[0],
                            $arString[1]
                        );

                    }
                } else {
                    if ($this->arBudgetElements[$arString['hash']]['plan'] != $arString[4]) {
                        $elem = $this->arBudgetElements[$arString['hash']];
                        $this->history->addMessage(
                            $arString['hash'],
                            'Обновление плана бюджета' .
                            ' БЕ - ' . $elem['biznesUnit'] .
                            ' Статья - ' . $elem['article'] .
                            ' Месяц - ' . $elem['month'] .
                            ' Год - ' . $nowFinYear .
                            ' Старый план - ' . $elem['plan'] .
                            ' Новый план - ' . $arString[4],
                            History::UPDATE_BUDGET,
                            '',
                            $elem['biznesUnit'],
                            $elem['article']
                        );
                    }
                    BudgetTable::update($this->arBudgetElements[$arString['hash']]['id'], ['plan' => $arString[4]]);
                }
            }
        }

        if (!empty($arBeAndArticles)) {
            $arBeAndArticles['LOGIC'] = 'OR';

            $budget = new Budget();

            $budget->recalculate($arBeAndArticles);
        }
    }
}



























