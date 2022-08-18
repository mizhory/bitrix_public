/**
 * @description Базовый класс-компонент для ячеек
 */
class BaseComponentCell {
    /**
     * @description Возвращает html ячейки
     * @return {*}
     */
    getGui() {
        return this.eGui;
    }

    /**
     * @description Рефшер
     * @param params
     * @return {boolean}
     */
    refresh(params) {
        return true;
    }
}

/**
 * @description Класс компонент для работы с ячейкой статьи
 */
class ArticleCell extends BaseComponentCell {
    /**
     * @description Инициализация ячейки
     * @param params
     */
    init(params) {
        this.eGui = BX.create('span', {
            attrs: {className: 'vertical-cell'},
            children: [
                BX.create('a', {
                    text: params.value,
                    attrs: {
                        className: 'min-line-height',
                        href: `/budget/edit/${params.data.beId ?? ''}/${params.data.articleId ?? ''}/${params.data.year ?? ''}/`
                    },
                })
            ]
        });
    }
}
/**
 * @description Класс компонент для работы с вертиалькой ячейкой бюджета
 */
class VerticalCell extends BaseComponentCell {
    /**
     * @description Инициализация ячейки
     * @param params
     */
    init(params) {
        this.eGui = BX.create('span', {
            attrs: {className: 'vertical-cell'},
            children: [
                BX.create('span', {text: params.value, attrs: {className: 'min-line-height'},})
            ]
        });
    }
}
/**
 * @description Класс компонент для работы с ячейкой бюджета
 */
class BudgetCell extends BaseComponentCell {
    /**
     * @description Инициализация ячейки
     * @param params
     */
    init(params) {
        let children = [];
        for (let key in params.value) {
            children.push(BX.create('span', {text: params.value[key]}));
        }

        this.eGui = BX.create('span', {attrs: {className: 'vertical-cell budget-cell'}, children: children});
    }
}

(function (window, BX) {
    if (typeof window.BudgetList !== 'function') {
        /**
         * @description Функция для работы с таблицей бюджета
         * @param params
         * @constructor
         */
        window.BudgetList = function (params = {}) {
            this.params = params;
        }
    }

    window.BudgetList.prototype = {
        /**
         * @description Инициализация
         */
        init: function () {
            this.initFilter();
            this.initSlider();
            this.initExcelDownload();
            this.initTable();
        },

        /**
         * @description Возвращает объект грид таблицы
         * @return {AgGridInstance}
         */
        getTable: function () {
            return window[this.params.id];
        },

        /**
         * @description Инициализция таблицы
         */
        initTable: function () {
            this.table = this.getTable();
            if (!this.table || !(this.table instanceof AgGridInstance)) {
                return;
            }

            this.table.gridOption.components = {
                BudgetCell: BudgetCell,
                VerticalCell: VerticalCell,
                ArticleCell: ArticleCell,
            }
            this.table.gridOption.getRowHeight = this.getRowHeight.bind(this);
            this.table.gridOption.rowHeight = 240;

            this.table.init();

            this.loadData();
        },

        /**
         * @description Расчитывает и возвращает высоту строки
         * @param params
         * @return {number}
         */
        getRowHeight: function (params) {
            const length = Object.keys(params.data.budget).length;
            return (length == 1) ? 60 : length * 40;
        },

        /**
         * @description Иницилизация фильтра для таблицы
         */
        initFilter: function () {
            BX.addCustomEvent('BX.Main.Filter:apply', this.loadData.bind(this));
        },

        /**
         * @description Загрузка данных в таблицу
         */
        loadData: function () {
            this.table.showLoader();

            BX.ajax.runComponentAction('immo:budget.list', 'loadData', {
                mode: 'class',
                signedParameters: this.params.signedParams
            })
                .then(({data = []}) => {
                    this.updateGridData(data);
                    this.table.hideLoader();
                })
                .catch((response) => {
                    console.log(response);
                    this.table.hideLoader();
                })
        },

        /**
         * @description Обновление данных в таблице
         * @param data
         */
        updateGridData: function (data = []) {
            this.table.gridOption.api.setRowData(data);
        },

        /**
         * @description Иницилизация слайдера
         */
        initSlider: function () {
            BX.SidePanel.Instance.bindAnchors({rules: [{
                condition: ["/budget/edit"],
                options: {
                    allowChangeHistory: false,
                    onclose: this.loadData.bind(this),
                    cacheable: false,
                    animationDuration: 100,
                    width: 540
                }
            }]});
        },

        /**
         * @description Инициализация кнопки выгрузки в excel
         */
        initExcelDownload: function () {
            this.excelBtn = BX('excel-download');
            if (!this.excelBtn) {
                return;
            }

            if (!window.InternalLoadFile) {
                return;
            }

            this.excelProccess = false;
            BX.bind(this.excelBtn, 'click', this.loadExcel.bind(this));
        },

        /**
         * @description Загрузка excel файла
         */
        loadExcel: function () {
            if (this.excelProccess) {
                return;
            }

            this.excelProccess = true;
            this.table.showLoader();

            const loader = new window.InternalLoadFile(
                '/local/ajax/xml.php',
                'Выгрузка бюджета.xlsx', () => {
                    this.excelProccess = false;
                    this.table.hideLoader();
                },
                this.params.excelParams ?? {}
            );
            loader.load();
        },
    };
}) (window, BX);
