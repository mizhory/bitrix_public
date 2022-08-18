<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Immo\Tools;
use Immo\Financial;

/**
 * @description Компонент для подключения и инициализации ассетов финансовых заявок
 * Class IblockFinancialAssets
 * @package Immo\Components
 */
class IblockFinancialAssets extends \CBitrixComponent implements Main\Engine\Contract\Controllerable
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult = $this->arParams;
        $this->includeComponentTemplate();
    }

    /**
     * @description Метод для обновления полей аяксом
     * @param int $id
     * @param int $iblockId
     * @param array $props
     * @return array
     * @throws Main\SystemException
     */
    public function updateFieldsAction(int $id, int $iblockId, array $props = []): array
    {
        if ($id <= 0 or $iblockId <= 0 or empty($props)) {
            throw new Main\SystemException('Ошибка обновления полей!', 100);
        }

        $this->tryCollectFilesRequest($props, $this->request);
        $user = Tools\User::getCurrent();
        $financialApp = Financial\App\FinancialApp::init([
            'ID' => $id,
            'IBLOCK_ID' => $iblockId,
            'MODIFIED_BY' => $user->getId(),
            'PROPERTY_VALUES' => $props
        ], $user);

        if (array_key_exists('FILES', $props) and count($props) == 1) {
            $resultCheck = new Main\Result();
            $financialApp->checkUserEdit($resultCheck);
            if (!$resultCheck->isSuccess()) {
                throw new Main\SystemException('Недостаточно прав для редактирования заявки.', 100);
            }
        }

        $result = $financialApp->save();
        if (!$result->isSuccess()) {
            $updateError = $result->getErrorCollection()->getErrorByCode(100);
            $message = (empty($updateError)) ? 'Ошибка обновления полей!' : $updateError->getMessage();
            throw new Main\SystemException($message, (empty($updateError)) ? 0 : 100);
        }

        return ['message' => $result->getData()['MESS']];
    }

    /**
     * @return \array[][]
     */
    public function configureActions()
    {
        return [
            'updateFields' => [
                'prefilters' => [
                    new Main\Engine\ActionFilter\Authentication(),
                    new Main\Engine\ActionFilter\HttpMethod([Main\Engine\ActionFilter\HttpMethod::METHOD_POST]),
                    new Main\Engine\ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description Собирает новые файлы из реквеста для каждого свойства
     * @param array $arProps Массив значений свойств [PROP_CODE => VALUE];
     * @param Main\HttpRequest $request Объект реквеста
     */
    protected function tryCollectFilesRequest(array &$arProps, Main\HttpRequest $request)
    {
        /**
         * Подготавливаем список файлов, которые есть запросе
         */
        $filesUpload = $this->prepareFileList((array)$request->getFile('FILES_UPLOAD'));
        if (empty($filesUpload)) {
            foreach ($arProps as $keyProp => $propValues) {
                if (is_array($propValues)) {
                    foreach ($propValues as $index => $value) {
                        $arProps[$keyProp][$index] = ((int)$value <= 0) ? null : $value;
                    }
                    $arProps[$keyProp] = array_filter($arProps[$keyProp]);
                } else {
                    $arProps[$keyProp] = ((int)$propValues <= 0) ? null : $propValues;
                }
            }
            return;
        }

        $tmpProps = $arProps;
        $arFileProps = [];
        /**
         * Пробегаемся по массиву значений свойств
         */
        foreach ($tmpProps as $propName => $arIndexes) {
            /**
             * Пробегаемся по индексам
             */
            foreach ($arIndexes as $index) {
                /**
                 * Если это новый файл, то индекс будет n0, n1 и тд
                 * Если привести его к целому числу, и он будет больше 0, то это ID значения. Нужно его так же забрать
                 */
                if ((int)$index > 0 and !array_key_exists("{$propName}_{$index}", $filesUpload)) {
                    $arFileProps[$propName][] = $index;
                    continue;
                } elseif (!array_key_exists("{$propName}_{$index}", $filesUpload)) {
                    continue;
                }

                $arFileProps[$propName][] = $filesUpload["{$propName}_{$index}"];
            }
        }

        if (empty($arFileProps)) {
            return;
        }

        /**
         * Видоизменяем массив значений
         */
        foreach ($arFileProps as $propCode => $files) {
            unset($arProps[$propCode]);
            $arProps[$propCode] = $files;
        }
    }

    /**
     * @description Подготавливает список файлов из реквеста
     * @param array $fileList Массив, который содержит файлы из запроса:
     * [
     *      name => [
     *          PROP_CODE1 => value1,
     *          PROP_CODE2 => value2,
     *          ...
     *      ],
     *      type => [
     *          PROP_CODE1 => value1,
     *          PROP_CODE2 => value2,
     *          ...
     *      ],
     *      ...
     * ]
     * @return array Подготовленный массив файлов:
     * [
     *      PROP_CODE1 => [
     *          name => value1,
     *          type => value1,
     *          ...
     *      ],
     *      ...
     * ]
     */
    protected function prepareFileList(array $fileList = []): array
    {
        if (empty($fileList)) {
            return [];
        }
        
        foreach ($fileList as $key => $fileInput) {
            foreach ($fileInput as $propKeyIndex => $value) {
                $arFiles[$propKeyIndex][$key] = $value;
            }
        }

        return $arFiles ?? [];
    }
}