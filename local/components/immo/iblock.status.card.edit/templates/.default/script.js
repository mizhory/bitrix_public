(function (BX, window) {
    if (typeof window.StatusCard !== 'function') {
        /**
         *  @description Функция отвечающая за визуальный вывод поля "статус заявки"
         *  @description Вид список
         *
         * @param id
         * @constructor
         */
        window.StatusCard = function ({id}) {
            this.id = id;

            this.wrapper = {};
            this.active = false;
            this.inited = false;

            this.popupCache = {};
        }
    }

    /**
     * @description Объект коллекций - кеш
     * @type {{}}
     */
    window.StatusCard.instances = {};

    /**
     * @description Возвращает экземпляр объекта StatusCard
     * @param id
     * @returns {*}
     */
    window.StatusCard.getInstance = function (id = '') {
        if (!this.instances[id] || Object.keys(this.instances[id]).length <= 0) {
            this.instances[id] = new window.StatusCard({id: id});
        }

        return this.instances[id];
    };

    window.StatusCard.prototype = {
        init: function () {
            if (this.inited) {
                return;
            }

            window.setTimeout(this.initTimeout.bind(this));
        },

        /**
         * @description Отложенная инициализция. Нужна для асинхронного запуска и совместимости с грид таблицами
         */
        initTimeout: function () {
            this.wrapper = BX(this.id);
            if (!this.wrapper) {
                console.error('Обертка не найдена!');
                return;
            }

            this.initEvents();

            this.inited = true;
        },

        /**
         * @description Инициализация обработчиков событий
         */
        initEvents: function () {
            let wrapp = BX('workarea-content');

            BX.bindDelegate(
                wrapp,
                'mouseover',
                {tag: 'span', className: 'status-card-item'},
                this.onMouseOverHandler.bind(this)
            );

            BX.bindDelegate(
                wrapp,
                'mouseout',
                {tag: 'span', className: 'status-card-item'},
                this.onMouseLeaveHandler.bind(this)
            );
        },

        /**
         * @description Обработчик на наведение мыши на статус
         */
        onMouseOverHandler: function () {
            window.setTimeout(this.showHint.bind(this, ...arguments), 300);
        },

        /**
         * @description Обработчик на уход мыши со статуса
         */
        onMouseLeaveHandler: function () {
            window.setTimeout(this.closeHint.bind(this, ...arguments), 300);
        },

        /**
         * @description Отвечает за раскрытие окна подсказки
         */
        showHint: function (event) {
            let id = event.target.dataset.id,
                popup = this.getPopup(id);

            if (!popup) {
                if (
                    (
                        !id
                        || id === ''
                    ) || (
                        !event.target.dataset.name
                        || event.target.dataset.name === ''
                    )
                ) {
                    return;
                }

                let pos = BX.pos(event.target);

                this.addPopup(id, BX.PopupWindowManager.create(
                    "step-hint-" + id,
                    event.target,
                    {
                        "angle": {
                            "position": "bottom",
                            "offset": 0
                        },
                        "offsetLeft": pos["width"] / 2,
                        "offsetTop": 5,
                        "content": BX.create(
                            "SPAN",
                            {
                                "attrs": { "class": "crm-list-bar-popup-text" },
                                "text": event.target.dataset.name
                            }
                        ),
                        "className": "crm-list-bar-popup-table"
                    }
                ));

                popup = this.getPopup(id);
            }

            if (!this.isPopupEnable(id)) {
                this.setPopupEnable(id);
                popup.show();
            }
        },

        /**
         * @description Отвечает за закрытие окна подсказки
         */
        closeHint: function (event) {
            let id = event.target.dataset.id,
                popup = this.getPopup(id);

            if (!popup) {
                return;
            }

            if (this.isPopupEnable(id)) {
                this.setPopupDisable(id);
                popup.close();
                this.destroyPopup(id);
            }
        },

        /**
         * @description Возвращает окно из локального кеша
         */
        getPopup: function (id = '') {
            if (!this.popupCache[id]) {
                return null;
            }

            return this.popupCache[id]['popup'];
        },

        /**
         * @description Добавляет новый экземпляр окна из локального кеша
         */
        addPopup: function (id, popup = {}) {
            this.popupCache[id] = {
                popup: popup,
                enable: false
            };
        },

        /**
         * @description Возвращает статус окна - закрыто/открыто
         */
        isPopupEnable: function (id) {
            return this.popupCache[id]['enable'] ?? false;
        },

        /**
         * @description Устанавливает статус раскрытия окна
         */
        setPopupEnable: function (id) {
            if (!this.popupCache[id]) { g
                return;
            }

            return this.popupCache[id]['enable'] = true;
        },

        /**
         * @description Устанавливает статус закрытия окна
         */
        setPopupDisable: function (id) {
            if (!this.popupCache[id]) {
                return;
            }

            return this.popupCache[id]['enable'] = false;
        },

        /**
         * @description Удаляет окно из кеша. Необходимо при ajax перезагрузки грид таблицы,
         * чтобы новые окна открывались рядом со статусом, а не где нибудь еще
         * @param id
         */
        destroyPopup: function (id) {
            if (!this.popupCache[id]) {
                return;
            }

            this.popupCache[id]['popup'].destroy();
            this.popupCache[id]['popup'] = null;
        },
    };

})(BX, window);