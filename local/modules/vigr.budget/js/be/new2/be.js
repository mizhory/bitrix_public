
class Be extends WrapItems {
    /**
     * Начальня инициализация
     * */
    startInit() {
        let self = this;
        this.baseBeAndProductInit();
        this.beAdditionalInit();

        this.checkInputs();

        if(this.id < 1){
            this.switchProductsMode('noP');
        }

        if(this.id > 1){
            this.setOstP();
            this.mode = this.wrap.querySelector('input[name=switcher_' + this.wrap.querySelector('.rand').value + ']:checked').value;
            if(this.mode !== 'noP'){
                this.ostPInput.closest('label').style.display = '';
                this.percentOstInput.closest('label').style.display = '';
            }

            if(this.mode === 'noP' || this.mode ==='allP'){
                this.callbackItems(function (item){
                    item.select.disabled = true;
                    if(self.distribution.checked){
                        item.sumInput.disabled = true;
                        item.percentInput.disabled = true;
                    }
                })
            }

            this.callbackItems(function (item){
                item.checkCanAddAndDelete();
            })

            this.hideSelects();
        }
    }

    /**
     * Стартовый инит для БЕ и продуктов
     * */

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

        this.addInput.addEventListener('click', function () {
            self.addItem();
            self.parent.hideSelects();
            self.checkCanAddAndDelete();
        })

        this.deleteInput.addEventListener('click', function () {
            self.deleteItem();
            self.parent.hideSelects();
            self.checkCanAddAndDelete();
        })
    }

    /**
     * Проверка возможности вводить сумму и процент
     * */

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
     * Скрытие уже выбранных БЕ / продуктов БЕ
     * */
    hideSelects() {
        let self = this;

        this.callbackItems(function (item) {
            let length = 0;
            item.selectOptions.forEach(function (option) {
                if (!self.selectedItems[option.value]) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
                if (option.value > 0) {
                    length++;
                }
            })
            self.maxItems = length;
        })
    }

    getAllData() {
        this.parent.getAllData();
    }

    /**
     * Проверка возможности добавления / удаления
     * */

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

        this.checkDistribution();
        this.getAllData();
        this.checkInputs();
    }

    /**
     * Инит уникального для БЕ поведения
     * */

    beAdditionalInit() {
        let self = this;
        this.distribution = this.wrap.querySelector('.distribution');
        if(this.distribution){
            this.distribution.addEventListener('click',function (){
                self.parent.checkDistribution();
            })
        }


        this.wrap.querySelectorAll('.beproduct-item.new').forEach(function (item) {
            let id = item.getAttribute('data-id');

            if (id >= 0) {
                self.items[id] = new BeProduct({
                    'wrap': item,
                    'id': id,
                    'itemType': 'beproduct-item',
                    'parent': self
                })
                self.selectedItems[id] = true;
            }

            self.hideSelects();
        })

        this.ostPInput = this.wrap.querySelector('.ostP');
        this.ostPInputEditor = this.createMask(this.ostPInput,function (){
            self.setOstP();
        })

        this.percentOstInput = this.wrap.querySelector('.ostPer');

        this.percentOstInputEditor = this.createMask(this.percentOstInput,function (){
            self.setOstPercent();
        })

        this.select.addEventListener('change', function () {
            let value = this.value;


            self.callbackItems(function (item){
                item.deleteItem();
            })

            if (value < 1) {
                value = Math.random();
                self.switchProductsMode('noP');
                if(self.wrap.querySelector('input[value=userP]')){
                    self.wrap.querySelector('input[value=userP]').closest('label').remove();
                    self.wrap.querySelector('input[value=allP]').closest('label').remove();
                }
            } else {
                self.callbackItems(function (item) {
                    item.deleteItem();
                })
            }

            if (self.parent.selectedItems[self.id]) {
                delete self.parent.selectedItems[self.id];
            }

            self.parent.resetKey(self.id, value);

            self.id = value;

            if (value > 1) {
                self.parent.selectedItems[self.id] = true;
                self.initEditors();
                self.getProductsAjax();

                self.callbackItems(function (item) {

                })


            }

            self.checkInputs();

            self.parent.hideSelects();
            self.parent.checkDistribution();

        })
        if(this.id > 1){
            this.initEditors();
        }
    }

    /**
     * установка остатка процента для БЕ
     * */

    setOstPercent(){
        if(this.mode !== 'noP'){
            let allPercent = 100;

            this.callbackItems(function (item) {
                if(item.id>1){
                    allPercent -= parseFloat(item.percentInputEditor.unmaskedValue);
                }
            })

            this.percentOstInputEditor.value = ''+allPercent;
        }
    }

    /**
     * установка остатка суммы для БЕ
     * */

    setOstP(){
        if(this.mode !== 'noP'){
            let allSum = parseFloat(this.sumInputEditor.unmaskedValue);
            let sum = 0;
            this.callbackItems(function (item) {
                if(item.id > 1){
                    sum += parseFloat(item.sumInputEditor.unmaskedValue);
                }
            })

            this.ostPInputEditor.value = ''+(getFloat(allSum - parseFloat(sum)));
            this.setOstPercent();
        }
    }

    /**
     * инит редакторов сумм
     * */

    initEditors() {
        let self = this;

        this.percentInputEditor = this.createMask(this.percentInput,function (){
            self.percentChanger();
        });

        this.sumInputEditor = this.createMask(this.sumInput,function (){
            self.sumChanger();
        });
    }

    /**
     * коллбэк при изменении суммы
     * */

    sumChanger(fromParent) {
        if(!this.parent.distribution.checked){
            if (parseFloat(this.sumInputEditor.unmaskedValue) > this.parent.getFreeSum(this.id)) {
                this.sumInputEditor.value = ''+this.parent.getFreeSum(this.id);
            }

            this.notPChange = true;
            this.percentInputEditor.value = ''+this.getFloat(100 /
                this.parent.sumInputEditor.unmaskedValue * this.sumInputEditor.unmaskedValue);
            this.notPChange = false;
        }

        if(this.distribution.checked){
            this.callbackItems(function(item){
                if(item.id>1){
                    item.sumInputEditor.value = (item.parent.sumInputEditor.unmaskedValue /
                        100 * item.percentInputEditor.unmaskedValue).toFixed(2);
                }
            })
        }

        this.parent.setFreeSum();
    }

    /**
     * коллбэк при изменении процента
     * */

    percentChanger() {
        if(!this.notPChange){
            if(!this.parent.distribution.checked){
                if (parseFloat(this.percentInputEditor.unmaskedValue) > this.parent.getFreePercent(this.id)) {
                    this.percentInputEditor.value = ''+this.parent.getFreePercent(this.id);
                }
            }

            this.sumInputEditor.value = '' + (this.parent.sumInputEditor.unmaskedValue /
                100 * this.percentInputEditor.unmaskedValue).toFixed(2);
        }
    }


    setFreeSum(){
        this.setOstP();
    }

    /**
     * добавить БЕ
     * */

    addItem() {
        let newItem = document.querySelector('.cloneBlocks .' + this.itemType).cloneNode(true);
        this.wrap.after(newItem);
        let id = Math.random();

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
        //this.checkCanAddAndDelete();
    }

    /**
     * Удалить БЕ
     * */

    deleteItem() {
        this.wrap.remove();
        delete this.parent.items[this.id];
        if (this.parent.selectedItems[this.id]) {
            delete this.parent.selectedItems[this.id];
        }

        this.parent.checkDistribution();
    }

    /**
     * Переключение типа продуктов(все,без, произвольно)
     * */

    switchProductsMode(mode) {
        let length = 0;
        let self = this;
        this.mode = mode;
        this.ostPInput.closest('label').style.display = '';

        this.percentOstInput.closest('label').style.display = '';
        switch (mode) {
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
                            'itemType': 'beproduct-item',
                            'parent': self
                        })
                        self.items[id].sumInput.value = 0;
                        self.items[id].percentInput.value = 0;
                        self.items[id].select.disabled = true;
                        self.items[id].select.value = option.value;
                        self.items[id].select.dispatchEvent(event);
                    }
                })

                firstItem.deleteItem();

                break;
            case 'noP':
                length = 0;
                this.ostPInput.closest('label').style.display = 'none';
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
                    } else {
                        item.deleteItem();
                    }
                    length++;
                })
                this.ostPInput.closest('label').style.display = 'none';
                this.percentOstInput.closest('label').style.display = 'none';
                break;
            case 'userP':
                this.callbackItems(function (item) {
                    item.select.disabled = false;
                    item.addInput.disabled = false;
                    item.deleteInput.disabled = false;
                    item.checkCanAddAndDelete();
                })

                break;
        }
        this.setOstP();
    }

    /**
     *инит функционала для продуктов
     * */

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
                this.budget = parseFloat(this.wrap.querySelector('.budgetInput').value);
            }
        }
    }

    /**
     * Проверка ошибок
     * */

    checkErrors(){
        let str = '';
        let sum = 0;
        let self = this;

        if(this.sumInputEditor){
            let haveStr = false;
            this.callbackItems(function (item){
                if(item.sumInputEditor){
                    sum += parseFloat(item.sumInputEditor.unmaskedValue);
                    if(parseFloat(item.sumInputEditor.unmaskedValue) <= 0 && !haveStr){
                        haveStr = true;
                        str += 'Не может быть продуктов с нулевой суммой!<br>';
                    }
                }
            })

            if(parseFloat(this.sumInputEditor.unmaskedValue) > parseFloat(this.budgetInput.value)){
                str += 'Средств для резервирования недостаточно. Просьба обратиться в фин дирекцию<br>';
            }

            if(parseFloat(this.sumInputEditor.unmaskedValue) <= 0 && this.id>1){
                str += 'Заполните распределение по ' + this.select[this.select.selectedIndex].text + ' или удалите ее из списка распределения <br>';
            }

            if(sum !== parseFloat(this.sumInputEditor.unmaskedValue) && this.mode !== 'noP' && this.id>1 && this.mode){
                str += 'Неккоректное распределение суммы по продуктам ' + this.select[this.select.selectedIndex].text +'<br>';
            }
        }

        return str;
    }

    /**
     * установка доступного бюджета
     * */

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
     * Получение продуктов БЕ
     * */

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

        BX.ajax.runComponentAction('vigr:budget.be', 'getProducts', {
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
                        'itemType': 'beproduct-item',
                        'id': id
                    })

                    self.initProductsFunctional();
                    self.switchProductsMode('noP');
                    self.hideSelects();
                });

                self.distribution.addEventListener('click',function (){
                    self.checkDistribution();
                })

                self.ostPInput = self.wrap.querySelector('.ostP');
                self.ostPInputEditor = self.createMask(self.ostPInput,function (){
                    self.setOstP();
                })

                self.percentOstInput = self.wrap.querySelector('.ostPer');
                self.percentOstInputEditor = self.createMask(self.percentOstInput,function (){
                    self.setOstPercent();
                })
            }, function (response) {

            })
    }
}