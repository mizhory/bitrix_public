/**
 * Класс для работы с блоком БЕ. Наследуется от общего класса формы БЕ
 */
class Be extends WrapItems {
    /**
     * Переопределенный метод инициализации класса
     */
    startInit() {
        this.procent = false;

        let self = this;
        this.baseBeAndProductInit();
        this.beAdditionalInit();

        /**
         * Проверяем инпуты на предмет распределения
         */
        this.checkInputs();

        /**
         * Если БЕ не выбрано, то выставялем режим продуктов "Без продуктов"
         */
        if(this.id < 1){
            this.switchProductsMode('noP');
        }

        /**
         * Если БЕ выбрано
         */
        if(this.id > 0){
            /**
             * Устанавливаем остаток по продуктам
             */
            this.setOstP();
            /**
             * Определяем тип распределения
             */
            let switcher = this.wrap.querySelector('input[name=switcher_' + this.wrap.querySelector('.rand').value + ']:checked');
            this.mode = (!!switcher) ? switcher.value: 'noP';
            if (Object.keys(this.items).length <= 0 || !this.productsSelected()) {
                this.switchProductsMode('noP');
            }
            /**
             * Если выбран режим распределения, то нужно отобразить инпуты остатков
             */
            if(this.mode !== 'noP'){
                this.showFreeInputs();
            } else {
                this.hideFreeInputs();
            }

            /**
             * Если выбран режим без продуктов или все продукты, то скрываем у продуктов инпуты суммы и процентов
             */
            if(this.mode === 'noP' || this.mode ==='allP'){
                this.callbackItems(function (item){
                    item.select.disabled = true;
                    if(self.distribution.checked){
                        item.sumInput.disabled = true;
                        item.percentInput.disabled = true;
                    }
                })
            }

            /**
             * Проверяем продукты, можно ли добавить/убавить
             */
            this.callbackItems(function (item){
                item.checkCanAddAndDelete();
            })

            /**
             * Скрываем селекты
             */
            this.hideSelects();
        }
    }

    /**
     * @description Выбранны ли продукты
     * @return {boolean}
     */
    productsSelected() {
        let selected = false;
        for (let id in this.items) {
            selected = this.items[id].id > 0;
        }

        return selected;
    }

    /**
     * Общий метод для инициализации блока БЕ и продуктов БЕ
     * У блока БЕ и блока продуктов одинаковые кнопки (добавить, удалить, процент, сумма, селект, бюджет)
     */
    baseBeAndProductInit() {
        let self = this;
        this.addInput = this.wrap.querySelector('.add');
        this.deleteInput = this.wrap.querySelector('.delete');


        this.percentInput = this.wrap.querySelector('.percentInput');
        this.sumInput = this.wrap.querySelector('.sumInput');

        this.select = this.wrap.querySelector('.select-item');
        this.selectOptions = this.select.querySelectorAll('option');

        this.budgetInput = this.wrap.querySelector('.budgetInput');

        this.budget = 0;

        /**
         * Обработчик на кнопку добавления
         */
        this.addInput.addEventListener('click', function () {
            self.addItem();
            self.parent.hideSelects();
            self.checkCanAddAndDelete();
        })

        /**
         * Обработчик на кнопку удаления
         */
        this.deleteInput.addEventListener('click', function () {
            self.deleteItem();
            self.parent.hideSelects();
            self.checkCanAddAndDelete();
        })
    }

    /**
     * Проверка инпутов блока. Если включено какое либо распределение для выбранного БЕ, блокируем инпуты суммы и процентов
     */
    checkInputs() {
        if (this.id >= 0) {
            if(this.parent.distribution.checked){
                this.sumInput.disabled = true;
                this.percentInput.disabled = true;
            }else{
                this.sumInput.disabled = false;
                this.percentInput.disabled = false;
            }
        } else {
            this.sumInput.disabled = true;
            this.percentInput.disabled = true;
        }
    }


    /**
     * Скрывает селекты у продкутов, которые не относятся к БЕ
     */
    hideSelects() {
        let self = this;

        this.callbackItems(function (item) {
            let length = 0;
            item.selectOptions.forEach(function (option) {
                option.disabled = (!!self.selectedItems[option.value]);
                if (option.value > 0) {
                    length++;
                }
            })
            self.maxItems = length;
        })
    }

    /**
     * Сброка данных
     */
    getAllData() {
        this.parent.getAllData();
    }

    /**
     * Проверяет и дизейблит кнопки добавления и удаления блоков БЕ
     */
    checkCanAddAndDelete() {
        let length = 0;

        this.parent.callbackItems(function (item) {
            length++;
        })

        let disDelete = false;

        if (length <= 1) {
            disDelete = true;
        }

        this.parent.callbackItems(function (item) {
            item.deleteInput.disabled = disDelete;
        })

        let addDis = false;

        if (length >= this.parent.maxItems) {
            addDis = true;
        }

        this.parent.callbackItems(function (item) {
            item.addInput.disabled = addDis;
        })

        this.getAllData();
        this.checkInputs();
    }

    /**
     * @description Скрывает инпуты свободного остатка и свободного процента
     */
    hideFreeInputs() {
        ['.free-sum'].forEach((selector) => {
            let inputBlock = Array.from(this.wrap.querySelectorAll(selector));
            if (!inputBlock || inputBlock.length <= 0) {
                return;
            }

            inputBlock.forEach((block) => {
                block.style.display = 'none';
            })
        })
    }

    /**
     * @description Показывает инпуты свободного остатка и свободного процента
     */
    showFreeInputs() {
        ['.free-sum'].forEach((selector) => {
            let inputBlock = Array.from(this.wrap.querySelectorAll(selector));
            if (!inputBlock || inputBlock.length <= 0) {
                return;
            }

            inputBlock.forEach((block) => {
                block.style.display = '';
            })
        })
    }

    /**
     * Дополнительная инициализация для блока БЕ
     */
    beAdditionalInit() {
        let self = this;
        /**
         * Элемент типа распределения.
         * Добавляем ему обработчик события на изменения, чтобы переопределять данные в распределении
         */
        this.distribution = this.wrap.querySelector('.distribution');
        if(this.distribution){
            this.distribution.addEventListener('click',function (){
                self.parent.checkDistribution();
            })
        }


        /**
         * Инициализация продуктов БЕ, если они были выбранны
         */
        this.wrap.querySelectorAll('.product-wrap.new').forEach(function (item) {
            let id = item.getAttribute('data-id');

            if (id >= 0) {
                self.items[id] = new BeProduct({
                    'wrap': item,
                    'id': id,
                    'itemType': 'product-wrap',
                    'parent': self
                })
                self.selectedItems[id] = true;
            }

            self.hideSelects();
        })

        /**
         * Остаток суммы по продуктам
         */
        this.ostPInput = this.wrap.querySelector('.ostP');
        this.ostPInputEditor = this.createMask(this.ostPInput,function (){
            self.setOstP();
        })

        this.percentOstInput = this.wrap.querySelector('.ostPer');

        this.percentOstInputEditor = this.createMask(this.percentOstInput,function (){
            self.setOstPercent();
        }, 2, true)

        /**
         * Обработчик на смену выбранного БЕ
         */
        this.select.addEventListener('change', function () {
            let value = this.value;

            /**
             * При смене БЕ, удаляем все продукты
             */
            self.callbackItems(function (item){
                item.deleteItem();
            })

            self.switchProductsMode('noP');
            self.hideFreeInputs();
            /**
             * Если новое значение не выбрано, то переключаем режим распределения и убираем лишние кнопки
             */
            if (value < 1) {
                value = Math.random();
                let inputUserProducts = self.wrap.querySelector('input[value=userP]');
                if (inputUserProducts) {
                    let freeSum = inputUserProducts.closest('.free-sum');
                    if (freeSum) {
                        self.wrap.querySelector('input[value=userP]').closest('.free-sum').remove();
                    }
                    if (!!self.wrap.querySelector('input[value=allP]').closest('.free-sum')) {
                        self.wrap.querySelector('input[value=allP]').closest('.free-sum').remove();
                    }
                }
            }

            /**
             * Удаляем выбранные блок у родителя
             */
            if (self.parent.selectedItems[self.id]) {
                delete self.parent.selectedItems[self.id];
            }

            /**
             * Сбрасываем ID старого БЕ и записываем на новый
             */
            self.parent.resetKey(self.id, value);

            self.id = value;

            /**
             * Если выбрана существубщая БЕ, то заново инициализируем едиторы и загружаем продукты по этому БЕ
             */
            if (value > 1) {
                self.parent.selectedItems[self.id] = true;
                self.initEditors();
                self.getProductsAjax();
            }

            /**
             * Проверяем инпуты
             */
            self.checkInputs();

            /**
             * Прячем селекты и проверяем распределение
             */
            self.parent.hideSelects();
            self.parent.checkDistribution();
            self.parent.onChangeArticle(false);
        })
        if(this.id >= 0){
            this.initEditors();
        }
    }

    /**
     * Проставляет свободный процент по продуктам
     */
    setOstPercent(){
        if(this.mode !== 'noP'){
            let allPercent = 100;

            this.callbackItems(function (item) {
                if (item.id > 1 && !!item.percentInputEditor) {
                    allPercent = getFloat(allPercent - parseFloat(item.percentInputEditor.unmaskedValue));
                }
            })

            this.percentOstInputEditor.value = ''+allPercent;
        }
    }

    /**
     * Если не выбран режим без продуктов, распределяет остаток суммы по продуктам
     */
    setOstP(){
        if(this.mode !== 'noP'){
            let allSum = parseFloat(this.sumInputEditor.unmaskedValue);
            let sum = 0;
            this.callbackItems(function (item) {
                if(item.id >= 0 && !!item.sumInputEditor){
                    sum += parseFloat(item.sumInputEditor.unmaskedValue);
                }
            })

            this.ostPInputEditor.value = ''+(getFloat(allSum - parseFloat(sum)));
            this.setOstPercent();
        }
    }

    /**
     * Инициализация едиторов, что бы навесить маску на инпуты
     */
    initEditors() {
        this.percentInputEditor = this.createMask(this.percentInput, this.percentChanger.bind(this));

        this.sumInputEditor = this.createMask(this.sumInput, this.sumChanger.bind(this));

        this.fromPercent = false;
        this.fromSum = false;
    }

    /**
     * Обработчик события при изменения суммы в блоке БЕ
     * @param notNeedCheckPercent
     */
    sumChanger(notNeedCheckPercent = false) {
        if (!this.parent.distribution.checked && this.parent.fromParent) {
            return;
        }

        this.fromParent = true;
        this.fromSum = true;

        /**
         * Если введенная сумма в БЕ больше чем сумма в заявке, и не выбрано распределение
         * То высчитываем сумму для текущего блока БЕ, за счет других блоков
         */
        if (parseFloat(this.sumInputEditor.unmaskedValue) > this.parent.getFreeSum(this.id) && !this.parent.distribution.checked) {
            this.sumInputEditor.value = ''+this.parent.getFreeSum(this.id);
        }

        if (!this.procent || this.parent.distribution.checked) {
            /**
             * Задаем процент по этому блоку
             * 100 делим на произведение суммы из формы на сумму блока БЕ
             * @type {string}
             */
            this.percentInputEditor.value = ''+this.getFloat(100 /
                this.parent.sumInputEditor.unmaskedValue * this.sumInputEditor.unmaskedValue);
        }

        this.parent.setFreeSum();

        /**
         * Проходимся по продуктам БЕ и меняем процент
         */
        if (!this.procent || this.parent.distribution.checked) {
            this.callbackItems(function (item){
                if(item.id >= 0){
                    item.percentChanger(notNeedCheckPercent);
                }
            })
        }

        this.fromParent = false;
        this.getAllData();
    }

    /**
     * Изменения процента распределения у блока БЕ/продукта
     * @param notNeedCheckPercent
     */
    percentChanger(notNeedCheckPercent) {
        if(!!this.percentInputEditor && this.percentInputEditor.unmaskedValue === ''){
            this.percentInputEditor.value = '0';
        }

        if (this.fromSum) {
            notNeedCheckPercent = true;
        } else {
            this.procent = true;
        }

        if (
            !!this.percentInputEditor
            && parseFloat(this.percentInputEditor.unmaskedValue) > this.parent.getFreePercent(this.id)
            && (
                notNeedCheckPercent === false
                || this.procent
            )
        ) {
            this.percentInputEditor.value = ''+this.parent.getFreePercent(this.id);
            return;
        }

        if(!this.fromSum && !!this.sumInputEditor){
            this.sumInputEditor.value = (this.parent.sumInputEditor.unmaskedValue /
                100 * (!!this.percentInputEditor ? this.percentInputEditor.unmaskedValue : 100)).toFixed(2);
        }

        if (this.fromSum) {
            this.fromSum = false;
        } else {
            this.parent.setFreeSum();
            this.procent = false;
        }
    }


    /**
     * Добавление нового блока БЕ
     */
    addItem() {
        /**
         * Клонируем прошлый блок и вставляем дальше.
         * @type {Node}
         */
        let newItem = document.querySelector('.cloneBlocks .' + this.itemType).cloneNode(true);
        this.wrap.after(newItem);
        const id = this.generateItemId();

        let self = this;

        this.parent.items[id] = new Be({
            'wrap': newItem,
            'id': id,
            'itemType': 'be-item',
            'parent': self.parent
        })

        this.parent.items[id].wrap.querySelector('.switchProduct').name = 'switcher_' + id;
        this.parent.items[id].wrap.querySelector('.switchProduct').checked = true;
        this.parent.hideSelects();
    }

    /**
     * Удаление объекта блока БЕ
     */
    deleteItem() {
        this.wrap.remove();
        delete this.parent.items[this.id];
        if (this.parent.selectedItems[this.id]) {
            delete this.parent.selectedItems[this.id];
        }

        this.parent.checkDistribution();
    }

    /**
     * Установка свободной цены
     */
    setFreeSum(){
       this.setOstP();
    }

    /**
     * Установка нового типа распределения. Запускает изменение сумм и процентов в продуктах
     * @param mode
     */
    switchProductsMode(mode) {
        let length = 0;
        let self = this;
        this.mode = mode;
        this.showFreeInputs();
        /**
         * Переключатель от типа распределения
         * allP - Все продукты
         * noP - Без продуктов
         * userP - пользовательский набор
         */
        switch (mode) {
            /**
             * Получаем первый продукт, остальные удаляем
             * У первого продукта проставляем значение 0 и запускаем обработчик изменения
             * Пробегаемся по значениям селекта первого продукта и создаем новые продукты из списка.
             * Затем удаляем первый элемент
             *
             * @type {number}
             */
            case 'allP':
                length = 0;
                let firstItem = '';
                this.callbackItems(function (item) {
                    if (length === 0) {
                        firstItem = item;
                    } else {
                        item.deleteItem();
                    }
                    length++;
                })
                firstItem.select.value = '0';
                let event = new Event('change');
                firstItem.select.dispatchEvent(event);

                firstItem.selectOptions.forEach(function (option) {
                    if (option.value > 0) {
                        let newItem = firstItem.wrap.cloneNode(true);
                        firstItem.wrap.after(newItem);
                        let id = Math.random();

                        self.items[id] = new BeProduct({
                            'wrap': newItem,
                            'id': id,
                            'itemType': 'product-wrap',
                            'parent': self
                        })
                        self.items[id].sumInput.value = 0;
                        self.items[id].percentInput.value = 0;
                        self.items[id].select.disabled = true;
                        self.items[id].select.value = option.value;
                        if (!!self.items[id].wrap) {
                            self.items[id].wrap.style.display = '';
                        }
                        self.items[id].select.dispatchEvent(event);
                    }
                })

                firstItem.deleteItem();

                this.distribution.parentNode.style.display = '';

                break;
            /**
             * Скрываем остальные типа распределения
             * Затем пробегаемся по выбранных продуктах
             * Если это первый элемент, то устанавливаем значени 0, запускаем событие изменения
             * Остальные элементы удаляем
             */
            case 'noP':
                length = 0;
                this.callbackItems(function (item) {
                    if (length === 0) {
                        item.select.value = 0;
                        let event = new Event('change');
                        item.select.dispatchEvent(event);
                        item.sumInput.value = 0;
                        item.percentInput.value = 0;
                        item.select.disabled = true;
                        item.addInput.disabled = true;
                        item.deleteInput.disabled = true;
                        if (!!item.wrap) {
                            item.wrap.style.display = 'none';
                        }
                    } else {
                        item.deleteItem();
                    }
                    length++;
                })
                this.hideFreeInputs();
                this.distribution.parentNode.style.display = 'none';
                break;
            /**
             * Пробегаемся по блокам продуктов и снимаем с них disabled
             */
            case 'userP':
                this.callbackItems(function (item) {
                    [...item.select.options].forEach((option) => option.disabled = false);
                    item.select.disabled = false;
                    item.addInput.disabled = false;
                    item.deleteInput.disabled = false;
                    if (!!item.wrap) {
                        item.wrap.style.display = '';
                    }
                    item.checkCanAddAndDelete();
                })

                this.distribution.parentNode.style.display = '';

                break;
        }
        /**
         * Записываем свободный остаток
         */
        this.setOstP();
    }

    /**
     * Инициализация дополнительного функционала для продуктов.
     * Регистрирует обработчик события на изменения распределения
     */
    initProductsFunctional() {
        if (this.wrap.querySelector('.rand')) {
            let number = this.wrap.querySelector('.rand').value;

            let self = this;
            this.distribution = this.wrap.querySelector('input.distribution');

            this.wrap.querySelectorAll('input[name=switcher_' + number + ']').forEach(function (item) {
                item.addEventListener('input', function () {
                    self.switchProductsMode(this.value)
                })
            })

            if (this.wrap.querySelector('.budgetInput')) {
                const budget = parseFloat(this.wrap.querySelector('.budgetInput').value);
                this.budget = isNaN(budget) ? 0 : budget;
                this.wrap.querySelector('.budgetInput').value = budget;
            }
        }
    }

    /**
     * Проверка ошибок блока БЕ
     * @returns {string}
     */
    checkErrors(){
        let str = '';
        let sum = 0;

        if(this.sumInputEditor){
            let haveStr = false,
                self = this;
            this.callbackItems(function (item){
                if (!self.mode || self.mode == 'noP') {
                    return;
                }
                if(item.sumInputEditor){
                    sum = self.getFloat(self.getFloat(sum) + self.getFloat(item.sumInputEditor.unmaskedValue));
                    if(parseFloat(item.sumInputEditor.unmaskedValue) <= 0 && !haveStr){
                        haveStr = true;
                        str += 'Не может быть продуктов с нулевой суммой!<br>';
                    }
                }
            })

            if(parseFloat(this.sumInputEditor.unmaskedValue) > parseFloat(this.budgetInput.value) && this.parent.startInitCompleted){
                str += 'Средств для резервирования недостаточно. Просьба обратиться в фин дирекцию<br>';
            }

            if(parseFloat(this.sumInputEditor.unmaskedValue) <= 0 && this.id>1){
                str += 'Заполните распределение по ' + this.select[this.select.selectedIndex].text + ' или удалите ее из списка распределения <br>';
            }

            if(sum !== parseFloat(this.sumInputEditor.unmaskedValue) && this.mode !== 'noP' && this.id>1 && this.mode){
                str += 'Некорректное распределение суммы по продуктам ' + this.select[this.select.selectedIndex].text +'<br>';
            }
        }

        return str;
    }

    /**
     * Простановка бюджета
     */
    setBudget(){
        if(parseFloat(this.budget) === 0){
            this.budgetInput.value = 0;
        }else{
            this.budgetInput.value = parseFloat((parseFloat(this.budget) / parseFloat(this.parent.rateCurseEditor.unmaskedValue)).toFixed(2));
            if(this.budgetInput.value === 'NaN'){
                this.budgetInput.value = 0;
            }
        }
        this.getAllData();
    }

    /**
     * Фоновая загрузка продуктов под выбранное БЕ
     * Работает на мутациях
     * Загружает html блок продуктов и заново его инициализируем
     */
    getProductsAjax() {
        let self = this;
        let data = {
            'article': this.parent.articlesList.value,
            'month': this.parent.nowMonth,
            'rateCurse': this.parent.rateCurseEditor.unmaskedValue,
            'year': this.parent.nowYear,
            'be': this.id,
            'baseRate': self.parent.rate
        }

        BX.ajax.runComponentAction('immo:iblock.budget.biznes.units', 'getProducts', {
            mode: 'class',
            data: data
        }).then(
            function (response) {
                self.callbackItems(function (item) {
                    item.deleteItem();
                })

                self.wrap.querySelector('.product-section').innerHTML = response.data.html;
                self.budgetInput.value = response.data.budget;
                self.wrap.querySelector('.product-section').querySelectorAll('.new').forEach(function (item) {
                    let id = item.getAttribute('data-id');

                    self.items[id] = new BeProduct({
                        'wrap': item,
                        'parent': self,
                        'itemType': 'product-wrap',
                        'id': id
                    })

                    self.initProductsFunctional();
                    self.switchProductsMode('noP');
                    self.hideSelects();
                });
                self.distribution.addEventListener('click', self.checkDistribution.bind(self))

                self.ostPInput = self.wrap.querySelector('.ostP');
                self.ostPInputEditor = self.createMask(self.ostPInput,function (){
                    self.setOstP();
                })

                self.percentOstInput = self.wrap.querySelector('.ostPer');
                self.percentOstInputEditor = self.createMask(self.percentOstInput,function (){
                    self.setOstPercent();
                }, 2, true)
            }, function (response) {

            })
    }

}