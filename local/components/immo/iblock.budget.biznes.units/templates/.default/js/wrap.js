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


    constructor(settings) {
        this.wrap = settings['wrap'];

        if(settings['parent']){
            this.parent = settings.parent;
        }

        if(settings['id']){
            this.id = settings.id;
        }

        if(settings['itemType']){
            this.itemType = settings.itemType;
        }

        this.baseInit();
        this.init();
        //this.getAllData();
    }

    baseInit(){
        let self = this;
        this.sumInput = this.wrap.querySelector('.sumInput');

        this.sumInputEditor = new BX.Currency.Editor({
            'input':this.sumInput,
            'callback' : function (){
                if(!this.fromPercent){
                    self.sumChanger(this);
                    if(self.articlesList){
                        self.getAllData();
                    }
                }
            }
        });

        this.sumInputEditor.fromPercent = false;
    }

    initDistribution(){
        let self = this;
        this.distribution.addEventListener('click',function (){
            self.checkDistribution();
        })
    }

    getFloat(value){
        return parseFloat(value.toFixed(2))
    }

    addError(errorType,message){
        this.errors[errorType] = message;
    }

    deleteError(errorType){
        if(this.errors[errorType]){
            delete this.errors[errorType];
        }
    }

    init() {
        this.startInit = false;
        let self = this;

        this.distribution = this.wrap.querySelector('#distribution');
        this.initDistribution();

        this.freeSumInput = this.wrap.querySelector('#free_sum');

        this.freePercentInput = this.wrap.querySelector('#free_percent');

        this.articlesList = this.wrap.querySelector('#article');
        this.articlesListOptions = this.articlesList.options;

        this.urList = this.wrap.querySelector('#urList');
        this.urListOptions = this.urList.options;

        this.beList = this.wrap.querySelector('#country');
        this.beListOption = this.beList.options;

        this.mainInput = document.querySelector('input[name=UF_CRM_BIZUINIT_WIDGET_2]');

        this.rateList = this.wrap.querySelector('#rate');
        this.rateListOptions = this.rateList.options;

        this.rateListBe = this.wrap.querySelector('#rateBe');
        this.rateListBeOptions = this.rateListBe.options;

        this.errorsDiv = this.wrap.querySelector('.errors');
        this.successDiv = this.wrap.querySelector('.success');

        this.buttonSave = document.querySelector('.ui-entity-section-control .ui-btn.ui-btn-success');

        this.nowMonth = this.wrap.querySelector('#nowMonth').value;
        this.nowYear = this.wrap.querySelector('#nowYear').value;

        this.country = this.wrap.querySelector('#country');

        this.rate = '';
        this.rateBe = '';

        this.rateCurse = this.wrap.querySelector('#rateCurse');

        this.rateCurse.addEventListener('input',function (){
            self.getAllData();
            self.callbackItems(function (item){
                item.setBudget();
            })
        })

        this.initSelects();

        //this.initMonth(document.querySelector('select[name=UF_CRM_1621001664568]'));
        //this.initYear(document.querySelector('select[name=UF_CRM_1612441050]'));
        this.initMainWrap();

        document.querySelector('.updateBudget').addEventListener('click',function (){
            self.checkErrors();
        })


        this.rate = this.rateListOptions[this.rateList.selectedIndex].getAttribute('data-rate');
        this.rateList.addEventListener('change',function (){
            self.rate = self.rateListOptions[this.selectedIndex].getAttribute('data-rate');
            self.checkRates();
            self.getAllData();
        })

        self.rateBe = self.rateListBeOptions[this.rateListBe.selectedIndex].getAttribute('data-rate');
        this.rateListBe.addEventListener('change',function (){
            self.rateBe = self.rateListBeOptions[this.selectedIndex].getAttribute('data-rate');
            self.callbackItems(function (item){
                item.select.value = 0;
                let event = new Event('change');
                item.select.dispatchEvent(event);
                self.maxItems = item.hideSelects();
                item.checkCanAddAndDelete();
            })

            let length = 0;

            self.callbackItems(function (item){
                length++;
                if(length > self.maxItems && length > 1){
                    item.deleteItem();
                }
            })
            self.checkRates();
            self.getAllData();
        })

        this.wrap.querySelectorAll('.be-item.new').forEach(function (item){
            let id = item.getAttribute('data-id');
            if(id >= 0){
                self.items[id] = new Be({
                    'wrap' : item,
                    'id' : id,
                    'itemType' : 'be-item',
                    'parent' : self
                })
                self.selectedItems[id] = true;
                //self.maxItems = self.items[id].hideSelects();
            }
        })

        this.callbackItems(function (item){
            self.maxItems = item.hideSelects();
        })

        this.freeSumInputEditor = new BX.Currency.Editor({
            'input':this.wrap.querySelector('#free_sum')
        });

        this.freePercentInputEditor = new BX.Currency.Editor({
            'input':this.wrap.querySelector('#free_percent')
        });

        this.articlesList.addEventListener('change',function (){
            let data = {
                'article':self.articlesList.value,
                'month':self.month[self.optionsMonth.selectedIndex].text,
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

        this.checkRates();

        if(!document.querySelector('select[name=UF_CRM_1621001664568]')){
            let obs = BX.Crm.EntityEditor.getDefault().getAllControls();
            /*
            for (let key in obs){
                if(
                    obs[key]['_id'] === 'UF_CRM_1621860643902' ||
                    obs[key]['_id'] === 'UF_CRM_1612441050' ||
                    obs[key]['_id'] === 'UF_CRM_1621001664568'
                    //obs[key]['_id'] === 'UF_CRM_BIZUINIT_WIDGET_2'
                ){
                    if(!obs[key]['customInit']){
                        var controlNode = BX.Crm.EntityEditor.getDefault().getAllControls()[key];
                        controlNode.setMode(BX.UI.EntityEditorMode.edit, {notify: true});
                        controlNode.refreshLayout();
                        obs[key]['customInit'] = true;
                    }
                }
            }
            */

            setTimeout(function (){
                self.initMonth(document.querySelector('select[name=UF_CRM_1621001664568]'));
                self.initYear(document.querySelector('select[name=UF_CRM_1612441050]'));
                document.querySelectorAll('[name=UF_CRM_1621860643902]')[1].addEventListener('click',function (){
                    self.getAllData();
                })
                self.getAllData();
                self.callbackItems(function (item){
                    if(item.mode === 'noP'){
                        item.getProductsAjax();
                    }
                })
                self.startInit = true;
            },500)

        }else{
            setTimeout(function (){
                self.initMonth(document.querySelector('select[name=UF_CRM_1621001664568]'));
                self.initYear(document.querySelector('select[name=UF_CRM_1612441050]'));
                document.querySelectorAll('[name=UF_CRM_1621860643902]')[1].addEventListener('click',function (){
                    self.getAllData();
                })
                let event = new Event('change');
                this.urList.dispatchEvent(event);
            })

        }

        setTimeout(function (){
            self.getAllData();
            //let event = new Event('change');
            //this.urList.dispatchEvent(event);
            self.startInit = true;
        },900)
    }

    buildByItem(item){
        let self = this;
        let data = {
            'sum' : item.sumInputEditor.value,
            'percent' : item.percentInputEditor.value,
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

    getAllData(){
        let self = this;

        let data = {
            //'draft' : this.draft,
            'random':Math.random(),
            'rate' : this.rate ,
            'distr':this.distribution.checked,
            'country' : this.country.value,
            'rateBe' : this.rateBe,
            'article' : this.articlesList.value,
            'rateCurse' : this.rateCurse.value,
            'sum' : this.sumInputEditor.input.value,
            'ur' : this.urList.value,
            'freeSum' : this.freeSumInput.value,
            'freePercent' : this.freePercentInput.value,
            'items' : {}
        };

        this.setFreeSum();

        if(this.yearOptions){
            data['year'] = this.yearOptions[this.year.selectedIndex].text;
        }else{
            data['year'] = this.month.value;
        }

        if(this.optionsMonth){
            data['month'] = this.optionsMonth[this.month.selectedIndex].text;
        }else{
            data['month'] = this.month.value;
        }

        this.callbackItems(function (item){
            data['items'][item.id] = self.buildByItem(item);
        })

        this.mainInput.value = JSON.stringify(data);
        this.checkErrors();
    }

    checkErrors(){
        if(this.sumInputEditor.value <= 0){
            this.addError('sum','Общая сумма не может быть меньше или равна 0!')
        }else{
            this.deleteError('sum');
        }

        if(this.freeSumInputEditor.input.value !== '0'){
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

        let sum = 0;

        if(this.distribution.checked){
            this.callbackItems(function (item){
                if(item.id>1){
                    sum+=parseFloat(item.sumInputEditor.value);
                }
            })
            if(length>0){
                if(sum === parseFloat(this.sumInputEditor.value)){
                    this.deleteError('rasp');
                }else{
                    this.addError('rasp','Ошибка распределения: введенные значения не могут быть распределены на указанное количество БЕ в равных частях. Скорректируйте данные вручную');
                }
            }
        }else{
            this.deleteError('rasp');
        }

        if(length >= 1){
            this.deleteError('Items');
        }else{
            this.addError('Items','Требуется выбрать как минимум 1 БЕ');
        }

        for(let key in this.errors){
            str+=this.errors[key]+'<br>';
        }

        this.errorsDiv.innerHTML = str;

        if(str !== '' && !document.querySelectorAll('[name=UF_CRM_1621860643902]')[1].checked){
            this.buttonSave.disabled = true;
        }else{
            this.buttonSave.disabled = false;
        }

    }

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
                self.rateCurse.value = response.data;
            }, function (response) {

            })
    }

    checkRates(){
        if(this.rate === this.rateBe){
            this.rateCurse.disabled = true;
            this.rateCurse.value = 1;
        }else if(this.country[this.country.selectedIndex].text === 'Россия'){
            this.getRate();
            this.rateCurse.disabled = false;
        }else{
            this.rateCurse.disabled = false;
        }
    }

    setFreeSum(){
        let allSum = this.sumInputEditor.value;

        let sum = 0;

        this.callbackItems(function (item) {
            sum += parseFloat(item.sumInputEditor.value);
        })

        this.freeSumInputEditor.input.value = allSum - sum;
        setValueEditor(this.freeSumInputEditor);
        this.setFreePercent();
    }

    setFreePercent(){
        let allPercent = 100;

        this.callbackItems(function (item) {
            allPercent -= parseFloat(item.percentInputEditor.value);
        })

        this.freePercentInputEditor.input.value = allPercent;
    }

    checkCanAddAndDelete(){
        let length = 0;

        this.callbackItems(function () {
            length++;
        })

        let disDelete = false;
        if(length <=1){
            disDelete = true;
        }

        this.callbackItems(function (item) {
            item.deleteInput.disabled = disDelete;
        })

        let addDis = false;

        if(length >= this.maxItems){
            addDis = true;
        }

        this.callbackItems(function (item) {
            item.addInput.disabled = addDis;
        })

        this.setFreeSum();
        this.checkDistribution();
        this.getAllData();
    }

    callbackItems(callback) {
        for (let key in this.items) {
            callback(this.items[key]);
        }
    }

    getFreeSum(nowId) {
        let allSum = this.sumInputEditor.value;

        let sum = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId) {
                sum += parseFloat(item.sumInputEditor.value);
            }
        })

        return parseFloat((allSum - sum).toFixed(2));
    }

    getFreePercent(nowId) {
        let allPercent = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId) {
                allPercent += parseFloat(item.percentInputEditor.value);
            }
        })

        return parseFloat((100 - allPercent).toFixed(2));
    }

    initMainWrap(){

        let self = this;
        this.urList.addEventListener('change',function (){
            let country = self.urListOptions[this.selectedIndex].getAttribute('data-countryid');
            self.country.value = country;

            let rate = self.country[self.country.options.selectedIndex].getAttribute('data-rate');


            self.rateList.value = rate;

            self.rate = self.rateList[self.rateList.selectedIndex].getAttribute('data-rate');

            self.checkRates();
            self.getAllData();
        })
    }

    resetKey(oldId, newId) {
        Object.defineProperty(this.items, newId,
            Object.getOwnPropertyDescriptor(this.items, oldId));
        delete this.items[oldId];
    }

    sumChanger(editor){
        if(this.startInit){
            this.callbackItems(function (item){
                item.setSum();
            })
            this.setFreeSum();
        }
    }

    checkDistribution(){
        let self = this;

        if(this.distribution.checked){
            let length = 0;
            this.callbackItems(function (item){
                if(item.id > 1){
                    length++;
                }
            })

            this.callbackItems(function (item){
                item.sumInputEditor.input.value = self.getFloat(self.sumInputEditor.value / length);
                setValueEditor(item.sumInputEditor);
            })
        }

        this.callbackItems(function (item){
            item.setPercent();
            if(item.id > 1 && item.distribution){
                item.checkDistribution();
            }
        })

        this.callbackItems(function (item){
            item.checkInputs();
        })
        this.getAllData();
    }

    getMonthNumber(name){
        let month = {
            'Май' : '1',
            'Июнь' : '2',
            'Июль' : '3',
            'Август' : '4',
            'Сентябрь' : '5',
            'Октябрь' : '6',
            'Ноябрь' : '7',
            'Декабрь' : '8',
            'Январь' : '9',
            'Февраль' : '10',
            'Март' : '11',
            'Апрель' : '12',
        }

        return parseInt(month[name]);
    }

    initMonth(select){
        let self = this;
        this.month = select;
        this.month.value = 35;
        this.optionsMonth = select.options;
        this.month.disabled = true;
        this.month.addEventListener('change',function (){
            let month = self.optionsMonth[this.selectedIndex].text;

            let monthNumber = self.getMonthNumber(month);
            /*
            console.log(monthNumber);
            console.log(self.getMonthNumber(self.nowMonth));

            if(self.nowYear < self.year.value){
                self.addError('month','Невозможно выбрать месяц след финансового года!')
            }else{
                self.deleteError('month')
            }
            */

            if(self.getMonthNumber(self.nowMonth) < monthNumber){
                self.addError('month','Невозможно создать заявку на след месяц!')
            }else{
                self.deleteError('month')
            }

            self.getAllData();
        })
    }

    initYear(select){
        let self = this;
        this.year = select;
        this.year.disabled = true;
        this.nowYearNumber = 0;

        for (let key in select.options){
            if(select.options[key].text === this.nowYear){
                self.nowYearNumber = select.options[key].value;
                this.year.value = select.options[key].value;
            }
        }

        this.yearOptions = select.options;

        this.year.addEventListener('change',function (){
            if(this.value !== self.nowYearNumber){
                self.addError('year','Выбрать возможно только текущий финансовый год!')
            }else{
                self.deleteError('year');
            }
            self.getAllData();
        })
    }

    initSelects(){
        //console.log(this);
    }
}


function setValueEditor(editor){
    editor.value = BX.Currency.MoneyEditor.getUnFormattedValue(editor.input.value, editor.currency);
    editor.input.value = BX.Currency.MoneyEditor.getFormattedValue(editor.value, editor.currency);
}