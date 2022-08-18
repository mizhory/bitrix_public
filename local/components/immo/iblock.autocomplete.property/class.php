<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\ORM\Query\Query;
use Immo\Iblock\Manager;
use Immo\Iblock\Property\AutoComplete;

/**
 * Class IblockAutocompleteProperty
 * @package Immo\Components
 * @description Класс компонента отвечающий за вывод свойства "строка с автоподстановкой"
 */
class IblockAutocompleteProperty extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{

    /**
     * @inheritDoc
     */
    public function configureActions()
    {
        return [
            'loadValues' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description Отвечает за загрузку значений из свойства. Отдает результат на фронтенд
     * Работает в двух режимах
     * 'property' - загрузка значений свойства по другому значению
     * 'items' - загружает все значения этого свойства
     *
     * @param string $value
     * @param string $mode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function loadValuesAction(string $value, string $mode = 'property'): array
    {
        switch ($mode) {
            case ('property'):
                $result = $this->getItemsProperty($value);
                break;

            case ('items'):
                $result = $this->getItems();
                break;
        }

        return $result ?? [];
    }

    /**
     * @description Возвращает массив значений по свойству
     * @param string $value
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getItemsProperty(string $value): array
    {
        $value = trim($value);
        $result = [
            'items' => [],
            'value' => [
                'id' => $value,
                'text' => "{$value}"
            ]
        ];

        $minLength = (int)$this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['MIN_LENGTH'];
        $minLength = ($minLength <= 0) ? 1 : $minLength;

        if (empty($value) or strlen($value) < $minLength) {
            return $result;
        }

        if ($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['USE_USER_SEARCH'] == 'Y') {
            $users = AutoComplete::loadUsers($value);
            if (!empty($users)) {
                foreach ($users as $userName) {
                    $result['items'][] = [
                        'id' => $userName,
                        'text' => $userName,
                    ];
                }
            }
        }

        $arValues = AutoComplete::loadValues($this->arParams['PROPERTY'], $value, $users);
        if (empty($arValues)) {
            return $result;
        }

        foreach ($arValues as $index => $arValue) {
            if (trim($arValue['PROP_VALUE']) == $value) {
                unset($result['value']);
            }

            $result['items'][] = [
                'id' => $arValue['PROP_VALUE'],
                'text' => $arValue['PROP_VALUE'],
            ];
        }

        return $result;
    }

    /**
     * @description Возвращает все значения свойства. Свойство задается в параметрах
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getItems(): array
    {
        $arItems = AutoComplete::loadItems($this->arParams['PROPERTY']);
        if (empty($arItems)) {
            return [];
        }

        foreach ($arItems as $id => $name) {
            $result['items'][] = [
                'id' => $name,
                'text' => $name,
                'bitrixId' => $id
            ];
        }

        return $result ?? [];
    }

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return ['PROPERTY'];
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult['PROPERTY'] = $this->arParams['PROPERTY'];
        $this->arResult['ID'] = implode('_', [
            $this->arResult['PROPERTY']['FIELD_ID'],
            $this->arResult['PROPERTY']['IBLOCK_ID'],
            $this->arResult['PROPERTY']['ELEMENT_ID'] ?? '',
        ]);
        $this->arResult['VALUE'] = $this->arParams['VALUE'];
        $this->arResult['HTML_CONTROL'] = $this->arParams['HTML_CONTROL'];
        $this->arResult['SIGNED_PARAMS'] = $this->getSignedParameters();
        if (AutoComplete::isCheckboxSelected($this->arResult['PROPERTY'])) {
            $this->arResult['CHECKBOX_PROPERTY'] = AutoComplete::collectCheckboxProperty(
                (int)$this->arResult['PROPERTY']['ELEMENT_ID'],
                $this->arResult['PROPERTY']
            );
        }

        $this->arResult['ID_ENTITY_PROPERTY'] = AutoComplete::getEntityProperty($this->arResult['PROPERTY']);

        $this->arResult['ITEMS_PROPERTY_LABEL']
            = (empty(trim($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['ITEMS_PROPERTY_LABEL'])))
                ? 'Выбрать из значений'
                : trim($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['ITEMS_PROPERTY_LABEL']);

        $this->includeComponentTemplate();
    }
}