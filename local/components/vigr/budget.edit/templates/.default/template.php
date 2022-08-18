<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalJS('/local/modules/vigr.budget/js/imask.js');
?>

<div class="disk-slider-content">
    <?
    \Bitrix\Main\UI\Extension::load("ui.entity-editor");

    $nowYear = $arParams['variables']['year'] ?? \Immo\Integration\Budget\BudgetHelper::defineFinancialYear();

    ?>
    <input id = 'monthStep' type="hidden" value="<?=$_SESSION['monthStep']??'2'?>">
    <input id = 'nowYear' type="hidden" value="<?=\Immo\Integration\Budget\BudgetHelper::defineFinancialYear()?>">
    <input id = 'nowMonth' type="hidden" value="<?=returnMonthInfo('fin', date('n'))?>">
    <table style="" class="table table-bordered">
        <tbody>
        <tr>
            <th>БЕ</th>
            <td>
                <select name='beId'>
                    <? foreach ($arResult['BE'] as $id => $be): ?>
                        <option
                            <?=($be['OLD_ID'] == $arParams['variables']['beId']) ? 'selected' : ''?>
                            value="<?=$be['OLD_ID']?>"
                        >
                            <?=$be['NAME']?>
                        </option>
                    <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Название статьи</th>
            <td>
                <select name='nowArticle'>
                    <? foreach ($arResult['ARTICLES'] as $id => $article): ?>
                        <option
                            <?=($id == $arParams['variables']['articleId']) ? 'selected' : ''?>
                            value="<?=$id?>"
                        >
                            <?=$article['NAME']?>
                        </option>
                    <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Финансовый год</th>
            <td>
                <select id="nowYearS">
                    <?foreach ($arResult['YEARS'] as $year):?>
                        <option <?=($nowYear == $year) ? 'selected' : ''?> value="<?=$year?>">
                            <?=$year?>
                        </option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
        <? foreach ($arResult['MONTHS'] as $key => $month): ?>
            <tr>
                <th><?= $month ?></th>
                <td>
                    <input
                        class='names'
                        data-month='<?= $key ?>' name='months[<?= $key ?>]'
                        value="<?= $arResult['DATA']['PLAN'][$key] ?? 0?>"
                    >
                </td>
            </tr>
        <? endforeach; ?>
        <tr>
            <th>Итого</th>
            <td>
                <input name='itogo' disabled type="text" value="<?=$arResult['DATA']['TOTAL']?>">
            </td>
        </tr>
        <tr>
            <th>Итого на текущий момент</th>
            <td>
                <div>
                    <input name='itogoNow' disabled type="text" value="<?= $arResult['DATA']['TOTAL'] ?>">
                </div>
                <div id="itogoError">

                </div>

            </td>
        </tr>
        <tr>
            <th>Списать со статьи</th>
            <td>
                <select name='reArticle'>
                    <option value=0>Не выбрано</option>
                    <? foreach ($arResult['ARTICLES'] as $id => $article): ?>
                        <option value="<?=$id?>"><?=$article['NAME']?></option>
                    <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Финансовый год статьи списания</th>
            <td>
                <select id="reYear">
                    <?foreach ($arResult['YEARS'] as $year):?>
                        <option <?=($nowYear == $year) ? 'selected' : ''?> value="<?=$year?>">
                            <?=$year?>
                        </option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Сумма списания</th>
            <td>
                <input name="deleteSum" type="text" value="0">
            </td>
        </tr>
        <tr>
            <th>Месяц списания</th>
            <td>
                <select name='reMonth'>
                    <option value="0">Не выбрано</option>
                    <? foreach ($arResult['MONTHS'] as $key => $month): ?>
                        <option value="<?= $key ?>"><?= $month ?></option>
                    <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Месяц зачисления</th>
            <td>
                <select name='nowMonth'>
                    <option value="0">Не выбрано</option>
                    <? foreach ($arResult['MONTHS'] as $key => $month): ?>
                        <option value="<?= $key ?>"><?= $month ?></option>
                    <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                Комментарий
            </th>
            <td>
                <textarea rows="10" cols="45" name="comment"></textarea>
            </td>
        </tr>
        <tr>
            <th>
                Ошибки формы
            </th>
            <td>
                <div id='errorDiv' class = 'none error'>
                    Введите комментарий!
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="form-group">
        <button type="button" class="btn btn-outline-success save">Сохранить</button>
        <button type="button" class="btn btn-outline-danger cancel">Отменить изменения</button>
    </div>
</div>
