(function (window, BX) {
    if (typeof window.PrintList !== 'function') {
        /**
         * @description Метод загрузки печати файлов из списка
         * @param grid {BX.Main.grid}
         * @constructor
         */
        window.PrintList = function (grid) {
            window.PrintList.loadFiles(grid);
        }
    }

    let print = window.PrintList;

    /**
     * @description Метод загркузки множественных файлов печати
     * @param grid {BX.Main.grid}
     */
    print.loadFiles = function (grid) {
        const data = print.getGridData(grid);
        if (data.length <= 0) {
            return;
        }

        print.startLoad(data, grid);
    }

    /**
     * @description Показывает лоадер в гриде
     * @param grid {BX.Main.grid}
     */
    print.showLoader = function (grid = {}) {
        grid.tableFade();
    }

    /**
     * @description Скрывает лоадер из грида
     * @param grid {BX.Main.grid}
     */
    print.hideLoader = function (grid) {
        grid.getRows().unselectAll();
        grid.tableUnfade();
    }

    /**
     * @description НАчинает загрузку файлов. Достает ссылки на генерацию файлов
     * @param data {Object[]}
     * @param grid {BX.Main.grid}
     */
    print.startLoad = function (data = [], grid = {}) {
        print.showLoader(grid);

        BX.ajax.runComponentAction('immo:iblock.print.values', 'printList', {mode: 'class', data: {data: data}})
            .then(print.onFileLinksLoaded.bind(this, data, grid))
            .catch(() => {
                console.error('Ошибка при множественной загрузке!');
                print.hideLoader(grid);
            })
    }

    /**
     * @description Обработчик срабатываемый при скачивании ссылок на генерацию файлов. Запускает скачивание файлов в браузер
     * @param gridData {Object[]}
     * @param grid {BX.Main.grid}
     * @param data {Object}
     */
    print.onFileLinksLoaded = function (gridData = [], grid, {data = {}}) {
        if (!data && Object.keys(data).length <= 0 && Array.isArray(data)) {
            this.hideLoader(grid);
            return;
        }

        const lastId = Object.keys(data).shift();
        const names = this.getRowNames(grid);
        let lastIdFounded = false;

        let printItems = [];

        gridData.forEach((element) => {
            if (!element.printable) {
                printItems.push(element);
                return;
            }

            if (!data[element.id] || data[element.id] == '') {
                return;
            }

            print.loadFileInternal(
                data[element.id] ?? '',
                names[element.id] ?? '',
                (lastId == element.id) ? this.hideLoader.bind(this, grid) : null
            );
        });

        if (gridData.length == printItems.length) {
            window.alert('Печать невозможна, так как в выборе есть заявка на безналичный расчет.');
        }

        if (!lastIdFounded) {
            window.setTimeout(this.hideLoader.bind(this, grid));
        }
    }

    /**
     * @description Низкоуровневое скачивание файла в барузере
     * @param src {string}
     * @param fileName {string}
     * @param callback {function}
     */
    print.loadFileInternal = function (src = '', fileName = '', callback) {
        (new window.InternalLoadFile(src, fileName, callback)).load();
    }

    /**
     * @description Получение данных грида
     * @param grid {BX.Main.grid}
     * @return Array
     */
    print.getGridData = function (grid) {
        return grid.getRows().getSelected().map(print.collectRowsData.bind(this));
    }

    /**
     * @description Возвращает названия строк
     * @param grid {BX.Main.grid}
     * @return Object
     */
    print.getRowNames = function (grid) {
        let names = {};
        grid.getRows().getSelected().forEach((row) => {
            names[row.getId()] = row.getDataset().name;
        });

        return names;
    }

    /**
     * @description Собирает и возвращает данные с одной строки грида
     * @param row {BX.Grid.Row}
     * @return {{id: (string|number), iblockId: (string|number)}}
     */
    print.collectRowsData = function (row) {
        return {
            id: row.getId(),
            iblockId: row.getDataset().iblockid ?? 0,
            printable: row.getDataset().printable == 'Y'
        }
    }
})(window, BX);