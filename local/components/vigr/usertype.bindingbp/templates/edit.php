<?php
?>
<div>
    <select name = 'types'>
        <?foreach ($arResult['DATA']['TYPES'] as $arData):?>
            <option <?if($arData['ID'] == $arResult['DATA']['IB_TYPE']):?>selected<?endif;?> value="<?=$arData['ID']?>"><?=$arData['NAME']?></option>
        <?endforeach;?>
    </select>
</div>
<div style="padding-top: 10px">
    <select name="<?=$arParams['userField']['FIELD_NAME']?>">
        <? include "selectIbs.php";?>
    </select>
</div>


<script>
    BX.ready(function (){
        document.querySelector('[name=types]').addEventListener('change',function (){
            BX.ajax.runComponentAction('vigr:usertype.bindingbp', 'getIbs', {
                'data': {
                   'iblockType':this.value
                },
                mode: 'class'
            }).then(function (response) {
                document.querySelector('[name=UF_BP_IBLOCK_ID]').innerHTML = response.data;
            });
        })
    })
</script>
