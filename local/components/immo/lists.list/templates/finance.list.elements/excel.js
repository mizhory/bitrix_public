if (typeof window.FinancialListExcel !== 'function') {
    /**
     * @description Функция для работы с выгрузкой в эксель финансовых заявок
     * @param gridId {string}
     * @param popupIp {string}
     * @param url {string}
     * @param addButton {Node}
     * @constructor
     */
    window.FinancialListExcel = function ({gridId = '', popupIp = '', url = '', addButton}) {
        this.gridId = gridId;
        this.popupIp = popupIp;
        this.url = url;
        this.addButton = addButton;
    }

    /**
     * Прототипы функции
     * @type {{getGrid: (function(): *), closeLoader: Window.FinancialListExcel.closeLoader, generateRandomString: (function(*=): string), openInternal: Window.FinancialListExcel.openInternal, showLoader: (function(): undefined), open: Window.FinancialListExcel.open}}
     */
    window.FinancialListExcel.prototype = {
        /**
         * @description Открытие страницы со ссылкой на скачивание
         * @param url {String}
         */
        open: function (url = '') {
            this.showLoader();
            window.setTimeout(this.closeLoader.bind(this), 2000);
            this.openInternal(url);
        },

        /**
         * @description Запускает генерацию файла выгрузки и открывает файл
         * @param data {Object}
         */
        download: function (data = {}) {
            this.showLoader();

            BX.ajax.runComponentAction('immo:iblock.financial.excel', 'generateExcel', {
                mode: 'class',
                data: {params: data},
            })
                .then(this.responseHandler.bind(this))
                .catch(this.errorHandler.bind(this));
        },

        /**
         * @description Открывает файл выгрузки в новой вкладке
         * @param data {Object}
         */
        responseHandler: function ({data = {}}) {
            if (!data.src) {
                throw new Error('Ошибка при генерации файла');
            }

            this.openInternal(data.src);
            this.closeLoader();
        },

        /**
         * @description Обработчик ошибок
         * @param errors
         */
        errorHandler: function ({errors = []}) {
            let mainError = errors.find((error) => error.code == 100);
            if (!mainError) {
                window.alert('Ошибка генерации');
            } else {
                window.alert(mainError.message);
            }

            this.closeLoader();
        },

        /**
         * @description Отображает лоадеры грида и кнопки
         */
        showLoader: function () {
            const grid = this.getGrid();
            const menu = BX.PopupMenu.getMenuById(this.popupIp);

            if (!menu) {
                return;
            }

            menu.close();
            if (!!menu.bindElement) {
                menu.bindElement.classList.add('ui-btn-disabled');
            }

            if (!!this.addButton) {
                this.addButton.classList.add('ui-btn-disabled');
            }

            if (!!grid) {
                grid.tableFade();
            }
        },

        /**
         * @description Закрывает лоадеры грида и кнопки
         */
        closeLoader: function () {
            const grid = this.getGrid();
            const menu = BX.PopupMenu.getMenuById(this.popupIp);

            if (!!menu.bindElement) {
                menu.bindElement.classList.remove('ui-btn-disabled');
            }

            if (!!this.addButton) {
                this.addButton.classList.remove('ui-btn-disabled');
            }

            grid.tableUnfade();

        },

        /**
         * @description Низкоуровневое открытие страницы
         * @param url {String}
         */
        openInternal: function (url) {
            let urlObj = new URL(window.location.origin + url);
            urlObj.searchParams.set('key', this.generateRandomString());
            window.open(urlObj.toString());
        },

        /**
         * @description Генерация случайной строки
         * @param length
         * @return {string}
         */
        generateRandomString: function(length = 25) {
            let random = 0, randString = '';
            while (randString.length < length) {
                random = Math.random() * 42
                randString += (random < 10 || random > 16) ? String.fromCharCode(48 + random | 0) : '';
            }

            return randString;
        },

        /**
         * @description Возвращает объект грида
         * @return {?BX.Main.grid}
         */
        getGrid: function () {
            return window.BX.Main.gridManager.getById(this.gridId).instance;
        }
    };

    /**
     * @description Инициализация общего списка финансовых заявок (выгрузка в эксель)
     * @param params {Object}
     */
    window.FinancialListExcel.initInstance = function (params) {
        window.FinancialListExcel.instance = new window.FinancialListExcel(params);
    }

    /**
     * @description Возвращает синглтон списка финансовых заявок
     * @return {?window.FinancialListExcel}
     */
    window.FinancialListExcel.getInstance = function () {
        return window.FinancialListExcel.instance;
    }

    /**
     * @description Синглтон списка финансовых заявок
     * @type {window.FinancialListExcel}
     */
    window.FinancialListExcel.instance = {};
}