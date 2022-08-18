<?php

?>
<input type="hidden" value="<?=$arParams['userField']['VALUE']?>" class = 'main' name = '<?=$arParams['userField']['FIELD_NAME']?>'>
<div style="padding-bottom: 10px">
    <input  <?if($arResult['PARAMS']['type'] == 'be'):?>checked<?endif;?>  name  = 'type' value = 'be' type="radio">БЕ
    <input <?if($arResult['PARAMS']['type'] == 'companies'):?>checked<?endif;?> name  = 'type' value = 'companies' type="radio">Юрлицо
</div>
<div>
    <select name="select">
        <?include 'selectIbs.php'?>
    </select>
</div>

<script>
    BX.ready(function (){
        writeData = function (){
            let type = document.querySelector('[name=type]:checked').value;
            let value = document.querySelector('[name=select]').value;

            document.querySelector('.main').value = JSON.stringify({
                'type':type,
                'value':value
            })
        }

        document.querySelector('[name=select]').addEventListener('change',function (){
            writeData();
        })

        document.querySelectorAll('[name=type]').forEach(function (item){
            item.addEventListener('change',function (){
                BX.ajax.runComponentAction('vigr:usertype.beandul', 'getData', {
                    'data': {
                        'type':this.value
                    },
                    mode: 'class'
                }).then(function (response) {
                    document.querySelector('[name=select]').innerHTML = response.data;
                    writeData();
                });
            })
        })
    })
</script>

