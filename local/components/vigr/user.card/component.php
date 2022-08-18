<?php

use \Bitrix\Main\UI\Extension;

Extension::load('vigr.usercard');

use Bitrix\Main\Loader;

Loader::includeModule('highloadblock');
CJSCore::Init(['vigr.usercard']);
if ($arParams['userField']['USER_TYPE_ID'] === 'vigrusercard') {
    if (CModule::IncludeModule('iblock')) {
        $iblockElementProvider = new \CIBlockElement(false);
        $beListDB = $iblockElementProvider->GetList([], [
            'IBLOCK_ID' => getIblockIdByCode('be')
        ], false, false, ['ID', 'NAME']);
        while ($be = $beListDB->Fetch()) {
            $arResult['SELECTOR_VALUES'][$be['ID']] = $be['NAME'];
        }
    }
    $arResult['FIELD_VALUE'] = json_decode(html_entity_decode(stripslashes(stripslashes($arParams['userField']['VALUE']))),
        1);
    foreach ($arResult['FIELD_VALUE'] as $index => $field) {
        if ($index === 'beSelect') {
            $arResult['FIELD_VALUE'][$index] = explode(',', $field);
            foreach ($arResult['FIELD_VALUE'][$index] as $be) {
                $arResult['FIELD_VALUE']['BE_TEXT'][] = $arResult['SELECTOR_VALUES'][$be];
            }
            $arResult['FIELD_VALUE']['BE_TEXT'] = implode(',', $arResult['FIELD_VALUE']['BE_TEXT']);
        }
        if ($index === 'salaryBeSelect') {
            $arResult['FIELD_VALUE']['SALARY_BE_TEXT'] = $arResult['SELECTOR_VALUES'][$field];
        }
    }
} elseif ($arParams['userField']['USER_TYPE_ID'] === 'vigrusercardlegalentities') {
    if (CModule::IncludeModule('iblock')) {
        $iblockElementProvider = new \CIBlockElement(false);
        $legalListDB = $iblockElementProvider->GetList([], [
            'IBLOCK_ID' => getIblockIdByCode('companies')
        ], false, false, ['ID', 'NAME']);
        while ($legal = $legalListDB->Fetch()) {
            $arResult['SELECTOR_VALUES'][$legal['ID']] = $legal['NAME'];
        }
    }
    $arResult['FIELD_VALUE'] = json_decode(html_entity_decode((stripslashes($arParams['userField']['VALUE']))), 1);
    $arResult['FIELD_VALUE']['legalSelectArray'] = explode(',', $arResult['FIELD_VALUE']['legalSelect']);
    $userID = $GLOBALS['_POST']['FIELDS'][1]['ENTITY_VALUE_ID'];
    $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch();
    $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    $rsData = $entity_data_class::getList([
        "select" => ["*"],
        "order" => ["ID" => "ASC"],
        "filter" => ["UF_COMPANY" => $arResult['FIELD_VALUE']['legalSelectArray']]
    ]);
    while ($arData = $rsData->Fetch()) {
        $arResult['LEGAL_ENTITIES'][] = array_merge($arData,
            ['NAME' => $arResult['SELECTOR_VALUES'][$arData['UF_COMPANY']]]);
    }
    echo '<pre>';
    print_r($userID);
    echo '</pre>';
}
$this->includeComponentTemplate();