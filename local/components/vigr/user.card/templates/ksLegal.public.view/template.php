<? foreach ($arResult['LEGAL_PUBLIC_DATA'] as $company): ?>
    <div x-data="{open<?= $company['ID'] ?>:true,action<?= $company['ID'] ?>:'Свернуть'}" class="mb-2">
        <div @click="open<?= $company['ID'] ?> = !open<?= $company['ID'] ?>;
    action<?= $company['ID'] ?> = open<?= $company['ID'] ?> === true?'Свернуть':'Развернуть'">
            <div class="flex bg-white pt-4 pb-4 pl-4 justify-between data-row">
                <div class="flex justify-center items-center">
                    <?= $company['UF_COMPANY_NAME'] ?>
                </div>
                <div class="ml-4 mr-4">
                    <button class="button"><span x-text="action<?= $company['ID'] ?>"></span></button>
                </div>
            </div>
        </div>
        <div x-show="open<?= $company['ID'] ?>">
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Вид работы
                </div>
                <div class="ml-4">
                    <?= $company['UF_WORK_TYPE'] ?>
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Дата приема
                </div>
                <div class="ml-4">
                    <?= $company['UF_START_DATE']->format('d.m.Y') ?>
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Структурное подразделение
                </div>
                <div class="ml-4">
                    <?= $company['UF_DEPARTMENT'] ?>
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Оклад по 1С
                </div>
                <div class="ml-4">
                    <?= $company['UF_SALARY_FIX'] ?> руб.
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Заработная плата по 1С
                </div>
                <div class="ml-4">
                    <?= $company['UF_SALARY_TOTAL'] ?> руб.
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Коэффициент участия
                </div>
                <div class="ml-4">
                    <?= $company['UF_PART_KOEF'] ?>
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Количество календарных дней отпуска
                </div>
                <div class="ml-4">
                    <?= $company['UF_VACATION_NUM'] ?>
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Количество календарных дней отпуска фактическое
                </div>
                <div class="ml-4">
                    <?= $company['UF_VACATION_NUM_FACT'] ?>
                </div>
            </div>
            <div class="flex mt-2 mb-2 data-row bg-white pt-2 pb-2">
                <div class="flex items-center mr-4 w-32 ml-4">
                    Количество отработанных дней в текущем месяце
                </div>
                <div class="ml-4">
                    <?= $company['UF_WORK_DAYS'] ?>
                </div>
            </div>
        </div>
    </div>
<? endforeach; ?>
<? if (count($arResult['LEGAL_PUBLIC_DATA']) === 0) { ?>
    <div>Информация недоступна</div>
<? } ?>

