<?php
namespace Vigr\Budget;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use MongoDB\Driver\Exception\ExecutionTimeoutException;
use Vigr\Budget\Internals\BudgetTable;
use Vigr\Budget\Traits\AjaxTrait;
use Vigr\Budget\Traits\ErrorTrait;
use Vigr\Helpers\Helper;

class FileDownloaderExcel
{
    use ErrorTrait;
    use AjaxTrait;


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
    protected $dataFiles = [];

    /**
     * @var array
     */
    protected $arBudgetElements = [];

    /**
     *Основная рабочая функция
     */
    public function checkFiles()
    {
        $history = new History();
        $this->history = $history;
        global $DB;

        try {
            $DB->StartTransaction();

            foreach ($_FILES as $arFile) {
                $this->checkFileStepOne($arFile['tmp_name'], $arFile['name']);
            }

            if (!empty($this->arErrors)) {
                throw new \Exception();
            }

            $this->checkFilesStepTwo();

            $this->status = 'ok';
            $DB->commit();
        } catch (\Exception $exception) {
            if ($exception->getCode() === 300) {
                $this->pushInErrors('recalculateInFile', $exception->getMessage());
            }
            $DB->Rollback();
            $history->addMessage(0, 'Ошибка при загрузке файлов', 'downloadFile',History::TYPES['BUDGET_ERROR']);
            $this->status = 'error';
            $this->response = $this->arErrors;

        } finally {
            $this->sendRequest();
        }
    }

    /**
     * @throws \Exception
     * Обновление или добавление записи
     *
     */
    public function checkFilesStepTwo()
    {
        $arBes = [];
        $arAs = [];

        $dbAs = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('articles')
            ]
        );

        $arANames = [];

        while ($arAs1 = $dbAs->fetch()) {
            $arANames[$arAs1['ID']] = $arAs1['NAME'];
        }

        $dbBs = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('be')
            ]
        );

        $arBNames = [];

        while ($arBes1 = $dbBs->fetch()) {
            $arBNames[$arBes1['ID']] = $arBes1['NAME'];

        }

        $dbBudget = BudgetTable::getList(
            [
                'filter' => [
                    'unicHash' => array_keys($this->dataFilesHashes)
                ]
            ]
        );


        while ($arBudget = $dbBudget->fetch()) {
            if($arBudget['month'] > 4){
                $arBudget['year']++;
            }

            $month = date('n') - 1;

            $year = date('Y');


            if($month <= 4){
                $year--;
            }

            $this->history->addMessage(
                $arBudget['unicHash'],
                'Обновление плана бюджета' .
                ' БЕ - ' . $arBNames[$arBudget['biznesUnit']] .
                ' Статья - ' . $arANames[$arBudget['article']] .
                ' Месяц - ' . $arBudget['month'] .
                ' Финансовый год - ' . $_POST['year'] .
                ' Старый план - ' . number_format($arBudget['plan'],2,',', ' ') .
                ' Новый план - ' . number_format($this->dataFilesHashes[$arBudget['unicHash']],2,',', ' '),
                History::UPDATE_BUDGET,
                History::TYPES['BUDGET'],
                $arBudget['biznesUnit'],
                $arBudget['article']
            );

            $arBeAndArticles[$arBudget['biznesUnit'] . $arBudget['article']] = [
                'biznesUnit' => $arBudget['biznesUnit'],
                'article' => $arBudget['article'],
                'year' => $_POST['year']
            ];

            BudgetTable::update($arBudget['id'], ['plan' => $this->dataFilesHashes[$arBudget['unicHash']]]);

            unset($this->dataFilesHashes[$arBudget['unicHash']]);
        }

        foreach ($this->dataFilesHashes as $key => $hash) {
            $arLoadArray = [
                'biznesUnit' => $this->dataFiles[$key]['be'],
                'article' => $this->dataFiles[$key]['articleId'],
                'year' => $_POST['year'],
                'month' => $this->dataFiles[$key]['month'],
                'plan' => $this->dataFiles[$key]['value'],
                'fact' => 0,
                'inReserve' => 0,
                'saldo' => 0,
                'cumulativeTotal' => 0,
                'total' => 0,
                'unicHash' => $key,
            ];

            $arBeAndArticles[$this->dataFiles[$key]['be'] . $this->dataFiles[$key]['articleId']] = [
                'biznesUnit' => $this->dataFiles[$key]['be'],
                'article' => $this->dataFiles[$key]['articleId'],
                'year' => $_POST['year']
            ];

            if($this->dataFiles[$key]['month'] > 4){
                $_POST['year']++;
            }

            $month = date('n') - 1;

            $year = date('Y');


            if($month <= 4){
                $year--;
            }

            $this->history->addMessage(
                $key,
                'Загрузка плана бюджета' .
                ' БЕ - ' . $arBNames[$this->dataFiles[$key]['be']] .
                ' Статья - ' . $arANames[$this->dataFiles[$key]['articleId']] .
                ' Месяц - ' . $this->dataFiles[$key]['month'] .
                ' Финансовый год - ' . $_POST['year'] .
                ' Новый план - ' . number_format($this->dataFiles[$key]['value'],2,',',' '),
                History::START_BUDGET,
                History::TYPES['BUDGET'],
                $this->dataFiles[$key]['be'],
                $this->dataFiles[$key]['articleId']
            );

            $id = BudgetTable::add($arLoadArray);
        }

        if (!empty($arBeAndArticles)) {
            $budget = new Budget();

            if (count($arBeAndArticles) > 1) {
                $arBeAndArticles['LOGIC'] = 'OR';
            } else {
                $arBeAndArticles[] = array_shift($arBeAndArticles);
            }

            $budget->recalculate($arBeAndArticles, [$_POST['year']]);
        }
    }

    /**
     * @param $file
     * @param $name
     * @throws \Exception
     * Считывание и проверка в файле что все значения корректны
     */
    public function checkFileStepOne($file, $name)
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file);

        $columns = [
            'C' => 'Май',
            'D' => 'Июнь',
            'E' => 'Июль',
            'F' => 'Август',
            'G' => 'Сентябрь',
            'H' => 'Октябрь',
            'I' => 'Ноябрь',
            'J' => 'Декабрь',
            'K' => 'Январь',
            'L' => 'Февраль',
            'M' => 'Март',
            'N' => 'Апрель'
        ];

        $arForHashes = [];
        $arIdsBes = [];
        $arIdsArticles = [];
        $arColumnsVsMonths = [];
        foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
            $index = $row->getRowIndex();

            $cellsIterator = $row->getCellIterator();
            $startHash = '';
            $beId = 0;
            $articleId = 0;
            if ($index == 1) {

                foreach ($row->getCellIterator() as $cell) {
                    $column = $cell->getColumn();
                    $hash = '';
                    switch ($column) {
                        case 'A':
                            break;
                        case 'B':
                            break;
                        default:
                            $month = returnNameMonth($cell->getValue());

                            if (!$month) {
                                $this->pushInErrors('additionalError', [
                                    'message' => 'Месяца ' . $cell->getValue() . ' не существует! . Файл - ' . $name
                                ]);
                            }

                            $finMonth = returnMonthInfo('fin',$month);

                            $nowMinMonth = returnMonthInfo('fin',date('n'));

                            if ($finMonth < $nowMinMonth && $_POST['year'] == 2021) {
                                $this->pushInErrors('additionalError', [
                                    'message' => 'Реальный месяц - ' .
                                        $month . '(' . $cell->getValue() . ')' . ' Фин.месяц - ' .
                                        $finMonth . ' меньше чем текущий финансовый месяц - ' . $nowMinMonth . '. Файл - ' . $name
                                ]);
                            }

                            $arColumnsVsMonths[$column] = $cell->getValue();
                    }
                }
            } else {
                foreach ($row->getCellIterator() as $cell) {
                    $column = $cell->getColumn();
                    $hash = '';
                    switch ($column) {
                        case 'A':
                            $startHash .= $cell->getValue();
                            $arIdsBes[$cell->getValue()] = true;
                            $beId = $cell->getValue();
                            break;
                        case 'B':
                            $startHash .= $cell->getValue();
                            $arIdsArticles[$cell->getValue()] = true;
                            $articleId = $cell->getValue();
                            break;
                        default:
                            if (empty($beId)) {
                                break;
                            }

                            $hash = $startHash . returnNameMonth($arColumnsVsMonths[$column]);
                            $hash .= $_POST['year'];
                            $arForHashes[$hash] = $cell->getValue();
                            if (preg_match('/[^0-9.]/', $cell->getValue())) {
                                $this->pushInErrors('additionalError', [
                                    'message' => 'Значение может быть только с цифрами и точкой . Файл - ' .
                                        $name . ' Координаты ' . $cell->getCoordinate()
                                ]);
                            } elseif (preg_match('/\./', $cell->getValue())) {
                                $newStr = preg_replace('/[^.]/', '', $cell->getValue());
                                if (iconv_strlen($newStr) > 1) {
                                    $this->pushInErrors('additionalError', [
                                        'message' => 'Значение может быть только с цифрами , запятой и точкой . Файл - ' .
                                            $name . ' Координаты ' . $cell->getCoordinate()
                                    ]);
                                }
                            }


                            $arStr = explode('.',$cell->getValue());

                            if(iconv_strlen($arStr[1]) > 2){
                                $this->pushInErrors('additionalError', [
                                    'message' => 'Значение может принимать только 2 цифры после точки / запятой . Файл - ' .
                                        $name . ' Координаты ' . $cell->getCoordinate()
                                ]);
                            }

                            $arStr = explode(',',$cell->getValue());
                            if(iconv_strlen($arStr[1]) > 2){
                                $this->pushInErrors('additionalError', [
                                    'message' => 'Значение может принимать только 2 цифры после точки / запятой . Файл - ' .
                                        $name . ' Координаты ' . $cell->getCoordinate()
                                ]);
                            }

                            $this->dataFiles[$hash] = [
                                'be' => $beId,
                                'month' => returnNameMonth($arColumnsVsMonths[$column]),
                                'value' => $cell->getValue(),
                                'articleId' => $articleId
                            ];
                    }
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
            ], false, false, ['ID']
        );

        $arBes = [];

        while ($arBe = $dbBes->fetch()) {
            $arBes[] = $arBe['ID'];
        }

        $arIdsBes = array_filter($arIdsBes);

        foreach (array_diff($arIdsBes, $arBes) as $beId) {
            $this->pushInErrors('additionalError', [
                'message' => 'БЕ с ИД ' . $beId . ' не существует, бюджет не был загружен . Файл - ' . $name
            ]);
        }

        $dbArticles = \CIBlockElement::getList(
            [],
            [
                'IBLOCK_ID' => getIblockIdByCode('articles'),
                'ID' => $arIdsArticles
            ], false, false, ['ID']
        );

        $arArticles = [];

        while ($arArticle = $dbArticles->fetch()) {
            $arArticles[] = $arArticle['ID'];
        }

        $arIdsArticles = array_filter($arIdsArticles);

        foreach (array_diff($arIdsArticles, $arArticles) as $articleId) {
            $this->pushInErrors('additionalError', [
                'message' => 'Статьи с ИД ' . $articleId . ' не существует, бюджет не был загружен . Файл - ' . $name
            ]);
        }


        foreach ($arForHashes as $key => $hash) {
            $this->dataFilesHashes[md5($key)] = $hash;
            $this->dataFiles[md5($key)] = $this->dataFiles[$key];
            unset($this->dataFiles[$key]);
        }


    }

}