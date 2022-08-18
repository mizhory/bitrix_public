<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$signer = new \Bitrix\Main\Security\Sign\Signer;
$APPLICATION->setTitle('Загрузка файлов бюджета');
$year = \Immo\Integration\Budget\BudgetHelper::defineFinancialYear();

$monthStart = \Immo\Integration\Budget\BudgetHelper::getMonthStart() - 1;
if ($monthStart <= 0) {
    $monthStart = 12;
}

$curDate = new \Bitrix\Main\Type\Date();

?>
<input id = 'sign' type="hidden" value="<?=$signer->sign('files',md5('18.10.2021'));?>">

<form>
    <div class="form-group">
        <label for="file-uploader">Загрузка файлов бюджета</label>
        <br>
        <input accept=".xlsx" type="file" class="form-control-file" id="file-uploader" multiple>
        Финансовый год:
        <select id = 'year' name = 'year'>
            <option value = '<?=$year?>'>Текущий</option>
            <?if($monthStart == $curDate->format('m')):?>
                <option value = '<?=$year+1?>'>Следущий</option>
            <?endif;?>
        </select>
    </div>

    <div class="form-group" role="status" id = 'files'>

    </div>
    <div class="form-group" id = 'errors'>
    </div>
</form>