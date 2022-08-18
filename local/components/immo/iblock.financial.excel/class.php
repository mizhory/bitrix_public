<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\SystemException;
use Immo\Iblock\ListExcelDocument;

/**
 * Class IblockFinancialExcel
 * @package Immo\Components
 * @description класс компонента генерации эксель выгрузки финансовых заявок
 */
class IblockFinancialExcel extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    /**
     * @description описание префильтров/фильтров действий
     * @return \array[][]
     */
    public function configureActions()
    {
        return [
            'generateExcel' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description Генерирует файл выгрузки
     * @param array $params
     * @return string[]
     * @throws SystemException
     */
    public function generateExcelAction(array $params): array
    {
        $arFile = ListExcelDocument::generate($params['TYPE'], $params);
        return [
            'src' => $arFile['SRC'] ?? ''
        ];
    }

    /**
     * @description Пустой метод, так как компонент используется в качестве контроллера,
     * поэтому никакие действия в самом компоненте не нужны
     * @return mixed|void|null
     */
    public function executeComponent()
    {
    }
}