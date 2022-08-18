/**
 * @description Класс для работы с несколькими формами БЕ
 */
class AvansWrapp extends WrapItems {
    /**
     * @description Инициализация класса
     */
    startInit() {
        this.defineElements([{query: 'div[data-item="true"]', key: 'blocks', all: true}]);

        this.changeMode = '';

        this.initElements();
        this.initEditors();
        this.initExtraEvents();
        this.initItems();
        this.calculateOverSpending();
        this.render();
    }

    /**
     * @description Инициализация элементов на странице
     */
    initElements() {
        this.defineElements([
            {query: '.wrap-block', key: 'wrapBlocks'},
            {query: '.add-block', key: 'addBlockBtn'},
            {query: '.error-block', key: 'errorBlock'},
            {query: '#workarea-content .bx-buttons input[name="save"]', key: 'buttonSave', global: true},

            {query: '.article-item.be-item.clone-html', key: 'cloneBlock'},

            {query: '.remove-block-all', key: 'removeAllBlockBtn'},
            {query: '.be-line.approving-remove-all', key: 'approvingRemove'},
            {query: '.be-line.approving-remove-all .remove-block-all-apply', key: 'removeBtnApply'},
            {query: '.be-line.approving-remove-all .remove-block-all-cancel', key: 'removeBtnCancel'},

            {query: 'input[name="common-sum"]', key: 'commonSum'},
            {query: 'input[name="incoming-balance"]', key: 'incomingBalance'},
            {query: 'input[name="cash-sum"]', key: 'cashSum'},
            {query: 'input[name="overspending"]', key: 'overspending'},
            {query: 'input[name="total-sum"]', key: 'totalSum'},

            {query: this.mainInputSelector, key: 'mainInput'},
            {query: `select[name^="PROPERTY_${this.props.METASTATUS ?? 0}"]`, key: 'statusSelect', global: true},
            {query: `select[name^="PROPERTY_${this.props.COUNTRY ?? 0}"]`, key: 'countrySelect', global: true},
            {query: `input[name^="PROPERTY_${this.props.SUM ?? 0}"]`, key: 'appSum', global: true},
            {query: `input[name^="PROPERTY_${this.props.CASH_SUM ?? 0}"]`, key: 'appCashSum', global: true},
            {query: `input[name^="PROPERTY_${this.props.INCOMING_BALANCE ?? 0}"]`, key: 'inBalance', global: true},
            {query: `input[name^="PROPERTY_${this.props.OVERSPENDING ?? 0}"]`, key: 'over', global: true},
            {query: `input[name^="PROPERTY_${this.props.TOTAL_SUM ?? 0}"]`, key: 'totalSumProp', global: true},
            {query: `#${this.formSelector}`, key: 'mainForm', global: true},
        ])

        if (!!this.cloneBlock) {
            BX.remove(this.cloneBlock);
        }

        if (
            (!!this.appSum && this.appSum.value != '')
            && !!this.commonSum
        ) {
            this.commonSum.value = this.appSum.value;
            if (this.createFromParent && this.autoCreate) {
                this.commonSum.disabled = true;
            }
        }

        if (
            (!!this.appCashSum && this.appCashSum.value != '')
            && !!this.cashSum
        ) {
            this.cashSum.value = this.appCashSum.value;
        }

        if (!!this.countrySelect && !!this.countryId) {
            if (this.countrySelect.value != this.countryId) {
                this.countrySelect.value = this.countryId;
            }
            this.countrySelect.disabled = true;
        }

        if (!!this.over && !!this.overspending) {
            this.overspending.value = this.over.value;
        }

        if (!!this.inBalance && !!this.incomingBalance) {
            this.incomingBalance.value = this.inBalance.value;
        }
    }

    /**
     * @description Инициализация инпутов с валидацией
     */
    initEditors() {
        if (!!this.commonSum) {
            this.commonSumEditor = this.createMaskWithEvent(this.commonSum, {
                callback: () => {
                    this.saveNewSum(this.commonSum, (!!this.appSum) ? this.appSum : {});
                    this.calculateOverSpending();
                }
            });
        }

        if (!!this.cashSum) {
            this.cashSumEditor = this.createMaskWithEvent(this.cashSum, {
                callback: this.saveNewSum.bind(this, this.cashSum, (!!this.appCashSum) ? this.appCashSum : {})
            });
        }

        if (!!this.totalSum) {
            this.totalSumEditor = this.createMaskWithEvent(this.totalSum, {
                callback: this.saveNewSum.bind(this, this.totalSum, (!!this.totalSumProp) ? this.totalSumProp : {})
            });
        }

        if (!!this.incomingBalance) {
            this.incomingBalanceEditor = this.createMaskWithEvent(this.incomingBalance, {
                callback: () => {
                    this.calculateOverSpending();
                    if (!!this.inBalance) {
                        this.inBalance.value = this.incomingBalance.value;
                    }
                }
            });
        }

        if (!!this.overspending) {
            this.overspendingEditor = this.createMask(this.overspending, {}, 2, true);
        }
    }

    /**
     * @description Инициализация дополнительных обработчиков событий
     */
    initExtraEvents() {
        if (!!this.addBlockBtn && !!this.cloneBlock) {
            this.setEventListener({
                element: this.addBlockBtn,
                eventName: 'click',
                callback: this.addBlock.bind(this)
            });
        }

        if (!!this.removeAllBlockBtn) {
            BX.bind(this.removeAllBlockBtn, 'click', this.switchApproveRemove.bind(this));
        }

        if (!!this.removeBtnCancel) {
            BX.bind(this.removeBtnCancel, 'click', this.switchApproveRemove.bind(this, false));
        }

        if (!!this.removeBtnApply) {
            this.setEventListener({
                element: this.removeBtnApply,
                eventName: 'click',
                callback: this.removeAllItems.bind(this)
            })
        }

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
    }

    /**
     * @description Проставление нового значения суммы в поле свойства
     * @param mainInput
     * @param prop
     */
    saveNewSum(mainInput = {}, prop) {
        if (!prop) {
            return;
        }

        let input = prop.parentNode.querySelector('input[id^=input]');
        if (!input) {
            if (prop instanceof Node) {
                prop.value = mainInput.value.toString();
            }
            return;
        }

        input.value = mainInput.value.toString();
        BX.fireEvent(input, 'keyup');
    }

    /**
     * @description Добавляет новый блок БЕ. Добавляет блок на страницу и в список элементов
     */
    addBlock() {
        if (!this.isArticleAvailable()) {
            return;
        }

        let cloneBlock = this.getCloneBlock();
        if (!cloneBlock) {
            return;
        }

        const wrap = this.insertNewBlock(cloneBlock);
        this.createNewBlock(wrap);
    }

    /**
     * @description Вставляет новый блок с формой БЕ на страницу
     * @param newBlock {Node}
     */
    insertNewBlock(newBlock) {
        if (!this.wrapBlocks) {
            return;
        }

        newBlock.classList.remove('clone-html')
        this.wrapBlocks.appendChild(newBlock);
        return newBlock;
    }

    /**
     * @description Возаращает список ID статей, который уже выбраны в распределениях
     * @returns {[]}
     */
    getUnavailableArticles() {
        let unAvailableArticles = [];
        this.callbackItems((item) => {
            if (item.articlesList.selectedIndex <= 0) {
                return;
            }

            unAvailableArticles.push(item.articlesList.options[item.articlesList.selectedIndex].value);
        });

        return unAvailableArticles;
    }

    /**
     * @description Проверяет, есть ли еще не занятые статьи
     * @returns {boolean}
     */
    isArticleAvailable() {
        return (this.articlesList.length <= 0)
            ? false
            : (this.articlesList.length !== this.getUnavailableArticles().length);
    }

    /**
     * @description Возращает клонированную верстку блока
     * @returns {Node}
     */
    getCloneBlock() {
        return this.cloneBlock.cloneNode(true);
    }

    /**
     * @description Инициализация блоков
     */
    initItems() {
        this.blocks.forEach(this.createNewBlock.bind(this));
    }

    /**
     * @description Создает новый объект блока по верстке
     * @param wrap {Node}
     */
    createNewBlock(wrap) {
        if (wrap.classList.contains('clone-html')) {
            return;
        }

        let id = this.generateItemId();

        this.items[id] = new WrapItems({
            id: id,
            wrap: wrap,
            mainInputSelector: this.mainInputSelector,
            props: this.props,
            draftId: this.draftId,
            cursRate: this.cursRate,
            parent: this,
            allMonth: this.allMonth,
            mainWrap: this.mainWrap,
            financialYear: this.financialYear
        })

        this.selectedItems[id] = true;
    }

    /**
     * @description Удаляем все блоки из формы и вставляет пустой блок
     */
    removeAllItems() {
        this.getItemsIds().forEach(this.removeItem.bind(this));
        this.addBlock();
        this.switchApproveRemove(false);
    }

    /**
     * @description Открывает окно предупреждения об удалении всех блоков
     * @param show {boolean}
     */
    switchApproveRemove(show = true) {
        if (!this.approvingRemove || !this.removeAllBlockBtn) {
            return;
        }

        if (show) {
            BX.hide(this.removeAllBlockBtn.parentNode);
            this.approvingRemove.style.display = 'flex';
        } else {
            BX.show(this.removeAllBlockBtn.parentNode);
            this.approvingRemove.style.display = 'none';
        }
    }

    /**
     * @description Общий метод отрисовки, сбора данных формы и сбора ошибок
     */
    render() {
        let data = {}, i = 0;

        this.calculateOverSpending();

        this.addBlockBtn.disabled = (!this.isArticleAvailable());
        this.removeAllBlockBtn.disabled = (this.getCountItems() <= 1);

        this.disableUnavailableArticles();

        this.callbackItems((item) => {
            data[i] = item.data;
            ++i;
            if (!item.removeBtn) {
                return;
            }

            item.removeBtn.disabled = (this.getCountItems() <= 1);
        })

        this.mainInput.value = JSON.stringify(data);

        this.checkErrors();
    }

    /**
     * @description Отключает выбранные статьи во всех блоках
     */
    disableUnavailableArticles() {
        let unavailableArticles = this.getUnavailableArticles();
        if (unavailableArticles.length <= 0) {
            return;
        }

        this.callbackItems((item) => {
            Array.from(item.articlesList.options).forEach((option) => {
                if (
                    (!option.value || option.value == 'N')
                    || option.value == item.articlesList.value
                ) {
                    return;
                }

                option.disabled = (unavailableArticles.includes(option.value));
            });
        });
    }

    /**
     * @description Проверяет ошибки формы
     */
    checkErrors() {
        this.checkCalculation();
        if (this.checkItems(this.items)) {
            this.deleteError('itemsError');
        }

        if (!!this.errorBlock) {
            let str = '';
            for (let key in this.errors){
                str += this.errors[key]+'<br>';
            }

            this.errorBlock.innerHTML = str;

            if (
                (
                    !!this.statusSelect
                    && this.statusSelect.value != this.draftId
                ) && (
                    str !== ''
                    || this.checkErrorRecursive(this)
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
    }

    /**
     * @description Рекурсивно проверяем ошибки у потомков
     * @param item {InheritItem}
     */
    checkErrorRecursive(item) {
        if (Object.keys(item.errors).length > 0) {
            return true;
        }

        if (item.getCountItems() <= 0) {
            return false;
        }

        let result = false;
        item.callbackItems((subItem) => {
            if (result) {
                return;
            }

            result = this.checkErrorRecursive(subItem);
        })

        return result;
    }

    /**
     * @description Собирает ошибки распределения блоков
     * @param items
     * @returns {boolean}
     */
    checkItems(items = {}) {
        if (Object.keys(items).length <= 0) {
            return;
        }

        for (let id in items) {
            if (!items[id]) {
                continue;
            }

            if (items[id].hasOwnProperty('items')) {
                this.checkItems(items[id].items);
            }

            if (!items[id].hasOwnProperty('errors') || Object.keys(items[id].errors).length <= 0) {
                continue;
            }

            this.addError('itemsError', 'Ошибка распределения!');
            return false;
        }

        return true;
    }

    /**
     * @description Проверка расчетов распределения по блокам
     */
    checkCalculation() {
        const calculated = this.calculate();

        this.totalSumEditor.value = calculated.total.toString();

        if (calculated.sum <= 0) {
            this.addError('zeroSum', 'Для корректной работы внесите данные в поле "Получили"');
            this.commonSum.parentNode.classList.add('ui-ctl-danger');
        } else {
            this.deleteError('zeroSum');
            this.commonSum.parentNode.classList.remove('ui-ctl-danger');
        }
    }

    /**
     * @description Проставляет сумму в поле Остаток/Перерасход
     */
    calculateOverSpending() {
        if (!this.overspendingEditor) {
            return;
        }

        const calculate = this.calculate();
        this.overspendingEditor.value = calculate.overspending.toString() ?? '0';
        if (!!this.over) {
            this.over.value = calculate.overspending ?? 0;
        }
    }

    /**
     * @description Метод обработчик, который срабатывает при изменении сумм
     */
    changeInputSum() {
        this.changeMode = 'editor';
        this.calculate();
        this.changeMode = '';
    }

    /**
     * @description Производит вычисление распределения блоков и возвращает расчеты
     * @returns {{total: number, sum: number, cash: number, overspending: number}}
     */
    calculate() {
        if (this.changeMode === 'editor') {
            return;
        }

        const
            sum = this.parseNumberInput(this.commonSumEditor.unmaskedValue),
            cash = this.parseNumberInput(this.cashSumEditor.unmaskedValue),
            incoming = this.parseNumberInput(this.incomingBalanceEditor.unmaskedValue);

        let total = 0;

        this.callbackItems((item) => {
            if (!item.sumInputEditor) {
                return;
            }

            total += this.parseNumberInput(item.sumInputEditor.unmaskedValue);
        })

        return {
            sum: sum,
            total: total,
            cash: cash,
            overspending: this.parseNumberInput(incoming + sum - total).toFixed(2)
        }
    }

    /**
     * @description Возвращает числовое значение инпута
     * @param value
     * @returns {number}
     */
    parseNumberInput(value) {
        let val = this.getFloat(value.toString().replace(' ', ''));
        return window.isNaN(val) ? 0 : val;
    }
}