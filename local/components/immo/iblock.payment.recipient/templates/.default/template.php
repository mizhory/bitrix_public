<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load(['jquery', 'jquery2']);
$this->addExternalJS($templateFolder. '/dadata.js');
$this->addExternalCss($templateFolder. '/dadata.css');

if (!empty($arResult['PROPERTY'])):?>
    <input value="<?=$arResult['VALUE']['VALUE']?>" name="<?=$arParams['HTML_CONTROL']['VALUE']?>" type="text" id="pay-recipient-<?=$arParams['PROPERTY']['ID']?>" size="30">

    <script type="text/javascript">
        BX(function () {
            (new PayRecipient({
                id: '<?="pay-recipient-{$arParams['PROPERTY']['ID']}"?>',
                config: <?=\CUtil::PhpToJSObject($arResult['CONFIG'])?>,
                token: '<?=$arResult['TOKEN']?>'
            })).init();
        });
    </script>

<?php
endif;