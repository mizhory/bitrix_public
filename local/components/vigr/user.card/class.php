<?php

use \Bitrix\Main;
use Bitrix\Main\Loader;

\Bitrix\Main\UI\Extension::load('vigr.usercard');
\Bitrix\Main\Loader::includeModule('highloadblock');
CJSCore::Init(array('vigr.usercard'));

/**
 *
 */
class ImmoUserCardComponent extends \CBitrixComponent
{
    private $currentUser;
    private $legalList, $buhgalter;

    public function __construct($component = null)
    {
        try {
            $this->currentUser = \CUser::GetID();
            $this->legalList = $this->getCompaniesList();
            parent::__construct($component);
        } catch (Throwable $e) {

        }
    }

    public function executeComponent()
    {
        if ($this->arParams['userField']['USER_TYPE_ID'] === 'vigrusercard') {
            $this->arResult = $this->getUserCardData();
        } elseif ($this->arParams['userField']['USER_TYPE_ID'] === 'vigrusercardlegalentities') {
            $this->arResult = $this->getLegalUserData();
        }
        $this->includeComponentTemplate();
    }

    const DEPARTAMENT = 'departments';

    /**
     *  Get data for field 'Общая информация'
     * @return array
     */
    private function getUserCardData(): array
    {
        $legalEntities = $this->accountantEntitites();
        if (CModule::IncludeModule('iblock')) {
            $iblockElementProvider = new \CIBlockElement(false);
            $beListDB = $iblockElementProvider->GetList([], [
                'IBLOCK_ID' => getIblockIdByCode('be'),
                'ACTIVE' => 'Y'
            ], false, false, ['ID', 'NAME']);
            while ($be = $beListDB->fetch()) {
                $arResult['SELECTOR_VALUES'][$be['ID']] = $be['NAME'];
            }
        }
        $arResult['FIELD_VALUE'] = $this->stripValue($this->arParams['userField']['VALUE'], 2, true);
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
        $arResult['DEP_ITEMS'] = $this->getDepartments(true);
        $arResult['PERMS'] = $this->headAndUserPermissions() || $this->buhgalter;
        return $arResult;
    }

    /**
     * Get data from hl to show in admin & public
     * @return array
     */
    private function getLegalUserData(): array
    {
        $arResult['FIELD_VALUE'] = $this->stripValue($this->arParams['userField']['VALUE'], 1, true);
        $arResult['FIELD_VALUE']['legalSelectArray'] = explode(',', $arResult['FIELD_VALUE']['legalSelect']);
        /*$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => [
                '=NAME' => 'CompanyEmplyeesData'
            ]
        ])->fetch();
        if (!$hlblock) {
            return [];
        }
        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $companyDataDB = $entity_data_class::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
        ));
        while ($companyData = $companyDataDB->Fetch()) {
            $arResult['LEGAL_DATA'][] = $companyData;
            $orgObjectId[] = $companyData['UF_COMPANY'];
        }*/
        //$departments = $this->getDepartments(false);
        //$arResult['LEGAL_DATA'] = $departments['companies'];

        /*foreach ($this->legalList as $company) {
            foreach ($orgObjectId as $object) {
                if ($company['ID'] === $object) {
                    $companiesData[$company['ID']] = $company['NAME'];
                }
            }
        }*/
        $companyDataRes = \IMMO\Entity\IMMOEmployeesDataTable::getList([
            'select' => ['*'],
            'order' => ['ID' => 'ASC']
        ]);

        while ($companyData = $companyDataRes->Fetch()) {
            $arResult['LEGAL_DATA'][] = $companyData;
            $orgObjectId[] = $companyData['UF_COMPANY'];
        }
        foreach ($this->legalList as $company) {
            foreach ($orgObjectId as $object) {
                if ($company['ID'] === $object) {
                    $companiesData[$company['ID']] = $company['NAME'];
                }
            }
        }

        foreach ($arResult['LEGAL_DATA'] as $hlRowIndex => $hlRow) {
            $hlRow['UF_COMPANY_NAME'] = $arResult['LEGAL_DATA'][$hlRowIndex]['UF_COMPANY_NAME'] = $companiesData[$hlRow['UF_COMPANY']];
            if (($this->headAndUserPermissions() || in_array($hlRow['UF_COMPANY'], $this->accountantEntitites())) &&
                in_array($hlRow['ID'], $arResult['FIELD_VALUE']['legalSelectArray'])
            ) {
                $arResult['LEGAL_PUBLIC_DATA'][] = $hlRow;
            }
        }
        return $arResult;
    }

    /**
     *
     * if current user is head or log.ruk or he can view all
     * @return bool
     */
    private function headAndUserPermissions(): bool
    {
        if ($GLOBALS['_POST']['FIELDS'][0]['ENTITY_ID'] === 'USER') {
            if (is_numeric($GLOBALS['_POST']['FIELDS'][0]['ENTITY_VALUE_ID']) && $GLOBALS['_POST']['FIELDS'][0]['ENTITY_VALUE_ID'] > 0) {
                $userSliderId = strip_tags($GLOBALS['_POST']['FIELDS'][0]['ENTITY_VALUE_ID']);
            }
        }
        if ($userSliderId <= 0) {
            return false;
        }
        $department = \CIntranetUtils::GetStructure()['DATA'];
        $user = \Bitrix\Main\UserTable::getList([
            'filter' => [
                'ID' => $userSliderId
            ],
            'select' => [
                'UF_DEPARTMENT',
                'UF_CAN_VIEW',
                'UF_LOG_RUK'
            ]
        ])->Fetch();
        $headID = $department[$user['UF_DEPARTMENT'][0]]['UF_HEAD'];
        $canViewAll = $user['UF_CAN_VIEW'];
        return $this->currentUser === $userSliderId || $headID === $this->currentUser ||
            $user['UF_LOG_RUK'] === $this->currentUser ||
            in_array($this->currentUser, $canViewAll);
    }

    /**
     * Returns array of companies id's where current user is accountant
     * @return array
     */
    private function accountantEntitites(): array
    {
        $legalEntites = [];
        foreach ($this->legalList as $company) {
            if ($company['PROPERTY_BUKHGALTER_VALUE'] === $this->currentUser) {
                $legalEntites[] = $company['ID'];
                $this->buhgalter = true;
            }
        }
        return $legalEntites;
    }

    /**
     *
     * Get all elements from list "Юр.лица"
     * @return array
     */
    private function getCompaniesList(): array
    {
        $companies = [];
        if (!Loader::IncludeModule('iblock'))
            return false;

        $iblockElementProvider = new \CIBlockElement(false);
        $legalListDB = $iblockElementProvider->GetList([],
            [
                'IBLOCK_ID' => getIblockIdByCode(static::DEPARTAMENT),
                'ACTIVE' => 'Y'
            ],
            false, false,
            ['ID', 'IBLOCK_ID', 'NAME', 'UF_ROLE_BUH' => 'PROPERTY_BUKHGALTER']
        );

        while ($legal = $legalListDB->Fetch()) {
            $companies[] = $legal;
        }

        return $companies;
    }

    /**
     * Standart strip with execute counter
     *
     * @param string $value value for stripslash and json_decode(optional)
     * @param int $stripQount counter for stripslash function times
     * @param bool $htmlDecode flag to html decode string
     */
    private function stripValue($value, $stripQount, $htmlDecode = false)
    {
        $newValue = '';
        for ($i = 0; $i < $stripQount; $i++) {
            $newValue = stripslashes($value);
        }
        if ($htmlDecode) {
            $newValue = json_decode(html_entity_decode($newValue), 1);
        }
        return $newValue;
    }

    const IBLOCK_DEPARTMENT_CODENAME = 'departments';

    public function getDepartments($flag = false)
    {
        $iblock_id = getIblockIdByCode('departments');
        $arFilter['IBLOCK_ID'] = $iblock_id;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) return false;

        $elementOb = CIBlockSection::GetList(
            ["SORT" => "ASC"], $arFilter,
            false, ['ID', 'UF_BE_AND_UL', 'NAME'], false
        );
        while ($elementResult = $elementOb->fetch()) {
            $UF_BE_AND_UL = json_decode($elementResult['UF_BE_AND_UL'], 1);
            $elems[$UF_BE_AND_UL['type']][] = $elementResult;
        }
        return ($flag == true) ? $elems['be'] : $elems;
    }
}
