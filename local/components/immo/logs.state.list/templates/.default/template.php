<?php

use Bitrix\Main;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
?>
    <table width="100%" border="1">
        <tr>
            <td>
                <b>Действие</b>
            </td>
            <td>
                <b>Пользователь</b>
            </td>
            <td>
                <b>Комментарий</b>
            </td>
            <td>
                <b>Дата / Время</b>
            </td>
        </tr>
        <?php
        foreach ($arResult['ELEMS'] as $arElem) {
            ?>
            <tr>
                <td>
                    <?php echo $arElem['ACTION_TITLE']; ?>
                </td>
                <td>
                    <?php echo $arElem['USER_NAME']; ?> <?php echo $arElem['USER_SECOND_NAME']; ?> <?php echo $arElem['USER_LAST_NAME']; ?>
                </td>
                <td><?php
                    if($arElem['STAGE_TITLE']) { ?><?php echo $arElem['STAGE_TITLE']; ?><?php }
                    if ($arElem['COMMENT']) { ?> с комментарием "<?php echo $arElem['COMMENT']; ?>"<?php }
                    ?>
                </td>
                <td>
                    <?php echo $arElem['DATE_CREATE']; ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
<?php

if ($arParams['BACK_NAME_LINK'] && $arParams['BACK_URL']) {
    ?>
    <br>
    <br>
    <a href="<?php echo $arParams['BACK_URL']; ?>"><?php echo $arParams['BACK_NAME_LINK']; ?></a>
    <?php
}

if ($arParams['BACK_LIST_URL'] && $arParams['BACK_LIST_NAME_LINK']) {
    ?>
    <br>
    <br>
    <a href="<?php echo $arParams['BACK_LIST_URL']; ?>"><?php echo $arParams['BACK_LIST_NAME_LINK']; ?></a>
    <?php
}
?>