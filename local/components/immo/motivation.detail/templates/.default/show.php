<?php

use Bitrix\Main;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var Immo\Components\MotivationDetail $component
 */
?>

    <table width="80%">
        <tr>
            <td width="50%">Выберите БЕ</td>
            <td><b>
                    <?php
                    foreach ($arResult['FORM']['SELECTED_BE'] as $arBusinessUnit) {
                        if ($arBusinessUnit['ID'] == $arResult['SELECTED_BE']) {
                            echo $arBusinessUnit['NAME'];
                            break;
                        }
                    } ?></b>
            </td>
        </tr>
        <tr>
            <td width="50%">Бюджетный год</td>
            <td>
                <b><?php echo $arResult['FORM']['FISCAL_YEAR']; ?></b>
            </td>
        </tr>

        <tr>
            <td width="50%">Выберите месяц выплаты</td>
            <td>
                <b><?php foreach ($arResult['FORM']['MONTH'] as $arMonth) {
                        if ($arMonth['ID'] == $arResult['F_MONTH']) {
                            echo $arMonth['VALUE'];
                            break;
                        }
                    } ?></b>

            </td>
        </tr>
        <tr>
            <td width="50%">Выберите статью расходов</td>
            <td>
                <b><?php foreach ($arResult['FORM']['ART'] as $arArt) {
                        if ($arArt['ID'] == $arResult['SELECTED_ART']) {
                            echo $arArt['NAME'];
                        }
                    } ?></b>
            </td>
        </tr>
        <tr>
            <td width="50%">Валюта</td>
            <td>
                <b><?php echo $arResult['FORM']['CURRENCY']; ?></b>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="text-align: center;"><b>Сотрудники к выплате</b></div>
                <table width="100%" class="table_top_border table_left_border table_bottom_border table_right_border">
                    <tr>
                        <td width="20%">№ Конверта</td>
                        <td width="40%">ФИО</td>
                        <td colspan="2">Сумма</td>
                    </tr>
                    <?php
                    foreach ($arResult['FORM']['USER_IN_MOTIVATION'] as $arUserInMotivation) {
                        $randString = Main\Security\Random::getString(5);
                        ?>
                        <tr id="<?php echo $randString; ?>">
                            <td><?php echo $arUserInMotivation['UF_CON_NUM']; ?></td>
                            <td><?php echo $arUserInMotivation['FIO']; ?></td>
                            <td class="js-sum-formated"><?php echo $arUserInMotivation['SUM_FORMATTED']; ?></td>
                            <td>

                            </td>
                        </tr>
                        <?
                    }
                    ?>

                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="text-align: center;"><b>Итоговые данные</b></div>
                <div id="js-erorrs_statement_amount"></div>
                <table width="100%" class="table_top_border table_left_border table_bottom_border table_right_border">
                    <tr>
                        <td style="width: 200px">Сумма по ведомости:</td>
                        <td>
                            <div class="js-sum-formated" id="js-statement_amount"><?php echo $arResult['STATEMENT_AMOUNT']; ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td>Осталось доступных средств:</td>
                        <td class="js-sum-formated" id="js-BALANCE_ACCUMULATIVE"><?php echo $arResult['BALANCE_ACCUMULATIVE']; ?></td>
                    </tr>
                    <tr>
                        <td>Доступно за весь период:</td>
                        <td class="js-sum-formated" id="js-BALANCE_PLAN"><?php echo $arResult['BALANCE_PLAN']; ?></td>
                    </tr>
                    <tr>
                        <td>Всего истрачено за период:</td>
                        <td class="js-sum-formated" id="js-BALANCE_FACT"><?php echo $arResult['BALANCE_FACT']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>Комментарий:</td>
            <td>
                <b><?php echo $arResult['PREVIEW_TEXT']; ?></b>
            </td>
        </tr>
    </table>
    <p>
        <a href="/sheets/motivation/<?php echo $arResult['ID']; ?>/approval-list/">Просмотреть историю согласования</a>
    </p>
<?php

\CJSCore::Init(['currency']);
$currencyFormat = \CCurrencyLang::GetFormatDescription('RUB');
$currencyFormat['DEC_POINT'] = ',';
$currencyFormat['THOUSANDS_SEP'] = ' ';

?>

<script type="text/javascript">
    BX.Currency.setCurrencyFormat('RUB', <? echo CUtil::PhpToJSObject($currencyFormat, false, true); ?>);
</script>

<?php
$APPLICATION->IncludeComponent(
    'immo:cutomBPAction',
    '',
    [
        'USER_ID' => $arResult['USER_ID'],
        'ELEMENT_ID' => $arResult['ID'],
        'IBLOCK_ID' => $arResult['IBLOCK_ID'],
        'FILTER_BP_TEMPLATE' => [
            'ID' => $arResult['BP_TEMPLATES_IDS']
        ],
    ],
    $component,
    [
        'HIDE_ICONS' => 'Y'
    ]
);
?>