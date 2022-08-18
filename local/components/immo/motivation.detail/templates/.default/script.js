/**
 * Добавить пользователя в премиальную ведомость
 */
function addUserToMotivationLists() {
    // Добавление новых строк перед этим ID
    let blockButton = BX('js-addUserToMotivationLists');
    let randName = (Math.random() + 1).toString(36).substring(7);
    if (!addUserToMotivationLists.numUser) {
        addUserToMotivationLists.numUser = 1;
    } else {
        addUserToMotivationLists.numUser++;
    }
    blockButton.insertAdjacentHTML('beforebegin', '<tr id="' + randName + '"><td><input class="js-numeric-envelope" name="envelope[' + randName + ']" type="text" readonly value="' + addUserToMotivationLists.numUser + '"></td><td><a data-id="' + randName + '" onclick="ShowSingleSelector(); return false;" href="#">Выбрать</a><input class="js-statement-user"  type="hidden"  name="users[' + randName + ']"></td><td><input id="js-add' + randName + '" name="sum_motivations[' + randName + ']" class="js-sendAjax js-statement-amount-item"></td><td><button data-id="' + randName + '" onclick="deleteRowUser();">x</button></td></tr>');

    // добавление маски для новой строчки
    setTimeout(() => {
        let elem = BX('js-add' + randName);
        createMask(elem);
    }, 5);

    numericEnvelope();
    recalculateAmount();
}

/**
 * Удаляет tr с конвертом пользователя
 * @param e
 */
function deleteRowUser(e) {
    if (!e) e = window.event;
    // по какому элемету кликнули
    let linkDeleteUser = e.target
    // у этой ссылки должен быть data-id
    let uniqId = BX.data(linkDeleteUser, 'id')
    let tr = document.getElementById(uniqId);
    if (tr) {
        tr.remove();
    }
    numericEnvelope();
    saveSendAjax();

    return e.preventDefault();
}

/**
 * Показывает попап для поиск пользователя
 * @param e
 * @constructor
 */
function ShowSingleSelector(e) {
    if (!e) e = window.event;
    // по какому элемету кликнули
    let linkUserName = e.target
    // у этой ссылки должен быть data-id
    let uniqId = BX.data(linkUserName, 'id')
    // если попап уже отрыт - надо закрыть
    if (ShowSingleSelector.singlePopup) {
        ShowSingleSelector.singlePopup.close();
    }
    // если попап еще не создался
    if (!ShowSingleSelector.singlePopup) {
        ShowSingleSelector.singlePopup = new BX.PopupWindow("single-employee-popup-user", this, {
            offsetTop: 1,
            autoHide: true,
            content: BX("findUser_selector_content"),
            zIndex: 3000
        });
    }
    // привязка попапа к элемету по которому кликнули
    ShowSingleSelector.singlePopup.setBindElement(linkUserName);
    // если попап закрыт, откроем
    if (ShowSingleSelector.singlePopup.popupContainer.style.display != "block")
        ShowSingleSelector.singlePopup.show();

    // на каждом клике переопределяем выбор пользователя
    window['O_findUser'].onChange = (arUser) => {
        if (arUser) {
            arUser.forEach(ele => {
                // сохранение выбранного пользователя
                let obElemUserId = document.querySelector('input[name="users[' + uniqId + ']"]');
                obElemUserId.value = ele.id;
                // в ссылку указываем имя пользователя
                linkUserName.innerText = ele.name;
                saveSendAjax()
            })
        }

    };
    return e.preventDefault();
}

/**
 * Перезапись номера конвертов
 */
function numericEnvelope() {
    let envelops = document.querySelectorAll('.js-numeric-envelope');
    let k = 1;
    for (let i = 0; i < envelops.length; i++) {
        envelops[i].value = k;
        k++;
    }
}

/**
 * Сохранение примиальной ведомости
 */
function saveMotivationAjax() {
    let myForm = BX('save_motivation');
    let fieldForm = BX.ajax.prepareForm(myForm);
    saveMotivationAjax.wait = BX.showWait(myForm, 'Сохранение ведомости');

    BX.ajax.runComponentAction(
        'immo:motivation.detail',
        'save',
        {
            // Вызывается без постфикса Action
            mode: 'class',
            data: {
                arField: fieldForm
            },
        })
        .then(function (response) {
            if (response.status === 'success') {
                let id = response.data.ID;
                if (location.pathname === '/sheets/motivation/0/') {
                    BX.ajax.history.put(null, '/sheets/motivation/' + id + '/');
                }
                if (BX('js-ult-mot', true)) {
                    BX.adjust(
                        BX('js-ult-mot', true),
                        {
                            props: {
                                href: '/sheets/motivation/' + id + '/approval-list/'
                            }
                        }
                    );
                    BX.show(BX('js-ult-mot', true));
                }
                BX('js-id-motivation').value = id;
                BX('js-BALANCE_ACCUMULATIVE').innerText = BX.Currency.currencyFormat(response.data.BALANCE_ACCUMULATIVE, 'RUB');
                BX('js-BALANCE_PLAN').innerText = BX.Currency.currencyFormat(response.data.BALANCE_PLAN, 'RUB');
                BX('js-BALANCE_FACT').innerText = BX.Currency.currencyFormat(response.data.BALANCE_FACT, 'RUB');
            }
            BX.closeWait(myForm, saveMotivationAjax.wait);
            recalculateAmount();
        });
}

/**
 * Отправка запроса на сохранение ведомости с задержкой 500 млс
 */
function saveSendAjax() {
    clearInterval(saveSendAjax.timeInterval);
    recalculateAmount();
    saveSendAjax.timeInterval = setTimeout(() => saveMotivationAjax(), 500)
}

/**
 * Пресчет Сумма по ведомости
 */
function recalculateAmount() {
    console.log(132)
    let sum = 0;
    let hasEmptyAmount = false;
    document.querySelectorAll('.js-statement-amount-item').forEach((input) => {
        let fValue = prepareFloat(input.value);
        if (isNaN(fValue) || fValue <= 0) {
            hasEmptyAmount = true;
        }
        sum += isNaN(fValue) ? 0 : fValue;
    });
    let arUserIds = [];
    let hasDuplicationUser = false;
    let hasEmptyUser = false;

    document.querySelectorAll('.js-statement-user').forEach((input) => {
        let iUserId = parseInt(input.value);
        if (isNaN(iUserId) || iUserId <= 0) {
            hasEmptyUser = true;
        }
        if (BX.util.in_array(iUserId, arUserIds)) {
            hasDuplicationUser = true;
        } else {
            arUserIds.push(iUserId);
        }
    });
    BX('js-statement_amount').innerText = BX.Currency.currencyFormat(sum, 'RUB');
    let balanceAccumulative = BX('js-BALANCE_ACCUMULATIVE').innerText;
    balanceAccumulative = prepareFloat(balanceAccumulative);
    if (isNaN(balanceAccumulative)) {
        balanceAccumulative = 0;
    }

    let bFillRequiredFields = false;
    let SELECTED_BE = document.querySelector('*[name="SELECTED_BE"]');
    let SELECTED_ART = document.querySelector('*[name="SELECTED_ART"]');
    if (SELECTED_BE.value <= 0 || SELECTED_ART.value <= 0) {
        bFillRequiredFields = true;
    }

    ///Внимание! Проверьте заполнение обязательных полей
    if (!recalculateAmount.Alert) {
        recalculateAmount.Alert = new BX.UI.Alert({
            text: "",
            inline: true,
            size: BX.UI.Alert.Size.SMALL,
            color: BX.UI.Alert.Color.DANGER
        });
    }
    if (
        sum <= 0
        || sum > balanceAccumulative
        || hasDuplicationUser
        || bFillRequiredFields
        || hasEmptyAmount
        || hasEmptyUser
    ) {

        if (sum <= 0) {
            recalculateAmount.Alert.setText("<strong>Внимание!</strong> Сумма по ведомости равна нулю")
        }
        if (sum > balanceAccumulative) {
            recalculateAmount.Alert.setText("<strong>Внимание!</strong> Средств для списания недостаточно. Просьба обратиться в фин дирекцию")
        }
        if (hasDuplicationUser) {
            recalculateAmount.Alert.setText("<strong>Внимание!</strong> Дублирование пользователей")
        }
        if (bFillRequiredFields) {
            recalculateAmount.Alert.setText("<strong>Внимание!</strong> Проверьте заполнение обязательных полей")
        }
        if (hasEmptyAmount) {
            recalculateAmount.Alert.setText("<strong>Внимание!</strong> Проверьте корректность заполненных сумм для сотрудников к выплате")
        } else if (hasEmptyUser) {
            recalculateAmount.Alert.setText("<strong>Внимание!</strong> Проверьте корректность заполненных сотрудников")
        }
        if (!recalculateAmount.AlertIsRender) {
            recalculateAmount.Alert.renderTo(BX('js-erorrs_statement_amount'));
            recalculateAmount.AlertIsRender = true;
        }
        if (!BX.hasClass(BX('js-send_to_agree', true), 'ui-btn-disabled')) {
            BX.addClass(
                BX('js-send_to_agree', true),
                'ui-btn-disabled'
            );
            BX.adjust(BX('js-send_to_agree', true), {props: {disabled: true}});
        }
    } else {
        recalculateAmount.Alert.destroy();
        recalculateAmount.Alert = null;
        BX('js-erorrs_statement_amount').innerHTML = '';
        recalculateAmount.AlertIsRender = false;
        if (BX.hasClass(BX('js-send_to_agree', true), 'ui-btn-disabled')) {
            BX.removeClass(
                BX('js-send_to_agree', true),
                'ui-btn-disabled'
            );
            BX.adjust(BX('js-send_to_agree', true), {props: {disabled: false}});
        }
    }
}

function stopDoubleSendForm() {
    if (BX.data(BX('js-send_to_agree', true), 'isSend') !== 'Y') {
        BX.data(BX('js-send_to_agree', true), 'isSend', 'Y');
        BX.addClass(
            BX('js-send_to_agree', true),
            'ui-btn-disabled'
        );
        return true;
    }
    return false;
}

function createMask(input, callback = {}, scale = 2, negative = false) {
    const mask = IMask(input, {
        scale: scale,
        mask: Number,
        thousandsSeparator: ' ',
        radix: ',',
        signed: negative
    });

    if (!!callback && typeof callback == 'function') {
        mask.on('complete', callback);
    }

    return mask;
}

function prepareFloat(floatOnString) {
    floatOnString = floatOnString.replace(/[^0-9\.\,\-]/g, '').replace(/\,/, '.');
    floatOnString = parseFloat(floatOnString);
    if (isNaN(floatOnString)) {
        return parseFloat(0);
    }
    return floatOnString;
}

function reFormatedPrice() {
    document.querySelectorAll('.js-sum-formated').forEach((elem) => {
        let folad = prepareFloat(elem.innerText);
        elem.innerText = BX.Currency.currencyFormat(folad, 'RUB');
    })
}

/**
 * Диалоговое окно при отмене (кнопка Отменить ведомость)
 * @returns {boolean}
 * @constructor
 */
function CancelStatement() {
    BX.UI.Dialogs.MessageBox.show(
        {
            message: "<p align='center'><b>Отмена ведомости является безвозвратным действием</b></p><p align='center'><b>Вы уверены в отмене ведомости?</b></p>",
            modal: true,
            buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
            onOk: function (messageBox) {
                BX('save_motivation').append(BX.create(
                    'input',
                    {
                        'attrs': {
                            'name': 'CANCEL',
                            'value': 'Y',
                            'type': 'hidden',
                        },
                    }
                ));
                messageBox.close();
                BX('save_motivation').submit()
            },
            onCancel: function (messageBox) {
                messageBox.close();
            },
        }
    );
    return false;
}


BX.ready(function () {
    if (typeof arUserListsApprove === 'undefined') {
        let eleSumPay =  document.querySelector('*[name="bprioact_fSumPay"]');
        if(eleSumPay){
            createMask(eleSumPay)
        }
        reFormatedPrice();
        return;
    }
    document.querySelectorAll('.js-statement-amount-item').forEach((input) => {
        createMask(input);
    });
    //при измении полей с классом js-sendAjax отправляется
    // запрос на сохранение
    BX.bindDelegate(
        BX('save_motivation'),
        'bxchange',
        {
            className: 'js-sendAjax'
        },
        function () {
            saveSendAjax()
        }
    );
    // удаление пользователя из премиальной ведомости
    BX.bindDelegate(
        BX('save_motivation'),
        'bxchange',
        {
            className: 'js-statement-amount-item'
        },
        recalculateAmount
    );
    // выбор пользвателей для согласовать с
    const tagSelector = new BX.UI.EntitySelector.TagSelector({
        dialogOptions: {
            items:
                [
                    ...arUserListsApprove
                ]
            ,
            tabs: [
                {id: 'user-approve-tab', title: 'Согласовать с'}
            ],
            dropdownMode: true,
        },
        items: [
            ...arSelectedUserListsApprove
        ],
        textBoxAutoHide: false,
        textBoxWidth: 350,
        maxHeight: 99,
        placeholder: 'введите фио согласующего',
        addButtonCaption: 'Согласовать с',
        addButtonCaptionMore: 'Согласовать с',
        showCreateButton: false,
        events: {
            onBeforeTagAdd: function (event) {
                const selector = event.getTarget();
                const {tag} = event.getData();
                BX('save_motivation').append(BX.create(
                    'input',
                    {
                        'attrs': {
                            'id': tag.entityId + '-' + tag.id,
                            'name': 'ADDITIONAL_USERS[]',
                            'value': tag.id,
                            'type': 'hidden',
                        },
                    }
                ));
                saveSendAjax()
            },
            onBeforeTagRemove: function (event) {
                const selector = event.getTarget();
                const {tag} = event.getData();
                if (BX(tag.entityId + '-' + tag.id)) {
                    BX(tag.entityId + '-' + tag.id).remove()
                }
                saveSendAjax()
            },
        },
    });
    tagSelector.renderTo(document.getElementById('container'));
    recalculateAmount();

});
