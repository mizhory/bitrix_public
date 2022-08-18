(function () {
    if (typeof window.FinancialElement !== 'function') {
        /**
         * @description Функция для работы с свойством привязка к заявке
         * @param params
         * @constructor
         */
        window.FinancialElement = function(params) {
            this.params = params;
            this.select = {};
            this.elements = {
                payerCash: {
                    id: 0,
                    type: 'NAL',
                    key: 'CASH',
                    node: {}
                },
                payerCompany: {
                    id: 0,
                    type: 'BEZNAL',
                    key: 'COMPANY',
                    node: {}
                },
                typeSwitch: {}
            };

            this.started = true;
            this.minLength = 1;
        }

        window.FinancialElement.prototype = {
            /**
             * @description Инициализация
             */
            init: function () {
                this.entitySelect = BX(this.params.selectEntity);
                this.initTypeSwitch(this.params.props);
                this.initEvents();
                $(this.initJq.bind(this));
            },

            /**
             * @description Инициализация функционала по переключению типа очета и плательщика (в зависимости от типа отчета)
             * @param props {Object}
             */
            initTypeSwitch: function (props = {}) {
                if (props.hasOwnProperty('TYPE_AVO') && !!props.TYPE_AVO) {
                    this.elements['typeSwitch'] = window.document.querySelector(
                        `select[name^="PROPERTY_${props.TYPE_AVO.id ?? 0}"]`
                    );
                }

                if (!this.elements.hasOwnProperty('typeSwitch') || !this.elements['typeSwitch']) {
                    return;
                }

                [{prop: 'CASH', xmlId: 'NAL', key: 'Cash'}, {prop: 'COMPANY', xmlId: 'BEZNAL', key: 'Company'}]
                    .forEach((type) => {
                        if (!props.hasOwnProperty(`PAYER_${type.prop}`) || !props[`PAYER_${type.prop}`]) {
                            return;
                        }

                        if (!this.elements.hasOwnProperty(`payer${type.key}`) || !this.elements[`payer${type.key}`]) {
                            return;
                        }

                        let prop = props[`PAYER_${type.prop}`];
                        this.elements[`payer${type.key}`]['node'] = window.document.querySelector(
                            `select[name^="PROPERTY_${prop.id ?? 0}"]`
                        )

                        if (
                            !props.TYPE_AVO.hasOwnProperty('values')
                            || !props.TYPE_AVO['values']
                            || !props.TYPE_AVO.values.hasOwnProperty(type.xmlId)
                            || !props.TYPE_AVO.values[type.xmlId]
                        ) {
                            return;
                        }

                        this.elements[`payer${type.key}`]['id'] = props.TYPE_AVO.values[type.xmlId];
                    });

                window.setTimeout(this.changeTypePayer.bind(this));

                if (this.params.disable == 'Y' && !!this.elements['typeSwitch']) {
                    this.elements['typeSwitch'].disabled = true;
                }
            },

            /**
             * @description Инициализация событий
             */
            initEvents: function () {
                if (!!this.entitySelect) {
                    BX.bind(this.entitySelect, 'change', this.clearValues.bind(this));
                    if (!!this.elements.typeSwitch) {
                        BX.bind(this.entitySelect, 'change', this.switchTypePayer.bind(this));
                    }
                }

                if (!!this.elements.typeSwitch) {
                    BX.bind(this.elements.typeSwitch, 'change', this.changeTypePayer.bind(this));
                }
            },

            /**
             * @description Обработчик, который срабатывает при изменении типа финансовой заявки.
             * Переключает тип отчета
             */
            switchTypePayer: function () {
                const type = this.entitySelect.value;
                const optionsType = [...this.elements.typeSwitch.options];

                for (let typeElement in this.elements) {
                    if (!this.elements[typeElement].hasOwnProperty('id')) {
                        continue;
                    }

                    let optionType = optionsType.find((option) => (option.value == this.elements[typeElement].id));
                    if (!optionType) {
                        continue;
                    }

                    if (!!type) {
                        if (this.elements[typeElement].type != type) {
                            optionType.disabled = true;
                        } else {
                            optionType.disabled = false;
                            this.elements.typeSwitch.value = optionType.value;
                        }
                    } else {
                        optionType.disabled = false;
                    }
                }

                this.changeTypePayer();
            },

            /**
             * @description Переключение поля плательщика, в зависимости от типа отчета
             */
            changeTypePayer: function () {
                if (!this.elements.typeSwitch) {
                    return;
                }

                const value = this.elements.typeSwitch.value;
                if (!value) {
                    return;
                }

                for (let type in this.elements) {
                    if (!this.elements[type].hasOwnProperty('node')) {
                        continue;
                    }

                    let node = this.elements[type].node;
                    if (this.elements[type].id == value) {
                        BX.show(node.parentNode.parentNode);

                        this.switchCashSumBe((this.elements[type].key == 'COMPANY'));
                    } else {
                        BX.hide(node.parentNode.parentNode);
                        node.value = null;
                    }
                }
            },

            /**
             * @description Переключает поле сдано в кассу в форме БЕ
             * @param disable {Boolean}
             */
            switchCashSumBe: function (disable = true) {
                if (!window.hasOwnProperty('AvansBeInstance')) {
                    return;
                }

                const elementId = this.params['elementId'];
                if (!elementId) {
                    return;
                }

                if (
                    !window['AvansBeInstance'].hasOwnProperty(elementId)
                    || !(window['AvansBeInstance'][elementId] instanceof AvansWrapp)
                    || (
                        !window['AvansBeInstance'][elementId].hasOwnProperty('cashSum')
                        || !window['AvansBeInstance'][elementId]['cashSum']
                    )
                ) {
                    return;
                }

                if (disable) {
                    window['AvansBeInstance'][elementId]['cashSum'].disabled = true;
                    window['AvansBeInstance'][elementId]['cashSum'].value = 0;
                } else {
                    window['AvansBeInstance'][elementId]['cashSum'].disabled = false;
                }
            },

            /**
             * @description Очищает значения селекта
             */
            clearValues: function ({target}) {
                this.select.val('').trigger('change');
                let options = this.select.find('option');
                if (options.length > 0) {
                    options.remove();
                }

                if (!target.value) {
                    this.disabledSelect('disabled');
                } else {
                    this.disabledSelect();
                }
            },

            /**
             * @description Дизейблит селект заявок
             * @param value
             */
            disabledSelect: function(value = '') {
                if (value === '') {
                    this.select.removeAttr('disabled');
                } else {
                    this.select.attr('disabled', value);
                }
            },

            /**
             * @description Инииализация на jquery
             */
            initJq: function () {
                this.select = $(`#${this.params.selectApp}`);
                if (this.select.length <= 0) {
                    return;
                }

                this.initSelect();
                if (!!this.entitySelect && !this.entitySelect.value) {
                    this.disabledSelect('disabled');
                }
            },

            /**
             * @description Инициализация селект2
             */
            initSelect: function () {
                let params = {
                    placeholder: `Введите минимум ${this.minLength} символа...`,
                    width: '50%',
                    language: 'ru',
                    ajax: {
                        transport: this.transportHandler.bind(this),
                        processResults: this.processResultsHandler.bind(this)
                    }
                };

                this.select.select2(params);
                this.select.on('select2:open', this.onOpenPopupHandler.bind(this));
            },

            /**
             * @description Метод обработчик события, который срабатывает при открытии селект2
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

                if (this.params.hasOwnProperty('element') && !!this.params.element && this.started) {
                    input.val(this.params.element.NAME);
                }

                input.trigger('input');
            },

            /**
             * @description Обработчик ввода данных в поле поиска, подгружает доп значения
             * @param params
             * @param success
             * @param failure
             */
            transportHandler: function (params, success, failure) {
                if (!!this.entitySelect.value) {
                    this.started = false;
                    if (!!params.data.q) {
                        this.ajaxLoadItems(
                            {query: params.data.q, type: this.entitySelect.value},
                            success,
                            failure
                        );
                    } else {
                        success({data: {
                            items: [],
                            value: {
                                id: '',
                                text: '',
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
                BX.ajax.runComponentAction('immo:iblock.financial.element', 'loadElements', {
                    mode: 'class',
                    data: data,
                    signedParameters: this.params.signedParams
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
    }
})(BX, window);
