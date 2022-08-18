/**
 *  Базовая обертка над всем полем
 *
 * */
class WrapItems {
    selectedItems = {};

    items = {};

    errors = {};

    wrap;

    maxItems;

    startInit;

    rate;

    parent;

    rateBe;

    id;

    itemType;

    changerMode;

    constructor(settings) {

        this.wrap = settings['wrap'];

        if (settings['parent']) {
            this.parent = settings.parent;
        }

        if (settings['id']) {
            this.id = settings.id;
        }

        if (settings['itemType']) {
            this.itemType = settings.itemType;
        }



        this.startInit();
    }

    startInit() {
        try {
            this.initFields();
        } catch (error) {
            console.log('Ошибка поля БЕ!');
            console.log(error);
        }
    }

    /**
     * инит связанных полей
     * */

    initFields() {
        this.fieldsIds = JSON.parse(document.querySelector('.fieldsJ').value);

        let self = this;
        let obs = BX.Crm.EntityEditor.getDefault().getAllControls();
        for (let key in obs) {
            if (
                this.fieldsIds['keys'][obs[key]['_id']]
            ) {
                var controlNode = BX.Crm.EntityEditor.getDefault().getAllControls()[key];
                controlNode.setMode(BX.UI.EntityEditorMode.edit, {notify: true});
                controlNode.refreshLayout();
            }
        }
        setTimeout(function (){
            try {
                self.initHtmlAndMainElements();
            }catch (error) {
                console.log('Ошибка поля БЕ!');
                console.log(error);
            }
        },1000)
    }

    /**
     * инит хтмл действий
     * */

    initHtmlAndMainElements() {
        this.startInitCompleted = false;
        this.distribution = this.wrap.querySelector('#distribution');

        this.freeSumInput = this.wrap.querySelector('#free_sum');

        this.freePercentInput = this.wrap.querySelector('#free_percent');

        this.sumInput = this.wrap.querySelector('.sumInput');

        this.articlesList = this.wrap.querySelector('#article');
        this.articlesListOptions = this.articlesList.options;

        this.urList = this.wrap.querySelector('#urList');
        this.urListOptions = this.urList.options;

        this.beList = this.wrap.querySelector('#country');
        this.beListOption = this.beList.options;

        this.mainInput = document.querySelector('input[name=UF_CRM_BIZUINIT_WIDGET_2]');

        this.rateList = this.wrap.querySelector('#rate');
        this.rateListOptions = this.rateList.options;
        this.rate = this.rateListOptions[this.rateList.selectedIndex].getAttribute('data-rate');

        this.rateListBe = this.wrap.querySelector('#rateBe');
        this.rateListBeOptions = this.rateListBe.options;
        this.rateBe = this.rateListBeOptions[this.rateListBe.selectedIndex].getAttribute('data-rate');

        this.errorsDiv = this.wrap.querySelector('.errors');
        this.successDiv = this.wrap.querySelector('.success');

        this.buttonSave = document.querySelector('.ui-entity-section-control .ui-btn.ui-btn-success');

        this.nowMonth = this.wrap.querySelector('#nowMonth').value;
        this.nowYear = this.wrap.querySelector('#nowYear').value;

        this.country = this.wrap.querySelector('#country');

        this.rateCurse = this.wrap.querySelector('#rateCurse');

        this.year = document.querySelector('select[name='+this.fieldsIds['values']['finYear']+']');

        this.yearOptions = this.year.options;

        this.month = document.querySelector('select[name='+this.fieldsIds['values']['month']+']');
        if(document.querySelector('#FD').value !== 'Y'){
            this.month.disabled = true;
            this.year.disabled = true;
        }

        this.monthOptions = this.month.options;

        this.errorsDiv = this.wrap.querySelector('.errors');

        if(location.href.match('/details/0/')){
            for(let key in this.monthOptions){
                if(this.monthOptions[key].text === this.nowMonth){
                    this.month.value = this.monthOptions[key].value;
                }
            }

            for(let key in this.yearOptions){
                if(this.yearOptions[key].text === this.nowYear){
                    this.year.value = this.yearOptions[key].value;
                }
            }
            this.distribution.checked = true;
        }

        this.initSelectors();
        this.initEditors();
        this.initItems();
        this.checkRates();
        this.startInitCompleted = true;
    }

    /**
     * проверка валюта / валюта БЕ
     * */

    checkRates(){
        if(this.country[this.country.selectedIndex]){
            if(!this.notNeedCheckRate){
                this.getRate();
                if(this.rate === this.rateBe){
                    this.rateCurse.disabled = true;
                    this.rateCurse.value = 1;
                }else if(this.country[this.country.selectedIndex].text === 'Россия'){

                    this.rateCurse.disabled = false;
                }else{
                    this.rateCurse.disabled = false;
                }
            }
        }


    }

    /**
     * создание БЕ + его инит
     * */

    initItems(){
        let self = this;
        this.wrap.querySelectorAll('.be-item.new').forEach(function (item){
            if(!item.closest('.cloneBlocks')){
                let id = item.getAttribute('data-id');

                if(id >= 0){
                    self.items[id] = new Be({
                        'wrap' : item,
                        'id' : id,
                        'itemType' : 'be-item',
                        'parent' : self
                    })
                    self.selectedItems[id] = true;
                    self.items[id].initProductsFunctional();
                }
            }

        })

        this.hideSelects();
        this.callbackItems(function (item){
            //item.checkCanAddAndDelete();
        })
        this.notNeedCheckRate = true;
        let event = new Event('change');
        this.urList.dispatchEvent(event);
        this.notNeedCheckRate = false;
        this.getAllData();
    }

    /**
     * смена ключа обьекта
     * */

    resetKey(oldId, newId) {
        Object.defineProperty(this.items, newId,
            Object.getOwnPropertyDescriptor(this.items, oldId));
        delete this.items[oldId];
    }

    /**
     * скрытие выбранных селектов
     * */

    hideSelects() {
        let self = this;

        this.callbackItems(function (item){
            let length = 0;
            item.selectOptions.forEach(function (option) {
                if (option.value > 0 && option.getAttribute('data-rate') !== self.rateBe) {
                    if(self.selectedItems[option.value]){
                        length++;
                    }
                    option.style.display = 'none';
                } else {
                    if (option.value > 0) {
                        if(!self.selectedItems[option.value]){
                            option.style.display = '';
                        }else{
                            option.style.display = 'none';
                        }
                    }else{
                        option.style.display = 'none';
                    }

                    if(option.value > 0){
                        length++;
                    }
                }
            })
            self.maxItems = length;
        })

    }

    /**
     * инит селекторов
     * */

    initSelectors() {
        let self = this;

        document.querySelectorAll('[name=UF_CRM_1621860643902]')[1].addEventListener('change',function (){
            self.getAllData();
        })

        this.rateListBe.addEventListener('change',function (){
            self.rateBe = self.rateListBeOptions[this.selectedIndex].getAttribute('data-rate');

            self.callbackItems(function (item){
                item.select.value = 0;
                let event = new Event('change');
                item.select.dispatchEvent(event);
                item.checkCanAddAndDelete();
            })


            let length = 0;
            self.hideSelects();
            self.callbackItems(function (item){
                length++;
                if(length > 1){
                    item.deleteItem();
                }
            })

            self.callbackItems(function (item){
                item.checkCanAddAndDelete();
            });

            self.checkRates();
            self.getAllData();
        })

        this.urList.addEventListener('change',function (){
            self.country.value =  self.urListOptions[this.selectedIndex].getAttribute('data-countryid');

            if(!self.notNeedCheckRate){
                self.rateList.value = self.country[self.country.options.selectedIndex].getAttribute('data-rate');
                self.rateListBe.value = self.country[self.country.options.selectedIndex].getAttribute('data-rate');
            }

            self.rate = self.rateList[self.rateList.selectedIndex].getAttribute('data-rate');
            self.rateBe = self.rateListBe[self.rateListBe.selectedIndex].getAttribute('data-rate');

            let event = new Event('change');

            self.rateListBe.dispatchEvent(event);

            self.checkRates();
            self.getAllData();
        })

        this.articlesList.addEventListener('change',function (){
            let data = {
                'article':self.articlesList.value,
                'month':self.month[self.monthOptions.selectedIndex].text,
                'year':self.year[self.yearOptions.selectedIndex].text,
                'be':[]
            }

            self.callbackItems(function (item){
                data['be'].push(item.id)
            })

            BX.ajax.runComponentAction('vigr:budget.be', 'getRates', {
                mode: 'class',
                data: data
            }).then(function (response){
                self.callbackItems(function (item){
                    item.budget = 0;
                    if(response.data[item.id]){
                        item.budget = response.data[item.id];
                    }

                    item.setBudget();
                })
                self.getAllData();
            })
        })

        this.rateList.addEventListener('change',function (){
            self.rate = self.rateListOptions[this.selectedIndex].getAttribute('data-rate');

            self.checkRates();
            self.callbackItems(function (item){
                item.setBudget();
            });
            self.getAllData();
        })

        this.rateCurse.addEventListener('input',function (){
            self.getAllData();
            self.callbackItems(function (item){
                item.setBudget();
            })
        })

        this.distribution.addEventListener('click',function (){
            self.checkDistribution();
        })
    }

    /**
     * инит хтмл действий
     * */

    getRate(){
        let self = this;
        BX.ajax.runComponentAction('vigr:budget.be', 'getRate', {
            mode: 'class',
            data: {
                'currency':this.rateBe,
                'currencyPayed' : this.rate
            }
        }).then(
            function (response) {
                self.rateCurseEditor.value = response.data;
                self.callbackItems(function (item){
                    item.setBudget();
                })
            }, function (response) {

            })
    }

    /**
     * создает imask для инпута
     * */

    createMask(input,callback,scale = 2){
        let mask = IMask(
            input,
            {
                scale:scale,
                mask: Number,
                thousandsSeparator: ' ',
                radix:','
            });
        if(callback){
            mask.on('complete',callback);
        }

        return mask;
    }

    /**
     * создание числовых редакторов
     * */

    initEditors(){
        let self = this;
        this.sumInputEditor = this.createMask(this.sumInput,function (){
            self.sumChanger();
        });

        this.freePercentInputEditor =
            this.createMask(this.wrap.querySelector('#free_percent'),function (){});

        this.freeSumInputEditor =
            this.createMask(this.wrap.querySelector('#free_sum'),function (){});

        this.rateCurseEditor = this.createMask(this.rateCurse,function (){
            self.callbackItems(function (item){
                item.setBudget();
            });
        },4);
    }

    /**
     * возвращает свободный процент
     * */

    getFreePercent(nowId) {
        let allPercent = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId && item.id > 1) {
                allPercent += parseFloat(item.percentInputEditor.unmaskedValue);
            }
        })

        return parseFloat((100 - allPercent).toFixed(2));
    }

    /**
     * парсинг до 2х после запятой
     * */

    getFloat(value){
        return parseFloat(value.toFixed(2))
    }

    /**
     * возвращает свободную сумму
     * */

    getFreeSum(nowId) {
        let allSum = this.sumInputEditor.unmaskedValue;

        let sum = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId && item.sumInputEditor) {
                sum += parseFloat(item.sumInputEditor.unmaskedValue);
            }
        })

        return parseFloat((allSum - sum).toFixed(2));
    }

    /**
     * установка свободного процента
     * */

    setFreePercent(){
        let allPercent = 100;

        this.callbackItems(function (item) {
            if(item.id>1){
                allPercent -= parseFloat(item.percentInputEditor.unmaskedValue);
            }
        })

        this.freePercentInputEditor.value = ''+allPercent;
        this.getAllData();
    }

    /**
     * установка свободной суммы
     * */

    setFreeSum(){
        let allSum = this.sumInputEditor.unmaskedValue;

        let sum = 0;

        this.callbackItems(function (item) {
            if(item.id > 1){
                sum += parseFloat(item.sumInputEditor.unmaskedValue);
            }
        })

        this.freeSumInputEditor.value = (''+(allSum - sum).toFixed(2));

        if(this.freeSumInputEditor.unmaskedValue === ''){
            this.freeSumInputEditor.value = '0';
        }
        this.setFreePercent();
    }

    /**
     * формирует все данные
     * */

    getAllData(){
        let self = this;

        let data = {
            'random':Math.random(),
            'rate' : this.rate ,
            'distr':this.distribution.checked,
            'country' : this.country.value,
            'rateBe' : this.rateBe,
            'article' : this.articlesList.value,
            'rateCurse' : this.rateCurseEditor.unmaskedValue,
            'sum' : this.sumInputEditor.unmaskedValue,
            'ur' : this.urList.value,
            'freeSum' : this.freeSumInputEditor.unmaskedValue,
            'freePercent' : this.freePercentInputEditor.unmaskedValue,
            'items' : {}
        };

        data['year'] = this.nowYear;

        data['month'] = this.nowMonth;

        this.callbackItems(function (item){
            if(item.id>1){
                data['items'][item.id] = self.buildByItem(item);
            }
        })
        this.mainInput.value = JSON.stringify(data);
        this.checkErrors();
    }

    /**
     * формирует данные по БЕ / продукту БЕ
     * */

    buildByItem(item){
        let self = this;

        let data = {
            'sum' : item.sumInputEditor.unmaskedValue,
            'percent' : item.percentInputEditor.unmaskedValue,
            'mode' : item.mode,
            'id' : item.id,
            'items' : {}
        };

        if(item.distribution){
            data['distr'] = item.distribution.checked;
        }

        item.callbackItems(function (item){
            if(item.id > 1){
                data['items'][item.id] = self.buildByItem(item);
            }
        })

        return data;
    }

    /**
     * Проверка на ошибки
     * */

    checkErrors(){
        if(this.urList[this.urList.selectedIndex].value === 'N'){
            this.addError('plat','Не выбран плательщик!')
        }else{
            this.deleteError('plat');
        }

        if(this.articlesList[this.articlesList.selectedIndex].value === 'N'){
            this.addError('ar','Не выбрана статья!')
        }else{
            this.deleteError('ar');
        }

        if(this.rateListBe[this.rateListBe.selectedIndex].value === 'N'){
            this.addError('rate','Не выбрана валюта заявки!')
        }else{
            this.deleteError('rate');
        }

        if(this.sumInputEditor.unmaskedValue <= 0){
            this.addError('sum','Общая сумма не может быть меньше или равна 0!')
        }else{
            this.deleteError('sum');
        }


        if(this.freeSumInputEditor.unmaskedValue !== '0'){
            this.addError('BEProblemSum','Общая сумма не была распределена или распределена не полностью, пожалуйста, скорректируйте данные')
        }else{
            this.deleteError('BEProblemSum');
        }

        let length = 0;

        let str = '';

        this.callbackItems(function (item){
            str += item.checkErrors();
            if(item.id>1){
                length++;
            }
        })

        if(length >= 1){
            this.deleteError('Items');
        }else{
            this.addError('Items','Требуется выбрать как минимум 1 БЕ');
        }

        for(let key in this.errors){
            str+=this.errors[key]+'<br>';
        }

        if(this.errorsDiv){
            this.errorsDiv.innerHTML = str;
        }

        if(this.buttonSave){
            if(document.querySelectorAll('[name='+this.fieldsIds['values']['draft']+']')[1].checked || str === ''){
                this.buttonSave.disabled = false;
            }else{
                this.buttonSave.disabled = true;
            }
        }

    }

    /**
     * Проверка выбрано ли равномерное распределение
     * */

    checkDistribution(){
        let self = this;

        if(this.distribution.checked){
            let length = 0;
            this.callbackItems(function (item){
                if(item.id > 1){
                    length++;
                }
            })

            let value = self.getFloat(100 / length);

            this.callbackItems(function (item){
                if(item.id > 1){
                    item.percentInputEditor.value = ''+value;
                    item.percentChanger();
                }
            })
        }

        this.callbackItems(function (item){
            item.checkInputs();
        })

        this.setFreeSum();
        this.getAllData();
    }

    /**
     * Коллбэк при изменении суммы
     * */

    sumChanger(){
        if(this.distribution.checked){
            this.callbackItems(function(item){
                if(item.id>1){
                    item.sumInputEditor.value = (item.parent.sumInputEditor.unmaskedValue /
                        100 * item.percentInputEditor.unmaskedValue).toFixed(2);
                }
            })
        }

        this.setFreeSum();
    }

    /**
     * Добавить ошибку
     * */

    addError(errorType,message){
        this.errors[errorType] = message;
    }

    /**
     * Удалить ошибку
     * */

    deleteError(errorType){
        if(this.errors[errorType]){
            delete this.errors[errorType];
        }
    }

    /**
     * проходит по всем айтемам сущеости и применяет ф-цию коллбэк
     * */

    callbackItems(callback) {
        for (let key in this.items) {
            callback(this.items[key]);
        }
    }
}

function getFloat(value){
    return parseFloat(value.toFixed(2))
}