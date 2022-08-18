(function () {
    if (typeof window.IblockPrintValues !== 'function') {
        window.IblockPrintValues = function ({id = '', signedParameters = '', formId = ''}) {
            this.id = id;
            this.formId = formId;
            this.signedParameters = signedParameters;

            this.btn = {};
            this.form = {};
        }
    }

    window.IblockPrintValues.prototype = {
        init: function () {
            if (
                (!this.id || this.id === '')
                || (!this.signedParameters || this.signedParameters === '')
            ) {
                return;
            }

            this.btn = BX(this.id);
            if (!this.btn) {
                return;
            }

            this.form = BX(this.formId);

            this.initEvents();
        },

        initEvents: function () {
            BX.bind(this.btn, 'click', this.clickHandler.bind(this));
        },

        clickHandler: function (event) {
            event.preventDefault();

            let formDataOriginal = {};

            if (!!this.form) {
                formDataOriginal = new FormData(this.form)
            }

            BX.ajax.runComponentAction('immo:iblock.print.values', 'printTemplate', {
                mode: 'class',
                data: formDataOriginal ?? {},
                signedParameters: this.signedParameters
            })
                .then(this.responseHandler.bind(this))
                .catch(this.errorHandler.bind(this))
        },

        errorHandler: function () {
            console.error('ошибка');
        },

        responseHandler: function ({data}) {
            if (!data.hasOwnProperty('SRC') || data['SRC'] === '') {
                return;
            }

            window.open(data['SRC']);
        }
    }

}) (window, BX);