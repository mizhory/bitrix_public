<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var $templateFolder string
 * @var $arResult array
 */

if (!empty($arResult['CHECKBOX_PROPERTY']) and !empty($arResult['CHECKBOX_PROPERTY']['VALUE'])) {
    foreach ($arResult['CHECKBOX_PROPERTY']['PROPERTY']['VALUES'] as $enum) {
        if ($enum['ID'] != $arResult['CHECKBOX_PROPERTY']['VALUE']) {
            continue;
        }

        $selectedValue = $enum;
        break;
    }
}

$type = ($arResult['PROPERTY']['ELEMENT_ID'] > 0) ? 'edit' : 'add';
$formId = "form_lists_element_{$type}_{$arResult['PROPERTY']['IBLOCK_ID']}";

if (!empty($arResult['PROPERTY'])):?>
    <div class="iblock-autocomplete-field">
        <div>
            <?if (!empty($arResult['PROPERTY']['USER_TYPE_SETTINGS']['ITEMS_PROPERTY'])):?>
                <label>
                    <?=$arResult['ITEMS_PROPERTY_LABEL']?>
                    <input id="use-values-<?=$arResult['ID']?>" <?=(!empty($selectedValue) and $selectedValue['XML_ID'] == 'Y') ? 'checked' : ''?> type="checkbox">
                </label>
            <?endif;?>
            <select <?=($arParams['DISABLE'] == 'Y') ? 'disabled' : ''?> name="<?=$arResult['HTML_CONTROL']['VALUE']?>" id="<?=$arResult['ID']?>" style="width: 50%">
                <?if ($arResult['VALUE']['VALUE'] !== ''):?>
                    <option value="<?=$arResult['VALUE']['VALUE']?>" selected><?=$arResult['VALUE']['VALUE']?></option>
                <?endif;?>
            </select>
            <?if ($arParams['DISABLE'] != 'Y'):?>
                <button id="button_clear_<?=$arResult['ID']?>" type="button">Очистить</button>
            <?endif;?>
        </div>
    </div>
    <script>
        BX.ready(function () {
            let autoCompleteField = new window.AutoCompleteField({
                id: '<?=$arResult['ID']?>',
                idButton: 'button_clear_<?=$arResult['ID']?>',
                signedParameters: '<?=$arResult['SIGNED_PARAMS']?>',
                minLength: window.parseInt('<?=$arResult['PROPERTY']['USER_TYPE_SETTINGS']['MIN_LENGTH']?>') ?? 1,
                value: '<?=$arResult['VALUE']['VALUE']?>',
                checkboxProperty: <?=\CUtil::PhpToJSObject($arResult['CHECKBOX_PROPERTY']) ?? []?>,
                formId: '<?=$formId?>',
                entityProperty: <?=\CUtil::PhpToJSObject($arResult['ID_ENTITY_PROPERTY'])?>
            });

            autoCompleteField.init();

            window['AutoCompleteFieldInstance'] = {
                '<?=$arParams['PROPERTY']['ID']?>': autoCompleteField
            }
        });
    </script>

<?php endif;
