<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(["jquery"]);
$this->addExternalJS('/local/modules/vigr.budget/js/dadata.js');
?>

<input  type='hidden'  id="UF_DADATA" name = 'UF_DADATA'>

<div class="ui-entity-editor-content-block ui-entity-editor-content-block-field-custom-text">
    <div class="ui-entity-editor-block-draggable-btn-container">
        <div class="ui-entity-editor-draggable-btn"></div>
    </div>
    <div class="ui-entity-editor-block-title ui-entity-widget-content-block-title-edit">
        <label class="ui-entity-editor-block-title-text"> Получатель платежа (ИНН) * </label>
    </div>
    <div class="ui-entity-editor-block-context-menu"></div>
</div>
<div class="ui-entity-editor-content-block">
    <span class="field-wrap">
        <span class="field-item">
            <input value="<?=$arResult['arValues']['inn']?>" id = 'getterINNNalBeznal' class="fields string" type="text">
        </span>
    </span>
</div>

<div class="ui-entity-editor-content-block ui-entity-editor-content-block-field-custom-text">
    <div class="ui-entity-editor-block-draggable-btn-container">
        <div class="ui-entity-editor-draggable-btn"></div>
    </div>
    <div class="ui-entity-editor-block-title ui-entity-widget-content-block-title-edit">
        <label class="ui-entity-editor-block-title-text"> Получатель платежа (Наименование) * </label>
    </div>
    <div class="ui-entity-editor-block-context-menu"></div>
</div>
<div class="ui-entity-editor-content-block">
    <span class="field-wrap">
        <span class="field-item">
            <input value="<?=$arResult['arValues']['name']?>" id = 'getterNalBeznal' class="fields string " tabindex="0" type="text">
        </span>
    </span>
</div>

<div class="ui-entity-editor-content-block">
    <span class="field-wrap fields boolean">
        <span class="field-item fields boolean">
                <label>
                    <?
                    $checked = '';
                    if($arResult['arValues']['checked'] === 'checked') {
                        $checked = 'checked';
                    }

                        ?>
                    <input <?=$checked?> id = 'notResident' type="checkbox" value="1">нерезидент РФ, физическое лицо без ИНН
                </label>
        </span>
    </span>
</div>


<style>
    .disabled{
        pointer-events: none;
        background-color: grey;
        opacity: 50%;
    }
</style>

<script type="text/javascript">
    BX.ready(function (){
        window.dadata = new DaData();
    })
</script>
