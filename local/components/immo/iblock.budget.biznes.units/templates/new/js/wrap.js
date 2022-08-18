/**
 * Общий класс формы бе
 * Инициализирует общие события и элементы
 */
class WrapItems {
    /**
     * Выбранные итемы
     * @type {{}}
     */
    selectedItems = {};

    /**
     * Объект элементов
     * @type {{}}
     */
    items = {};

    /**
     * Объект ошибок
     * @type {{}}
     */
    errors = {};

    /**
     * Элемент обертки
     * @type Node
     */
    wrap;

    /**
     * Максимум элементов
     */
    maxItems;

    /**
     * Валюта
     */
    rate;

    /**
     * Родительский элемент
     */
    parent;

    /**
     * Валюта БЕ
     */
    rateBe;

    /**
     * Уникальный ID
     */
    id;

    /**
     * Тип элементов
     */
    itemType;

    /**
     * @description Флаг. Изменяется сумма из родителя
     * @type {boolean}
     */
    fromParent = false;

    /**
     * Конструктор. Задает параметры в свойства
     * @param settings
     */
    constructor(settings = {}) {
        this.canSave = true;

        for (let key in settings) {
            this[key] = settings[key];
        }

        this.startInit();
    }

    /**
     * Инициализация
     */
    startInit() {
        try {
            this.initFields();
        } catch (error) {
            console.log('Ошибка поля БЕ!');
            console.log(error);
        }
    }

    /**
     * Инициализация полей
     */
    initFields() {
        try {
            this.initHtmlAndMainElements();
        } catch (error) {
            console.log('Ошибка поля БЕ!');
            console.log(error);
        }
    }

    /**
     * Инициализация элементов на странице
     */
    initHtmlAndMainElements() {
        this.startInitCompleted = false;

        this.defineElements([
            {query: '#distribution', key: 'distribution'},
            {query: '#free_sum', key: 'freeSumInput'},
            {query: '#free_percent', key: 'freePercentInput'},
            {query: '.sumInput', key: 'sumInput'},
            {query: '#article', key: 'articlesList', listKey: 'articlesListOptions'},
            {query: '#urList', key: 'urList', listKey: 'urListOptions'},
            {query: this.mainInputSelector, key: 'mainInput'},
            {query: '#rate', key: 'rateList', listKey: 'rateListOptions'},
            {query: '#rateBe', key: 'rateListBe', listKey: 'rateListBeOptions'},
            {query: '.errors', key: 'errorsDiv'},
            {query: '.success', key: 'successDiv'},
            {query: '#country', key: 'country'},
            {query: '#rateCurse', key: 'rateCurse'},
            {query: '.errors', key: 'errorsDiv'},
            {query: '#date-currency-rate', key: 'dateCurrencyRate'},
            {query: '#workarea-content .bx-buttons input[name="save"]', key: 'buttonSave', global: true},
            {query: `select[name^="PROPERTY_${this.props.METASTATUS ?? 0}"]`, key: 'statusSelect', global: true},
            {query: `#${this.formSelector}`, key: 'mainForm', global: true},
            {query: '.updateBudget', key: 'updateBudgetBtn'}
        ]);

        if (!!this.bankParams) {
            if (this.bankParams.hasOwnProperty('elementBank')) {
                this.defineElements([{
                    query: '#bank',
                    key: 'bankSelect',
                }])
            }
        }

        if (!!this.rateListOptions) {
            this.rate = this.rateListOptions[this.rateList.selectedIndex].getAttribute('data-rate');
        }
        if (!!this.rateListBeOptions) {
            this.rateBe = this.rateListBeOptions[this.rateListBe.selectedIndex].getAttribute('data-rate');
        }

        const date = new Date();
        this.nowMonth = date.toLocaleDateString('ru-Ru', {month: 'numeric'});
        this.nowYear = this.financialYear ?? date.toLocaleDateString('ru-Ru', {year: 'numeric'});

        /**
         * Инит селекторов
         * Инит едиторов
         * Инит элементов
         * Проверка валюты
         */
        this.initSelectors();
        this.initEditors();
        this.initItems();
        this.checkRates();

        this.initExtraEvents();

        if (this.distribution.checked) {
            this.checkDistribution();
        }

        this.onChangeArticle(false);

        this.startInitCompleted = true;
    }

    /**
     * @description Инициализация зависимости на поле получателя платежа. Используется для внтуреннего банка
     * @param autoCompleteId {string}
     */
    initAutocompleteField(autoCompleteId) {
        if (
            !window.hasOwnProperty('AutoCompleteFieldInstance')
            || !window['AutoCompleteFieldInstance']
            || !window['AutoCompleteFieldInstance'][autoCompleteId]
        ) {
            return;
        }

        /**
         * @var autocompleteField {AutoCompleteField}
         * */
        const autocompleteField = window['AutoCompleteFieldInstance'][autoCompleteId];

        autocompleteField.subscribe('select2:select', this.onChangeAutocompleteField.bind(this));
    }

    /**
     * @description Метод для обработки события изменения поле получателя. Используется для внутреннего банка
     * @param event
     */
    onChangeAutocompleteField(event) {
        [...this.bankSelect.options].forEach((option) => {
            option.disabled = false;
        })

        const selectedParams = event.params.data;
        if (!selectedParams || !selectedParams.hasOwnProperty('bitrixId') || !selectedParams.bitrixId) {
            return;
        }

        if (this.bankSelect.value == selectedParams.bitrixId) {
            this.bankSelect.value = 0;
            this.onChangeBankSelect({target: this.bankSelect});
        }

        [...this.bankSelect.options].forEach((option) => {
            if (option.value != selectedParams.bitrixId) {
                return;
            }

            option.disabled = true;
        })
    }

    /**
     * @description Обработчик события изменения выбора банка
     * @param target {HTMLSelectElement}
     */
    onChangeBankSelect({target}) {
        const selectedOption = target.options[target.selectedIndex];
        if (!selectedOption) {
            return;
        }

        let countryId = window.parseInt(selectedOption.dataset.countryid);
        if (countryId <= 0) {
            countryId = window.parseInt(this.defCountry);
        }

        if (countryId <= 0 || this.country.value == countryId) {
            return;
        }

        this.country.value = countryId;
        this.onChangeCountryBe({target: this.country});
    }

    /**
     * Регистрирует дополнительные обработчики событий
     */
    initExtraEvents() {
        /**
         * Обработчик события на сохранение формы УС (отменяем если в форме БЕ есть ошибки)
         */
        if (this.hasOwnProperty('mainForm') && !!this.mainForm) {
            BX.bind(this.mainForm, 'submit', (event) => {
                if (!this.canSave) {
                    event.preventDefault();
                }
            })
        }

        if (this.hasOwnProperty('updateBudgetBtn') && !!this.updateBudgetBtn) {
            this.defineEvents([{
                element: this.updateBudgetBtn,
                eventName: 'click',
                callback: this.onChangeArticle.bind(this, false)
            }])
        }
    }

    /**
     * @description Показывает кнопку обновления бюджета
     */
    showUpdateBtn() {
        if (this.hasOwnProperty('updateBudgetBtn') && !!this.updateBudgetBtn) {
            this.updateBudgetBtn.closest('.be-line').style.display = 'flex';
        }
    }

    /**
     * @description Скрывает кнопку обновления бюджета
     */
    hideUpdateBtn() {
        if (this.hasOwnProperty('updateBudgetBtn') && !!this.updateBudgetBtn) {
            this.updateBudgetBtn.closest('.be-line').style.display = 'none';
        }
    }

    /**
     * Определяет объекты на странице и записывает в свойства текущего объекта
     * @param selects
     * @type Array
     */
    defineElements(selects = []) {
        selects.forEach(({query, key, listKey = '', global = false}) => {
            if (query == '' || key == '') {
                return;
            }

            if (this.hasOwnProperty(key)) {
                return;
            }

            this[key] = (!global) ? this.wrap.querySelector(query) : window.document.querySelector(query);
            if (listKey != '' && !!this[key] && !!this[key]['options']) {
                this[listKey] = this[key].options;
            }
        });
    }

    /**
     * Обработчик на изменения валюты БЕ
     */
    onChangeRateListBe(event) {
        this.rateBe = event.target.options[event.target.selectedIndex].getAttribute('data-rate');

        /**
         * Для каждой бе в заявке проходит запуск события по изменению.
         */
        this.callbackItems(function (item){
            item.select.value = 0;
            let event = new Event('change');
            item.select.dispatchEvent(event);
            /**b
             * для каждой БЕ проверка, можно ли добавить (?)
             */
            item.checkCanAddAndDelete();
        })

        this.hideSelects();

        let length = 0;
        this.callbackItems((item) => {
            length++;
            if (length > 1) {
                item.deleteItem();
            }

        })

        this.callbackItems(function (item){
            item.checkCanAddAndDelete();
        });

        /**
         * Проверяем валюты и собираем данные
         */
        this.checkRates();
        this.getAllData();
    }

    /**
     * Обработчик события на изменения Юр. лица
     * При измении юр лица меняем валюту плательщика и валюту
     */
    onChangeUrList(event) {
        const country = event.target.options[event.target.selectedIndex];
        const countryId = (!!country.getAttribute('data-countryid'))
            ? country.getAttribute('data-countryid')
            : (!!this.defCountry ? this.defCountry : '');

        if (countryId == '' || this.country.value == countryId) {
            return;
        }

        this.country.value = countryId;
        this.onChangeCountryBe({target: this.country});

        if (!this.notNeedCheckRate) {
            let selectedValue = this.country[this.country.options.selectedIndex];
            if (!!selectedValue) {
                this.rateList.value = selectedValue.getAttribute('data-rate');
            }
        }

        this.rate = this.rateList[this.rateList.selectedIndex].getAttribute('data-rate');

        /**
         * Проверяем валюты и собираем данные
         */
        this.checkRates();
        this.getAllData();
    }

    /**
     * Обработчик события на изменения статьи расходов
     * При измении статьи проверяем остатки по бюджету
     * @param changeRates {boolean}
     */
    onChangeArticle(changeRates = true) {
        let data = {
            'article': this.articlesList.value,
            'month': this.nowMonth,
            'year': this.nowYear,
            'be':[]
        }

        this.callbackItems((item) => {
            data['be'].push(item.id)
        })

        if (data['be'].length <= 0) {
            if (changeRates) {
                this.checkRates();
            }
            this.getAllData();
            return;
        }

        const hasInit = this.startInitCompleted;

        BX.ajax.runComponentAction('immo:iblock.budget.biznes.units', 'getBudgets', {
            mode: 'class',
            data: data
        }).then((response) => {
            /**
             * Проставляем бюджеты элементам БЕ
             * После собирает и проверяет все данные
             */
            this.callbackItems((item) => {
                if (hasInit) {
                    item.budget = 0;
                }
                let budget = (hasInit)
                    ? response.data[item.id]
                    : (item.budget <= 0 ? response.data[item.id] : item.budget);

                if (!!budget) {
                    item.budget = budget;
                }
                item.setBudget();
            })
            if (changeRates && hasInit) {
                this.checkRates();
            }
            this.getAllData();
        })
    }

    /**
     * обработчик события на изменения валюты
     * При изменении валюты нужно проверить валюту и проставить бюджет.
     * В самом конце собираем данные
     */
    onChangeRate(event) {
        this.rate = event.target.options[event.target.selectedIndex].getAttribute('data-rate');
        this.checkRates();
        this.callbackItems((item) => {item.setBudget()});
        this.getAllData();
    }

    /**
     * Обработчик изменения курса валюты. В этом случае собираем данные и проставляем бюджет
     */
    onRateCurseChange() {
        this.getAllData();
        this.callbackItems((item) => {item.setBudget()});
    }

    /**
     * Обработчик события изменения страны плательщика
     * @param event
     */
    onChangeCountryBe(event) {
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

    /**
     * Генератор уникальных ID ключей
     * @returns {number}
     */
    generateItemId() {
        const existIds = Object.keys(this.items) ?? [];
        let id = Math.random();
        while (existIds.includes(id)) {
            id = Math.random();
        }

        return id;
    }

    /**
     * Проверка валюты
     * Если валюта БЕ и валюта плательщика совпадает, курс собирать не нужно, так как они будет равен 1
     */
    checkRates(){
        if(!this.notNeedCheckRate){
            if(this.rate === this.rateBe){
                this.rateCurse.disabled = true;
                this.rateCurseEditor.value = '1';

                if (!!this.dateCurrencyRate) {
                    BX.hide(this.dateCurrencyRate.parentNode);
                    this.dateCurrencyRate.innerText = '';
                }
            }else if(this.country[this.country.selectedIndex].text === 'Россия'){
                if (this.startInitCompleted) {
                    this.getRate();
                }
                this.rateCurse.disabled = false;
            }else{
                this.rateCurse.disabled = false;
            }
        }

    }

    /**
     * Инициализация блоков БЕ
     */
    initItems(){
        let self = this;
        /**
         * Проход по всем html блокам БЕ. Создаем объекты под каждый блок, если он не клонированный
         */
        this.wrap.querySelectorAll('.be-item.new').forEach((item) => {
            if(!item.closest('.cloneBlocks')){
                let id = item.getAttribute('data-id');

                if(id >= 0){
                    this.items[id] = new Be({
                        'wrap' : item,
                        'id' : id,
                        'itemType' : 'be-item',
                        'parent' : self
                    })
                    this.selectedItems[id] = true;
                    this.items[id].initProductsFunctional();
                }
            }

        })

        /**
         * Скрываем лишние БЕ из блоков
         */
        this.hideSelects();
        this.getAllData();
    }

    /**
     * Сбрасываем старый выбранный блок БЕ и заполняем новым
     * @param oldId
     * @param newId
     */
    resetKey(oldId, newId) {
        Object.defineProperty(this.items, newId,
            Object.getOwnPropertyDescriptor(this.items, oldId));
        delete this.items[oldId];
    }

    /**
     * Скрывает некоторые селекты в форме
     */
    hideSelects() {
        let self = this;

        /**
         * Проход по всем БЕ.
         * Для каждого блока БЕ скрывает бе, которые не подходят по валюте БЕ
         */
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
                            option.disabled = false;
                            option.style.display = '';
                        }else{
                            option.disabled = true;
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
     * Инициализация обработчиков на селекторы
     */
    initSelectors() {
        this.defineEvents([
            {element: this.rateListBe, eventName: 'change', callback: this.onChangeRateListBe.bind(this)},
            {element: this.urList, eventName: 'change', callback: this.onChangeUrList.bind(this)},
            {element: this.articlesList, eventName: 'change', callback: this.onChangeArticle.bind(this)},
            {element: this.rateList, eventName: 'change', callback: this.onChangeRate.bind(this)},
            {element: this.rateCurse, eventName: 'input', callback: this.onRateCurseChange.bind(this)},
            {element: this.distribution, eventName: 'click', callback: this.checkDistribution.bind(this)},
            {element: this.country, eventName: 'change', callback: this.onChangeCountryBe.bind(this)},
            {element: this.statusSelect, eventName: 'change', callback: this.getAllData.bind(this)},
        ]);

        if (!!this.bankSelect) {
            this.defineEvents([{
                element: this.bankSelect, eventName: 'change', callback: this.onChangeBankSelect.bind(this)
            }])
        }
    }

    /**
     * Инициализирует обработчики событий для элементов
     * @param eventElements
     */
    defineEvents(eventElements = []) {
        eventElements.forEach(({element = {}, eventName, callback}) => {
            if (!element) {
                return;
            }

            element.addEventListener(eventName, callback);
        })
    }

    /**
     * Получение актуальной валюты через запрос
     */
    getRate(){
        BX.ajax.runComponentAction('immo:iblock.budget.biznes.units', 'getCurrencyRate', {mode: 'class', data: {
            currency: this.rateBe,
            currencyPayed: this.rate
        }}).then(({data}) => {
            let {currency, date} = data;
            if (!currency.hasOwnProperty('value') || currency.value == '') {
                throw new Error('Ошибка расчета курса. Повторите попытку или обратитесь к администратору');
            }

            this.rateCurseEditor.value = currency.value;

            if (!!date && !!this.dateCurrencyRate) {
                BX.show(this.dateCurrencyRate.parentNode);
                this.dateCurrencyRate.innerText = date.value;
            }

            this.callbackItems((item) => item.setBudget());
        }).catch((response) => {
            console.log(response);
        });
    }

    /**
     * @description Возвращает свободный процент БЕ
     * @param nowId
     * @return {number}
     */
    getFreePercent(nowId) {
        let allPercent = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId && item.id > 1 && !!item.percentInputEditor) {
                allPercent = getFloat(allPercent + parseFloat(item.percentInputEditor.unmaskedValue));
            }
        })

        return parseFloat((100 - allPercent).toFixed(2));
    }

    /**
     * Получение свободной цены блока БЕ
     * Для этого получаем общую сумму из формы, собираем общую сумму из других блоков БЕ
     * Затем вычитаем из общую суммы и суммы других блоков
     * @param nowId
     * @returns {number}
     */
    getFreeSum(nowId) {
        let allSum = this.sumInputEditor.unmaskedValue;

        let sum = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId && !!item.sumInputEditor) {
                sum += parseFloat(item.sumInputEditor.unmaskedValue);
            }
        })
        return parseFloat((allSum - sum).toFixed(2));
    }

    /**
     * Создает и возвращает маску по инпуту
     * Нужно для валидации вводных данных и проставлении плавающей точки
     * @param input
     * @param callback
     * @param scale
     * @param negative
     * @returns {InputMask}
     */
    createMask(input, callback = {}, scale = 2, negative = false){
        let mask = IMask(
            input,
            {
                scale:scale,
                mask: Number,
                thousandsSeparator: ' ',
                radix:',',
                signed: negative
            });

        if (!!callback && typeof callback == 'function') {
            mask.on('complete', callback);
        }

        return mask;
    }

    /**
     * Инициализации едиторов инпутов
     */
    initEditors(){
        /**
         * Создаем едитор инпута суммы
         * @type {InputMask}
         */
        this.sumInputEditor = this.createMask(this.sumInput,this.sumChanger.bind(this));

        /**
         * @description Создаем едитор инпута процентов
         * @type {InputMask}
         */
        this.freePercentInputEditor = this.createMask(this.wrap.querySelector('#free_percent'));

        /**
         * @description Создаем едитор инпута свободной суммы
         * @type {InputMask}
         */
        this.freeSumInputEditor = this.createMask(this.wrap.querySelector('#free_sum'));

        /**
         * Создаем едитор курса валюты
         * @type {InputMask}
         */
        this.rateCurseEditor = this.createMask(this.rateCurse,() => {
            this.callbackItems((item) => {item.setBudget()});
        },4);
    }

    /**
     * Возвращает float от числа с 2 знаками после запятой
     * @param value
     * @returns {number}
     */
    getFloat(value){
        value = (typeof value !== 'number') ? parseFloat(value) : value;
        return parseFloat(value.toFixed(2))
    }


    /**
     * Проставление процента распределения по блокам БЕ
     */
    setFreePercent(){
        let allPercent = 100;

        this.callbackItems(function (item) {
            if (item.id > 1) {
                allPercent = getFloat(allPercent - parseFloat(item.percentInputEditor.unmaskedValue));
            }
        })

        this.freePercentInputEditor.value = ''+allPercent;
        this.getAllData();
    }

    /**
     * Простановка свободной суммы в блоках БЕ
     * После простановки суммы, проставляем процент
     */
    setFreeSum(){
        let allSum = this.sumInputEditor.unmaskedValue;

        let sum = 0;

        this.callbackItems(function (item) {
            if(item.id > 1){
                sum += parseFloat(item.sumInputEditor.unmaskedValue);
            }
        })

        this.freeSumInputEditor.value = ''+(allSum - sum);

        if(this.freeSumInputEditor.unmaskedValue === ''){
            this.freeSumInputEditor.value = '0';
        }
        this.setFreePercent();
    }

    /**
     * Сборка выбранных данных и записывает их в главный инпут
     * После сборки данных проходимся по ошибкам формы
     */
    getAllData(){
        let self = this;

        let data = {
            'random': Math.random(),
            'rate' : this.rate ,
            'distr':this.distribution.checked,
            'country' : this.country.value,
            'rateBe' : this.rateBe,
            'article' : this.articlesList.value,
            'rateCurse' : this.rateCurseEditor.unmaskedValue,
            dateCurrencyRate: this.dateCurrencyRate.innerText,
            'sum' : this.sumInputEditor.unmaskedValue,
            'freeSum' : this.freeSumInputEditor.unmaskedValue,
            'freePercent' : this.freePercentInputEditor.unmaskedValue,
            'items' : {}
        };

        if (!!this.urList) {
            data['ur'] = this.urList.value;
        }

        if (!!this.bankSelect) {
            data['beBank'] = this.bankSelect.value;
        }

        data['year'] = this.nowYear;

        data['month'] = this.nowMonth;

        this.callbackItems(function (item){
            if(item.id>1){
                data['items'][item.id] = self.buildByItem(item);
            }
        })
        this.mainInput.value = JSON.stringify(data);
        /**
         * Проверка ошибок формы
         */
        this.checkErrors();
    }

    /**
     * Собирает json объект по блокам БЕ
     * @param item
     * @returns {{mode: *, sum: *, id: *, percent: *, items: {}}}
     */
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
     * Проверка ошибок формы
     */
    checkErrors(){
        if (!!this.urList) {
            if (!this.urList.value || this.urList.value == 0) {
                this.addError('companyError', 'Не выбрано Юр. Лицо плательщика!');
            } else {
                this.deleteError('companyError');
            }
        }
        if (!!this.bankSelect) {
            if (!this.bankSelect.value || this.bankSelect.value == 0) {
                this.addError('bankError', 'Не выбран внутренний банк плательщика!');
            } else {
                this.deleteError('bankError');
            }
        }

        if (!this.articlesList.value || this.articlesList.value <= 0) {
            this.addError('articleError', 'Не выбрана статья расходов!');
        } else {
            this.deleteError('articleError');
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

        let sum = 0;

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

        if (
            str !== ''
            && (
                !!this.statusSelect
                && this.statusSelect.value != this.draftId
            )
        ) {
            if (!!this.buttonSave) {
                BX.hide(this.buttonSave);
            }
            this.canSave = false;
        } else {
            if (!!this.buttonSave) {
                BX.show(this.buttonSave);
            }
            this.canSave = true;
        }
    }

    /**
     * Проверка типа распределения суммы
     */
    checkDistribution(){
        let self = this;

        this.fromParent = true;
        if(this.distribution.checked){

            let length = 0;
            this.callbackItems(function (item){
                if(item.id > 1){
                    length++;
                }
            })

            this.setDistributionInternal(length);
        }

        this.callbackItems(function (item){
            item.checkInputs();
            item.callbackItems(function (subItem){
                subItem.parent.checkDistribution();
            })
        })

        this.fromParent = false;
        /**
         * После выбра типа распределения, проставляем сумму и обновляем данные
         */
        this.setFreeSum();
        this.getAllData();
    }

    /**
     * @description Возвращает ID последнего выбранного элемента из формы
     * @return {string}
     */
    getLastSelectedItem() {
        if (!(this instanceof WrapItems) && !(this instanceof Be)) {
            return;
        }

        const productsNode = (this instanceof Be)
            ? [...this.wrap.querySelectorAll('.product-section .product-wrap')]
            : [...this.wrap.querySelectorAll('.be-item.new')];

        if (productsNode.length <= 0) {
            return '';
        }

        let lastId = '';
        productsNode.reverse().forEach((productWrap) => {
            if (lastId != '') {
                return;
            }

            let select = productWrap.querySelector('select.select-item');
            if (!select || !select.value || select.value == '0') {
                return;
            }

            lastId = select.value;
        })

        return lastId;
    }

    /**
     * @description Устанавливает автоматическое распределение, если выбрано распредление в равных %
     * @param length
     */
    setDistributionInternal(length = 0) {
        if (length <= 0) {
            return;
        }

        const calculations = this.calculateDistribution(length);

        let arItems = Object.values(this.items);
        if (arItems.length == 1) {
            arItems.shift().sumInputEditor.value = calculations.sum.toString();
            return;
        }

        // dirty
        const lastId = this.getLastSelectedItem();

        arItems.forEach((item) => {
            if (item.id < 1) {
                return;
            }

            /**
             * Если текущий элемент - последний
             * И нужно скорректировать последний элемент
             */
            if (lastId == item.id && calculations.isPeriod) {
                item.sumInputEditor.value = this.getFloat(calculations.sum + calculations.diff).toString();
                return;
            }

            item.sumInputEditor.value = calculations.sum.toString();
        });
    }

    /**
     * @description Расчитывает распределение и возвращает его результат
     * @param length
     * @return {{
     * total: number, Сумма для проверки распределения
     * parentSum: string, Родительская сумма
     * sum: number, Сумма распределения по частям исправленная
     * originSum: number, Сумма распределения по частям
     * diff: number, Разница родительской суммы и суммы проверки
     * isPeriod: boolean Расхождение родительской суммы с суммой проверки
     * }}
     */
    calculateDistribution(length = 0) {
        let result = {
            parentSum: this.sumInputEditor.unmaskedValue,
            sum: 0,
            originSum: 0,
            total: 0,
            diff: 0,
            isPeriod: false
        }

        if (length <= 0) {
            return result;
        }

        /**
         * Считаем ссуму в равных частях
         */
        result.originSum = this.getFloat(result.parentSum / length);
        result.sum = result.originSum;

        /**
         * Для дальнейших проверок нужно умножить кол-во продуктов на получившуюся сумму
         * Необходимо для корректной проверки пероида, так как:
         * Сумма 10. Кол-во продуктов:
         *
         * 1) 2.
         * Тогда
         * 10 / 2 = 5
         * 5 * 2 = 10 - корректно
         *
         * 2) 3.
         * Тогда
         * 10 / 3 = 3.33
         * 3.33 * 3 = 9.99 - не корректно, нужно скорректировать распределение у последнего продукта
         *
         * 3) 6.
         * Тогда
         * 10 / 6 = 1.67
         * 1.67 * 6 = 10.02 - не корректно, так как полученная сумма больше исходной. Нужно скорректировать
         */
        result.total = result.originSum * length;

        /**
         * Проверка, корректная ли получилась ссума
         * Если true, значит получившаяся сумма не совпадает с исходной.
         * Дальше нужно будет скорректировать распределение
         *
         * Иначе, все ок, распределяем сумму по всем продуктам в равных частях
         */
        result.isPeriod = result.total != result.parentSum;

        /**
         * Если расчитанная сумма больше исходной
         * То уменьшаем сумму распределения на 0.01 и перерасчитываем расчитанную сумму
         */
        if (result.total > result.parentSum) {
            result.sum = this.getFloat(result.sum - 0.01);
            result.total = this.getFloat(result.sum * length);
        }

        /**
         * Считаем разницу, которая получается при не корректном распеределении
         */
        result.diff = (result.isPeriod) ? this.getFloat( result.parentSum - result.total) : 0;

        return result;
    }

    /**
     * Обработчик, который запускается при изменении суммы в форме.
     * При изменении суммы проходимся по блокам БЕ и распределяем сумму в равных долях
     */
    sumChanger(){
        this.setFreeSum();
        if(this.distribution.checked){
            this.checkDistribution();
        }
    }

    /**
     * Добавление ошибки в список ошибок
     * @param errorType
     * @param message
     */
    addError(errorType,message){
        this.errors[errorType] = message;
    }

    /**
     * Удаление ошибки по ее ключу
     * @param errorType
     */
    deleteError(errorType){
        if(this.errors[errorType]){
            delete this.errors[errorType];
        }
    }

    /**
     * Запуск коллбек функции по привязанным элементам
     * @param callback
     */
    callbackItems(callback) {
        for (let key in this.items) {
            callback(this.items[key]);
        }
    }
}

/**
 * @param value
 * @returns {number}
 */
function getFloat(value){
    return parseFloat(value.toFixed(2))
}