(function (window, BX, $) {
    if (typeof window.AutoCompleteField !== 'function') {
        /**
         * @description Функция отвечающая за работу поля с автозаполнением. Работает на select2
         * @param id
         * @param signedParameters
         * @param minLength
         * @param value
         * @param idButton
         * @param checkboxProperty
         * @param formId
         * @constructor
         */
        window.AutoCompleteField = function ({
            id = '',
            signedParameters = '',
            minLength = 0,
            value = '',
            idButton = '',
            checkboxProperty = {},
            formId = '',
            entityProperty = {}
        }) {
            this.id = id;
            this.formId = formId;
            this.idButton = idButton;
            this.minLength = minLength;
            this.minLength = (window.parseInt(this.minLength) <= 0) ? 1 : this.minLength;

            this.signedParameters = signedParameters;
            this.checkboxProperty = checkboxProperty;
            this.selectProp = {};

            this.select = {};
            this.value = value;

            this.isAjax = false;
            this.entityProperty = entityProperty;

            this.inited = false;
            this.cacheValues = {};
            this.eventHandlers = {};
        }
    }

    window.AutoCompleteField.prototype = {
        init: function () {
            if (this.signedParameters == '') {
                console.error('Ошибка параметров')
                return;
            }

            $(this.jqueryInit.bind(this));
        },

        /**
         * @description Инициализация селекта. Низкоуровневая
         */
        initSelectInternal: function () {
            delete this.select;
            this.select = $(`#${this.id}`);
        },

        /**
         * @description отложенная инициализация для jquery
         */
        jqueryInit: function () {
            let checkBox = this.initValuesBtn(this.id);
            if (!!checkBox && checkBox.checked) {
                this.initSelectInternal();
                if (this.select.length <= 0) {
                    return;
                }
                this.checkboxSelectedHandler({target: checkBox}, this.select.val());
            } else {
                this.initSelect({});
            }
            this.initClearBtn(this.idButton);
        },

        /**
         * @description Подписывает обработчик на событие select2
         * @param eventName {string}
         * @param callback {function}
         */
        subscribe: function (eventName, callback) {
            if (!this.eventHandlers.hasOwnProperty(eventName)) {
                this.eventHandlers[eventName] = [];
            }

            this.eventHandlers[eventName].push(callback);
        },

        /**
         * @description Инициализирует обработчик чекбокса, по выборе которого переключается режим работы селекта
         * Если чекбокс выбран - загружаются готовые значения. Свое значение ввести нельзя
         * Если чекбокс не выбран - каждый раз подгружаются предпологаемые значения, можно ввести свое значение
         * @param idCheckbox
         */
        initValuesBtn: function (idCheckbox = '') {
            let checkBox = BX(`use-values-${idCheckbox}`);

            if (!checkBox) {
                return;
            }

            BX.bind(checkBox, 'change', this.checkboxSelectedHandler.bind(this));

            if (this.isSelectPropChecked()) {
                checkBox.checked = true;
            }

            return checkBox;
        },

        /**
         * @description Проверяет, установлен ли селкт свойства в положение "Да"
         * @return {boolean}
         */
        isSelectPropChecked: function () {
            const select = this.getSelectProp();
            if (!select) {
                return false;
            }

            const enums = this.getPropEnums();
            if (!enums) {
                return false;
            }

            return select.value == enums['Y'];
        },

        /**
         * @description Возвращает элемент селекта свойства
         * @return {*|{}|Node|Node}
         */
        getSelectProp: function () {
            if (!!this.selectProp && this.selectProp instanceof Node) {
                return this.selectProp;
            }

            let form = BX(this.formId);
            if (
                !form
                || !this.checkboxProperty
                || (
                    !this.checkboxProperty.PROPERTY
                    || !this.checkboxProperty.PROPERTY.VALUES
                    || !Array.isArray(this.checkboxProperty.PROPERTY.VALUES)
                )
            ) {
                return;
            }

            this.selectProp = BX.findChild(form, {
                tag: 'select',
                attribute: {name: `PROPERTY_${this.checkboxProperty.PROPERTY.ID}`}
            }, true);

            return this.selectProp;
        },

        /**
         * @description Возвращает значения свойства списка
         * @return {{}}
         */
        getPropEnums: function () {
            let enums = {};
            this.checkboxProperty.PROPERTY.VALUES.forEach((enumValue) => {
                enums[enumValue.XML_ID] = enumValue.ID;
            });

            return enums;
        },

        /**
         * @description Метод проставляет чекбоксу аттрибуты (name и value) из параметров класса
         * @param set
         */
        setCheckboxAttr: function (set = true) {
            const enums = this.getPropEnums();
            if (enums['Y'] <= 0 || enums['N'] <= 0) {
                return;
            }

            const select = this.getSelectProp();
            if (!select) {
                return;
            }

            select.value = (set) ? enums['Y'] : enums['N'];
        },

        /**
         * @description Функция обработчик для чекбокса "выбрать кассу"
         * @param event
         * @param value
         */
        checkboxSelectedHandler: function (event, value = '') {
            this.setCheckboxAttr(event.target.checked);

            if (!event.target.checked) {
                this.clearValues();
                this.destroySelect();
                this.initSelect({});
                return;
            }

            if (this.isValuesLoaded()) {
                this.setValues(this.cacheValues);
                return;
            }

            let resetValue = true;

            this.ajaxLoadItems(
                {value: '', mode: 'items'},
                (response) => {
                    if (!response.data.items || !Array.isArray(response.data.items)) {
                        return;
                    }

                    response.data.items.unshift({
                        id: '',
                        text: 'Выберите значение...'
                    });

                    let values = response.data.items.map((loadValue) => {
                        if ((!!value && value !== '') && loadValue.id == value) {
                            resetValue = false;
                            loadValue.selected = true;
                        }
                        return loadValue;
                    });

                    this.setValues(values, resetValue);
                },
                () => {}
            );
        },

        /**
         * @description Устанавливает значения для селекта
         * @param values
         * @param resetValue
         */
        setValues: function (values, resetValue = true) {
            this.clearValues();
            this.destroySelect();
            this.initSelect({ajax: false, data: values});
            if (resetValue) {
                this.select.val(null);
            }

            this.cacheValues = values;
        },

        /**
         * @description Очищает значения селекта
         */
        clearValues: function () {
            this.select.val(null).trigger('change');
            let options = this.select.find('option');
            if (options.length > 0) {
                options.remove();
            }

            this.setItemId({params: {data: {bitrixId: 0}}});
        },

        /**
         * @description Проверяет, загружались ли готовые значения ранее
         * @returns {boolean}
         */
        isValuesLoaded: function () {
            return (Object.keys(this.cacheValues).length > 0);
        },

        /**
         * @description Очищает значение селектора
         * @param idButton
         */
        initClearBtn: function (idButton = '') {
            let button = BX(idButton);

            BX.bind(button, 'click', (event) => {
                event.preventDefault();
                this.clearValues();
            });
        },

        /**
         * @description инициализация селекта с параметрами
         */
        initSelect: function ({ajax = true, data = []}) {
            this.initSelectInternal();
            if (this.select.length <= 0) {
                return;
            }

            let params = {
                placeholder: 'Выберите значение...',
                width: 'resolve',
                language: 'ru'
            };

            if (data.length > 0) {
                params.data = data;
            }

            this.isAjax = ajax;

            if (this.isAjax) {
                params.placeholder = `Введите минимум ${this.minLength} символа...`;
                params.ajax = {
                    transport: this.transportHandler.bind(this),
                    processResults: this.processResultsHandler.bind(this)
                }
            }

            this.select.select2(params);

            this.initEvents(this.eventHandlers, ajax, data);
        },

        initEvents: function (events = {}) {
            let arEvents = Object.create(events);

            if (!!this.entityProperty && Object.keys(this.entityProperty).length > 0) {
                if (!arEvents.hasOwnProperty('select2:select')) {
                    arEvents['select2:select'] = [];
                }

                arEvents['select2:select'].push(this.setItemId.bind(this));
            }

            if (this.isAjax) {
                if (!arEvents.hasOwnProperty('select2:open')) {
                    arEvents['select2:open'] = [];
                }

                arEvents['select2:open'].push(this.onOpenPopupHandler.bind(this));
            }

            for (const eventName in arEvents) {
                const callbackStack = arEvents[eventName];
                if (!Array.isArray(callbackStack) || callbackStack.length <= 0) {
                    continue;
                }

                this.select.off(eventName);
                this.select.on(eventName, this.fireEventCallbackStack.bind(this, callbackStack));
            }
        },

        /**
         * @description Проставляет ID сущности из списка, при выбранном чекбоксе
         * @param event
         */
        setItemId: function (event = {}) {
            if (this.isAjax || !this.entityProperty || Object.keys(this.entityProperty).length <= 0) {
                return;
            }

            const selectedParams = event.params.data;
            const id = (
                !!selectedParams
                && Object.keys(selectedParams).length > 0
                && selectedParams.hasOwnProperty('bitrixId')
            )
                ? selectedParams.bitrixId
                : 0;

            const entityInput = document.querySelector(`input[name^=PROPERTY_${this.entityProperty.ID}]`);
            if (!entityInput) {
                return;
            }

            entityInput.value = id;
        },

        /**
         * @description Метод для запуска всех обработчиков событий
         * @param stack {[Function]}
         * @param event {Object}
         */
        fireEventCallbackStack: function (stack = [], event = {}) {
            stack.forEach((callback) => callback(event));
        },

        /**
         * @description Разрушает селект и его значения
         */
        destroySelect: function () {
            if (this.select.hasClass('select2-hidden-accessible')) {
                this.select.select2('destroy');
            }
        },

        /**
         * @description обработчик, который подставляет текущее значение селекта при открытии окна поиска
         */
        onOpenPopupHandler: function () {
            let value = this.select.val();
            if (!value || value === '') {
                return;
            }

            let input = $('input.select2-search__field');
            if (
                !input
                || !input.attr('type')
                || input.attr('type') !== 'search'
            ) {
                return;
            }

            input.val(value);
            input.trigger('input');
        },

        /**
         * @description Обработчик ввода данных в поле поиска, подгружает доп значения
         * @param params
         * @param success
         * @param failure
         */
        transportHandler: function (params, success, failure) {
            if (!!params.data.q) {
                if (params.data.q.length >= this.minLength) {
                    this .ajaxLoadItems({value: params.data.q, mode: 'property'}, success, failure);
                } else {
                    success({data: {
                        items: [],
                        value: {
                            id: params.data.q,
                            text: params.data.q,
                        }
                    }});
                }
            }
        },

        /**
         * @description Функция отвечающая за подгрузку значений
         * @param data
         * @param success
         * @param failure
         */
        ajaxLoadItems: function (data, success, failure) {
            BX.ajax.runComponentAction('immo:iblock.autocomplete.property', 'loadValues', {
                mode: 'class',
                data: data,
                signedParameters: this.signedParameters
            }).then(success).catch(failure);
        },

        /**
         * @description Обработчик обработки ответа от сервера, приводит полученные данные в валидный вид для select2
         * @param data
         * @returns {{results: []}}
         */
        processResultsHandler: function ({data}) {
            let result = {results: []};

            if (!!data.items && data.items.length > 0) {
                result.results = data.items;
            }

            if (!!data.value) {
                result.results.push(data.value);
            }

            return result;
        },
    }

}) (window, BX, window.$);
