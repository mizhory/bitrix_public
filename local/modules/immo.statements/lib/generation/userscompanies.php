<?php


namespace Immo\Statements\Generation;


use Bitrix\Main\UserTable;
use Immo\Manager;
use Immo\Statements\Data\HLBlock;
use Immo\Structure\Organization;

/**
 * @description Абстрактный класс генерации для работы с данными пользователей
 * Class UsersCompanies
 * @package Immo\Statements\Generation
 */
abstract class UsersCompanies implements BaseGeneration
{
    /**
     * @description Название хайлоадблока с данными о сотрудниках
     */
    public const HL_NAME_USERS_COMPANY = Manager::HL_NAME_USERS_COMPANY;

    /**
     * @description Загрузка данных по юрлицам
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function loadRows(array $filter = [], int $limit = 0, int $page = 1): array
    {
        $companyUsersData = new HLBlock(static::HL_NAME_USERS_COMPANY);

        $class = $companyUsersData->getEntity();
        $fields = array_keys($companyUsersData->getFields());

        $params = [
            'select' => $fields,
            'filter' => $filter,
            'count_total' => true
        ];

        if ($limit > 0 and $page > 0) {
            $params['limit'] = $limit;
            $params['offset'] = ($page - 1) * $limit;
        }

        $result = $class::getList($params);

        return [
            'ROWS' => $result->fetchAll(),
            'COUNT' => $result->getCount()
        ];
    }

    /**
     * @description Сбор информации о пользователях. Формирует массив данных с разделением по БЕ и юрлицам
     * @param array $companyUsers
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function collectUsersInfo(array $companyUsers): array
    {
        $arUsers = $this->loadUsersBySnils(array_column($companyUsers, 'UF_SNILS'));
        if (empty($arUsers)) {
            return [];
        }

        $arStructure = Organization::getStructure();
        if (empty($arStructure)) {
            return [];
        }

        foreach ($arStructure as $beId => ['BE' => $arBe, 'COMPANIES' => $companies]) {
            if (empty($companies)) {
                continue;
            }

            foreach ($companies as $company) {
                $arStructureCompanies[$company['OLD_ID']] = [
                    'BE' => $arBe,
                    'COMPANY' => $company
                ];
            }
        }

        if (empty($arStructureCompanies)) {
            return [];
        }

        foreach ($companyUsers as $user) {
            if (
                empty($user['UF_COMPANY'])
                or empty($structure = $arStructureCompanies[$user['UF_COMPANY']])
                or empty($arUser = $arUsers[$user['UF_SNILS']])
            ) {
                continue;
            }

            if (empty($arUsersBe[$structure['BE']['ID']])) {
                $arUsersBe[$structure['BE']['ID']] = [
                    'BE' => $structure['BE'],
                    'COMPANIES' => [],
                    'USERS' => []
                ];
            }

            $arUsersBe[$structure['BE']['ID']]['COMPANIES'][$structure['COMPANY']['OLD_ID']]
                = $structure['COMPANY'];
            $arUsersBe[$structure['BE']['ID']]['USERS'][$structure['COMPANY']['OLD_ID']][$arUser['ID']]['DATA']
                = $arUser;
            $arUsersBe[$structure['BE']['ID']]['USERS'][$structure['COMPANY']['OLD_ID']][$arUser['ID']]['COMPANY_INFO']
                = $user;
        }

        if (!empty($arUsersBe)) {
            foreach ($arUsersBe as $id => $beUsers) {
                if (!empty($beUsers['USERS'])) {
                    continue;
                }

                unset($arUsersBe[$id]);
            }
        }

        return $arUsersBe ?? [];
    }

    /**
     * @description Загрузка пользователей по снилсам
     * @param array $arSnils
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function loadUsersBySnils(array $arSnils): array
    {
        $rsUsers = UserTable::query()
            ->whereIn('UF_SNILS', $arSnils)
            ->setSelect([
                'ID',
                'UF_SNILS',
                'UF_CS_BE',
            ])
            ->exec();

        while ($user = $rsUsers->fetch()) {
            $arUsers[$user['UF_SNILS']] = $user;
            $arUsers[$user['UF_SNILS']]['UF_CS_BE'] = (array)json_decode($arUsers[$user['UF_SNILS']]['UF_CS_BE']);
        }

        return $arUsers ?? [];
    }
}