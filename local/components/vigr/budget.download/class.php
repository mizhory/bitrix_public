<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

/**
 * Class CBudgetDownload
 * загрузка файла
 */
class CBudgetDownload extends CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        $this->IncludeComponentTemplate();
    }

    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'download' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }
}
