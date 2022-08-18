class StatementsDetails {

    static init(options) {
        const obj = new StatementsDetails(options);
        obj.handleEvents();
    }

    constructor(options) {
        this.currency = options.currency??'RUB';
        this.columnDefs = options.arHeaderTable;
        this.arOptionsRowsTable = options.arOptionsRowsTable;
        this.arColumnsRowsTable = options.arColumnsRowsTable;
        // добавление рендера для ячеек
        this.columnDefs.forEach((elem => {
            // свойста ячеек
            let options = this.findOptionsRowByCode(elem.field);
            elem.cellRendererParams = {
                immoOptions: {...options},
            }
            elem.cellRenderer = (params) => {
                let sValueField = params.getValue();
                let sCodeField = params.colDef.field;
                let iIdRowField = params.data.ID;
                const eTemp = document.createElement('div');
                // это сделано только для футера, у футуера в sValueField = {value, valueFormatted}
                if (typeof sValueField === 'object') {
                    eTemp.innerHTML = sValueField.valueFormatted;
                } else {
                    console.log(params.immoOptions.type)
                    // обычное отрисовка поелй
                    switch (params.immoOptions.type) {
                        case 'employee':
                            // для этого типа свойства надо поучить его представление, чтобы не
                            //выводить ID пользователя а получить Мостовой Алексей
                            let sTmpValue = this.findColumnsRowByCode(sCodeField, iIdRowField);
                            eTemp.innerHTML = sTmpValue;
                            break
                        case 'money':
                            if(BX.Currency && BX.Currency.currencyFormat){
                                eTemp.innerText = BX.Currency.currencyFormat(sValueField, this.currency);
                            }else{
                                eTemp.innerHTML = sValueField;
                            }
                            break
                        case 'money_editable':
                        case 'string_editable':
                            let id = 'field[' + iIdRowField + '][' + sCodeField + ']';
                            let obData = {
                                type: 'text',
                                attrs: {
                                    id: id,
                                    name: id
                                },
                                props: {
                                    className: 'ui-ctl ui-ctl-textbox ui-ctl-element grid-editable-field',
                                    value: sValueField
                                },
                                events: {
                                    input: (event) => {
                                        this.updateRowData(
                                            iIdRowField,
                                            sCodeField,
                                            event.target.value
                                        );
                                    },
                                    paste: (event) => {
                                        this.updateRowData(
                                            iIdRowField,
                                            sCodeField,
                                            event.target.value
                                        );
                                    },
                                    copy: (event) => {
                                        this.updateRowData(
                                            iIdRowField,
                                            sCodeField,
                                            event.target.value
                                        );
                                    },
                                    change: (event) => {
                                        this.updateRowData(
                                            iIdRowField,
                                            sCodeField,
                                            event.target.value
                                        );
                                    },
                                },
                            }
                            let input = BX.create(
                                'input',
                                obData,
                            );
                            if (params.immoOptions.type === 'money_editable') {
                                this.createMask(input);
                            }
                            eTemp.append(input)
                            break
                        case 'enumeration_editable':
                            // this work here
                            console.log(params.immoOptions.items)
                            break;
                        case 'hidden':
                        case 'default':
                        default:
                            eTemp.innerHTML = sValueField;
                            break
                    }
                }
                return eTemp;
            }
        }));
        this.arRowsTable = options.arRowsTable;
        this.columFooter = options.arFooterTable;
        this.signedParameters = options['signedParameters'];
        this.gridOptions = {
            getRowId: function (params) {
                return params.data.ID;
            },
            defaultColDef: {
                resizable: false,
            },
            columnDefs: this.columnDefs,
            rowData: this.arRowsTable,
            pinnedBottomRowData: [
                this.columFooter
            ],
            // onFirstDataRendered: (params) => params.api.sizeColumnsToFit()
            onFirstDataRendered: (params) => {
                // this.api.sizeColumnsToFit()

                // const allColumnIds = [];
                // params.columnApi.getAllColumns().forEach((column) => {
                //     if (column.getId() !== 'UF_COMMENT' && column.getId() !== 'UF_TOTAL_SUM_CALCULATION_TYPE') {
                //         allColumnIds.push(column.getId());
                //     }
                // });
                // params.columnApi.autoSizeColumns(allColumnIds, true);
            }
        };
        this.gridId = options['grid_id'];
        this.saveTimer = 0;
        this.formId = document.querySelector('form[name="form_' + this.gridId + '"]');
        this.eGridDiv = document.querySelector('#table-wrapper');
    }

    /**
     * Свойства полей в таблице
     * @param sCodeField
     * @returns {boolean|*}
     */
    findOptionsRowByCode(sCodeField) {
        let resultObject = this.arOptionsRowsTable[sCodeField];
        if (resultObject) {
            if (!resultObject.type) {
                resultObject.type = 'default';
            }
            if (typeof resultObject.editable !== 'boolean') {
                resultObject.editable = false;
            }
            if(resultObject.editable){
                resultObject.type = resultObject.type + '_editable';
            }
            return resultObject;
        }
        return false;
    }

    /**
     * Получить представляемое занчение свойства
     * @param sCodeField
     * @param iRowId
     * @returns {boolean|*}
     */
    findColumnsRowByCode(sCodeField, iRowId) {
        let resultObject = this.arColumnsRowsTable[sCodeField][iRowId];
        if (resultObject) {
            return resultObject;
        }
        return false;
    }

    /**
     * Обновление в глобальном хранилице данных, далее эти данные отправятся на сервер
     * @param iId
     * @param sCodeField
     * @param sValue
     */
    updateByIdFieldInRow(iId, sCodeField, sValue) {
        this.arRowsTable.forEach(
            (elm) => {
                if (elm.ID == iId) {
                    elm[sCodeField] = sValue;
                }
            }
        )
    }

    /**
     * Данные которые оправятся на сервер
     * @returns {*}
     */
    prepareFormData() {
        return this.arRowsTable;
    }

    /**
     * Вызывается при изменни данных в ипуте
     * @param iId
     * @param sCodeField
     * @param sValue
     */
    updateRowData(iId, sCodeField, sValue) {
        BX.onCustomEvent(
            'updateRowData',
            [
                iId, sCodeField, sValue
            ]
        );
    }

    /**
     * обнолвение данных
     * @param data
     */
    updateForm(data) {
        let wait = BX.showWait(this.formId);
        this.postComponentData('updateForm', {
            formData: data,
        }).then(response => {
            if (response.status === 'success') {
                if (response.data && response.data.obResult) {
                    if (response.data.obResult.arRowsTable) {
                        this.gridOptions.api.setRowData(response.data.obResult.arRowsTable);
                    }
                    if (response.data.obResult.arFooterTable) {
                        this.gridOptions.api.setPinnedBottomRowData(
                            [
                                response.data.obResult.arFooterTable
                            ]
                        )
                    }
                }
            }
            BX.closeWait(this.formId, wait);
        });
    }


    /**
     * Запускает процедуру обновления данных с ожиданием в 2 сек
     * после последного измения данных
     */
    updateEvent() {
        if (this.saveTimer) {
            clearInterval(this.saveTimer);
            this.saveTimer = 0;
        }
        this.saveTimer = setTimeout(() => {
            this.updateForm(this.prepareFormData());
        }, 2000)
    }

    /**
     * Добавляется события для изменения данных
     */
    handleEvents() {
        BX.bindDelegate(
            this.formId,
            'change',
            {
                attrs: {
                    name: 'UF_TOTAL_SUM_CALCULATION_TYPE'
                }

            },
            (event) => this.updateGeneralTotalSumCalculation(event)
        );
        this.ob = new agGrid.Grid(this.eGridDiv, this.gridOptions);

        // событие изменение данных
        BX.addCustomEvent('updateRowData', (iId, sCodeField, sValue) => {
            this.updateByIdFieldInRow(iId, sCodeField, sValue);
            this.updateEvent()
        });
    }

    updateGeneralTotalSumCalculation(event) {
        const params = {
            elementId: Number(event.target.dataset.elementId),
            role: this.role,
            value: Number(event.target.value),
        };
        this.postComponentData('updateGeneralTotalSumCalculation', params)
            .then(response => {
                if (response.data && response.data.obResult) {
                    if (response.data.obResult.arRowsTable) {
                        this.gridOptions.api.setRowData(response.data.obResult.arRowsTable);
                    }
                    if (response.data.obResult.arFooterTable) {
                        this.gridOptions.api.setPinnedBottomRowData(
                            [
                                response.data.obResult.arFooterTable
                            ]
                        )
                    }
                }
            });
    }

    /**
     * Добавляет маску для инпута
     * @param input
     * @param callback
     * @param scale
     * @param negative
     * @returns {InputMask}
     */
    createMask(input, callback = {}, scale = 2, negative = false) {
        const mask = IMask(input, {
            scale: scale,
            mask: Number,
            thousandsSeparator: ' ',
            radix: ',',
            signed: negative
        });
        if (!!callback && typeof callback == 'function') {
            mask.on('complete', callback);
        }
        BX.addClass(
            input,
            'js-money-mask-ready'
        );
        return mask;
    }


    /**
     * Отправляет post-запрос к классу компонента
     *
     * @param action
     * @param params
     * @returns {Promise<*>}
     */
    async postComponentData(action, params = {}) {
        return await BX.ajax.runComponentAction('immo:statements.details', action, {
            mode: 'class',
            data: params,
            signedParameters: this.signedParameters
        })
    }
}