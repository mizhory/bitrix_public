<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Immo\Iblock\IblockDocument;
use Immo\Iblock\Manager;
use Immo\Iblock\Property\BiznesUnitsIblockField;
use Immo\Iblock\Property\PrintValues;
use Immo\Integration\Budget\BudgetHelper;
use Immo\Integration\Budget\CurrencyManager;
use Immo\Tools\File\FileDownload;
use Immo\Tools\User;

/**
 * Class IblockPrintValues
 * @package Immo\Components
 * @description класс компонента вывода кнопки "Распечатать"
 */
class IblockPrintValues extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    /**
     * @description описание префильтров/фильтров действий
     * @return \array[][]
     */
    public function configureActions()
    {
        return [
            'printTemplate' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
            'printList' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description метод генерирует файл из реквеста и возвращает ссылку на этот файл
     * @return array
     */
    public function printTemplateAction(): array
    {
        $link = FileDownload::generateLink(
            (int)$this->arParams['PROPERTY']['ELEMENT_ID'],
            (int)$this->arParams['PROPERTY']['IBLOCK_ID'],
        );

        return [
            'SRC' => $link ?? ''
        ];
    }

    /**
     * @description Возвращает ссылки для генерации заявок
     * @param array $data
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function printListAction(array $data): array
    {
        $arPrintProperties = \Immo\Iblock\Manager::loadProperties(
            \Bitrix\Main\ORM\Query\Query::filter()
                ->whereIn('IBLOCK_ID', \Immo\Financial\App\FinancialApp::getFinancialIblocks())
                ->where('CODE', 'PRINT'),
            ['ID', 'IBLOCK_ID']
        );
        $arIblockPrintProps = array_column($arPrintProperties, 'IBLOCK_ID');
        if (empty($arIblockPrintProps)) {
            return [];
        }

        foreach ($data as $index => ['iblockId' => $iblockId]) {
            if (in_array($iblockId, $arIblockPrintProps)) {
                continue;
            }

            unset($data[$index]);
        }

        if (empty($data)) {
            return [];
        }

        foreach ($data as ['id' => $id, 'iblockId' => $iblockId]) {
            $this->arParams['PROPERTY']['ELEMENT_ID'] = $id;
            $this->arParams['PROPERTY']['IBLOCK_ID'] = $iblockId;
            $arLinks[$id] = $this->printTemplateAction()['SRC'];
        }

        return $arLinks ?? [];
    }

    /**
     * @description Генерирует файл печати и возвращает массив этого файла
     * @param bool $forceGenerate
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getPrint(bool $forceGenerate = false): array
    {
        $elementId = (int)$this->arParams['PROPERTY']['ELEMENT_ID'];
        $iblockId = (int)$this->arParams['PROPERTY']['IBLOCK_ID'];
        if ($elementId <= 0 or $iblockId <= 0) {
            throw new SystemException('Ошибка элемента');
        }

        $userId = User::getCurrent()->getId();
        if ($userId <= 0) {
            throw new SystemException('Ошибка авторизации');
        }

        $document = new IblockDocument();
        if ($forceGenerate) {
            $document->setForceGenerate();
        }
        $document->setParams($this->arParams['PROPERTY']);
        $document->setExtraFields(array_merge(
            [],
            $this->addBeFields($elementId, $iblockId),
            [
                'DATE_CREATE_STRICT' => $this->getStrictDateCreate($elementId, $iblockId),
                'RETURN_SUMM' => $this->getSumValues($elementId, $iblockId, 'RETURN_SUMM'),
                'APP_SUM' => $this->getSumValues($elementId, $iblockId, 'APP_SUM'),
                'TOTAL_SUM' => $this->getSumValues($elementId, $iblockId, 'TOTAL_SUM'),
                'INCOMING_BALANCE' => $this->getSumValues($elementId, $iblockId, 'INCOMING_BALANCE'),
                'OVERSPENDING' => $this->getSumValues($elementId, $iblockId, 'OVERSPENDING'),
            ]
        ));
        $arFile = $document->getDocumentFile();

        if (empty($arFile)) {
            throw new SystemException('Ошибка генерации файла');
        }

        $this->updatePrintInfo($userId, $arFile, $iblockId, $elementId);

        return $arFile;
    }

    /**
     * @description Возвращает денежные значения свойств
     * @param int $elementId
     * @param int $iblockId
     * @param string $code
     * @return string
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getSumValues(int $elementId, int $iblockId, string $code): string
    {
        static $arSum = [];
        if (!empty($arSum[$iblockId][$elementId]["{$code}_VALUE"])) {
            return $arSum[$iblockId][$elementId]["{$code}_VALUE"];
        }

        $query = Manager::getQueryElements($iblockId);
        if (empty($query)) {
            return '';
        }

        $properties = Manager::loadProperties(
            Query::filter()
                ->whereIn('CODE', Manager::SUM_PROPERTIES)
                ->where('IBLOCK_ID', $iblockId),
            ['ID', 'CODE', 'USER_TYPE']
        );

        foreach (Manager::SUM_PROPERTIES as $property) {
            if (!in_array($property, array_column($properties, 'CODE'))) {
                continue;
            }

            $select["{$property}_VALUE"] = "{$property}.VALUE";
        }
        $select['BE_VALUE'] = \Immo\Iblock\Manager::PROPERTY_CODE_BE . '.VALUE';

        $arElement = $query
            ->where('ID', $elementId)
            ->setSelect($select)
            ->exec()
            ->fetch();
        if (empty($arElement) or $arElement === false) {
            return '';
        }

        $arBe = !empty($arElement['BE_VALUE']) ? current(BudgetHelper::convertFrom($arElement['BE_VALUE'])) : [];
        unset($arSum[$iblockId][$elementId]['BE_VALUE']);

        foreach ($properties as $property) {
            $codeProp = "{$property['CODE']}_VALUE";
            if (!array_key_exists($codeProp, $arElement)) {
                continue;
            }

            $arElement[$codeProp] = CurrencyManager::convertSumValue($arElement[$codeProp], [
                'USER_TYPE' => \Bitrix\Currency\Integration\IblockMoneyProperty::USER_TYPE,
                'CURRENCY' => $arBe['rate']
            ]);
        }

        $arSum[$iblockId][$elementId] = $arElement;

        return $arSum[$iblockId][$elementId]["{$code}_VALUE"] ?? "0 {$arBe['rate']}";
    }

    /**
     * @description Возвращает дату создания заявки (DATE_CREATE - хранит в себе и дату и время. Нужна только дата)
     * @param int $elementId
     * @param int $iblockId
     * @return string
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getStrictDateCreate(int $elementId, int $iblockId): string
    {
        $query = Manager::getQueryElements($iblockId);
        if (empty($query)) {
            return '';
        }

        $arElement = $query->where('ID', $elementId)->addSelect('DATE_CREATE')->exec()->fetch();
        if (empty($arElement['DATE_CREATE'])) {
            return '';
        }

        return ($arElement['DATE_CREATE'] instanceof Date) ? $arElement['DATE_CREATE']->format('d.m.Y') : '';
    }

    /**
     * @description Возвращает поля из формы БЕ для печати
     * @param int $elementId
     * @param int $iblockId
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function addBeFields(int $elementId, int $iblockId): array
    {
        if (!class_exists(BiznesUnitsIblockField::class)) {
            return [];
        }

        return BiznesUnitsIblockField::getPrintableValues($elementId, $iblockId);
    }

    /**
     * @description Собирает новые значения полей из реквеста
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function collectValuesFromRequest(): array
    {
        $arSystemFields = array_keys(IblockDocument::getIblockElementSystemFields());

        $arValues = [];
        $arRequest = $this->request->toArray();
        foreach ($arRequest as $fieldName => $value) {
            if (!in_array($fieldName, $arSystemFields)) {
                continue;
            }

            $arValues[$fieldName] = $value;
        }

        $arProperties = IblockDocument::loadProperties($this->arParams['PROPERTY']);
        if (!empty($arProperties)) {
            foreach ($arProperties as $id => $property) {
                if (!array_key_exists("PROPERTY_{$id}", $arRequest)) {
                    continue;
                }

                if ($property['MULTIPLE'] == 'Y') {
                    $arValues[$property['CODE']] = (array)$arRequest["PROPERTY_{$id}"];
                } else {
                    if (is_array($arRequest["PROPERTY_{$id}"])) {
                        $arValues[$property['CODE']] = current($arRequest["PROPERTY_{$id}"])['VALUE'];
                    } else {
                        $arValues[$property['CODE']] = $arRequest["PROPERTY_{$id}"];
                    }
                }
            }
        }

        return $arValues;
    }

    /**
     * @description метод обновляет значение свойства печати, записывает в значение информацию о последней печати
     * @param int $userId
     * @param array $arFile
     * @param int $iblockId
     * @param $elementId
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function updatePrintInfo(int $userId, array $arFile, int $iblockId, $elementId): void
    {
        $printInfo = $this->getPrintInfo($iblockId, $elementId) ?? [];
        $printInfo[] = [
            'USER_ID' => $userId,
            'DATETIME' => (!empty($arFile['TIMESTAMP_X']) and $arFile['TIMESTAMP_X'] instanceof DateTime)
                ? (string)$arFile['TIMESTAMP_X']
                : (string)(new DateTime()),
            'FILE_ID' => $arFile['ID'] ?? 0
        ];

        \CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [
            $this->arParams['PROPERTY']['CODE'] => ['VALUE' => $printInfo]
        ]);
    }

    /**
     * @description возвращает информацию о печати
     * @param int $iblockId
     * @param int $elementId
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getPrintInfo(int $iblockId, int $elementId): array
    {
        $propField = "{$this->arParams['PROPERTY']['CODE']}.VALUE";
        $arElement = Manager::loadIblockElement($iblockId, $elementId, ['ID', 'PRINT_INFO' => $propField]);
        $printInfo = (empty($arElement['PRINT_INFO']))
            ? []
            : PrintValues::ConvertFromDB([], ['VALUE' =>$arElement['PRINT_INFO']]);

        return (empty($printInfo['VALUE']) or $printInfo['VALUE'] === false) ? [] : $printInfo['VALUE'];
    }

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters()
    {
        return ['PROPERTY', 'VALUE'];
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult['PROPERTY'] = $this->arParams['PROPERTY'];
        $this->arResult['VALUE'] = $this->arParams['VALUE'];
        $this->arResult['HTML_CONTROL'] = $this->arParams['HTML_CONTROL'];
        $this->arResult['SIGNED_PARAMS'] = $this->getSignedParameters();
        $this->arResult['ID'] = implode('_', [
            $this->arResult['PROPERTY']['FIELD_ID'],
            $this->arResult['PROPERTY']['IBLOCK_ID'],
            $this->arResult['PROPERTY']['ELEMENT_ID'] ?? '',
        ]);
        $this->includeComponentTemplate();
    }
}