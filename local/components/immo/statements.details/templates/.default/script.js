class StatementsDetails
{
    constructor(options) {
        this.gridId = options['grid_id'];
        this.isAdmin = options['is_admin'];
        this.role = options['role'];
        this.elementId = options['element_id'];

        this.gridManager = BX.Main.gridManager.getById(this.gridId);
    }

    /**
     * Инициализирует js в шаблоне компонента
     *
     * @param gridId - id грида
     * @param isAdmin - является ли текущий пользователь админом
     */
    static init(options) {
        const obj = new StatementsDetails(options);

        BX.addCustomEvent('Grid::ready', grid => {
        });

        BX.addCustomEvent('Grid::updated', grid => {
            obj.hideSettingsIcon();
            obj.handleEvents();
            obj.setFinalRowsStyles();
        });

        obj.hideSettingsIcon();
        obj.setFinalRowsStyles();
        obj.handleEvents();
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
            data: params
        })
    }

    /**
     * Автосохранение полей
     *
     * @param rowId
     * @param field
     * @param value
     * @param input
     */
    updateFields(rowId, field, value, input, role) {
        this.postComponentData('updateHlElement', {
            rowId: rowId,
            field: field,
            role: role,
            value: value
        }).then(response => {
            console.log('row id:',  rowId, '|', 'field:', field, '|', 'value:', value, '|', 'status:', response.status, '|', 'role:', role);
            const reloadParams = {
                clear_nav: 'Y',
                elementId: this.elementId
            };
            if (response.status === 'success' && this.gridManager.hasOwnProperty('instance')) {
                input.value = value;
                this.gridManager.instance.reloadTable('POST', reloadParams);
            }
        });
    }

    updateGeneralTotalSumCalculation(params) {
        this.postComponentData('updateGeneralTotalSumCalculation', params)
            .then(response => {
                if(response.data['update_hl_elements'] && response.data['update_salary_statement_element']) {
                    const reloadParams = {
                        clear_nav: 'Y'
                    };
                    this.gridManager.instance.reloadTable('POST', reloadParams);
                }
            });
    }

    /**
     * Обработчик событий
     */
    handleEvents() {
        const inputs = document.querySelectorAll('.grid-editable-field');
        if(inputs) {
            inputs.forEach(input => {
                input.addEventListener('change', e => {

                    let field = e.target.name;
                    let rowId = e.target.dataset.rowId;
                    let value = e.target.value;

                    this.updateFields(rowId, field,value, input, this.role);
                })
            })
        }

        const generalTotalSumSelector = document.getElementById('UF_TOTAL_SUM_CALCULATION_TYPE_GENERAL');

        if(generalTotalSumSelector) {
            generalTotalSumSelector.addEventListener('change', event => {
                const params = {
                    elementId: Number(event.target.dataset.elementId),
                    role: this.role,
                    value: Number(event.target.value),
                };

                this.updateGeneralTotalSumCalculation(params);
            })
        }

        const approvalHistoryLink = document.getElementById('approval-history');
        if(approvalHistoryLink) {
            approvalHistoryLink.addEventListener('click', e => {
                BX.SidePanel.Instance.open('bitrix:bizproc.log', {
                    contentCallback: slider => {
                        return new Promise((resolve, reject) => {
                            this.postComponentData('getBizprocLog', {
                                role: this.role,
                                elementId: this.elementId
                            })
                                .then(response => {
                                    resolve({html: response.data.html});
                                })
                        })
                    },
                    cacheable: false,
                    allowChangeHistory:false
                });
            })
        }

    }

    /**
     * Устанавливает стили на итоговую строку
     */
    setFinalRowsStyles()
    {
        const finalRowTitle = document.querySelector('td.final-row');

        if(finalRowTitle) {
            const tr = BX.findParent(finalRowTitle, {
                'tag': 'tr'
            });
            const actionCell = BX.findChild(tr, {
                'tag': 'td',
                'class': 'main-grid-cell-action'
            })

            if(actionCell) {
                actionCell.classList.add('final-row', 'final-row-title');
                actionCell.innerHTML = 'Итого:'
            }
        }
    }

    /**
     * скрывает иконку выбора полей в таблице для пользователей, не имеющих админправ
     */
    hideSettingsIcon()
    {
        const icon = document.querySelector('span.main-grid-interface-settings-icon');

        if(!this.isAdmin) {
            icon.remove();
        }
    }
}