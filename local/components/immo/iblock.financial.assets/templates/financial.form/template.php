<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load(['fx', 'popup']);
/**
 * @description JS для формы УС
 */
$this->addExternalJs('/local/assets/js/financialApp.js');

/**
 * @description Стили для формы УС
 */
$this->addExternalCss('/local/assets/css/financialApp.css');

if (!empty($arResult)):
    ?>

    <script type="text/javascript">
        BX(function () {
            const financial = new FinancialApp(<?=\CUtil::PhpToJSObject($arResult['jsParams'])?>);
            financial.init();

            window['FinancialInstance'] = financial;
        });
    </script>

<?php endif;

if (!in_array(\Bitrix\Main\Context::getCurrent()->getRequest()->get('bp'), ['Y', 'y', '1'])):?>
    <style type="text/css">
        table.bx-edit-tabs td#tab_cont_tab_bp {
            display: none;
        }
    </style>
<?endif;
