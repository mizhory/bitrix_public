/**
 * Класс для работы с блоком БЕ. Наследуется от общего класса формы БЕ
 */
class Be extends WrapItems {
    /**
     * Переопределенный метод инициализации класса
     */
    startInit() {
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
        if(this.id > 1){
            /**
             * Устанавливаем остаток по продуктам
             */
            this.setOstP();
            /**
             * Определяем тип распределения
             */
            this.mode = this.wrap.querySelector('input[name=switcher_' + this.wrap.querySelector('.rand').value + ']:checked').value;
            /**
             * Если выбран режим распределения, то нужно отобразить инпуты остатков
             */
            if(this.mode !== 'noP'){
                this.ostPInput.closest('.free-sum').style.display = '';
                this.percentOstInput.closest('.free-sum').style.display = '';
            }

            /**
             * Если выбран режим без продуктов или все продукты, то скрываем у продуктов инпуты суммы и процентов
             */
            if(this.mode === 'noP' || this.mode ==='allP'){
                this.callbackItems((item) => {
                    item.select.disabled = true;
                    if(self.distribution.checked){
                        item.sumInput.disabled = true;
                        item.percentInput.disabled = true;
                    }
                })
            }

            if (this.mode === 'noP') {
                this.ostPInput.closest('.free-sum').style.display = 'none';
                this.percentOstInput.closest('.free-sum').style.display = 'none';
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
     * Общий метод для инициализации блока БЕ и продуктов БЕ
     * У блока БЕ и блока продуктов одинаковые кнопки (добавить, удалить, процент, сумма, селект, бюджет)
     */
    baseBeAndProductInit() {
        let self = this;

        this.budget = 0;

        this.defineElements([
            {query: 'textarea[name="description"]', key: 'description'},
            {query: '.add', key: 'addInput'},
            {query: '.delete', key: 'deleteInput'},
            {query: '.percentInput', key: 'percentInput'},
            {query: '.sumInput', key: 'sumInput'},
            {query: '.select-item', key: 'select',},
            {query: '.budgetInput', key: 'budgetInput'},
        ])

        this.selectOptions = this.select.querySelectorAll('option');

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

        if (!!this.description) {
            this.setEventListener({
                element: this.description,
                eventName: 'input'
            })
        }
    }

    /**
     * Проверка инпутов блока. Если включено какое либо распределение для выбранного БЕ, блокируем инпуты суммы и процентов
     */
    checkInputs() {
        if (this.id > 1) {
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
        this.select.addEventListener('change', () => {
            this.onChangeSelectBe();
            this.parent.onChangeArticle();
        })
        if(this.id > 1){
            this.initEditors();
        }
    }

    /**
     * @description Обработчик события изменения БЕ
     */
    onChangeSelectBe() {
        let value = this.select.value;

        /**
         * При смене БЕ, удаляем все продукты
         */
        this.callbackItems(function (item){
            item.deleteItem();
        })

        /**
         * Если новое значение не выбрано, то переключаем режим распределения и убираем лишние кнопки
         */
        if (value < 1) {
            value = this.generateItemId();
            this.switchProductsMode('noP');
            if(this.wrap.querySelector('input[value=userP]')){
                [
                    this.wrap.querySelector('input[value=userP]').closest('.free-sum'),
                    this.wrap.querySelector('input[value=allP]').closest('.free-sum')
                ].forEach((element) => {
                    if (!element) {
                        return;
                    }

                    element.remove();
                })
            }
        }

        /**
         * Удаляем выбранные блок у родителя
         */
        if (this.parent.selectedItems[this.id]) {
            delete this.parent.selectedItems[this.id];
        }

        /**
         * Сбрасываем ID старого БЕ и записываем на новый
         */
        this.parent.resetKey(this.id, value);

        this.id = value;

        /**
         * Если выбрана существубщая БЕ, то заново инициализируем едиторы и загружаем продукты по этому БЕ
         */
        if (value > 1) {
            this.parent.selectedItems[this.id] = true;
            this.switchProductsMode('noP');
            this.initEditors();
            this.getProductsAjax();
        }

        /**
         * Проверяем инпуты
         */
        this.checkInputs();

        /**
         * Прячем селекты и проверяем распределение
         */
        this.parent.hideSelects();
        this.parent.checkDistribution();
    }

    /**
     * Проставляет свободный процент по продуктам
     */
    setOstPercent(){
        if(this.mode !== 'noP'){
            let allPercent = 100;

            this.callbackItems((item) => {
                if(item.id>1){
                    allPercent = this.getFloat(allPercent - parseFloat(item.percentInputEditor.unmaskedValue));
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
                if(item.id > 1){
                    sum += parseFloat(item.sumInputEditor.unmaskedValue);
                }
            })

            this.ostPInputEditor.value = ''+(this.getFloat(allSum - parseFloat(sum)));
            this.setOstPercent();
        }
    }

    /**
     * Инициализация едиторов, что бы навесить маску на инпуты
     */
    initEditors() {
        this.percentInputEditor = this.createMask(this.percentInput, () => {
            this.percentChanger();
            this.getHeadParent(this).render();
        });

        this.sumInputEditor = this.createMask(this.sumInput, () => {
            this.sumChanger();
            this.getHeadParent(this).render();
        });

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
            this.callbackItems(function (item) {
                if (item.id >= 0) {
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
        if (this.percentInputEditor.unmaskedValue === '') {
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

        if (!this.fromSum) {
            this.sumInputEditor.value = (this.parent.sumInputEditor.unmaskedValue /
                100 * this.percentInputEditor.unmaskedValue).toFixed(2);
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
            'parent': self.parent,
            mainWrap: this.mainWrap
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
        this.ostPInput.closest('.free-sum').style.display = '';

        this.percentOstInput.closest('.free-sum').style.display = '';
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

                this.getAllData();

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
                this.ostPInput.closest('.free-sum').style.display = 'none';
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
                this.ostPInput.closest('.free-sum').style.display = 'none';
                this.percentOstInput.closest('.free-sum').style.display = 'none';

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
                    self.switchProductsMode(this.value);
                    self.parent.render();
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
            let haveStr = false;
            if (!!this.mode && this.mode != 'noP') {
                this.callbackItems((item) => {
                    if(item.sumInputEditor){
                        sum = this.getFloat(this.getFloat(sum) + this.getFloat(item.sumInputEditor.unmaskedValue));
                        if(parseFloat(item.sumInputEditor.unmaskedValue) <= 0 && !haveStr){
                            haveStr = true;
                            str += 'Не может быть продуктов с нулевой суммой!<br>';
                        }
                    }
                })
            }

            if(parseFloat(this.sumInputEditor.unmaskedValue) > parseFloat(this.budgetInput.value) && this.parent.startInitCompleted){
                this.addError(
                    'distribution',
                    'Средств для резервирования недостаточно. Просьба обратиться в фин дирекцию'
                );
                str += 'Средств для резервирования недостаточно. Просьба обратиться в фин дирекцию<br>';
            } else {
                this.deleteError('distribution');
            }

            if(parseFloat(this.sumInputEditor.unmaskedValue) <= 0 && this.id>1){
                this.addError(
                    'distributionSum',
                    'Заполните распределение по ' + this.select[this.select.selectedIndex].text + ' или удалите ее из списка распределения'
                );
                str += 'Заполните распределение по ' + this.select[this.select.selectedIndex].text + ' или удалите ее из списка распределения <br>';
            } else {
                this.deleteError('distributionSum');
            }

            if(sum !== parseFloat(this.sumInputEditor.unmaskedValue) && this.mode !== 'noP' && this.id>1 && this.mode){
                this.addError(
                    'distributionSumEx',
                    'Средств для резервирования недостаточно. Просьба обратиться в фин дирекцию'
                );
                str += 'Общая сумма значения "Потратили" не была распределена, скорректируйте данные ' + this.select[this.select.selectedIndex].text +'<br>';
            } else {
                this.deleteError('distributionSumEx');
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
            'baseRate': self.parent.rate,
            template: 'avans'
        }

        this.openLoader();

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
                        'id': id,
                        mainWrap: self.mainWrap
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

                self.switchProductsMode('noP');
                self.destroyLoader();
            }, function (response) {

            })
    }

}