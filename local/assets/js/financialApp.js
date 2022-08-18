(function (window, BX = {}) {

    if (typeof window.FinancialApp !== 'function') {
        /**
         * @description Функция для работы с финансовой заявкой
         * @param iblockId ID инфоблока
         * @param select Объект селекторов для свойств, и других полей
         * @param triggerProps Объект полей, при изменении которых должен меняться текст кнопки сохранить
         * @param hideProps Массив свойств, которые будут скрыты
         * @param employeeType Массив свойств, которые являются полями типа "привязка к сотруднику"
         * @param isCreateTemplate Флаг. Если Y - создается шаблон заявки. N - обычное содание заявки
         * @param ajaxUpdateProps Объект параметров свойств, которые принадлежать к полям фин дирекции
         * @param elementParams Объект параметров текущего элемента и страницы
         * @param removeSelectEmptyValue Флаг, отвечающий за сброс пустых значений в селектах
         * @param notRemoveEmptyValue Массив названий селекторов, у которых не нужно обрезать пустые значения
         * @constructor
         */
        window.FinancialApp = function ({
            iblockId = '',
            select = {},
            triggerProps = {},
            hideProps = [],
            employeeType = [],
            isCreateTemplate = 'N',
            ajaxUpdateProps = {},
            elementParams = {},
            removeSelectEmptyValue = 'Y',
            notRemoveEmptyValue = [],
            defaultValues = {}
        }) {
            this.iblockId = iblockId;
            this.select = select;
            this.triggerProps = triggerProps;
            this.hideProps = hideProps ?? [];
            this.employeeType = employeeType ?? [];
            this.isCreateTemplate = isCreateTemplate;
            this.ajaxUpdateProps = ajaxUpdateProps;
            this.btnText = '';
            this.originalName = '';
            this.elements = {};
            this.triggered = false;
            this.typePage = '';
            this.elementParams = elementParams;
            this.removeSelectEmptyValue = removeSelectEmptyValue;
            this.notRemoveEmptyValue = notRemoveEmptyValue ?? [];
            this.defaultValues = defaultValues ?? {};

            this.currentPropValues = {};
            this.newPropValues = {};
            this.fdButton = {};
            this.updateProcessed = false;

            this.submitForm = true;
        }
    }

    window.FinancialApp.prototype = {
        /**
         * @description Инициализация формы
         */
        init: function () {
            try {
                this.initDomElements();
                this.initEvents();
                this.setBtnText();
                this.initElementPage();
                this.initDefaultValues();
                this.initFd();
                this.replaceListUrl();

                if (this.removeSelectEmptyValue == 'Y') {
                    this.removeEmptySelectValues();
                }
            } catch (e) {
                console.error(e);
            }
        },

        replaceListUrl: function () {
            let backUrl = this.findElement('.lists-list-back');
            if (!!backUrl) {
                backUrl.setAttribute('href', '/finance/');
            }
        },

        /**
         * @description Инициализация дом элементов формы
         */
        initDomElements: function () {
            if (!this.select || Object.keys(this.select).length <= 0) {
                return;
            }

            for (let element in this.select) {
                switch (element) {
                    case 'form':
                        this.initForm(this.select[element]);
                        break;

                    case 'saveBtn':
                        this.initSaveBtn(this.select[element]);
                        break;

                    case 'props':
                        this.initProps(this.select[element]);
                        break;

                    case 'name':
                        this.initName(this.select[element]);
                        break;

                    case 'popup':
                        this.initPopupErrors(this.select[element]);
                        break;

                    case 'files':
                        this.initFiles(this.select[element], element);
                        break;

                    case 'assignedBy':
                        this.initAssignedBy(this.select[element], element);
                        break;
                }
            }
        },

        /**
         * @description Инициализация формы
         * @param selector
         */
        initForm: function (selector) {
            for (let typeFrom in selector) {
                let formSelector = selector[typeFrom],
                    form = this.findElement(formSelector, window.document);

                if (!form) {
                    continue;
                }

                this.typePage = typeFrom;

                this.elements['form'] = form;
            }
        },

        /**
         * @description Возвращает элемент формы на сранице
         * @returns {HTMLFormElement|null}
         */
        getForm: function () {
            return (
                this.elements.hasOwnProperty('form')
                && !!this.elements.form
            ) ? this.elements.form : null;
        },

        /**
         * @description Инициализация кнопки сохранить
         * @param selector
         */
        initSaveBtn: function (selector) {
            let form = this.getForm();
            if (!form) {
                return;
            }

            let saveBtn = this.findElement(selector, form);
            if (!!saveBtn) {
                this.elements['saveBtn'] = saveBtn;
                this.btnText = saveBtn.value;
            }
        },

        /**
         * @description Инициализация доп логики поля ответсвенный
         * @param params {Object}
         */
        initAssignedBy: function (params) {
            let form = this.getForm();
            if (!form) {
                return;
            }

            let assignedBy = this.findElement(params.selector, form);
            if (!assignedBy) {
                return;
            }

            this.elements.props['assignedBy'] = assignedBy;
            this.triggerProps['assignedBy'] = {
                originValue: params.originValue,
                text: params.text
            }

            let id = assignedBy.getAttribute('id'),
                objectUser = window[`O_${id}`];
            if (!objectUser) {
                return;
            }

            BX.bind(assignedBy, 'bxchange', this.setBtnText.bind(this));

            /**
             * Переопределяем родительские функции поля
             * @param arUser
             */
            objectUser.onSelect = (arUser) => {
                BX(id).value = arUser.id;
                BX(`${id}_name`).innerHTML = BX.util.htmlspecialchars(arUser.name);
                BX(`structure-department-head${id}`).style.visibility = "visible";
                this.setBtnText();
            }

            BX.bind(BX(`structure-department-head${id}`), 'click', ({target}) => {
                BX(id).value = "";
                BX(`${id}_name`).innerHTML = "";
                target.style.visibility="hidden";
                this.setBtnText();
            });
        },

        /**
         * @description Вырезает первое пустое значенеие из селекторов из формы ("не выбрано")
         */
        removeEmptySelectValues: function () {
            let form = this.getForm();
            if (!form) {
                return;
            }

            window.setTimeout(() => {
                Array.from(this.findElement('select', form, true)).forEach((select) => {
                    if (
                        select.dataset['emptyValue'] == 'N'
                        || select.classList.contains('select2-hidden-accessible')) {
                        return;
                    }

                    if (!!this.notRemoveEmptyValue && this.notRemoveEmptyValue.length > 0) {
                        let notPass = false;
                        this.notRemoveEmptyValue.forEach((propString) => {
                            if (notPass) {
                                return;
                            }

                            notPass = select.name.includes(propString);
                        });

                        if (notPass) {
                            return;
                        }
                    }

                    let [option] = Array.from(select.options ?? null);
                    if (!option || option.getAttribute('value').trim() != '') {
                        return;
                    }

                    BX.remove(option);
                });
            });


        },

        /**
         * @description Инициализаци свойств
         * @param params
         */
        initProps: function (params) {
            let form = this.getForm();
            if (!form) {
                return;
            }

            this.elements['props'] = {};

            for (let propType in params) {
                if (params[propType].hasOwnProperty('new') || params[propType].hasOwnProperty('edit')) {
                    ['new', 'edit'].forEach((type) => {
                        if (!params[propType][type]) {
                            return;
                        }

                        let element = this.findElement(params[propType][type], form);
                        if (!!element) {
                            element[type] = true;
                            this.elements['props'][propType] = element;
                        }
                    })
                } else {
                    let propElement = this.findElement(params[propType], form);
                    if (!propElement) {
                        continue;
                    }

                    this.elements['props'][propType] = propElement;
                }
            }
        },

        /**
         * @description Инициализирует свойство вложений (множественное)
         * @param params
         * @param propElementName
         */
        initFiles: function (params, propElementName = '') {
            let form = this.getForm();
            if (!params.hasOwnProperty('selector') || !params.selector || !form) {
                return;
            }

            if (params.hasOwnProperty('multiple') && params.multiple == 'Y') {
                this.collectFilesInput(params.selector, form);
                let addInputBtn = this.findElement(`input[onclick*=${params.id}]`, form);
                if (!!addInputBtn) {
                    BX.bind(addInputBtn, 'click', () => {
                         setTimeout(() => {
                             this.collectFilesInput(params.selector, form);
                             this.initUpdatePropEvents(propElementName);
                         });
                    });

                    // если в первом инпуте будет кнопка "обновить", то она переед в следующий инпут при добалвении
                    // используем штатный обработчик, чтобы удалить следующие кнопки
                    BX.addCustomEvent(window, 'onAddNewRowBeforeInner', (eventObj) => {
                        let td = BX.create('td', {html: eventObj.html});
                        let [, addBtn] = Array.from(td.children);
                        if (!addBtn) {
                            return;
                        }

                        td.removeChild(addBtn);
                        eventObj.html = td.innerHTML;
                    });
                }
            }
        },

        /**
         * @description Собирает все инпуты файлов
         * @param selector
         * @param form
         */
        collectFilesInput: function(selector, form) {
            this.elements.props.files = Array.from(this.findElement(selector, form, true));
        },

        /**
         * @description Возвращает элемент на странице
         *
         * @param params
         * @param parent
         * @param findAll
         */
        findElement: function (params, parent = HTMLElement, findAll = false) {
            if (typeof params === 'string') {
                if (findAll) {
                    return (parent instanceof Node)
                        ? parent.querySelectorAll(params)
                        : window.document.querySelectorAll(params);
                } else {
                    return (parent instanceof Node)
                        ? parent.querySelector(params)
                        : window.document.querySelector(params);
                }
            } else if (typeof params === 'object') {
                if (findAll) {
                    return (parent instanceof Node)
                        ? BX.findChildren(parent, params, true)
                        : BX.findChildren(window.document, params, true);
                } else {
                    return (parent instanceof Node)
                        ? BX.findChild(parent, params, true)
                        : BX.findChild(window.document, params, true);
                }
            }

            return null;
        },

        /**
         * @description Инициализирует свойство названия
         * @param params
         */
        initName: function (params) {
            let form = this.getForm();
            if (!form) {
                return;
            }

            let nameInput = this.findElement(params, form);
            if (!nameInput) {
                return;
            }

            nameInput.disabled = true;
            this.elements['name'] = nameInput;
            this.originalName = nameInput.value;
        },

        /**
         * @description При загрузке страницы находит с ошибками и помещает его в попап
         * @param wrapParams
         */
        initPopupErrors: function (wrapParams) {
            let wrap = this.findElement(wrapParams),
                errors;
            if (!wrap) {
                return;
            }

            BX.findChildren(wrap, {tag: 'p'}).forEach((element) => {
                if (!!errors) {
                    return;
                }

                errors = element.querySelector('font.errortext');
            });

            if (!errors) {
                return;
            }

            this.createPopup('error-text-form', errors, 'Ошибка сохранения').show();
        },

        /**
         * @description Создает и возвращает попап по параметрам
         * @param id
         * @param content
         * @param title
         * @param onClose
         * @returns {BX.PopupWindow}
         */
        createPopup: function (id, content, title = '', onClose = {}) {
            if (BX.PopupWindowManager.isPopupExists(id)) {
                BX.PopupWindowManager.getPopupById(id).destroy();
            }

            let params = {
                content: content,
                titleBar: title,
                overlay: {backgroundColor: 'black', opacity: '80' },
                autoHide: true,
            }

            params.buttons = [new BX.PopupWindowButton({
                text: "Закрыть",
                events: {click: function () {
                    this.popupWindow.close();
                }}
            })];

            let popup = new BX.PopupWindow(id, null, params);

            if (typeof onClose === 'function') {
                BX.addCustomEvent(popup, 'onPopupAfterClose', onClose.bind(this));
            }

            return popup;
        },

        /**
         * @description Инициализация обработчиков событий
         */
        initEvents: function () {
            this.initTriggerEvents();
            this.initUpdatePropEvents();
            this.initSubmitForm();
            this.initConfirmLeave();
        },

        /**
         * @description Инициализирует подтверждение выхода со страницы
         */
        initConfirmLeave: function () {
            if (!!this.elementParams && this.elementParams.hasOwnProperty('id') && this.elementParams.id > 0) {
                return;
            }

            const form = this.getForm();
            if (!form) {
                return;
            }

            BX.bind(form, 'submit', () => {
                this.submitForm = false;
            })

            window.onbeforeunload = () => {
                if (this.submitForm) {
                    return 'Are you sure you want to leave?'
                }
            }
        },

        /**
         * @description Инициализация обработчика сабмита формы УС.
         * Исправляет disabled селекты при сабмите формы
         * Удаляет двойной клик при сохранении
         */
        initSubmitForm: function () {
            let form = this.getForm();
            if (!form) {
                return;
            }

            this.formProccessed = false;

            BX.bind(form, 'submit', (event) => {
                if (this.formProccessed) {
                    event.preventDefault();
                    return;
                }

                this.formProccessed = true;

                [
                    ...Array.from(this.findElement('select[disabled]', form, true)),
                    this.elements.name
                ].forEach((element) => {
                    if (!['SELECT', 'INPUT'].includes(element.tagName)) {
                        return;
                    }

                    if (element.tagName == 'SELECT' && !element.name.includes('PROPERTY_')) {
                        return;
                    }

                    element.removeAttribute('disabled');
                });
            })
        },

        /**
         * @description Инициализация обработчиков событий, которые меняют тексты на кнопке сохранить
         */
        initTriggerEvents: function () {
            if (
                (
                    !this.elements.hasOwnProperty('props')
                    || !this.elements.props
                ) || (
                    !this.triggerProps
                    || Object.keys(this.triggerProps).length <= 0
                )
            ) {
                return;
            }

            Object.keys(this.triggerProps).forEach((prop) => {
                if (!this.elements.props.hasOwnProperty(prop)) {
                    return;
                }

                let element = this.elements.props[prop];
                if (!element) {
                    return;
                }

                BX.bind(element, 'change', this.setBtnText.bind(this));
            });
        },

        /**
         * Проверяет, является ли текущая заявка шаблоном
         * @returns {boolean}
         */
        isDraft: function() {
            if (
                (
                    !this.hasOwnProperty('triggerProps')
                    || !this.triggerProps
                ) || (
                    !this.triggerProps.hasOwnProperty('draft')
                    || !this.triggerProps.draft
                )
            ) {
                return false;
            }

            if (
                !this.elements.props.hasOwnProperty('draft')
                || !this.elements.props['draft']
            ) {
                return false;
            }

            return this.triggerProps.draft.value == this.elements.props.draft.value;
        },

        /**
         * Проверяет, является ли текущая заявка шаблоном
         * @returns {boolean}
         */
        isTemplate: function () {
            return (this.elements.props.hasOwnProperty('template')
                && !!this.elements.props['template']
                && this.elements.props['template'].checked)
        },

        /**
         * @description Иницилизация обработчиков событий для обновляемых свойств
         * @param prop - Код свойства, для которого нужно обновить обработчик
         */
        initUpdatePropEvents: function (prop = '') {
            if (
                !this.ajaxUpdateProps
                || !Array.isArray(this.ajaxUpdateProps)
                || this.ajaxUpdateProps.length <= 0
                || this.typePage != 'edit'
                || this.isTemplate()
                || this.isDraft()
            ) {
                return;
            }

            this.ajaxUpdateProps.forEach((updateProp, index) => {
                if (
                    (
                        !this.elements.props.hasOwnProperty(updateProp.prop)
                        || !this.elements.props[updateProp.prop]
                    ) || (
                        !this.elementParams
                        || Object.keys(this.elementParams).length <= 0
                    )
                ) {
                    return;
                }

                this.ajaxUpdateProps[index]['changed'] = false;

                if (updateProp.hasOwnProperty('enums')) {
                    this.currentPropValues[updateProp.propertyCode] = this.elements.props[updateProp.prop].value;
                }

                if (prop !== '' ) {
                    if (prop !== updateProp.prop) {
                        return;
                    }

                    if (
                        updateProp.hasOwnProperty('multiple')
                        && updateProp.multiple == 'Y'
                        && Array.isArray(this.elements.props[updateProp.prop])
                    ) {
                        this.elements.props[updateProp.prop].forEach((element) => {
                            BX.unbind(element, 'change', this.updatePropsChange.bind(this, updateProp));
                        });
                    } else {
                        BX.unbind(
                            this.elements.props[updateProp.prop],
                            'change',
                            this.updatePropsChange.bind(this, updateProp)
                        );
                    }
                }

                if (
                    updateProp.hasOwnProperty('multiple')
                    && updateProp.multiple == 'Y'
                    && Array.isArray(this.elements.props[updateProp.prop])
                ) {
                    this.elements.props[updateProp.prop].forEach((element) => {
                        BX.bind(element, 'change', this.updatePropsChange.bind(this, updateProp));
                    });
                } else {
                    BX.bind(
                        this.elements.props[updateProp.prop],
                        'change',
                        this.updatePropsChange.bind(this, updateProp)
                    );
                }
            });
        },

        /**
         * @description Обработчик события, который срабатывает при изменении полей для фин дерекции
         * @param updateProp
         * @param target
         */
        updatePropsChange: function (updateProp, {target}) {
            if (updateProp.hasOwnProperty('enums') && this.currentPropValues[updateProp.prop] == target.value) {
                return;
            }

            this.ajaxUpdateProps.forEach((prop, index) => {
                if (prop.propertyCode != updateProp.propertyCode) {
                    return;
                }

                this.ajaxUpdateProps[index]['changed'] = true;
            });

            if (!this.isButtonRendered()) {
                this.renderButton(target);
            }
        },

        /**
         * @description Проверяет, кнопка для сохранения новых значений отрисована?
         * @returns {boolean}
         */
        isButtonRendered: function () {
            return this.fdButton instanceof Node;
        },

        /**
         * @description Запускает рендер кнопки сохранения полей фин дирекции
         * @param targetElement
         */
        renderButton: function (targetElement) {
            let tr = targetElement.closest('tr');
            if (!tr) {
                return;
            }

            tr.style.position = 'relative';

            this.fdButton = BX.create('button', {
                attrs: {
                    className: 'ui-btn ui-btn-success ui-ctl-block ui-btn-sm',
                },
                style: {
                    position: 'absolute',
                    left: '700px',
                },
                events: {click: this.updateFdFields.bind(this)},
                text: 'Обновить'
            })

            tr.appendChild(this.fdButton);
        },

        /**
         * @description Отправляет ajax запрос с обновлением полей финдерекции
         * @param event
         */
        updateFdFields: function (event) {
            event.preventDefault();

            if (this.updateProcessed) {
                return;
            }
            this.updateProcessed = true;

            let formData = this.collectValuesUpdate();

            BX.ajax.runComponentAction('immo:iblock.financial.assets', 'updateFields', {
                mode: 'class',
                data: formData,
            })
                .then(this.renderPopupResult.bind(this))
                .catch(this.renderPopupResult.bind(this));
        },

        /**
         * Собирает значения полей которые нужно обновить на ajax
         * @returns {FormData}
         */
        collectValuesUpdate: function () {
            let formData = new FormData();
            formData.append('id', this.elementParams.id);
            formData.append('iblockId', this.elementParams.iblockId);

            this.ajaxUpdateProps.forEach(({propertyCode, prop, multiple = 'N', changed = false}) => {
                if (!changed) {
                    return;
                }

                let elementNode = this.elements.props[prop];

                if (!Array.isArray(elementNode) && elementNode instanceof Node && elementNode.disabled) {
                    return;
                }

                if (multiple == 'Y' && Array.isArray(elementNode)) {
                    let removeValues = [],
                        values = {};

                    elementNode.forEach((input) => {
                        if (input instanceof Node && input.disabled) {
                            return;
                        }

                        let id = this.select[prop].id,
                            originalName = input.getAttribute('name'),
                            name = originalName,
                            remove = false,
                            intId = 0;
                        if (!id) {
                            return;
                        }

                        if (originalName.includes('_del')) {
                            remove = (input.value == 'Y' && input.checked);
                            name = name.replace('_del', '');
                        }

                        id = name
                            .replace(`${id}[`, '')
                            .replace('][VALUE]', '');

                        intId = parseInt(id);
                        if (remove && !isNaN(intId)) {
                            removeValues.push(intId);
                            return;
                        }

                        if (removeValues.indexOf(intId) >= 0) {
                            return;
                        }

                        if (input.getAttribute('type') == 'file') {
                            let files = Array.from(input.files);
                            values[id] = (files.length > 0)
                                ? (
                                    isNaN(intId)
                                        ? files.shift()
                                        : intId
                                )
                                : intId;
                        } else if (!originalName.includes('_del')) {
                            values[id] = input.value;
                        }
                    });

                    if (removeValues.length > 0) {
                        removeValues.forEach((removeId) => {
                            if (!values.hasOwnProperty(removeId)) {
                                return;
                            }

                            delete values[removeId];
                        });
                    }

                    if (Object.keys(values).length > 0) {
                        for (let index in values) {
                            if (values[index] instanceof File) {
                                formData.append(`FILES_UPLOAD[${propertyCode}_${index}]`, values[index]);
                            }

                            formData.append(`props[${propertyCode}][]`, index);
                        }
                    }
                } else {
                    formData.append(`props[${propertyCode}]`, elementNode.value);
                }
            });

            return formData;
        },

        /**
         * @description Открывает попап с результатами сохранения полей фин дирекции
         * @param status
         * @param data
         * @param errors
         */
        renderPopupResult: function ({status = '', data, errors = []}) {
            let params = {
                id: 'update-result-popup',
                content: '',
                title: '',
            }

            switch (status) {
                case 'success':
                    params.title = 'Обновление полей';
                    params.content = data.message;
                    if (this.elementParams.hasOwnProperty('listUrl') && this.elementParams.listUrl !== '') {
                        let self = this;
                        params.onClose = function () {
                            window.location.href = self.elementParams.listUrl;
                        }
                    }
                    break;

                case 'error':
                    let errorsMessages = errors.reduce((messages = [], error) => {
                        if (error.code === 100) {
                            messages.push(error.message);
                        }
                        return messages;
                    }, []);

                    params.content = (errorsMessages.length > 0) ? errorsMessages.join('<br>') : 'Ошибка';
                    params.title = 'Ошибка обновления';
                    break;
            }

            BX.remove(this.fdButton);
            this.updateProcessed = false;
            this.createPopup(params.id, params.content, params.title, params.onClose).show();
        },

        /**
         * @description Устанавливает текст для кнопки сохранить.
         * Если значение поля черновик == Да, текст меняется на: Сохранить как черновик
         * Если значение поля является шаблонов == Да, текст меняется на: Сохранить как шаблон
         */
        setBtnText: function () {
            let setOrigin = true;
            if (!this.elements.saveBtn) {
                return;
            }

            let text = '';

            for (let prop in this.elements.props) {
                let element = this.elements.props[prop],
                    value,
                    originValue;

                if (!this.triggerProps.hasOwnProperty(prop)) {
                    continue;
                }

                if (this.triggerProps[prop].hasOwnProperty('value')) {
                    value = this.triggerProps[prop].value;
                } else if (this.triggerProps[prop].hasOwnProperty('originValue')) {
                    originValue = this.triggerProps[prop].originValue;
                }

                if (!value && !originValue) {
                    continue;
                }

                let trigger = false;

                if (element.tagName == 'INPUT' && element.type == 'checkbox') {
                    trigger = (element.checked && value == element.value);

                    if (prop === 'template' && !!this.elements['name']) {
                        if (!trigger) {
                            this.elements['name'].value = this.originalName;
                        }
                        this.elements['name'].disabled = (!trigger);
                    }
                } else if (element.tagName == 'INPUT') {
                    if (!!value) {
                        trigger = (value == element.value);
                    } else if (!!originValue) {
                        if (prop == 'assignedBy') {
                            trigger = (this.isDraft() && element.value != '' && originValue != element.value);
                        } else {
                            trigger = (originValue != element.value);
                        }
                    }
                } else if (element.tagName == 'SELECT') {
                    trigger = (value == element.value);
                }

                setOrigin = (setOrigin) ? !trigger : setOrigin;

                if (trigger) {
                    text = this.triggerProps[prop].text;
                }
            }

            this.triggered = !setOrigin;

            if (setOrigin) {
                this.setBtnTextInternal(this.btnText);
            } else if (!!text) {
                this.setBtnTextInternal(text);
            }
        },

        /**
         * @description Установка текста в кнопку сохранения
         * @param text {String}
         */
        setBtnTextInternal: function (text = '') {
            this.elements.saveBtn.value = text;
        },

        /**
         * @description Инициализация страницы
         */
        initElementPage: function () {
            if (this.hideProps.length <= 0) {
                return;
            }

            let hide = (this.triggered && this.typePage === 'edit');

            this.hideProps.forEach((prop) => {
                if (!this.elements['props'].hasOwnProperty(prop) || !this.elements['props'][prop]) {
                    return;
                }

                let element = this.elements['props'][prop];

                if (this.employeeType.length > 0 && this.employeeType.indexOf(prop) >= 0) {
                    this.switchEmployeeProp(hide, element);
                } else if (hide && !element.parentNode.classList.contains('bx-buttons')) {
                    BX.hide(element.closest('tr'));
                }
            })

            if (this.isCreateTemplate == 'Y' && !!this.elements['props']['template']) {
                this.elements['props']['template'].checked = 'checked';
                this.elements['props']['template'].disabled = 'disabled';
                this.elements['props']['template'].dispatchEvent(new Event('change'));
            }
        },

        /**
         * @description Проставляет значения по умолчанию в поля
         */
        initDefaultValues: function () {
            if (!this.defaultValues || Object.keys(this.defaultValues).length <= 0) {
                return;
            }

            for (let propKey in this.defaultValues) {
                let property = this.elements['props'][propKey];
                if (!property) {
                    return;
                }

                if (!!property.value || property.value == this.defaultValues[propKey]) {
                    return;
                }

                property.value = this.defaultValues[propKey];
            }
        },

        /**
         * @description Скрывает/убирает редактирование свойства с типом привязка к сотруднику.
         * @param hide
         * @param element
         */
        switchEmployeeProp: function (hide, element) {
            if (hide) {
                // span < td < tr
                BX.hide(element.parentNode.parentNode.parentNode);
            } else if (this.typePage == 'edit') {
                // получаем id
                let id = element.parentNode
                    .getAttribute('id')
                    .replace('_hids', '');

                if (id != '') {
                    // по родителю находим ссылку "выбрать"
                    let selectBtn = element.parentNode.parentNode.querySelector(`a[id^="single-user-choice${id}"]`);
                    if (!!selectBtn) {
                        BX.remove(selectBtn);
                    }
                }
            }
        },

        /**
         * @description Скрывает поля для всех пользоветелей, кроме сотрудников фин дирекции
         */
        initFd: function () {
            if (!this.ajaxUpdateProps) {
                return;
            }

            this.ajaxUpdateProps.forEach((param) => {
                let propElement = this.elements['props'][param.prop];
                if (!propElement) {
                    return;
                }

                // если значение не выбрано, проставляем текущее
                if (
                    propElement.selectedIndex <= 0
                    && (
                        param.hasOwnProperty('defaultValue')
                        && param.defaultValue != ''
                    )
                ) {
                    propElement.value = param.defaultValue;
                }

                // скрываем/открываем в зависимости от роли
                if (param.hasOwnProperty('disable') && param.disable == 'Y') {
                    propElement.setAttribute('disabled', 'disabled');
                }
            });
        }
    }
}) (window, BX);