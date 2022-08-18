<?php

namespace Immo\Component;

use Bitrix\Main;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use CSite;
use CIBlockSection;
use CIBlockElementRights;
use CIBlock;
use Immo\Iblock\Manager;
use Immo\Motivation\Access;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @description Компонент рутинга премиальной выедомсти
 * Class SalaryRout
 */
class Motivation extends Router
{
    /**
     * Событие, вызываемое из includeComponent перед выполнением компонента.
     * @param $arParams
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws Main\ObjectPropertyException
     * @throws SystemException
     */
    function onPrepareComponentParams($arParams): array
    {
        if(!$arParams['IBLOCK_ID']){
            $arParams['IBLOCK_ID'] = Manager::getIblockId('motivation');
        }
        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * @description Отображение компонента
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            $this->arResult = $this->definePage($this->arParams);
            if (empty($this->arResult['PAGE'])) {
                throw new SystemException('Ошибка адреса. Запрашиваемая страница не найдена');
            }
            $this->arResult['CAN_READ'] = 'N';
            $this->arResult['CAN_CREATE'] = 'N';
            $obAccess = Access::getInstance();
            $obCanAdd = $obAccess->isCanCreate();
            $obCanRead = $obAccess->isCanRead();
            if($obCanAdd->isSuccess()){
                $this->arResult['CAN_CREATE'] = 'Y';
            }
            if($obCanRead->isSuccess()){
                $this->arResult['CAN_READ'] = 'Y';
            }
            if(!$obCanAdd->isSuccess() && !$obCanRead->isSuccess()){
                $this->arResult['PAGE'] = 'errors';
                $this->arResult['ERROR_TEXT'] = 'У вас нет прав для просмотра этой страницы';
            }
            $this->IncludeComponentTemplate($this->arResult['PAGE']);
        } catch (\Exception $error) {
            ShowError($error->getMessage());
        }
    }
}