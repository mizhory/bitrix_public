<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load(['ui.buttons',]);

if (!empty($arResult)):?>

    <button id="btn_print_values_<?=$arResult['ID']?>" class="ui-btn ui-btn-success" type="button">Распечатать</button>

    <script type="text/javascript">
        BX(function () {
            let printValues = new window.IblockPrintValues({
                id: 'btn_print_values_<?=$arResult['ID']?>',
                signedParameters: '<?=$arResult['SIGNED_PARAMS']?>',
                formId: 'form_lists_element_edit_<?=$arResult['PROPERTY']['IBLOCK_ID']?>'
            });

            printValues.init();
        });
    </script>

<?endif;
