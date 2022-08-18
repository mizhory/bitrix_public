/**
 * @description Объект для работы с генерацией зарплатных ведомостей
 */
if (typeof window.GenerationSalary != "object") {
    window.GenerationSalary = {};
}

/**
 * @description Обработчик события клика генерации из меню
 * @param event {PointerEvent}
 * @param item {MenuItem}
 */
window.GenerationSalary.eventHandler = function (event, item) {
    if (!item || !item.hasOwnProperty('data') || !item.data) {
        return;
    }

    window.GenerationSalary.generate(item.data);
    item.getMenuWindow().close();
}

/**
 * @description Метод отправки ajax запроса для генерации ведомости
 * @param data {Object}
 */
window.GenerationSalary.generate = function (data = {}) {
    window.GenerationSalary.checkGeneration(data)
        .then(window.GenerationSalary.generateInternal.bind(this, data))
        .catch(window.GenerationSalary.errorHandler.bind(this))
}

/**
 * @description Метод проверки генерации ведомости
 * @param data {Object}
 */
window.GenerationSalary.checkGeneration = function (data = {}) {
    return BX.ajax.runComponentAction('immo:statements.list', 'checkGeneration', {mode: 'class', data: data});
}

/**
 * @description Запуск генерации ведомости
 * @param data {Object}
 * @param response {Object}
 */
window.GenerationSalary.generateInternal = function (data, response) {
    if (response.status != 'success') {
        throw new Error('Ошибка генерации!');
    }

    let {result: structure, text: arText} = window.GenerationSalary.processResult(response.data);

    if (!!structure && Object.keys(structure).length > 0) {
        BX.ajax.runComponentAction('immo:statements.list', 'generateSalary', {mode: 'class', data: {
            time: data.time ?? '',
            structure: structure
        }})
            .then(window.GenerationSalary.generateStarted.bind(this, arText))
            .catch(window.GenerationSalary.errorHandler.bind(this));
    } else if (arText.length > 0) {
        window.GenerationSalary.createPopup(arText.join('<br>'), 'Генерация зарплатной ведомости').show();
    } else {
        throw new Error('Ошибка генерации!');
    }
}

/**
 * @description Обработка проверки генерации
 * @param result {Object}
 * @return {Object}
 */
window.GenerationSalary.processResult = function (result) {
    let newResult = {}, arText = [];

    for (let beId in result) {
        let be = result[beId];

        if (!be.hasOwnProperty('companies') || Object.keys(be.companies).length <= 0) {
            console.error('Ошибка обработки данных!');
            continue;
        }

        if (be.hasOwnProperty('error') && !! be.error) {
            arText.push(be.error);
            continue;
        }

        for (let id in be.companies) {
            let resultCheck = be.companies[id];

            if (!resultCheck) {
                continue;
            }

            if (Array.isArray(resultCheck.errors) && resultCheck.errors.length > 0) {
                arText.push(resultCheck.errors.join('; '));
            } else if (resultCheck.status == 'success' && resultCheck.text != '') {
                if (!newResult.hasOwnProperty(beId)) {
                    newResult[beId] = {};
                }
                newResult[beId][id] = id;
                arText.push(resultCheck.text);
            } else {
                console.error('Ошибка обработки данных!');
            }
        }
    }

    return {
        result: newResult,
        text: arText
    }
}

/**
 * @description Метод проверки генерации ведомости
 * @param arText {String[]}
 * @param status {String}
 */
window.GenerationSalary.generateStarted = function (arText, {status} = {}) {
    if (status != 'success') {
        throw new Error('Ошибка генерации!');
    }

    window.GenerationSalary.createPopup(arText.join('<br>'), 'Генерация зарплатной ведомости').show();
}

/**
 * @description Обработка ошибок и вывод в попап
 * @param errors {Object[]}
 */
window.GenerationSalary.errorHandler = function ({errors = []}) {
    let [mainError] = errors,
        text = 'Ошибка при генерации!';
    if (!mainError || Object.keys(mainError).length <= 0) {
        console.error(text);
        return;
    }

    window.GenerationSalary.createPopup(mainError.message ?? text, 'Ошибка').show();
}

/**
 * @description Создание попапа с произвольным текстом и заголовком
 * @param content {String|HTMLElement}
 * @param title {?String}
 * @returns {BX.PopupWindow}
 */
window.GenerationSalary.createPopup = function (content, title = '') {
    if (BX.PopupWindowManager.isPopupExists('salary-generate-result')) {
        BX.PopupWindowManager.getPopupById('salary-generate-result').destroy();
    }

    let params = {
        content: content,
        titleBar: title,
        overlay: {backgroundColor: 'black', opacity: '80'},
        autoHide: true,
    }

    params.buttons = [new BX.PopupWindowButton({
        text: "Закрыть",
        events: {click: function () {this.popupWindow.close()}}
    })];

    return new BX.PopupWindow('salary-generate-result', null, params);
}

/**
 * Обработчик выбора представления по роли
 *
 * Текущее состояние: поля в соответствии с viewMode не подгружаются, нужно разбираться
 *
 * @param event {PointerEvent}
 * @param item {MenuItem}
 */
window.GenerationSalary.selectViewByRole = function (event, item) {
    const data = item.data;
    const div = document.querySelector('.workarea-content-paddings');
    const gridManager = BX.Main.gridManager.getById(data['grid_id']);

    if(gridManager.hasOwnProperty('instance')) {
        BX.ajax.runComponentAction(data.component, data.action, {
            mode: data.mode,
            data: data

        }).then(response => {
            if(response.status === 'success') {
                gridManager.instance.reloadTable('POST', data);
            }
        })

    }
}
