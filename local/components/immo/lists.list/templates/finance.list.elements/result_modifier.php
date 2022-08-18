<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @param array $arParams
 * @param array $arResult
 */

if (!empty($arResult["ELEMENTS_ROWS"]))
{
    foreach ($arResult["ELEMENTS_ROWS"] as $key => &$row)
    {
        if (!empty($row["actions"]))
        {
            foreach($row["actions"] as &$arAction)
            {
                if ($arAction["ID"] == "delete")
                {
                    $arAction["ONCLICK"] = "javascript:BX.Lists['".$arResult['JS_OBJECT']."'].deleteElement('".
                        $arResult["GRID_ID"]."', '".$row["id"]."')";
                }
            }
        }
    }
}

if (!empty($arResult['FILTER'])) {
    /**
     * Исправляет отображение вывода свойства "Статус заявки" в фильтре
     */
    \Immo\Tools\UIGridFilter::fixCustomPropertiesView($arResult['FILTER'], $arResult['FIELDS']);
}

if ($arResult['IBLOCK']['ID'] > 0) {
    $typeProperty = \Immo\Iblock\Manager::getPropertyByCode('TYPE_ELEMENT', $arResult['IBLOCK']['ID']);
    if (!empty($typeProperty['VALUES'])) {
        $iblockCodes = array_column($typeProperty, 'XML_ID');
        foreach ($typeProperty['VALUES'] as $arEnum) {
            $arIblocks[$arEnum['ID']] = \Immo\Iblock\Manager::getIblockId($arEnum['XML_ID']);
        }

        $arResult['IBLOCKS'] = array_values($arIblocks ?? []);
    }
}

if (
    $arResult['IBLOCK']['ID'] > 0
    and !empty($arResult['ELEMENTS_ROWS'])
    and !empty($typeProperty)
    and !empty($arIblocks)
) {
    $parentProperty = \Immo\Iblock\Manager::getPropertyByCode('PARENT_ELEMENT', $arResult['IBLOCK']['ID']);
    if (!empty($parentProperty)) {
        $ids = array_column($arResult['ELEMENTS_ROWS'], 'id');
        $query = \Immo\Iblock\Manager::getQueryElements($arResult['IBLOCK']['ID']);
        $rsElements = $query
            ->whereIn('ID', $ids)
            ->setSelect([
                'ID',
                'TYPE_FINANCE_ELEMENT' => "{$typeProperty['CODE']}.VALUE",
                'FINANCE_APP_REF' => "{$parentProperty['CODE']}.VALUE"
            ])
            ->exec();
        while ($element = $rsElements->fetch()) {
            if (!array_key_exists($element['TYPE_FINANCE_ELEMENT'], $arIblocks)) {
                continue;
            }

            $arIblockElements[$element['ID']] = $arIblocks[$element['TYPE_FINANCE_ELEMENT']];
            $parentRef[$element['ID']] = (int)$element['FINANCE_APP_REF'];
        }
    }
}

if (
    $arResult['IBLOCK']['ID'] > 0
    and !empty($arResult['ELEMENTS_ROWS'])
    and !empty($typeProperty)
    and !empty($arIblocks)
    and !empty($arResult['IBLOCKS'])
    and class_exists(\Immo\Iblock\Property\TemplateCard::class)
) {
    \Bitrix\Main\Localization\Loc::loadMessages(
        \Bitrix\Main\Application::getDocumentRoot() . $this->getComponent()->getPath() . '/component.php'
    );

    if (!empty($arIblockElements)) {
        $arResult['IBLOCK_REF'] = $arIblockElements;
    }

    if (!empty($arResult['FIELDS'])) {
        foreach ($arResult['FIELDS'] as $propertyKey => $property) {
            if ($property['CODE'] != 'PRINT') {
                continue;
            }

            $arPrintProperty = $property;
            break;
        }

        $arPrintProperties = \Immo\Iblock\Manager::loadProperties(
            \Bitrix\Main\ORM\Query\Query::filter()
                ->whereIn('IBLOCK_ID', \Immo\Financial\App\FinancialApp::getFinancialIblocks())
                ->where('CODE', 'PRINT'),
            ['ID', 'IBLOCK_ID']
        );
        $arIblockPrintProps = array_column($arPrintProperties, 'IBLOCK_ID');
    }

    foreach ($arResult['ELEMENTS_ROWS'] as $index => $arRow) {
        $iblockId = (int)$arIblockElements[$arRow['data']['ID']];
        $elementId = (int)$parentRef[$arRow['data']['ID']];
        if ($iblockId <= 0 or $elementId <= 0) {
            continue;
        }

        if (!empty($arRow['actions'])) {
            $isCopyAvailable = in_array($iblockId, \Immo\Iblock\Property\TemplateCard::getIblockCopyAvailable());

            unset($copyActionIndex);
            foreach ($arRow['actions'] as $actionIndex => $action) {
                if ($action['ID'] == 'delete') {
                    unset($arRow['actions'][$actionIndex]);
                    unset($arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndex]);
                    continue;
                }

                if (array_key_exists('ONCLICK', $action)) {
                    $arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndex]['ONCLICK'] = str_replace(
                        "finance/{$arResult['IBLOCK']['ID']}/element/0/{$arRow['data']['ID']}",
                        "finance/{$iblockId}/element/0/{$elementId}",
                        $action['ONCLICK']
                    );
                    $arRow['actions'][$actionIndex]['ONCLICK']
                        = $arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndex]['ONCLICK'];
                }
                if (array_key_exists('HREF', $action)) {
                    $arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndex]['HREF'] = str_replace(
                        "finance/{$arResult['IBLOCK']['ID']}",
                        "finance/{$iblockId}",
                        $action['HREF']
                    );
                    $arRow['actions'][$actionIndex]['HREF']
                        = $arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndex]['HREF'];
                }
                
                if (!array_key_exists('HREF', $action) or empty($action['HREF'])) {
                    continue;
                }

                if (strpos((new \Bitrix\Main\Web\Uri($action['HREF']))->getQuery(), 'copy_id') === false) {
                    continue;
                }

                $copyActionIndex[] = $actionIndex;
            }
            /**
             * Генерируем ссылку как в component.php
             */
            $urlCopy = \CHTTP::urlAddParams(str_replace(
                ["#list_id#", '#section_id#', "#element_id#", "#group_id#"],
                [$iblockId, 0, 0, (int)$arParams["SOCNET_GROUP_ID"]],
                $arParams["LIST_ELEMENT_URL"]
            ),
                ["copy_id" => $elementId],
                ["skip_empty" => true, "encode" => true]
            );

            /**
             * Проверяем, если ссылки копирования нет, но она должна быть, то генрируем ее
             * Иначе проверяем, если ссылка есть и ее не должно быть, обрезаем ее
             */
            if (empty($copyActionIndex) and $isCopyAvailable) {
                $arResult['ELEMENTS_ROWS'][$index]['actions'][] = [
                    'TEXT' => 'Создать шаблон',
                    'HREF' => $urlCopy
                ];
            } elseif (!empty($copyActionIndex) and $isCopyAvailable) {
                foreach ($copyActionIndex as $actionIndexValue) {
                    $arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndexValue]['TEXT'] = 'Создать шаблон';
                    $arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndexValue]['HREF'] = $urlCopy;
                }
            } elseif (!empty($copyActionIndex) and !$isCopyAvailable) {
                foreach ($copyActionIndex as $actionIndexValue) {
                    unset($arResult['ELEMENTS_ROWS'][$index]['actions'][$actionIndexValue]);
                }
                $arResult['ELEMENTS_ROWS'][$index]['actions'] = array_values($arResult['ELEMENTS_ROWS'][$index]['actions']);
            }
        }

        if (
            !empty($arRow['columns']['NAME'])
            and !empty($arResult["FIELDS"]['NAME'])
        ) {
            $arResult['ELEMENTS_ROWS'][$index]['columns']['NAME'] = \Bitrix\Lists\Field::renderField([
                "LIST_SECTIONS_URL" => $arParams["LIST_SECTIONS_URL"],
                "LIST_URL" => $arParams["LIST_URL"],
                "SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
                "LIST_ELEMENT_URL" => '/finance/#list_id#/element/#section_id#/#element_id#/',
                "LIST_FILE_URL" => $arParams["~LIST_FILE_URL"],
                "IBLOCK_ID" => $arIblockElements[$arRow['data']['ID']],
                "SECTION_ID" => $arParams["~SECTION_ID"],
                "ELEMENT_ID" => $parentRef[$arRow['data']['ID']],
                "VALUE" => $arRow['data']['NAME'],
                'TYPE' => 'NAME'
            ]);
        }

        if (
            !empty($arPrintProperty)
            and !empty($arRow['columns'][$arPrintProperty['FIELD_ID']])
        ) {
            if (!empty($arIblockPrintProps) and in_array($iblockId, $arIblockPrintProps)) {
                $arPrintProperty['ELEMENT_ID'] = $elementId;
                $arPrintProperty['IBLOCK_ID'] = $iblockId;
                $arResult['ELEMENTS_ROWS'][$index]['columns'][$arPrintProperty['FIELD_ID']] = call_user_func(
                    $arPrintProperty['PROPERTY_USER_TYPE']['GetPublicViewHTML'],
                    $arPrintProperty,
                    [],
                    []
                );
                $arResult['ELEMENTS_ROWS'][$index]['data']['IS_PRINT'] = 'Y';
            } else {
                $arResult['ELEMENTS_ROWS'][$index]['columns'][$arPrintProperty['FIELD_ID']] = '';
            }
        }

        $arResult['ELEMENTS_ROWS'][$index]['id'] = $elementId;
        $arResult['ELEMENTS_ROWS'][$index]['data']['ID'] = $elementId;
        $arResult['ELEMENTS_ROWS'][$index]['data']['IBLOCK_ID'] = $iblockId;
    }
}

/**
 * Изменяем вывод свойств типа "Деньги"
 */
if (
    $arResult['IBLOCK']['ID'] > 0
    and !empty($arResult['ELEMENTS_ROWS'])
    and !empty($arIblockElements)
) {
    $propsMoney = \Immo\Iblock\Manager::loadProperties(
        \Bitrix\Main\ORM\Query\Query::filter()
            ->where('IBLOCK_ID', $arResult['IBLOCK']['ID'])
            ->where('USER_TYPE', \Bitrix\Currency\Integration\IblockMoneyProperty::USER_TYPE),
        ['ID', 'CODE', 'USER_TYPE']
    );

    if (!empty($propsMoney)) {
        $query = \Immo\Iblock\Manager::getQueryElements($arResult['IBLOCK']['ID']);
        if (!empty($query)) {
            $arSelect = ['ID'];

            $arType = \Immo\Integration\Budget\Iblock::defineTypeBe($arResult['IBLOCK']['ID']);
            foreach ($propsMoney as $property) {
                $arSelect["{$property['CODE']}_VALUE"] = "{$property['CODE']}.VALUE";
            }

            /**
             * В авансовом отчет свойство "сдано в кассу" - не деньги, а число.
             * Поэтому прокидываем его принудительно в массив, чтобы оно тоже отформатировалось
             */
            $returnSumm = \Immo\Iblock\Manager::getPropertyByCode('RETURN_SUMM', $arResult['IBLOCK']['ID']);
            if (!empty($returnSumm)) {
                $propsMoney[$returnSumm['ID']] = $returnSumm;
                $arSelect['RETURN_SUMM_VALUE'] = 'RETURN_SUMM.VALUE';
            }

            $rsElements = $query
                ->whereIn('ID', array_keys($arIblockElements))
                ->setSelect($arSelect)
                ->exec();

            while ($element = $rsElements->fetch()) {
                if ($arResult['IBLOCK']['ID'] == \Immo\Iblock\Manager::getAvansIBlockId()) {
                    $element['RETURN_SUMM_VALUE'] = $element['RETURN_SUMM_VALUE'] . '|';
                }

                foreach ($propsMoney as $property) {
                    if (
                        !array_key_exists("{$property['CODE']}_VALUE", $element)
                        or is_null($element["{$property['CODE']}_VALUE"])
                    ) {
                        continue;
                    }

                    $arValues[$element['ID']][$property['ID']]
                        = \Immo\Integration\Budget\CurrencyManager::convertSumValue(
                            $element["{$property['CODE']}_VALUE"],
                            $property
                    );
                }
            }

            if (!empty($arValues)) {
                foreach ($arResult['ELEMENTS_ROWS'] as $index => $arRow) {
                    if (empty($arValues[$arRow['data']['~ID']])) {
                        continue;
                    }

                    foreach ($arValues[$arRow['data']['~ID']] as $propId => $value) {
                        if (!array_key_exists("PROPERTY_{$propId}", $arRow['columns'])) {
                            continue;
                        }

                        $arResult['ELEMENTS_ROWS'][$index]['columns']["PROPERTY_{$propId}"] = $value;
                    }
                }
            }
        }
    }
}

if (!empty($arResult['FIELDS']) and (!empty($arResult["ELEMENTS_HEADERS"]) or !$arResult['FILTER'])) {
    $systemProperties = array_flip(\Immo\Iblock\Manager::SYSTEM_PROPERTIES);
    unset($systemProperties['BE'], $systemProperties['PRINT']);
    $systemProperties = array_values(array_flip($systemProperties));
    $systemProperties[] = 'LIST_SECTION_ID';

    foreach ($arResult['ELEMENTS_HEADERS'] as $index => $header) {
        if (
            (
                !empty($arResult['FIELDS'][$header['id']])
                and (
                    in_array($arResult['FIELDS'][$header['id']]['FIELD_ID'], \Immo\Iblock\Manager::SYSTEM_FIELDS)
                    or in_array($arResult['FIELDS'][$header['id']]['CODE'], $systemProperties)
                )
            ) or (
                in_array($header['id'], \Immo\Iblock\Manager::SYSTEM_FIELDS)
            )
        ) {
            unset($arResult['ELEMENTS_HEADERS'][$index]);
        }
    }

    $systemProperties[] = 'BE';
    foreach ($arResult['FILTER'] as $index => $filter) {
        $filter['id'] = strtoupper($filter['id']);

        if (empty($arResult['FIELDS'][$filter['id']])) {
            continue;
        }

        if (in_array($arResult['FIELDS'][$filter['id']]['FIELD_ID'], \Immo\Iblock\Manager::SYSTEM_FIELDS)) {
            unset($arResult['FILTER'][$index]);
        }
        if (in_array($arResult['FIELDS'][$filter['id']]['CODE'], $systemProperties)) {
            unset($arResult['FILTER'][$index]);
        }
    }
}
