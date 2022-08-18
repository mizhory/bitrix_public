<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Loader::includeModule("bizproc");
\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");

CUtil::InitJSCore(['popup']);

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/bizproc.workflow.faces/templates/.default/style.css');

$class = "blue";

//print_de($arResult['DOP_SOGLAS_BUTTONS']);


foreach ($arResult['START_BUTTONS'] as $addButton) :
    ?>
    <div class='bizproc-task-button'>
        <table>
            <tr>
                <td></td>
                <td><?=$addButton['NAME']?></td>
                <td>
                    <div class="bp-btn-panel"><a onclick="<?=$addButton['ONCLICK']?>" class="bp-button bp-button bp-button-<?=$class?>"><?=$addButton['BUTTON_TEXT']?></a></div>
                </td>
            </tr>
        </table>
    </div>

    <?
endforeach;



foreach ($arResult['AR_TASKS'] as $task) :
    ?>
    <div class='bizproc-task-button'>
        <table>
            <tr>
                <td><?=$task['WORKFLOW_STARTED']?></td>
                <td><?=$task['NAME']?></td>

                <td>
                    <div class="bp-btn-panel"><a href="#" onclick="return BX.Bizproc.showTaskPopup(<?=$task['ID']?>, function(){window['bxGrid_bizproc_task_list'].Reload()}, <?=$arResult['TARGET_USER_ID']?>, this)" class="bp-button bp-button bp-button-<?=$class?>"><?=$arResult['BUTTON_TEXT']?></a></div>
                </td>
            </tr>
        </table>
    </div>

    <?
endforeach;

if(!empty($arResult['DOP_SOGLAS_BUTTONS'])):
    ?>
    <div class='bizproc-task-button'>
        <table>
            <tr>
                <td></td>
                <td><?=$arResult['DOP_SOGLAS_BUTTONS']['NAME']?></td>
                <td>
                    <div class="bp-btn-panel"><a onclick="<?=$arResult['DOP_SOGLAS_BUTTONS']['ONCLICK']?>" class="bp-button bp-button bp-button-<?=$class?>"><?=$arResult['DOP_SOGLAS_BUTTONS']['BUTTON_TEXT']?></a></div>
                </td>
            </tr>
        </table>
    </div>
    <?
endif;



?>
<input type="hidden" value="1" name="UF_BP_TASK_BUTTON">

<script>
    var bspStarter = new BPStarter();
</script>