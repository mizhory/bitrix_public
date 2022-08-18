(function () {
    if (typeof window.PayRecipient !== 'function') {
        /**
         * @description Функция для работы с свойством получатель платежа
         * @param params
         * @constructor
         */
        window.PayRecipient = function(params) {
            this.params = params;
            this.elements = {};
            this.jq = {};

            this.nameAttr = '';
            this.userSelect = {};
            this.selectWrap = {};
            this.started = true;
            this.minLength = 1;
        }

        window.PayRecipient.prototype = {
            /**
             * @description Инициализация
             */
            init: function () {
                this.initElements(this.params.id, this.params.config);
                $(this.initJq.bind(this));
            },

            /**
             * @description Инициализация элементов на странице
             * @param id {String}
             * @param config {Object}
             */
            initElements: function (id = '', config = {}) {
                if (!id) {
                    return;
                }

                this.elements.main = BX(id);
                if (!this.elements.main) {
                    return;
                }

                if (!!config.NAME) {
                    this.elements.name = document.querySelector(config.NAME.select);
                    this.nameAttr = this.elements.name.getAttribute('name');
                }

                if (!!config.CHECKBOX) {
                    this.elements.checkbox = document.querySelector(config.CHECKBOX.select);
                }

                this.initInputs([this.elements.main, this.elements.name]);
            },

            /**
             * Инициализация обертки для инпутов
             * @param inputs {HTMLInputElement[]}
             */
            initInputs: function (inputs = []) {
                inputs.forEach((input) => {
                    if (!input) {
                        return;
                    }

                    input.classList.add('ui-ctl-element');

                    input.parentNode.appendChild(
                        BX.create('div', {children: [
                            BX.create('div', {
                                attrs: {className: 'ui-ctl ui-ctl-textbox ui-ctl-inline ui-ctl-sm ui-ctl-w75'},
                                children: [input]
                            })
                        ]
                    }))
                })
            },

            /**
             * @description Инициализация событий
             */
            initEvents: function () {
                if (!!this.elements.checkbox) {
                    BX.bind(
                        this.elements.checkbox,
                        'change',
                        this.checkValue.bind(this, this.params.config.CHECKBOX.values)
                    );
                }
            },

            /**
             * @description Проверка значений чекбокса
             * @param values {[Object]}
             * @param event {target: {}}
             */
            checkValue: function (values = [], event = {target: {}}) {
                const yesEnum = values.find((enumProp) => enumProp.ID == event.target.value);
                const isEvent = (event instanceof Event);

                if (yesEnum.XML_ID == 'Y') {
                    this.jq['main'].disable();
                    this.elements.main.value = '';
                    this.elements.main.disabled = true;
                    this.jq['name'].disable();
                    this.switchNameField(true, isEvent);
                } else {
                    this.jq['main'].enable();
                    this.elements.main.disabled = false;
                    this.jq['name'].enable();
                    this.switchNameField(false, isEvent);
                }
            },

            /**
             * @description Инииализация на jquery
             */
            initJq: function () {
                [
                    {key: 'main', element: this.elements.main},
                    {key: 'name', element: this.elements.name}
                ].forEach(({element, key}) => {
                    if (!element) {
                        return;
                    }

                    this.jq[key] = $(element).suggestions({
                        token: this.params.token,
                        type: "PARTY",
                        onSelect: (suggestion) => {
                            this.elements.main.value = suggestion.data.inn;
                            this.elements.main.setAttribute('value', this.elements.main.value);

                            this.elements.name.value = suggestion.value.replaceAll('"','');
                            this.elements.name.setAttribute('value', this.elements.name.value);
                        }
                    }).suggestions();
                })

                this.initSelect();
                this.initEvents();
                this.checkValue(this.params.config.CHECKBOX.values, {target: this.elements.checkbox});
            },

            /**
             * @description Переключатель режима работы поля получателя платежа
             * @param select
             * @param clearValues
             */
            switchNameField: function (select = true, clearValues = false) {
                if (clearValues) {
                    this.clearValues();
                }

                if (select) {
                    BX.hide(this.elements.name.parentNode.parentNode);
                    BX.show(this.selectWrap);
                    this.userSelect.attr('name', this.nameAttr);
                    this.elements.name.removeAttribute('name');
                } else {
                    BX.show(this.elements.name.parentNode.parentNode);
                    BX.hide(this.selectWrap);
                    this.userSelect.removeAttr('name');
                    this.elements.name.setAttribute('name', this.nameAttr);
                }
            },

            /**
             * @description инициализация селектора для поиска по пользователям
             */
            initSelect: function () {
                if (!this.elements.name) {
                    return;
                }

                let parentWrap = this.elements.name.closest('table.bx-edit-table tr td.bx-field-value');
                if (!parentWrap) {
                    return;
                }

                let selectParams = {
                    attrs: {
                        name: this.elements.name.getAttribute('name'),
                        className: 'ui-ctl-element'
                    },
                }

                if (!!this.elements.name.value) {
                    selectParams.children = [
                        BX.create('option', {text: this.elements.name.value, attrs: {value: this.elements.name.value}})
                    ]
                }

                let select = BX.create('select', selectParams);

                let wrap = BX.create('div', {children: [
                    BX.create('div', {
                        attrs: {className: 'ui-ctl-w75'},
                        children: [select]
                    })
                ]});

                parentWrap.appendChild(wrap);
                this.selectWrap = wrap;
                this.userSelect = $(select);

                this.userSelect.select2({
                    width: 'resolve',
                    language: 'ru',
                    allowClear: true,
                    placeholder: `Введите минимум ${this.minLength} символа...`,
                    ajax: {
                        transport: this.transportHandler.bind(this),
                        processResults: this.processResultsHandler.bind(this)
                    }
                });

                this.userSelect.on('select2:open', this.onOpenPopupHandler.bind(this));
                this.userSelect.on('select2:clearing', this.clearValues.bind(this));
            },

            /**
             * @description Очитска значений полей наименование получателя
             */
            clearValues: function () {
                if (!!this.elements.name) {
                    this.elements.name.value = '';
                }

                if (!!this.userSelect) {
                    this.userSelect.val(null).trigger('change');
                    let options = this.userSelect.find('option');
                    if (options.length > 0) {
                        options.remove();
                    }
                }
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
                        this .ajaxLoadItems({value: params.data.q}, success, failure);
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
                BX.ajax.runComponentAction('immo:iblock.payment.recipient', 'loadUsers', {
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

            /**
             * @description обработчик, который подставляет текущее значение селекта при открытии окна поиска
             */
            onOpenPopupHandler: function () {
                let value = this.userSelect.val();
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
        }
    }
})(BX, window);
