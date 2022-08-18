<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$isAutoCreated = (
    (
        !empty($arParams['PARENT'])
        and $arParams['PARENT']['AUTO_CREATE'] == 'Y'
    ) and (
        !empty($arParams['VALUE']['VALUE']['ID'])
        and !empty($arResult['ELEMENT'])
    )
);

if (!empty($arResult['PROPERTY'])):?>
    <div>
        <select <?=($arParams['DISABLE'] == 'Y') ? 'disabled' : ''?> name="<?="{$arParams['HTML_CONTROL']['VALUE']}[ENTITY]"?>" data-empty-value="N" id="financial-element-select-entity" <?=($isAutoCreated) ? 'disabled' : ''?>>
            <option value="">Не выбрано</option>
            <?foreach (\Immo\Iblock\Property\FinancialElement::getEntities() as $entity => $arEntity):?>
                <option
                    value="<?=$entity?>"
                    <?=($arParams['VALUE']['VALUE']['ENTITY'] == $entity) ? 'selected' : ''?>
                >
                    <?=$arEntity['NAME']?>
                </option>
            <?endforeach;?>
        </select>

        <select name="<?="{$arParams['HTML_CONTROL']['VALUE']}[ID]"?>" id="financial-element-select-app" <?=($isAutoCreated or $arParams['DISABLE'] == 'Y') ? 'disabled' : ''?>>
            <?if (!empty($arParams['VALUE']['VALUE']['ID']) and !empty($arResult['ELEMENT'])):?>
                <option value="<?=$arParams['VALUE']['VALUE']['ID']?>" selected><?=$arResult['ELEMENT']['NAME']?></option>
            <?endif;?>
        </select>
    </div>

    <script type="text/javascript">
        BX(function () {
            (new FinancialElement({
                entities: <?=\CUtil::PhpToJSObject(\Immo\Iblock\Property\FinancialElement::getEntities())?>,
                signedParams: '<?=$arResult['SIGNED_PARAMS']?>',
                selectEntity: 'financial-element-select-entity',
                selectApp: 'financial-element-select-app',
                element: <?=\CUtil::PhpToJSObject($arResult['ELEMENT'] ?? [])?>,
                props: <?=\CUtil::PhpToJSObject($arParams['PROPERTIES'] ?? [])?>,
                elementId: '<?=$arParams['PROPERTY']['ELEMENT_ID']?>',
                disable: '<?=($arParams['DISABLE'] == 'Y' or $arParams['PARENT']['AUTO_CREATE'] == 'Y') ? 'Y' : 'N'?>'
            })).init();
        });
    </script>

<?php
endif;