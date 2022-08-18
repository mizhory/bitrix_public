<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult)):?>

<div>
    <label>
        Является шаблоном
        <input
            name="<?=$arResult['HTML_CONTROL']['VALUE']?>"
            id="template-card-checkbox"
            type="checkbox"
            value="IS_TEMPLATE"
        >
    </label>

    <select id="template-card-select" data-empty-value="N">
        <option value="">Выбрать</option>
        <? foreach ($arResult['TEMPLATES'] as $arTemplate):?>
            <option value="<?=$arTemplate['ID']?>"><?=$arTemplate['NAME']?></option>
        <?endforeach;?>
    </select>
    <span id="btn-create-wrapper"></span>
</div>

<script type="text/javascript">
    BX(function () {
        let templateCard = new window.IblockTemplateCard({
            id: 'template-card-select',
            idCheckbox: 'template-card-checkbox',
            name: '<?=$arResult['HTML_CONTROL']['VALUE']?>',
            idBtnWrapper: 'btn-create-wrapper',
            signedParams: '<?=$arResult['SIGNED_PARAMS']?>'
        });
        templateCard.init();
    });
</script>

<?php endif;
