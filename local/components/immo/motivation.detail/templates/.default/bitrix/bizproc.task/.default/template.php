<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load("ui.viewer");
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Loader::includeModule('socialnetwork');
CJSCore::Init(['socnetlogdest', 'bp_user_selector']);
\Bitrix\Main\UI\Extension::load("ui.tooltip");

$cmpId = RandString();

$showDelegationButton = (
    !$arResult['IsComplete']
    && ($arResult['isAdmin'] || (int)$arResult['TASK']['DELEGATION_TYPE'] !== CBPTaskDelegationType::None)
    && IsModuleInstalled('intranet')
);

if (empty($arResult['DOCUMENT_ICON'])) {
    $moduleIcon = 'default';
    if (in_array($arResult['TASK']['MODULE_ID'], ['crm', 'disk', 'iblock', 'lists', 'tasks']))
        $moduleIcon = $arResult['TASK']['MODULE_ID'];

    $arResult['DOCUMENT_ICON'] = $templateFolder . '/images/bp-' . $moduleIcon . '-icon.png';
}
?>
<script type="text/javascript">
    BX.message({
        BPAT_DELEGATE_SELECT: '<?=GetMessageJS('BPAT_DELEGATE_SELECT')?>',
        BPAT_DELEGATE_CANCEL: '<?=GetMessageJS('BPAT_DELEGATE_CANCEL')?>'
    });
</script>
<div class="bp-task-page bp-lent <?php if (empty($arResult["TASK"]['STARTED_BY_PHOTO_SRC'])){ ?>no-photo<?php } ?>">
        <?php
        if (!empty($arResult["TASK"]['STARTED_BY_PHOTO_SRC'])) { ?>
            <span class="bp-avatar" bx-tooltip-user-id="<?= (int)$arResult["TASK"]['STARTED_BY'] ?>"
                  bx-tooltip-classname="intrantet-user-selector-tooltip">
		            <img src="<?= $arResult["TASK"]['STARTED_BY_PHOTO_SRC'] ?>" alt="">
	            </span>
            <?php
        }
        ?>
        <div class="bp-short-process-inner">
            <div style="display: none;">
                <?php
                $APPLICATION->IncludeComponent(
                    "bitrix:bizproc.workflow.faces",
                    "",
                    [
                        "WORKFLOW_ID" => $arResult["TASK"]["WORKFLOW_ID"],
                        "TARGET_TASK_ID" => $arResult["TASK"]["ID"]
                    ],
                    $component
                );
                ?></div>
            <?php

            if ($arResult['ReadOnly']) {
                echo '<span class="bp-status"></span>';
            } elseif ($arResult["ShowMode"] == "Success") {
                switch ($arResult["TASK"]['USER_STATUS']) {
                    case CBPTaskUserStatus::Yes:
                        echo '<span class="bp-status-ready"><span>' . GetMessage('BPATL_USER_STATUS_YES') . '</span></span>';
                        break;
                    case CBPTaskUserStatus::No:
                    case CBPTaskUserStatus::Cancel:
                        echo '<span class="bp-status-cancel"><span>' . GetMessage('BPATL_USER_STATUS_NO') . '</span></span>';
                        break;
                    default:
                        echo '<span class="bp-status-ready"><span>' . GetMessage('BPATL_USER_STATUS_OK') . '</span></span><style>.bp-task-block{display: none}</style>';
                }
            } elseif ($arResult["TASK"]['IS_INLINE'] == 'Y') { ?>
                <div class="bp-btn-panel">
                    <div class="bp-btn-panel-inner">
                        <form method="post" action="<?= POST_FORM_ACTION_URI ?>">
                            <?= bitrix_sessid_post() ?>
                            <input type="hidden" name="action" value="doTask"/>
                            <input type="hidden" name="id" value="<?= (int)$arResult["TASK"]["ID"] ?>"/>
                            <input type="hidden" name="TASK_ID" value="<?= (int)$arResult["TASK"]["ID"] ?>"/>
                            <input type="hidden" name="workflow_id"
                                   value="<?= htmlspecialcharsbx($arResult["TASK"]["WORKFLOW_ID"]) ?>"/>
                            <input type="hidden" name="back_url"
                                   value="<?= htmlspecialcharsbx($arResult['backUrl']) ?>"/>
                            <?php
                            foreach ($arResult['TaskControls']['BUTTONS'] as $control):
                                $class = $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No || $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel ? 'decline' : 'accept';
                                $props = CUtil::PhpToJSObject([
                                    'TASK_ID' => $arResult["TASK"]['ID'],
                                    $control['NAME'] => $control['VALUE']
                                ]);
                                ?>
                                <button type="submit" name="<?= htmlspecialcharsbx($control['NAME']) ?>"
                                        value="<?= htmlspecialcharsbx($control['VALUE']) ?>"
                                        class="bp-button bp-button bp-button-<?= $class ?>"
                                        style="border: none">
                                    <span class="bp-button-icon"></span><span
                                            class="bp-button-text"><?= $control['TEXT'] ?></span>
                                </button>
                            <?php
                            endforeach;
                            ?>
                        </form>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="bp-task-block">
            <?php

            if (!empty($arResult["ERROR_MESSAGE"])) {
                ShowError($arResult["ERROR_MESSAGE"]);
            }
            if ($arResult["TASK"]["DESCRIPTION"] <> '') {
                echo \CBPViewHelper::prepareTaskDescription($arResult["TASK"]["DESCRIPTION"]);
            } else {
                echo $arResult["TASK"]["NAME"];
            }

            if ($showDelegationButton && $arResult["TASK"]['IS_INLINE'] == 'Y') {
                ?>
                <a href="#" class="bp-button bp-button-transparent bp-button-first"
                   onclick="return BX.Bizproc.showDelegationPopup(this, <?= (int)$arResult["TASK"]["ID"] ?>, <?= (int)$arParams["USER_ID"] ?>)"><span></span><?= GetMessage('BPAT_DELEGATE_LABEL') ?>
                </a>
                <?php
            }
            if ($arResult["ShowMode"] != "Success" && $arResult["TASK"]['IS_INLINE'] != 'Y') {
                ?>
                <form method="post" name="bp_task_<?= $cmpId ?>" action="<?= POST_FORM_ACTION_URI ?>"
                      enctype="multipart/form-data"
                    <?php if ($arParams['POPUP']) { ?> onsubmit="return BX.Bizproc.postTaskForm(this, event)"<?php } ?>>
                    <?= bitrix_sessid_post() ?>
                    <input type="hidden" name="" value="" id="bp_task_<?= $cmpId ?>_submiter">
                    <input type="hidden" name="action" value="doTask"/>
                    <input type="hidden" name="id" value="<?= (int)$arResult["TASK"]["ID"] ?>"/>
                    <input type="hidden" name="TASK_ID" value="<?= (int)$arResult["TASK"]["ID"] ?>"/>
                    <input type="hidden" name="workflow_id"
                           value="<?= htmlspecialcharsbx($arResult["TASK"]["WORKFLOW_ID"]) ?>"/>
                    <input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($arResult['backUrl']) ?>"/>
                    <table class="bizproc-table-main bizproc-task-table" cellpadding="3" border="0">
                        <?php echo $arResult["TaskForm"]; ?>
                    </table>
                    <div class="bizproc-item-buttons">
                        <?php
                        if (!empty($arResult['TaskControls']['BUTTONS'])) {
                            foreach ($arResult['TaskControls']['BUTTONS'] as $control) {
                                $class = $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No || $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel ? 'decline' : 'accept';
                                $props = CUtil::PhpToJSObject([
                                    'TASK_ID' => $arResult["TASK"]['ID'],
                                    $control['NAME'] => $control['VALUE']
                                ]);
                                ?>
                                <button type="submit" name="<?= htmlspecialcharsbx($control['NAME']) ?>"
                                        value="<?= htmlspecialcharsbx($control['VALUE']) ?>"
                                        class="bp-button bp-button bp-button-<?= $class ?>"
                                        style="border: none">
                                    <?= $control['TEXT'] ?>
                                </button>
                                <?php
                            }
                        } else {
                            echo $arResult["TaskFormButtons"];
                        }
                        ?>
                        <?php if ($showDelegationButton): ?>
                            <a href="#" class="bp-button bp-button-transparent"
                               onclick="return BX.Bizproc.showDelegationPopup(this, <?= (int)$arResult["TASK"]["ID"] ?>, <?= (int)$arParams["USER_ID"] ?>)"><span></span><?= GetMessage('BPAT_DELEGATE_LABEL') ?>
                            </a>
                        <?php endif ?>
                    </div>
                    <script>
                        BX.ready(function () {
                            var form = document.forms['bp_task_<?=$cmpId?>'],
                                submiter = BX('bp_task_<?=$cmpId?>_submiter');
                            var children = BX.findChildren(form, {property: {type: 'submit'}}, true);
                            for (var i = 0; i < children.length; i++) {
                                var cb = function () {
                                    submiter.name = this.name;
                                    submiter.value = this.value;
                                };

                                BX.bind(children[i], 'click', cb);
                                BX.bind(children[i], 'tap', cb);
                            }
                        });
                    </script>
                </form>
                <?php
            }
            ?>
        </div>
    </div>