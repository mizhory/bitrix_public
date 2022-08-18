<?php

use Bitrix\Main;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 *
 */

?>
    <script>
        function onMultipleSelectTmp() {
        }
    </script>
    <form id="save_motivation" method="post">
        <input id="js-id-motivation" name="ID" type="hidden" value="<?php echo $arResult['ID']; ?>">
        <table>
            <tr>
                <td width="50%">Выберите БЕ <span class="required-motivation">*</span></td>
                <td>
                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown" style="width: 100%;">
                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                        <select name="SELECTED_BE" class="ui-ctl-element js-sendAjax">
                            <option value="0">Выбрать</option>
                            <?php foreach ($arResult['FORM']['SELECTED_BE'] as $arBusinessUnit) {
                                ?>
                                <option <?php echo($arBusinessUnit['ID'] == $arResult['SELECTED_BE'] ? 'selected="selected"' : ''); ?>
                                value="<?php echo $arBusinessUnit['ID']; ?>"><?php echo $arBusinessUnit['NAME']; ?></option><?php
                            } ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="50%">Бюджетный год</td>
                <td>
                    <div class="ui-ctl ui-ctl-textbox" style="width: 100%;">
                        <input name="F_YEAR" readonly="readonly" type="text"
                               value="<?php echo $arResult['FORM']['FISCAL_YEAR']; ?>" class="ui-ctl-element"
                               placeholder="Бюджетный год">
                    </div>
                </td>
            </tr>
            <tr>
                <td width="50%">Выберите месяц выплаты <span class="required-motivation">*</span></td>
                <td>
                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown" style="width: 100%;">
                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                        <select name="F_MONTH" class="ui-ctl-element js-sendAjax">
                            <?php foreach ($arResult['FORM']['MONTH'] as $arMonth) {
                                ?>
                                <option <?php echo($arMonth['ID'] == $arResult['F_MONTH'] ? 'selected="selected"' : ''); ?>
                                value="<?php echo $arMonth['ID']; ?>"><?php echo $arMonth['VALUE']; ?></option><?php
                            } ?>

                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="50%">Выберите статью расходов <span class="required-motivation">*</span></td>
                <td>
                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown" style="width: 100%;">
                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                        <select name="SELECTED_ART" class="ui-ctl-element js-sendAjax">
                            <option value="0">Выбрать</option>
                            <?php foreach ($arResult['FORM']['ART'] as $arArt) {
                                ?>
                                <option <?php echo($arArt['ID'] == $arResult['SELECTED_ART'] ? 'selected="selected"' : ''); ?>
                                value="<?php echo $arArt['ID']; ?>"><?php echo $arArt['NAME']; ?></option><?php
                            } ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td width="50%">Валюта</td>
                <td>
                    <input readonly="readonly" name="UF_CURRENCY" type="text"
                           value="<?php echo $arResult['FORM']['CURRENCY']; ?>" class="ui-ctl-element"
                           placeholder="Валюта">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="text-align: center;"><b>Сотрудники к выплате</b></div>
                    <table width="100%"
                           class="table_top_border table_left_border table_bottom_border table_right_border">
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
                                <td><input class="js-numeric-envelope" name="envelope[<?php echo $randString; ?>]"
                                           type="text" readonly=""
                                           value="<?php echo $arUserInMotivation['UF_CON_NUM']; ?>">
                                </td>
                                <td><a data-id="<?php echo $randString; ?>"
                                       onclick="ShowSingleSelector(); return false;"
                                       href="#"><?php echo $arUserInMotivation['FIO']; ?></a><input
                                            type="hidden" name="users[<?php echo $randString; ?>]"
                                            value="<?php echo $arUserInMotivation['UF_USER']; ?>"
                                            class="js-statement-user"></td>
                                <td><input value="<?php echo $arUserInMotivation['SUM_FORMATTED']; ?>"
                                           class="js-sendAjax js-statement-amount-item"
                                           name="sum_motivations[<?php echo $randString; ?>]"></td>
                                <td>
                                    <button data-id="<?php echo $randString; ?>" onclick="deleteRowUser();">x</button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr id="js-addUserToMotivationLists">
                            <td colspan="3"></td>
                            <td>
                                <button class="ui-btn ui-btn-success ui-btn-sm"
                                        onclick="addUserToMotivationLists(); return false;">Добавить сотрудника
                                </button>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="text-align: center;"><b>Итоговые данные</b></div>
                    <div id="js-erorrs_statement_amount"></div>
                    <table width="100%"
                           class="table_top_border table_left_border table_bottom_border table_right_border">
                        <tr>
                            <td style="width: 200px">Сумма по ведомости:</td>
                            <td>
                                <div class="js-sum-formated"
                                     id="js-statement_amount"><?php echo $arResult['STATEMENT_AMOUNT']; ?></div>
                            </td>
                        </tr>
                        <tr>
                            <td>Осталось доступных средств:</td>
                            <td class="js-sum-formated"
                                id="js-BALANCE_ACCUMULATIVE"><?php echo $arResult['BALANCE_ACCUMULATIVE']; ?></td>
                        </tr>
                        <tr>
                            <td>Доступно за весь период:</td>
                            <td class="js-sum-formated"
                                id="js-BALANCE_PLAN"><?php echo $arResult['BALANCE_PLAN']; ?></td>
                        </tr>
                        <tr>
                            <td>Всего истрачено за период:</td>
                            <td class="js-sum-formated"
                                id="js-BALANCE_FACT"><?php echo $arResult['BALANCE_FACT']; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <div style="text-align: center;"><b>Согласовать с</b></div>
                    <table width="100%"
                           class="table_top_border table_left_border table_bottom_border table_right_border">
                        <tr>
                            <td>
                                <div id="container"></div>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
            <tr>
                <td>Комментарий:</td>
                <td>
                    <div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
                        <textarea rows="5" cols="55" name="PREVIEW_TEXT"
                                  class="ui-ctl-element js-sendAjax"><?php echo $arResult['PREVIEW_TEXT']; ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
        <button onclick="return stopDoubleSendForm()" id="js-send_to_agree" class="ui-btn ui-btn-success" type="submit"
                name="SEND_TO_AGREE" value="Y">
            Отправить на согласование
        </button>
        <button class="ui-btn ui-btn-danger" type="submit" name="CANCEL" onclick="CancelStatement(); return false;"
                value="Y">Отменить ведомость
        </button>
        <button class="ui-btn ui-btn-primary" type="submit" name="UPDATE" onclick="saveSendAjax(); return false;"
                value="Y">Обновить данные по бюджету
        </button>
        <?php foreach ($arResult['FORM']['SELECTED_ADDITIONAL_USERS'] as $arSelectedAdditionalUser) {
            ?>
            <input id="<?php echo $arSelectedAdditionalUser['js_id']; ?>"
                   name="<?php echo $arSelectedAdditionalUser['js_name']; ?>"
                   value="<?php echo $arSelectedAdditionalUser['id']; ?>" type="hidden">
            <?php
        } ?>
    </form>
    <p>
        <a <?php if ($arResult['ID'] == 0) { ?> style="display: none;" <?php } ?> id="js-ult-mot"
                                                                                  href="/sheets/motivation/<?php echo $arResult['ID']; ?>/approval-list/">Просмотреть
            историю согласования</a>
    </p>
    <script>
        const arUserListsApprove = <?php echo Main\Web\Json::encode($arResult['FORM']['ADDITIONAL_USERS']); ?>;
        const arSelectedUserListsApprove = <?php echo Main\Web\Json::encode($arResult['FORM']['SELECTED_ADDITIONAL_USERS']); ?>;
    </script>
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
    "bitrix:intranet.user.selector.new",
    "",
    [
        "MULTIPLE" => "N",
        "NAME" => 'findUser',
        "VALUE" => 0,
        "POPUP" => "Y",
        "ON_CHANGE" => "onMultipleSelectTmp",
        "SITE_ID" => SITE_ID,
        "SHOW_EXTRANET_USERS" => "NONE",
    ],
    null,
    ["HIDE_ICONS" => "Y"]
);
?>