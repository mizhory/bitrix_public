<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult)):?>

    <label class="ui-ctl ui-ctl-file-btn">
        <input
            id="file-input-<?=$arResult['PROPERTY']['IBLOCK_ID']?>-<?=$arResult['PROPERTY']['ID']?>"
            name="<?="{$arResult['HTML_CONTROL']["NAME"]}[DOCUMENT_TEMPLATE]"?>"
            type="file"
            class="ui-ctl-element"
        >
        <div class="ui-ctl-label-text">Добавить шаблон</div>
    </label>

    <script type="text/javascript">
        BX(function () {
            let id = 'file-input-<?=$arResult['PROPERTY']['IBLOCK_ID']?>-<?=$arResult['PROPERTY']['ID']?>',
                signedParameters = '<?=$arResult['']?>',
                fileInput = BX(id);

            if (!fileInput) {
                return;
            }

            BX.bind(fileInput, 'change', ({target}) => {
                if (target.files.length <= 0) {
                    return;
                }

                let files = Array.from(target.files),
                    formData = new FormData();
                files.forEach((file, index) => {
                    console.log(file);
                    formData.append(`FILE_${index}`, file);
                });

                BX.ajax.runComponentAction('immo:iblock.print.values', 'setTemplateDoc', {
                    mode: 'class',
                    data: formData,
                    signedParameters: signedParameters
                }).then((response) => {
                    console.log(response);
                });
            })
        });
    </script>

<?endif;
