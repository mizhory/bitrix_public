<?php

?>
<input  name="<?= $arParams['userField']['FIELD_ID'] ?>"  type="text" id = 'mainValueD'>
<div>
    Первый день отпуска :
    <input data-type = 'start' class="start bx-lists-input-calendar" type="text"
           onclick="BX.calendar({node: this.parentNode, field: this, bTime: false, bHideTime: false});" value="<?=(new DateTime())->format('d.m.Y')?>"
    >
    Последний день отпуска:
    <input data-type = 'end'  class="end bx-lists-input-calendar" type="text"
           onclick="BX.calendar({node: this.parentNode, field: this, bTime: false, bHideTime: false});" value=""
    >
</div>
<div style="width: 22%">
    Кол-во дней отпуска : <input class = 'days' type="text" value=0>
</div>

<script>
    class windowCheckerFields{
        button;

        fieldsStatus = {};

        constructor() {
            this.button = document.getElementById('blog-submit-button-save');
        }

        setStatus(field,status){
            this.fieldsStatus[field] = status;
        }

        checkStatus(){
            let haveErrors = false;
            try {
                for (let key in this.fieldsStatus){
                    if(!this.fieldsStatus[key]){
                        haveErrors = true;
                        throw new Error();
                    }
                }
            }catch (e){

            }
            this.button.disabled = haveErrors;
        }
    }



    BX.ready(function (){
        //alert('Просим заполнять заявления на отпуск не позднее двух недель до начала отпуска!');

        document.querySelectorAll('.bx-lists-input-calendar').forEach(function (item){
            item.addEventListener('change',function (){
                getData('input');
            })
        })

        document.querySelector('.days').addEventListener('input',function (){
            getData('day');
        })
        window.checker = new windowCheckerFields();

        getData = function (type){
            let dateOne = document.querySelector('.start').value;
            let dateTwo = document.querySelector('.end').value;
            let days = document.querySelector('.days').value;
            let data = {};

            let nowDate = new Date();


            let arNowD = document.querySelector('.start').value.split('.');
            let arEndD = document.querySelector('.end').value.split('.');

            let startDate = new Date(arNowD[2],arNowD[1] - 1,arNowD[0]);
            //startDate.setMinutes(1);
            let endDate = new Date(arEndD[2],arEndD[1] - 1,arEndD[0]);
            nowDate.setHours(0);
            nowDate.setMinutes(0);
            nowDate.setSeconds(0);
            nowDate.setMilliseconds(0);

            switch (type){
                case 'input':
                    if(dateOne !== '' && dateTwo !== ''){
                        data['start'] = dateOne;
                        data['end'] = dateTwo;
                        data['mode'] = 'interval';
                        document.querySelector('.days').disabled = true;
                    }else{
                        document.querySelector('.days').disabled = false;
                        document.querySelector('.days').value = 0;
                    }
                    break;
                case 'day':
                    if(dateOne === ''){
                        data['end'] = dateTwo;
                        data['mode'] = 'start';
                    }else{
                        data['start'] = dateOne;
                        data['mode'] = 'end';
                    }
                    data['days'] = days;
                    break;
            }
            if(endDate.getTime() >= startDate.getTime()){
                BX.ajax.runComponentAction('vigr:usertype.vacationdays', 'getDays', {
                    data,
                    mode: 'class'
                }).then(function (response) {
                    switch (data['mode']){
                        case 'interval':
                            document.querySelector('.days').value = response.data;
                            break;
                        case 'start':
                            document.querySelector('.start').value = response.data;
                            break;
                        case 'end':
                            document.querySelector('.end').value = response.data;
                            break;
                    }
                    document.getElementById('mainValueD').value = JSON.stringify({
                        'start':document.querySelector('.start').value,
                        'end':document.querySelector('.end').value,
                        'days':document.querySelector('.days').value,
                    })
                });
            }


            document.getElementById('mainValueD').value = JSON.stringify({
                'start':document.querySelector('.start').value,
                'end':document.querySelector('.end').value,
                'days':document.querySelector('.days').value,
            })
            window.checker.setStatus('days',false);
            if(parseInt(days) === 0 || endDate.getTime() < startDate.getTime() || nowDate.getTime() > startDate.getTime()){
                window.checker.setStatus('days',false);
                //document.getElementById('blog-submit-button-save').disabled = true;
                alert('Выберите корректные даты!');
            }else{
                window.checker.setStatus('days',true);
                //document.getElementById('blog-submit-button-save').disabled = false;
            }
            window.checker.checkStatus();
        }

        document.getElementById('blog-submit-button-save').disabled = true;
    })




</script>