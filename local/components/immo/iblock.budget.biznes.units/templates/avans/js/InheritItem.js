/**
 * Общий класс с общими методами
 */
class InheritItem {
    /**
     * Выбранные итемы
     * @type {{}}
     */
    selectedItems = {};

    /**
     * Объект элементов
     * @type {{InheritItem}}
     */
    items = {};

    /**
     * Элемент обертки
     * @type {Node}
     */
    wrap;

    /**
     * @description Элемент главной обертки
     * @type {Node}
     */
    mainWrap;

    /**
     * Уникальный ID
     * @type {String}
     */
    id = '';

    /**
     * Родительский элемент
     * @type {InheritItem}
     */
    parent;

    /**
     * @description Экземпляр лоадера
     * @type {Node}
     */
    loaderInstance;

    /**
     * @description Флаг открытия лоадера
     * @type {boolean}
     */
    isLoaderOpened = false;

    /**
     * Объект ошибок
     * @type {{}}
     */
    errors = {};

    /**
     * Общий обработчик, который будет запускать обработку формы после обработки события
     * @param element {Node}
     * @param eventName {String}
     * @param callback {Function}
     * @param async {boolean}
     * @param params {Object}
     */
    setEventListener({element, eventName = '', callback, async = false, params = {}}) {
        element.addEventListener(eventName, this.commonEventHandler.bind(
            params['context'] ?? this,
            callback,
            async,
            params,
        ));
    }

    /**
     * Общий обработчик, который будет запускать обработку формы после обработки события
     * @param callback {Function}
     * @param async {boolean}
     * @param params {Object}
     * @param event {Event}
     */
    commonEventHandler(callback, async, params, event) {
        if (!async) {
            if (!!callback) {
                callback(event);
            }

            this.render();
        } else {
            if (!!callback) {
                this.openLoader();

                callback
                    .apply(event)
                    .then((this.renderWithLoader.bind(this)))
                    .catch((this.renderWithLoader.bind(this)));
            } else {
                this.render();
            }
        }
    }

    /**
     * Общий метод отрисовки.
     * Запускается после отработки обработчика событий (если обработчик зареган через setEventListener)
     * Должен быть переопределен в дочернем класса
     * @override
     */
    render() {
        console.warn('Метод render() должен быть переопределен в потомке!');
    }

    /**
     * Инициализирует обработчики событий для элементов
     * @param eventElements
     */
    defineEvents(eventElements = []) {
        eventElements.forEach((params) => {
            if (!params.element) {
                return;
            }

            this.setEventListener(params);
        })
    }

    /**
     * Определяет объекты на странице и записывает в свойства текущего объекта
     * @param selects
     * @type Array
     */
    defineElements(selects = []) {
        selects.forEach(({query, key, listKey = '', global = false, all = false}) => {
            if (query == '' || key == '') {
                return;
            }

            if (this.hasOwnProperty(key)) {
                return;
            }

            if (all) {
                this[key] = Array.from((!global)
                    ? this.wrap.querySelectorAll(query)
                    : window.document.querySelectorAll(query)
                );
            } else {
                this[key] = (!global) ? this.wrap.querySelector(query) : window.document.querySelector(query);
                if (listKey != '' && !!this[key] && !!this[key]['options']) {
                    this[listKey] = this[key].options;
                }
            }
        });
    }

    /**
     * Запуск коллбек функции по привязанным элементам
     * @param callback
     */
    callbackItems(callback) {
        for (let key in this.items) {
            callback(this.items[key]);
        }
    }

    /**
     * Генератор уникальных ID ключей
     * @returns {String}
     */
    generateItemId() {
        const existIds = Object.keys(this.items) ?? [];
        let id = Math.random().toString(36).substr(2, 5);
        while (existIds.includes(id)) {
            id = Math.random().toString(36).substr(2, 5);
        }

        return id;
    }

    /**
     * Удаляет элемент по ID
     * @param id {String}
     */
    removeItem(id = '') {
        let item = this.items[id];
        if (!item) {
            return;
        }

        BX.remove(item.wrap);
        delete this.items[id];
    }

    /**
     * Возвращает кол-во элементов
     * @returns {number}
     */
    getCountItems() {
        return this.getItemsIds().length;
    }

    /**
     * Возвращает список ID блоков распределения
     * @returns {string[]}
     */
    getItemsIds() {
        return Object.keys(this.items);
    }

    /**
     * Возвращает последний элемент из списка
     * @returns {{}|InheritItem}
     */
    getLastBlock() {
        if (this.getCountItems() <= 0) {
            return {};
        }

        const lastId = this.getItemsIds().pop();
        return this.items[lastId];
    }

    /**
     * Создает и возвращает маску по инпуту
     * Нужно для валидации вводных данных и проставлении плавающей точки
     * @param input {HTMLInputElement}
     * @param callback {?Function}
     * @param scale {number}
     * @param negative {boolean}
     * @returns {InputMask}
     */
    createMask(input, callback = {}, scale = 2, negative = false){
        const mask = IMask(input, {
            scale:scale,
            mask: Number,
            thousandsSeparator: ' ',
            radix:',',
            signed: negative
        });

        if (!!callback && typeof callback == 'function') {
            mask.on('complete', callback);
        }

        return mask;
    }

    /**
     * Создает и возвращает маску по инпуту
     * Нужно для валидации вводных данных и проставлении плавающей точки
     * @param input {Node}
     * @param callback {Function}
     * @param scale {Number}
     * @param async {boolean}
     * @returns {InputMask}
     */
    createMaskWithEvent(input, {callback, scale = 2, async = false}){
        const mask = IMask(input, {
            scale: scale,
            mask: Number,
            thousandsSeparator: ' ',
            radix: ','
        });

        if (!!callback && typeof callback == 'function') {
            mask.on('complete', this.commonEventHandler.bind(this, callback, async, {}));
        }

        return mask;
    }

    /**
     * Возвращает самый первый родительский объект
     * @param item
     * @returns {{parent}|{}}
     */
    getHeadParent(item) {
        if (!(item instanceof InheritItem)) {
            return {};
        }

        return (!!item.parent && item.parent instanceof InheritItem) ? this.getHeadParent(item.parent) : item;
    }

    /**
     * @description Создает и возвращает экземпляр лоадера
     * @returns {div}
     */
    createLoader() {
        return BX.create("div", {
            props: {className: "side-panel-loader form-be-loader"},
            children: [
                BX.create("div", {
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
                })
            ]
        });
    }

    /**
     * @description Рендер в лоадером
     */
    renderWithLoader() {
        if (!this.mainWrap) {
            this.render();
            return;
        }

        if (!this.isLoaderOpened) {
            this.openLoader();
        }

        this.render();

        setTimeout(this.destroyLoader.bind(this), 1000);
    }

    /**
     * Открывает лоадер и спустя секудну закрывает его
     * @param callback {Function}
     */
    showLoader(callback = {}) {
        if (!this.mainWrap) {
            return;
        }

        this.openLoader();

        if (!!callback && typeof callback == 'function') {
            callback();
        }

        setTimeout(this.destroyLoader.bind(this), 1000);
    }

    /**
     * @description Создает и возвращает синглтон лоадера
     * @returns {Node}
     */
    getLoaderInstance() {
        if (!this.loaderInstance) {
            this.loaderInstance = this.createLoader();
        }

        return this.loaderInstance;
    }

    /**
     * @description Открывает лоадер в форме
     */
    openLoader() {
        if (!this.mainWrap) {
            return;
        }

        let loader = this.getLoaderInstance();
        if (!loader) {
            return;
        }
        
        this.mainWrap.appendChild(loader);
        this.isLoaderOpened = true;
    }

    /**
     * @description Закрывает лодер и уничтожает синглтон
     */
    destroyLoader() {
        if (!this.isLoaderOpened) {
            return;
        }

        let loader = this.getLoaderInstance();
        if (!loader) {
            return;
        }

        BX.remove(loader);
        this.isLoaderOpened = false;
    }
}