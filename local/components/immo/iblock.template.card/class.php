<?php


namespace Immo\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Immo\Financial\App\FinancialApp;
use Immo\Iblock\Manager;
use Immo\Iblock\Property;
use Bitrix\Main\Engine\ActionFilter;
use Immo\Tools\BizprocHelper;
use Immo\Tools\User;

/**
 * Class IblockTemplateCard
 * @package Immo\Components
 */
class IblockTemplateCard extends \CBitrixComponent implements Controllerable
{
    /**
     * @description описание префильтров/фильтров действий
     * @return \array[][]
     */
    public function configureActions()
    {
        return [
            'createDraftByTemplate' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    /**
     * @description создает заявку с мета статусом "черновик" по переданному шаблону.
     * Возвращает ссылку на созданую заявку
     *
     * @param int $templateId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function createDraftByTemplateAction(int $templateId): array
    {
        if (empty($this->arParams['PROPERTY']['IBLOCK_ID'])) {
            throw new SystemException('Ошибка параметров');
        }

        $templateId = (int)$templateId;
        if ($templateId <= 0) {
            throw new SystemException('Ошибка запрашиваемого шаблона');
        }

        $arTemplate = Property\TemplateCard::loadByTemplate($this->arParams['PROPERTY']['IBLOCK_ID'], $templateId, [
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'ACTIVE',
            'SORT',
            'NAME',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'CODE',
        ], true);

        if (empty($arTemplate)) {
            throw new SystemException('Запрашиваемый шаблон не найден');
        }

        $arTemplate["PROPERTY_{$this->arParams['PROPERTY']['CODE']}"] = $templateId;
        $this->setDraftStatus($arTemplate);
        $this->prepareCreate($arTemplate);
        $result = $this->createCardByTemplate($arTemplate);

        if (!$result->isSuccess()) {
            throw new SystemException(implode('; ', $result->getErrorMessages()));
        }

        $this->runBizproc($result->getData()['ID'], $this->arParams['PROPERTY']['IBLOCK_ID']);

        return [
            'url' => $result->getData()['URL']
        ];
    }

    /**
     * @description Запускает бизнес процесс (предварительный этап) по созданной заявке
     * @param int $id
     * @param int $iblockId
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function runBizproc(int $id, int $iblockId)
    {
        BizprocHelper::runBizprocByElement($id, $iblockId);
    }

    /**
     * @description подготавливает поля заявки перед созданием
     *
     * @param array $arFields
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function prepareCreate(array &$arFields): void
    {
        unset($arFields['ID']);
        $arProperties = [];
        foreach ($arFields as $keyName => $value) {
            if (strpos($keyName, 'PROPERTY_') === false) {
                continue;
            }

            $arProperties[str_replace('PROPERTY_', '', $keyName)] = $value;
            unset($arFields[$keyName]);
        }

        $arPropertiesIblock = Manager::loadProperties(
            Query::filter()
                ->where('IBLOCK_ID', $arFields['IBLOCK_ID'])
                ->whereIn('PROPERTY_TYPE', [PropertyTable::TYPE_FILE, PropertyTable::TYPE_STRING]),
            ['ID', 'CODE', 'MULTIPLE', 'USER_TYPE', 'PROPERTY_TYPE']
        );

        if (!empty($arPropertiesIblock)) {
            foreach ($arPropertiesIblock as $property) {
                if (empty($arProperties[$property['CODE']])) {
                    continue;
                }

                if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_FILE) {
                    if ($property['MULTIPLE'] == 'Y' and is_array($arProperties[$property['CODE']])) {
                        foreach ($arProperties[$property['CODE']] as $index => $fileId) {
                            $arProperties[$property['CODE']][$index] = \CFile::MakeFileArray($fileId);
                        }
                    } else {
                        $arProperties[$property['CODE']] = \CFile::MakeFileArray($arProperties[$property['CODE']]);
                    }
                } elseif (
                    $property['PROPERTY_TYPE'] == PropertyTable::TYPE_STRING
                    and $property['USER_TYPE'] == 'HTML'
                ) {
                    $htmlValue = (is_array($arProperties[$property['CODE']]))
                        ? $arProperties[$property['CODE']]
                        : ['VALUE' => $arProperties[$property['CODE']], 'TYPE' => 'HTML'];

                    unset($arProperties[$property['CODE']]);
                    $arProperties[$property['CODE']]['VALUE'] = $htmlValue;
                }
            }
        }

        foreach (FinancialApp::SYSTEM_FIELDS_DROP as $field) {
            if (!array_key_exists($field, $arFields)) {
                continue;
            }

            unset($arFields[$field]);
        }

        if (!empty($arProperties)) {
            $arFields['PROPERTY_VALUES'] = $arProperties;
            $desc = $arFields['PROPERTY_VALUES']['DESCRIPTION'];
            unset($arFields['PROPERTY_VALUES']['DESCRIPTION']);
            $arFields['PROPERTY_VALUES']['DESCRIPTION'] = $desc;
        }

        if (
            !empty($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['PROPERTY_VALUES_DROP'])
            and !empty($arFields['PROPERTY_VALUES'])
        ) {
            foreach ($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['PROPERTY_VALUES_DROP'] as $dropProperty) {
                if (!array_key_exists($dropProperty, $arFields['PROPERTY_VALUES'])) {
                    continue;
                }

                unset($arFields['PROPERTY_VALUES'][$dropProperty]);
            }
        }
    }

    /**
     * @description устанавливает метастатус "черновик" переданным полям заявки
     *
     * @param array $arFields
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function setDraftStatus(array &$arFields): void
    {
        if (
            empty($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['STATUS_PROP'])
            or empty($this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['DRAFT_XML_ID'])
        ) {
            return;
        }

        $arPropertyStatus = Manager::getPropertyByCode(
            $this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['STATUS_PROP'],
            $this->arParams['PROPERTY']['IBLOCK_ID']
        );
        if (empty($arPropertyStatus)) {
            throw new SystemException('Ошибка статуса заявки');
        }

        $propertyCode = $arPropertyStatus['CODE'];

        $enum = Manager::getEnumByCode(
            $arPropertyStatus['ID'],
            $this->arParams['PROPERTY']['USER_TYPE_SETTINGS']['DRAFT_XML_ID']
        );

        $arFields["PROPERTY_{$propertyCode}"] = $enum['ID'] ?? 0;
    }

    /**
     * @description создает новую заявку по переданным полям
     *
     * @param array $arTemplateFields
     * @return Result
     */
    protected function createCardByTemplate(array $arTemplateFields): Result
    {
        $result = new Result();
        if (empty($arTemplateFields['IBLOCK_ID'])) {
            $result->addError(new Error('Отсутствует ID инфоблока'));
            return $result;
        }

        $elementObj = new \CIBlockElement();
        $id = $elementObj->Add($arTemplateFields);
        if (empty($id) or $id === false or !empty($elementObj->LAST_ERROR)) {
            $result->addError(new Error($elementObj->LAST_ERROR));
        }

        if (!$result->isSuccess()) {
            return $result;
        }
        
        $detailPage = Manager::getElementDetailPage($id, $arTemplateFields['IBLOCK_ID']);

        if (empty($detailPage)) {
            $result->addError(new Error('Не удалось получить ссылку на заявку'));
            return $result;
        }

        $result->setData(['URL' => $detailPage, 'ID' => $id]);

        return $result;
    }

    /**
     * @description определяет и возвращает страницу, которая должна быть подключена для конкретной страницы
     *
     * @return string
     */
    protected function getViewTemplateName(): string
    {
        // редактирование
        if (empty($this->arResult['VALUE']['VALUE']) or $this->arResult['VALUE']['VALUE'] == 'IS_TEMPLATE') {
            if ($this->arResult['PROPERTY']['ELEMENT_ID'] <= 0) {
                $templateName = 'create';
            } else {
                $templateName = 'edit';
            }
        } elseif ($this->arResult['VALUE']['VALUE'] > 0) {
            $templateName = 'template';
        } elseif ($this->arResult['PROPERTY']['ELEMENT_ID'] <= 0) {
            $templateName = 'create';
        }

        if ($this->request->get('copy_id') > 0) {
            $templateName = 'create';
        }

        return $templateName ?? '';
    }

    /**
     * @description возвращает список ключей параметров, которые нужно зашифровать
     *
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return ['PROPERTY'];
    }

    /**
     * @description собирает доп. информацию по значению свойства.
     */
    protected function collectExtraInfoValue(): void
    {
        $id = (int)$this->arResult['VALUE']['VALUE'];
        if ($id <= 0) {
            return;
        }

        $item = Manager::getElementCompatibility($id, $this->arResult['PROPERTY']['IBLOCK_ID'], [
            'ID',
            'IBLOCK_ID',
            'DETAIL_PAGE_URL',
            'NAME'
        ]);
        $this->arResult['VALUE']['NAME'] = $item['NAME'];

        if (!empty($item['DETAIL_PAGE_URL'])) {
            $this->arResult['VALUE']['LINK'] = $item['DETAIL_PAGE_URL'];
        }
    }

    /**
     * @return mixed|void|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        $this->arResult['PROPERTY'] = $this->arParams['PROPERTY'];
        $this->arResult['VALUE'] = $this->arParams['VALUE'];
        $this->arResult['HTML_CONTROL'] = $this->arParams['HTML_CONTROL'];
        $this->arResult['INCLUDE_HTML'] = $this->arParams['INCLUDE_HTML'];
        $this->arResult['SIGNED_PARAMS'] = $this->getSignedParameters();

        if (!empty($this->arResult['PROPERTY']['IBLOCK_ID']) and !empty($this->arResult['PROPERTY']['ID'])) {
            $this->arResult['TEMPLATES'] = Property\TemplateCard::loadTemplates(
                $this->arResult['PROPERTY']['IBLOCK_ID'],
                $this->arResult['PROPERTY']['ID'],
                User::getCurrent()->getId()
            );
        }

        $template = $this->getViewTemplateName();

        if ($template == 'template') {
            $this->collectExtraInfoValue();
        }

        if (!empty($template)) {
            $this->includeComponentTemplate($template);
        }
    }
}