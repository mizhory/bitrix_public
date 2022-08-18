/**Класс редактирования статей*/
class EditArticle {

    nowArticleSelect = document.querySelector('select[name="nowArticle"]');
    nowArticleValue = this.nowArticleSelect.value;
    lastArticleDisabledOption = null;

    reArticleSelect = document.querySelector('select[name="reArticle"]');
    reArticleValue = 0;
    lastReArticleDisabledOption = this.reArticleSelect.querySelector('option[value="' + this.nowArticleValue + '"]');

    nowArticleMonth = document.querySelector('select[name="nowMonth"]');
    nowArticleMonthValue = this.nowArticleMonth.value;
    lastNowArticleMonthDisabledOption = null;

    reArticleMonth = document.querySelector('select[name="reMonth"]');
    reArticleMonthValue = 0;
    lastReArticleMonthDisabledOption = this.reArticleMonth.querySelector('option[value="' + this.nowArticleMonthValue + '"]');

    itogo = document.querySelector('input[name="itogo"]');
    itogoNow = document.querySelector('input[name="itogoNow"]');

    deleteSum = document.querySelector('input[name="deleteSum"]');

    monthsInputs = {};

    errors = {};

    editorObjects = {};

    editorDel;

    startValues = {};

    articles = {};

    constructor() {
        this.beSelect = document.querySelector('select[name="beId"]');

        document.querySelectorAll('input.names').forEach((input) => {
            this.monthsInputs[input.getAttribute('data-month')] = input;
        })

        this.nowYearSelect = document.getElementById('nowYearS');
        this.reYearSelect = document.getElementById('reYear');

        this.beSelect.addEventListener('change', this.switchArticle.bind(this));
        this.nowYearSelect.addEventListener('change', this.switchArticle.bind(this));

        this.initMutations();
        this.initArticleStart();
        this.initButtons();

        this.slider = BX.SidePanel.Instance.getSlider(window.location.pathname);
        this.isSliderOpened = (!!this.slider && Object.keys(this.slider).length > 0);
    }

    /**Добавляет ошибку*/
    addError(type,message){
        this.errors[type] = message;
    }

    /**Удаляет ошибку*/
    deleteError(type){
        if(this.errors[type]){
            delete this.errors[type];
        }
    }
    /**Инициализация кнопок*/
    initButtons() {
        let self = this;
        document.querySelector('.cancel').addEventListener('click', function () {
            self.initValuesByArticle();
        })
        document.querySelector('.save').addEventListener('click', function () {
            self.save();
        })
    }

    /**Начальная инициализация*/
    initArticleStart() {
        let article = {
            'articleId': this.nowArticleValue,
            'months': {},
            'itogo': this.itogo.value
        }

        for (let key in this.monthsInputs) {
            article['months'][key] = ''+this.monthsInputs[key].value;
        }

        this.articles[this.nowArticleValue] = article;
    }


    /**Переключение статьи*/
    switchArticle() {
        let self = this;
        BX.ajax.runComponentAction('vigr:budget.edit', 'getArticleData', {
            mode: 'class',
            data: {
                'articleId': this.nowArticleValue,
                'beId': this.beSelect.value,
                'year' : this.nowYearSelect.value
            }
        }).then(function (response) {
            self.articles[self.nowArticleValue] = response.data;

            if(response.data['months'].length === 0){
                self.addError('articlesErrors','Не загружен бюджет с данными параметрами!')
                alert('Не загружен бюджет с данными параметрами!');
            }else{
                self.deleteError('articlesErrors')
            }

            self.initValuesByArticle();
        })
    }
    /**
     * Инициализация значений на статье
     * */
    initValuesByArticle() {
        this.itogoNow.value = this.articles[this.nowArticleValue].itogo;
        this.itogo.value = this.articles[this.nowArticleValue].itogo;

        this.deleteSum.value = 0;
        let self = this;
        for (let key in this.articles[this.nowArticleValue]['months']) {
            this.monthsInputs[key].value = ''+this.articles[this.nowArticleValue]['months'][key];
            this.editorObjects[key].value = this.monthsInputs[key].value;

            this.createMask(this.monthsInputs[key],function (){
                if(self.startInit){
                    self.setAll();
                    self.checkValue(key);
                }
            })
            this.editorObjects[key].defaultV = this.editorObjects[key].unmaskedValue;

            this.checkValue(key);
        }

        this.setAll(true);
    }
    /**Создает imask
     * @param input DOM инпут
     * @param callback функция коллбэк
     * @param scale размерность после запятой
     * */
    createMask(input,callback,scale = 2){
        let mask = IMask(
            input,
            {
                scale:scale,
                mask: Number,
                thousandsSeparator: ' ',
                radix:','
            });
        if(callback){
            mask.on('complete',callback);
        }

        return mask;
    }

    /**Инициализация мутаций*/
    initMutations() {
        let self = this;

        this.nowArticleSelect.addEventListener('change', function () {
            self.nowArticleValue = this.value;
            self.switchArticle();
        })

        this.reArticleSelect.addEventListener('change', function () {
            self.reArticleValue = this.value;
        })

        this.nowArticleMonth.addEventListener('change', function () {
            self.nowArticleMonthValue = this.value;
        })

        this.reArticleMonth.addEventListener('change', function () {
            self.reArticleMonthValue = this.value;
        })

        this.startInit = false;

        this.editorDel = self.createMask(this.deleteSum,function (){

        })
        for (let key in this.monthsInputs) {
            self.editorObjects[key] = self.createMask(this.monthsInputs[key],function (){
                if(self.startInit){
                    self.setAll();
                    self.checkValue(key);
                }
            })
            self.editorObjects[key].defaultV = self.editorObjects[key].unmaskedValue;
        }
        this.startInit = true;

        this.itogoNow = document.querySelector('[name="itogoNow"]');

        this.itogoEditor = this.createMask(this.itogo,function (){

        })

        this.itogoNowEditor = this.createMask(this.itogoNow,function (){

        })

        this.setAll();
    }
    /**Проверка значения на превышение или нет
     * @param id ид обьекта редактирования статьь
     * */
    checkValue(id){
        let editor = this.editorObjects[id];

        let el = editor.el;
        let value = parseFloat(editor.unmaskedValue);

        if(isNaN(value)){
            value = 0;
            editor.value = ''+value;
        }
        let defValue = parseFloat(editor.defaultV);

        if(value > defValue){
            el.input.classList.remove('minus');
            el.input.classList.add('plus');
        }else if(value < defValue){
            el.input.classList.remove('plus');
            el.input.classList.add('minus');
        }else{
            el.input.classList.remove('plus');
            el.input.classList.remove('minus');
        }
    }
    /**Возвращает имя месяца*/
    getMonthNumberByName(name){
        let month = {
            'Апрель':1,
            'Май':2,
            'Июнь':3,
            'Июль':4,
            'Август':5,
            'Сентябрь':6,
            'Октябрь':7,
            'Ноябрь':8,
            'Декабрь':9,
            'Январь':10,
            'Февраль':11,
            'Март':12
        }

        return month[name];
    }

    /**Возращает номер месяца фин года*/
    getMonthNumber(name){
        let month = {
            '5' : '1',
            '6' : '2',
            '7' : '3',
            '8' : '4',
            '9' : '5',
            '10' : '6',
            '11' : '7',
            '12' : '8',
            '1' : '9',
            '2' : '10',
            '3' : '11',
            '4' : '12',
        }

        return parseInt(month[name]);
    }

    /**Установка итого на тек момент*/
    setAll(byNew){
        let itogoNow = 0;

        for (let key in this.editorObjects){
            itogoNow += parseFloat(this.editorObjects[key].unmaskedValue);
        }

        this.itogoNowEditor.value = ''+itogoNow;
        if(byNew){
            this.itogoEditor.value = ''+itogoNow;
        }
        let str = '';
        if(parseFloat(this.itogoNowEditor.unmaskedValue) > parseFloat(this.itogoEditor.unmaskedValue)){
            str = 'План по статье был превышен';
        }else if(parseFloat(this.itogoNowEditor.unmaskedValue)  < parseFloat(this.itogoEditor.unmaskedValue)){
            str = 'План по статье был занижен';
        }
        document.getElementById('itogoError').innerHTML = str;
    }

    /**Проверка на ошибки*/

    checkErrors(data){
        let hasErrors = false;
        if(data.comment === ''){
            hasErrors = true;
            this.addError('comment','Введите комментарий');
        }else{
            this.deleteError('comment');
        }

        let nowYear = parseInt(this.nowYearSelect.value);
        let reYear = parseInt(this.reYearSelect.value);

        if(this.editorDel.unmaskedValue>0){
            if(this.reArticleSelect.value > 0 && this.nowArticleMonth.value>0 && this.reArticleMonth.value>0 /*&& this.deleteSum.value>0*/){
                this.deleteError('reAM');
                this.deleteError('nowA');
                this.deleteError('reA');
                this.deleteError('month');
                if(nowYear > parseInt(document.getElementById('nowYear').value)){
                    hasErrors = true;
                    this.addError('year','Невозможно выбрать год списания больше текущего!');
                }else if(nowYear - reYear > 1){
                    hasErrors = true;
                    this.addError('year','Невозможно выбрать год двумя годами ранее!');
                }else if(nowYear > reYear){
                    let nowRealMonth = parseInt(document.getElementById('nowMonth').value);
                    let stepMonth = parseInt(document.getElementById('monthStep').value);

                    if(nowRealMonth > stepMonth){
                        hasErrors = true;
                        this.addError('month','Невозможно распредение за предыдущий год');
                    }else{
                        this.deleteError('month');
                    }
                }else {
                    if(this.reArticleSelect.value === this.nowArticleSelect.value){
                        hasErrors = true;
                        this.addError('art','Выберите разные статьи для перераспределения!');
                    }else{
                        this.deleteError('art');
                    }
                }
            }else{
                hasErrors = true;
                if(this.reArticleMonth.value <= 0){
                    this.addError('reAM','Ошибка распределения: не был заполнен месяц списания');
                }else{
                    this.deleteError('reAM');
                }

                if(this.nowArticleMonth.value <= 0){
                    this.addError('nowA','Ошибка распределения: не был заполнен месяц зачисления');
                }else{
                    this.deleteError('nowA');
                }

                if(this.reArticleSelect.value <= 0){
                    this.addError('reA','Ошибка распределения: не была заполнена статья списания');
                }else{
                    this.deleteError('reA');
                }

            }
        }

        return hasErrors;
    }

    /**Сохранение*/

    save() {
        let data = {
            'nowArticleId': this.nowArticleValue,
            'nowYear':this.nowYearSelect.value,
            'reYear':this.reYearSelect.value,
            'reArticleId': this.reArticleValue,
            'nowArticleMonth': this.nowArticleMonthValue,
            'reArticleMonth': this.reArticleMonthValue,
            'deleteSum': this.editorDel.value,
            'beId': this.beSelect.value,
            'comment':document.querySelector('[name=comment]').value,
            'months': {}
        };

        if(!this.checkErrors(data)){
            document.getElementById('errorDiv').classList.add('none');
            for (let key in this.editorObjects) {
                data['months'][key] = this.editorObjects[key].unmaskedValue;
            }

            BX.ajax.runComponentAction('vigr:budget.edit', 'save', {
                mode: 'class',
                data: data
            }).then((response) => {
                window.parent.postMessage('close', '*');
                if (this.isSliderOpened) {
                    this.slider.close(false, () => {
                        if (
                            !this.slider.options.hasOwnProperty('onclose')
                            || typeof this.slider.options.onclose != 'function'
                        ) {
                            return;
                        }

                        this.slider.options.onclose();
                    });
                }
            }, (response) => {
                alert(response.data.ajaxRejectData.response.error);
            })

        }else{
            document.getElementById('errorDiv').classList.remove('none');
            let str = '';
            for (let key in this.errors){
                str += this.errors[key] + '<br>';
            }
            document.getElementById('errorDiv').innerHTML = str;
        }
    }
}


BX.ready(function () {
    try {
        (new EditArticle());
    } catch (error) {
        console.log('Ошибка при создании класса редактирования!');
        console.log(error);
    }

})