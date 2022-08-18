<input type="hidden" name="<?= $arParams['userField']['FIELD_NAME'] ?>" class="main-field-legal"
       value="<?= $arParams['userField']['VALUE'] ?>">
<select disabled class="legal-select" multiple>
    <? foreach ($arResult['LEGAL_DATA'] as $hlRowIndex => $hlRow): ?>
        <option value="<?= $hlRow['ID'] ?>" <?= in_array($hlRow['ID'], $arResult['FIELD_VALUE']['legalSelectArray']) ? 'selected' : '' ?>><?= $hlRow['UF_COMPANY_NAME'] ?>
            (<?= $hlRow['UF_SNILS'] ?>)
        </option>
    <? endforeach; ?>
</select>