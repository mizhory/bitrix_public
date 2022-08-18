(function (BX, window) {
    
    if (typeof window.IblockTemplateCard) {
        /**
         * @description функция, отвечающая за загрузку/создание заявки и переход на эту заявку
         * @param id
         * @param signedParams
         * @param idBtnWrapper
         * @param idCheckbox
         * @param name
         * @constructor
         */
        window.IblockTemplateCard = function ({
            id = '',
            signedParams = '',
            idBtnWrapper = '',
            idCheckbox = '',
            name = ''
        }) {
            this.id = id;
            this.idCheckbox = idCheckbox;
            this.name = name;
            this.idBtnWrapper = idBtnWrapper;
            this.signedParams = signedParams;
            this.select = {};
            this.btnWrapperCreate = {};
            this.checkbox = {};

            this.buttonCreate = {};

            this.inProgress = false;
        }
    }

    window.IblockTemplateCard.prototype = {
        init: function () {
            this.select = BX(this.id);
            if (!this.select) {
                return;
            }

            this.btnWrapperCreate = BX(this.idBtnWrapper);
            if (!this.btnWrapperCreate) {
                return;
            }

            this.checkbox = BX(this.idCheckbox);
            if (!this.checkbox) {
                return;
            }

            this.initEvents();
        },

        initEvents: function () {
            BX.bind(this.select, 'change', this.onChangeTemplateHandler.bind(this));
            BX.bind(this.checkbox, 'change', this.onChangeMode.bind(this));
        },

        /**
         * @description Обработчик события изменения чекбокса - является шаблоном.
         * Если чекбокс checked - то ставим disabled на селектор шаблонов
         * Иначе, снимаем disabled
         * @param event
         */
        onChangeMode: function (event) {
            this.select.value = '';
            BX.cleanNode(this.btnWrapperCreate);

            if (event.target.checked) {
                this.select.disabled = true;
            } else {
                this.select.disabled = false;
            }
        },

        /**
         * @description обработчик изменения списка шаблонов. Рисует кнопку рядом со списком, если выбрано значение
         * @param event
         */
        onChangeTemplateHandler: function (event) {
            BX.cleanNode(this.btnWrapperCreate);

            let templateId = event.target.value;

            if (!templateId || templateId === "") {
                return;
            }

            this.buttonCreate = BX.create('button', {
                text: 'Создать по шаблону',
                events: {click: this.createNewCard.bind(this, templateId)}
            });

            BX.adjust(this.btnWrapperCreate, {children: [this.buttonCreate]});
        },

        /**
         * @description создает новую заявку и открывает ее страницу
         * @param templateId
         * @param event
         */
        createNewCard: function (templateId, event) {
            event.preventDefault();

            /**
             * Блокируем двойной клик по кнопке + так же блокируем саму кнопку
             */
            if (this.inProgress) {
                return;
            }

            this.inProgress = true;
            this.buttonCreate.setAttribute('disabled', 'disabled');

            BX.ajax.runComponentAction('immo:iblock.template.card', 'createDraftByTemplate', {
                mode: 'class',
                data: {templateId: templateId},
                signedParameters: this.signedParams
            })
                .then(this.cardCreateHandler.bind(this))
                .catch(this.errorHandler.bind(this));
        },

        /**
         * @description метод - обработчик при создании шаблон
         * @param status
         * @param data
         */
        cardCreateHandler: function ({status = '', data = {}}) {
            if (status === 'success' && (!!data.url && data.url !== '')) {
                const financial = (window.hasOwnProperty('FinancialInstance') && !!window.FinancialInstance)
                    ? window.FinancialInstance
                    : null;
                if (!!financial) {
                    financial.submitForm = false;
                }

                window.location.href = window.location.origin + data.url;
            } else {
                this.errorHandler({errors: [{message: 'Ошибка запроса'}]});
                /**
                 * В случае ошибки разблокируем кнопку создания
                 */
                this.buttonCreate.removeAttribute('disabled');
                this.inProgress = false;
            }
        },

        /**
         * @description обработчик, выводит сообщения об ошибках в консоль
         * @param errors
         */
        errorHandler: function ({errors}) {
            errors.forEach(({message}) => {
                console.error(message);
            });
        }
    }
    
})(BX, window);