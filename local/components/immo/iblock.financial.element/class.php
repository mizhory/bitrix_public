<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Immo\Iblock;

/**
 * @description Компонент для вывода информации о заявке
 * Class IblockFinancialElement
 * @package Immo\Components
 */
class IblockFinancialElement extends \CBitrixComponent implements Main\Engine\Contract\Controllerable
{
    /**
     * @return mixed|void|null
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function executeComponent()
    {
        $this->arResult = $this->arParams;
        $this->arResult['SIGNED_PARAMS'] = $this->getSignedParameters();
        if (!empty($this->arParams['VALUE']['VALUE']['ID'])) {
            $this->arResult['ELEMENT'] = Iblock\Property\FinancialElement::collectInfo(
                $this->arParams['VALUE']['VALUE'],
                $this->arParams['PROPERTY']
            );
        }
        $this->includeComponentTemplate();
    }

    /**
     * @description Возвращает массив ключей $arParams, которые будут зашифрованы методом getSignedParameters
     * @return string[]
     */
    protected function listKeysSignedParameters()
    {
        return ['PROPERTY'];
    }

    /**
     * @description Метод для обновления полей аяксом
     * @param string $type
     * @param string $query
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    public function loadElementsAction(string $type, string $query): array
    {
        return ['items' => Iblock\Property\FinancialElement::searchElement(
            $type,
            $query,
            $this->arParams['PROPERTY']['USER_TYPE_SETTINGS'],
            $this->arParams['PROPERTY']
        )];
    }

    /**
     * @return \array[][]
     */
    public function configureActions()
    {
        return [
            'loadElements' => [
                'prefilters' => [
                    new Main\Engine\ActionFilter\Authentication(),
                    new Main\Engine\ActionFilter\HttpMethod([Main\Engine\ActionFilter\HttpMethod::METHOD_POST]),
                    new Main\Engine\ActionFilter\Csrf(),
                ],
            ],
        ];
    }
}