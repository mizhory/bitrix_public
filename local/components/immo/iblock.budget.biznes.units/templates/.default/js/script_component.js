if (typeof BX.Currency.Editor !== 'undefined') {
    BX.Currency.Editor.defaultFormat.DEC_POINT = ',';
}

class WrapItemsIblock extends WrapItems {
    mainInputSelector = '';

    constructor(settings) {
        super(settings);

        this.mainInput = document.querySelector(settings['mainInputSelector']);
        this.props = settings['props'];
        this.draftId = settings['draftId'];
        this.currentCurseRate = settings['cursRate'];

        this.settings = settings;

        this.canSave = true;
        this.initEvents();
    }

    init() {
        this.startInit = false;

        this.distribution = this.wrap.querySelector('#distribution');
        this.initDistribution();

        this.freeSumInput = this.wrap.querySelector('#free_sum');

        this.freePercentInput = this.wrap.querySelector('#free_percent');

        this.articlesList = this.wrap.querySelector('#article');
        this.articlesListOptions = this.articlesList.options;

        // this.beList = this.wrap.querySelector('#country');
        // this.beListOption = this.beList.options;

        this.rateList = this.wrap.querySelector('#rate');
        this.rateListOptions = this.rateList.options;

        this.rateListBe = this.wrap.querySelector('#rateBe');
        this.rateListBeOptions = this.rateListBe.options;

        this.errorsDiv = this.wrap.querySelector('.errors');
        this.successDiv = this.wrap.querySelector('.success');

        this.buttonSave = document.querySelector('#workarea-content .bx-buttons input[name="save"]');

        this.nowMonth = this.wrap.querySelector('#nowMonth').value;
        this.nowYear = this.wrap.querySelector('#nowYear').value;

        this.country = this.wrap.querySelector('#country');

        this.rate = '';
        this.rateBe = '';

        this.rateCurse = this.wrap.querySelector('#rateCurse');
    }

    baseInit(){
        let self = this;
        this.sumInput = this.wrap.querySelector('.sumInput');

        this.sumInputEditor = new BX.Currency.Editor({
            'input':this.sumInput,
            currency: {

            },
            'callback' : function (){
                if (this.value.toString().includes('.')) {
                    let arValue = this.value.toString().split('.');
                    this.value = [arValue.shift(), arValue.pop().slice(0, 2)].join('.');
                }
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

    getRate(){
        console.log('this.rateBe 2: ' + this.rateBe);
        console.log('this.rate 2: ' + this.rate);
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

    initEvents() {
        let self = this;

        if (this.settings.hasOwnProperty('formSelector') && this.settings.formSelector !== '') {
            this.form = BX(this.settings.formSelector);
            if (!!this.form) {
                BX.bind(this.form, 'submit', (event) => {
                    if (!this.canSave) {
                        event.preventDefault();
                    }
                })
            }
        }

        this.rateCurse.addEventListener('input',function (){
            self.getAllData();
            self.callbackItems(function (item){
                item.setBudget();
            })
        })

        this.initSelects();

        let monthSelect = document.querySelector(`select[name="PROPERTY_${this.props.MONTH ?? 0}"]`),
            yearSelect = document.querySelector(`select[name^="PROPERTY_${this.props.YEAR ?? 0}"]`),
            metastatusSelect = document.querySelector(`select[name^="PROPERTY_${this.props.METASTATUS ?? 0}"]`);

        this.metastatusSelect = metastatusSelect;

        this.initMonth(monthSelect);
        this.initYear(yearSelect);
        this.initMainWrap();

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
                self.items[id] = new BeIblock({
                    'wrap' : item,
                    'id' : id,
                    'itemType' : 'be-item',
                    'parent' : self,
                    start: true
                })
                self.selectedItems[id] = true;
                self.items[id].startInit = true;
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

        if (!this.currentCurseRate || window.parseFloat(this.currentCurseRate) <= 0) {
            this.checkRates();
        }

        if(!monthSelect){
            setTimeout(function (){
                // self.initMonth(monthSelect);
                // self.initYear(yearSelect);
                if (!!metastatusSelect) {
                    metastatusSelect.addEventListener('change',function (){
                        self.getAllData();
                    })
                }
                self.getAllData();
                self.callbackItems(function (item){
                    if(item.mode === 'noP'){
                        item.getProductsAjax();
                    }
                })
                self.startInit = true;
            },500)

        } else {
            setTimeout(() => {
                if (!!metastatusSelect) {
                    metastatusSelect.addEventListener('change',function (){
                        self.getAllData();
                    });
                }
                this.changeCountryBe({initForm: true});
            })
        }

        setTimeout(function (){
            self.getAllData();
            self.startInit = true;
        },900);
    }

    initMainWrap() {
        this.country.addEventListener('change', this.changeCountryBe.bind(this));
    }

    changeCountryBe(event = {}) {
        if (!event.target) {
            event.target = this.country;
        }

        let rateId,
            isEventTrigger = !event.hasOwnProperty('initForm') || event.initForm !== true;

        if (isEventTrigger) {
            this.rateList.value = this.country[this.country.options.selectedIndex].dataset.rate;
            this.rate = this.rateList[this.rateList.selectedIndex].dataset.rate;
        }

        if (
            (
                event.target.selectedIndex >= 0
                && !!event.target.options
                && !!event.target.options[event.target.selectedIndex]
            ) && !!this.rateListBe
        ) {
            rateId = event.target.options[event.target.selectedIndex].dataset.rate;
            if (!!rateId && isEventTrigger) {
                this.rateListBe.value = rateId;
                this.rateBe = this.rateListBeOptions[this.rateListBe.selectedIndex].dataset.rate;

                this.rateListBe.dispatchEvent(new Event('change'));
            }
        }

        if (isEventTrigger) {
            this.checkRates();
        }

        this.getAllData();
    }

    getAllData(){
        let self = this;

        let data = {
            'random':Math.random(),
            'rate' : this.rate ,
            'distr':this.distribution.checked,
            'country' : this.country.value,
            'rateBe' : this.rateBe,
            'article' : this.articlesList.value,
            'rateCurse' : this.rateCurse.value,
            'sum' : this.sumInputEditor.input.value,
            'freeSum' : this.freeSumInput.value,
            'freePercent' : this.freePercentInput.value,
            'items' : {}
        };

        this.setFreeSum();

        if(this.yearOptions){
            data['year'] = this.yearOptions[this.year.selectedIndex].text;
        }else{
            data['year'] = this.nowYear.value;
            // data['year'] = 2021;
        }

        if(this.optionsMonth && this.month.selectedIndex >= 0){
            data['month'] = this.optionsMonth[this.month.selectedIndex].text;
        }else{
            data['month'] = this.nowMonth.value;
        }

        this.callbackItems(function (item){
            data['items'][item.id] = self.buildByItem(item);
        })

        this.mainInput.value = JSON.stringify(data);
        this.checkErrors();
    }

    checkErrors(){
        if (!this.articlesList.value || this.articlesList.value == 'N') {
            this.addError('articleError', 'Не выбрана статья расходов!');
        } else {
            this.deleteError('articleError');
        }

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

        if(
            str !== ''
            && (
                !!this.metastatusSelect
                && this.metastatusSelect.value != this.draftId
            )
        ){
            BX.hide(this.buttonSave);
            this.canSave = false;
        }else{
            BX.show(this.buttonSave);
            this.canSave = true;
        }

    }

    initYear(select){
        let self = this;
        this.year = select;
        // this.year.disabled = true;
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

    initMonth(select){
        let self = this;
        this.month = select;
        // this.month.disabled = true;
        this.optionsMonth = select.options;
        this.month.addEventListener('change',function (){
            let month = self.optionsMonth[this.selectedIndex].text;

            let monthNumber = self.getMonthNumber(month);

            if(self.getMonthNumber(self.nowMonth) < monthNumber){
                self.addError('month','Невозможно создать заявку на след месяц!')
            }else{
                self.deleteError('month')
            }

            self.getAllData();
        })

        if ((!this.month || this.month.value == '') && this.optionsMonth.length > 0) {
            let nowMonth = [...this.optionsMonth].find((option) => {
                    return option.innerText == this.nowMonth;
                }),
                idValue = 0;

            if (!nowMonth) {
                return;
            }

            idValue = window.parseInt(nowMonth.getAttribute('value'), 0);
            if (idValue <= 0) {
                return;
            }

            this.month.value = idValue;
        }
    }
}


class BeIblock extends Be {
    constructor(settings) {
        super(settings);
        this.settings = settings;
        this.postInit();
    }

    init() {}

    setSum(){
        if(parseFloat(this.percentInputEditor.value) > 0){
            this.sumInputEditor.input.value = (this.parent.sumInputEditor.value / 100 * this.percentInputEditor.value).toFixed(2);
            setValueEditor(this.sumInputEditor);
        }

        this.callbackItems(function (item){
            item.setSum();
        })

        this.getAllData();
    }

    postInit() {
        let self = this;

        this.budgetInput = this.wrap.querySelector('.budgetInput');
        this.baseItemInit();
        this.addInput.addEventListener('click', function () {
            self.addItem();
        })

        this.deleteInput.addEventListener('click', function () {
            self.deleteItem();
            self.checkCanAddAndDelete();
        })
        this.initEditors();
        this.checkInputs();
        this.initSelects();
        //this.hideSelects();

        this.budget = 0;

        if(this.wrap.querySelector('#distribution')){
            this.distribution = this.wrap.querySelector('#distribution');
        }

        this.wrap.querySelectorAll('.beproduct-item.new').forEach(function (item){
            let id = item.getAttribute('data-id');
            if(id >= 0){
                self.items[id] = new BeProduct({
                    'wrap' : item,
                    'id' : id,
                    'itemType' : 'beproduct-item',
                    'parent' : self
                })
                self.selectedItems[id] = true;
                self.maxItems = self.items[id].hideSelects();
            }
        })

        setTimeout(() => {
            this.initProductsFunctional();
            if (this.settings.hasOwnProperty('start') && this.settings.start === true) {
                this.settings.start = false;
            }

            let number = this.wrap.querySelector('.rand').value;
            if (this.wrap.querySelector('input[name=switcher_' + number + ']:checked').value == 'noP') {
                this.getProductsAjax();
            }
        },700)
    }

    switchProductsMode(mode) {
        if (mode == 'allP' && (this.settings.hasOwnProperty('start') && this.settings.start === true) ) {
            mode = ''
        }
        super.switchProductsMode(mode);
    }

    initEditors(re) {
        let self = this;
        this.percentInputEditor = new BX.Currency.Editor({
            'input': this.percentInput,
            'callback': function () {
                if (!this.fromSum) {
                    self.percentChanger(this);
                    // self.getAllData();
                }
            }
        });

        if (re) {
            this.sumInputEditor = new BX.Currency.Editor({
                'input': this.sumInput,
                'callback': function () {
                    if (!this.fromPercent) {
                        self.sumChanger(this);
                        self.getAllData();
                    }
                }
            });
            this.sumInputEditor.fromPercent = false;
        }
        this.percentInputEditor.fromSum = false;
    }

    getProductsAjax(){
        let self = this;
        let data = {
            'article': this.parent.articlesList.value,
            'month': this.parent.optionsMonth[this.parent.month.selectedIndex].text,
            'rateCurse': this.parent.rateCurse.value,
            'year': this.parent.yearOptions[this.parent.year.selectedIndex].text,
            'be': this.id,
            'baseRate': self.parent.rate
        }

        BX.ajax.runComponentAction('immo:iblock.budget.biznes.units', 'getProducts', {
            mode: 'class',
            data: data
        }).then(
            function (response) {

                self.wrap.querySelector('.product-section').innerHTML = response.data.html;
                self.wrap.querySelector('.product-section').querySelectorAll('.new').forEach(function (item) {
                    let id = item.getAttribute('data-id');

                    self.items[id] = new BeProduct({
                        'wrap': item,
                        'parent': self,
                        'itemType' : 'beproduct-item',
                        'id': id
                    })
                    self.budgetInput.value = response.data.budget;
                    self.budget = parseFloat(response.data.budget);
                    setTimeout(function (){
                        self.initProductsFunctional();
                        self.startInit = true;
                        self.setBudget();
                        self.parent.getAllData();
                    },500)

                });
            }, function (response) {

            })
    }

    initSelects() {
        let self = this;
        this.select.addEventListener('change', function () {
            let value = this.value;

            self.callbackItems(function (item){
                item.deleteItem();
            })

            if (value < 1) {
                value = Math.random();
            }

            if(self.parent.selectedItems[self.id]){
                delete self.parent.selectedItems[self.id];
            }

            self.parent.resetKey(self.id, value);

            self.id = value;

            self.initEditors(true);

            self.checkInputs();

            if (value < 1) {
                self.percentInputEditor.formatValue();
            }

            if(value > 1){
                self.parent.selectedItems[self.id] = true;
                self.getProductsAjax();
            }

            self.setSum();
            self.parent.checkDistribution();
            self.parent.callbackItems(function (item){
                item.hideSelects();
            })
            self.getAllData();
        })
    }

    addItem(returned) {
        let newItem = document.querySelector('.cloneBlocks .' + this.itemType).cloneNode(true);
        this.wrap.after(newItem);
        let id = Math.random();

        let self = this;

        this.parent.items[id] = new BeIblock({
            'wrap': newItem,
            'id': id,
            'itemType': 'be-item',
            'parent': self.parent
        })

        this.parent.items[id].hideSelects();

        if(returned){
            return this.parent.items[id];
        }

        this.checkCanAddAndDelete();
    }
}
