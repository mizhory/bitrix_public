<?php

namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Class IblockStatusCardEdit
 * @package MediaCom\Components
 */
class IblockStatusCardEdit extends \CBitrixComponent
{
    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['VALUE'] = intval($arParams['VALUE']);
        $arParams['ID'] = strval($arParams['ID']);
        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * Подготовка статусов, задает всем статусам до текущего активный статус
     */
    protected function prepareActiveItems(): void
    {
        if (!in_array($this->arResult['VALUE'], array_keys($this->arResult['ITEMS']))) {
            return;
        }

        foreach ($this->arResult['ITEMS'] as $id => $arItem) {
            $this->arResult['ITEMS'][$id]['ACTIVE'] = true;

            if ($this->arResult['VALUE'] == $id) {
                break;
            }
        }
    }

    /**
     * @description Устанавливает значение по умолчанию, в случае, если значение не задано
     * @param array $arItems
     */
    protected function setDefaultValue(array $arItems): void
    {
        foreach ($arItems as $id => $arItem) {
            if ($arItem['DEF'] != 'Y') {
                continue;
            }

            $this->arResult['VALUE'] = $id;
            break;
        }
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult['ITEMS'] = $this->arParams['ITEMS'];
        $this->arResult['VALUE'] = $this->arParams['VALUE'];
        if (
            (empty($this->arResult['VALUE']) or $this->arResult['VALUE'] <= 0)
            and !empty($this->arResult['ITEMS'])
        ) {
            $this->setDefaultValue($this->arResult['ITEMS']);
        }
        $this->arResult['ID'] = $this->arParams['ID'];
        $this->arResult['HTML_CONTROL'] = $this->arParams['HTML_CONTROL'];

        $settings = $this->arParams['PROPERTY']['USER_TYPE_SETTINGS'];
        if (
            !empty($this->arParams['NAME_FAIL_STATUS'])
            and (
                !empty($settings['IS_FINAL_FAIL'])
                and !empty($this->arResult['ITEMS'][$this->arResult['VALUE']])
                and $settings['IS_FINAL_FAIL'] == $this->arResult['ITEMS'][$this->arResult['VALUE']]['XML_ID']
            )
        ) {
            $this->arResult['NAME_FAIL_STATUS'] = $this->arParams['NAME_FAIL_STATUS'];
        }

        $this->prepareActiveItems();

        $this->includeComponentTemplate();
    }
}