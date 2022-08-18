/**
 * @description Кастомный класс лоадера
 */
class CustomLoadingOverlay {
    /**
     * @description Инициализация лоадера
     * @param params
     */
    init(params) {
        this.eGui = this.createLoader();
    }

    /**
     * @description Рендер лоадера
     * @return {div}
     */
    createLoader() {
        return BX.create("div", {
            props: {
                className: "side-panel-default-loader-container"
            },
            html:
                '<svg class="side-panel-default-loader-circular" viewBox="25 25 50 50">' +
                '<circle ' +
                'class="side-panel-default-loader-path" ' +
                'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"' +
                '/>' +
                '</svg>'
        });
    }

    /**
     * @description Возвращает лоадер
     * @return {div|*}
     */
    getGui() {
        return this.eGui;
    }

    /**
     * @description Рефреш
     * @param params
     * @return {boolean}
     */
    refresh(params) {
        return false;
    }
}

(function (window, BX) {
    if (typeof window.AgGridInstance !== 'function') {
        /**
         * @description Функция обертка для грид таблицы
         * @param params
         * @constructor
         */
        window.AgGridInstance = function (params = {}) {
            this.params = params;
            this.initWrap();
            this.initTable();
        }
    }

    /**
     *
     * @type {{init: Window.AgGridInstance.init, hideLoader: Window.AgGridInstance.hideLoader, initWrap: Window.AgGridInstance.initWrap, showLoader: Window.AgGridInstance.showLoader, getData: (function(): *|*[]), initTable: (function(): undefined), getColumns: (function(): *|*[])}}
     */
    window.AgGridInstance.prototype = {
        /**
         * @description Инициализация обертки
         */
        initWrap: function () {
            this.wrap = BX(this.params.id);
        },

        /**
         * @description Возвращает массив колонок
         * @return {Object[]}
         */
        getColumns: function () {
            return !!this.params.columns ? this.params.columns : [];
        },

        /**
         * @description Возвращает массив данных
         * @return {Object[]}
         */
        getData: function () {
            return !!this.params.data ? this.params.data : [];
        },

        /**
         * @description Инициализация параметров таблицы
         */
        initTable: function () {
            if (!this.wrap) {
                return;
            }

            const columns = this.getColumns();
            if (columns.length <= 0) {
                return;
            }

            const data = this.getData();

            this.gridOption = {
                suppressRowTransform: true,
                defaultColDef: {
                    width: 170,
                },
                columnDefs: columns,
                rowData: data,
                animateRows: true,
                loadingOverlayComponent: CustomLoadingOverlay,
            };
        },

        /**
         * @description Инициализация самой таблицы
         */
        init: function () {
            this.grid = new agGrid.Grid(this.wrap, this.gridOption);
        },

        /**
         * @description Открывает лоадер
         */
        showLoader: function () {
            this.gridOption.api.showLoadingOverlay();
        },

        /**
         * @description Убирает лоадер
         */
        hideLoader: function () {
            this.gridOption.api.hideOverlay();
        },
    };
}) (window, BX);
