<?php

use \Bitrix\Main\
{
    UI\Extension,
    Loader,
    UserTable
};
use IMMO\Manager\UserAccessManager;

IncludeTemplateLangFile(__FILE__);

class CSliderUserCard extends CBitrixComponent
{
    const IBLOCK_COMP_CODE = 'departments';

    public function executeComponent()
    {
        global $APPLICATION, $CACHE_MANAGER, $USER;
        if (!intval($this->arParams['USER_ID']))
            $this->arResult['ERRORS'][] = 'Для работы компонента необходима идентифицировать данные о пользователе';


        if (!intval($this->arParams['USER_ID'])) {
            //0xFF0A01
            $this->arResult['ERRORS'][] = 'Произошла ошибка 0xFF0A01 - Идентификатор пользователя не опознан!';
        }

        if ($this->startResultCache(false, array())) {
            Extension::load('ui.bootstrap4');
            $this->GenerateComponent();
            $this->arResult['USER'] = $USER->GetByID($this->arParams['USER_ID'])->fetch();
            $this->arResult['ITEMS']['UF_USER_TYPE_HTML'] = $this->generateUFUserTypeHTMLSelector($this->arParams['USER_ID']);
        } else {
            $this->AbortResultCache();
        }
        $this->includeComponentTemplate();
    }

    public function GenerateComponent()
    {
        global $APPLICATION, $CACHE_MANAGER, $USER;

        $arFilter = [
            'ID' => intval($this->arParams['USER_ID'])
        ];
        $arSelect = ['ID', 'UF_CS_BE'];
        $res = UserTable::getList([
            'select' => $arSelect,
            'filter' => $arFilter
        ]);
        $ret = $res->fetch();
        if (!intval($ret['ID'])) {
            $this->arResult['ERRORS'][] = 'Данные о пользователе не найдены или пользователь с таким идентификатором не существует';
        }
        if (Loader::IncludeModule('iblock')) {

            $arF = [
                'IBLOCK_ID' => getIblockIdByCode(static::IBLOCK_COMP_CODE)
            ];

            $legalListDB = $obRes = \CIBlockSection::GetList(
                $arSort = ['NAME' => 'asc'],
                $arF,
                false,
                ["ID", "NAME"]
            );

            while ($legal = $legalListDB->Fetch()) {
                $this->arResult['BE_ITEMS'][$legal['ID']] = $legal['NAME'];
            }
        }
        if (strlen($ret['UF_CS_BE']) <= 0) {
            $this->arResult['DATA'] = false;
        } else {
            $json_decode = json_decode(html_entity_decode(stripslashes(stripslashes($ret['UF_CS_BE']))));

            $this->arResult['DATA']['legalSelectArray']
                = explode(',', $this->arResult['DATA']['legalSelect']);

            foreach ($json_decode as $field_id => $field_value) {
                $this->arResult['DATA'][$field_id] = $field_value;
            }

        }
    }

    const GEN_UF_USER_TYPE_FIELDS = [
        'UF_USER_TYPE', 'ID'
    ];
    const IBLOCK_UF_USER_TYPE_CODENAME = 'user_type';

    protected function generateUFUserTypeHTMLSelector($user_id = 0)
    {
        if (!$user_id || !intval($user_id) || $user_id == 0)
            $user_id = $this->arParams['USER_ID'];
        if ($user_id <= 0) return false;

        $arUF_USER_TYPE = UserTable::getList([
            'select' => static::GEN_UF_USER_TYPE_FIELDS,
            'filter' => ['ID' => $user_id]
        ])->fetch();
        $iblock_id = getIblockIdByCode(static::IBLOCK_UF_USER_TYPE_CODENAME);

        if (!Loader::includeModule('iblock')) return false;
        $arFilter = [
            'IBLOCK_ID' => $iblock_id
        ];
        $arSelect = [
            'NAME', 'ID'
        ];
        $iblockElemRes = \CIBlockElement::GetList(
            $arOrder = [],
            $arFilter,
            false,
            false,
            $arSelect
        );
        $option = '';
        while ($element = $iblockElemRes->fetch()) {
            $selected = false;
            if ($element['ID'] == $arUF_USER_TYPE['UF_USER_TYPE']) $selected = ' selected="selected"';
            $option .= '<option' . $selected . ' value="' . $element['ID'] . '">' . $element['NAME'] . '</option>';
        }
        return '<select class="immo-uf-usertype ui-ctl-element" name="UF_USER_TYPE">' . $option . '</select>';
    }

}