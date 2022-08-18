
class Be extends WrapItems {
    init() {
        let self = this;

        this.budgetInput = this.wrap.querySelector('.budgetInput');
        this.baseItemInit();
        this.addInput.addEventListener('click', function () {
            self.addItem();
        })

        this.deleteInput.addEventListener('click', function () {
            self.deleteItem();
            self.checkCanAddAndDelete();
        })
        this.initEditors();
        this.checkInputs();
        this.initSelects();
        //this.hideSelects();

        this.budget = 0;

        if(this.wrap.querySelector('#distribution')){
            this.distribution = this.wrap.querySelector('#distribution');
        }

        this.wrap.querySelectorAll('.beproduct-item.new').forEach(function (item){
            let id = item.getAttribute('data-id');
            if(id >= 0){
                self.items[id] = new BeProduct({
                    'wrap' : item,
                    'id' : id,
                    'itemType' : 'beproduct-item',
                    'parent' : self
                })
                self.selectedItems[id] = true;
                self.maxItems = self.items[id].hideSelects();
            }
        })

        let length = 0;

        this.callbackItems(function (item){
            length++;
        })

        if(length > 0){
            setTimeout(function (){
                self.initProductsFunctional();
            },700)
        }
    }

    baseItemInit(){
        this.percentInput = this.wrap.querySelector('.percentInput');

        this.select = this.wrap.querySelector('.select-item');
        this.selectOptions = this.select.querySelectorAll('option');

        this.addInput = this.wrap.querySelector('.add');
        this.deleteInput = this.wrap.querySelector('.delete');
    }

    getAllData() {
        this.parent.getAllData();
    }

    initDistribution(){
        let self = this;
        this.distribution.addEventListener('click',function (){
            self.checkDistribution();
        })
    }

    initEditors(re) {
        let self = this;
        this.percentInputEditor = new BX.Currency.Editor({
            'input': this.percentInput,
            'callback': function () {
                if (!this.fromSum) {
                    self.percentChanger(this);
                    //self.getAllData();
                }
            }
        });

        if (re) {
            this.sumInputEditor = new BX.Currency.Editor({
                'input': this.sumInput,
                'callback': function () {
                    if (!this.fromPercent) {
                        self.sumChanger(this);
                        self.getAllData();
                    }
                }
            });
            this.sumInputEditor.fromPercent = false;
        }
        this.percentInputEditor.fromSum = false;
    }

    initSelects() {
        let self = this;
        this.select.addEventListener('change', function () {
            let value = this.value;


            self.callbackItems(function (item){
                item.deleteItem();
            })

            if (value < 1) {
                value = Math.random();
            }

            if(self.parent.selectedItems[self.id]){
                delete self.parent.selectedItems[self.id];
            }

            self.parent.resetKey(self.id, value);

            self.id = value;

            self.initEditors(true);

            self.checkInputs();

            if (value < 1) {
                self.percentInputEditor.formatValue();
            }

            if(value > 1){
                self.parent.selectedItems[self.id] = true;
                self.getProductsAjax();
            }

            self.setSum();
            self.parent.checkDistribution();
            self.parent.callbackItems(function (item){
                item.hideSelects();
            })
            self.getAllData();
        })
    }

    hideSelects() {
        let self = this;
        let length = 0;
        this.selectOptions.forEach(function (option) {
            if ((option.value > 0 && option.getAttribute('data-rate') !== self.parent.rateBe) || self.parent.selectedItems[option.value]) {
                if(self.parent.selectedItems[option.value]){
                    length++;
                }
                option.style.display = 'none';
            } else {
                if (option.value > 0) {
                    length++;
                }
                option.style.display = '';
            }
        })
        this.parent.maxItems = length;

        return length;
    }

    deleteItem(){
        this.wrap.remove();
        delete this.parent.items[this.id];
        if(this.parent.selectedItems[this.id]){
            delete this.parent.selectedItems[this.id];
        }
        this.parent.callbackItems(function (item){
            item.hideSelects();
        })
        this.checkCanAddAndDelete();
        this.parent.checkDistribution();
    }

    checkErrors(){
        let str = '';

        let self = this;

        if(parseFloat(this.sumInputEditor.value) > parseFloat(this.budgetInput.value)){
           // str += 'Недостаточно бюджета! ' + this.select[this.select.selectedIndex].text +'<br>';
           str += 'Средств для резервирования недостаточно. Просьба обратиться в фин дирекцию<br>';
        }

        /*
        if(parseFloat(this.sumInputEditor.value) <= 0 && this.id>1){
            str += 'Требуется распеределить сумму по ' + this.select[this.select.selectedIndex].text + '<br>';
        }
        */


        if(parseFloat(this.sumInputEditor.value) <= 0 && this.id>1){
            str += 'Заполните распределение по ' + this.select[this.select.selectedIndex].text + ' или удалите ее из списка распределения <br>';
        }

        let sum = 0;

        this.callbackItems(function (item){
            sum += parseFloat(item.sumInputEditor.value);
        })

        if(sum !== parseFloat(this.sumInputEditor.value) && this.mode !== 'noP' && this.id>1 && this.mode){
            str += 'Неккоректное распределение суммы по продуктам ' + this.select[this.select.selectedIndex].text +'<br>';
        }

        return str;
    }

    addItem(returned) {
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

        this.parent.items[id].hideSelects();

        if(returned){
            return this.parent.items[id];
        }

        this.checkCanAddAndDelete();
    }

    checkCanAddAndDelete() {
        let length = 0;

        this.parent.callbackItems(function () {
            length++;
        })

        let disDelete = false;
        if(length <= 1 || this.mode === 'allP'){
            disDelete = true;
        }
        this.parent.callbackItems(function (item) {
            item.deleteInput.disabled = disDelete;
        })

        let addDis = false;

        if(length >= this.parent.maxItems){
            addDis = true;
        }

        this.parent.callbackItems(function (item) {
            item.addInput.disabled = addDis;
        })

        this.checkDistribution();
        this.getAllData();
    }

    checkInputs() {
        if (this.id < 1) {
                this.percentInputEditor.input.value = 0;
                this.percentInputEditor.input.disabled = true;
                this.sumInputEditor.input.disabled = true;
        } else {
            if(this.parent.distribution && this.parent.distribution.checked){
                this.percentInputEditor.input.disabled = true;
                this.sumInputEditor.input.disabled = true;
            }else{
                this.percentInputEditor.input.disabled = false;
                this.sumInputEditor.input.disabled = false;
            }
        }
    }

    getProductsAjax(){
        let self = this;
        let data = {
            'article': this.parent.articlesList.value,
            'month': this.parent.optionsMonth[this.parent.month.selectedIndex].text,
            'rateCurse': this.parent.rateCurse.value,
            'year': this.parent.yearOptions[this.parent.year.selectedIndex].text,
            'be': this.id,
            'baseRate': self.parent.rate
        }

        BX.ajax.runComponentAction('vigr:budget.be', 'getProducts', {
            mode: 'class',
            data: data
        }).then(
            function (response) {

                self.wrap.querySelector('.product-section').innerHTML = response.data.html;
                self.wrap.querySelector('.product-section').querySelectorAll('.new').forEach(function (item) {
                    let id = item.getAttribute('data-id');

                    self.items[id] = new BeProduct({
                        'wrap': item,
                        'parent': self,
                        'itemType' : 'beproduct-item',
                        'id': id
                    })
                    self.budgetInput.value = response.data.budget;
                    self.budget = parseFloat(response.data.budget);
                    self.initProductsFunctional();
                    setTimeout(function (){

                        self.startInit = true;
                        self.setBudget();
                        self.parent.getAllData();
                    },300)

                });
            }, function (response) {

            })
    }

    initProductsFunctional(){
        if(this.wrap.querySelector('.rand')){
            let number = this.wrap.querySelector('.rand').value;

            let self = this;
            this.distribution = this.wrap.querySelector('input.distribution');

            self.switchProductsMode(this.wrap.querySelector('input[name=switcher_' + number + ']:checked').value)

            this.initDistribution();
            this.wrap.querySelectorAll('input[name=switcher_' + number + ']').forEach(function (item){
                item.addEventListener('input',function (){
                    self.switchProductsMode(this.value)
                })
            })

            if(this.wrap.querySelector('.budgetInput')){
                this.budget = parseFloat(this.wrap.querySelector('.budgetInput').value);
            }

            this.setBudget();
        }
    }

    setBudget(){
        if(parseFloat(this.budget) === 0){
            this.budgetInput.value = 0;
        }else{
            this.budgetInput.value = parseFloat((parseFloat(this.budget) / parseFloat(this.parent.rateCurse.value)).toFixed(2));
            if(this.budgetInput.value === 'NaN'){
                this.budgetInput.value = 0;
            }
        }

    }

    switchProductsMode(mode){
        let length = 0;
        let self = this;
        this.mode = mode;
        switch (mode){
            case 'allP':
                length = 0;
                let firstItem = '';

                this.callbackItems(function (item){
                    if(length === 0){
                        firstItem = item;
                    }else{
                        item.deleteItem();
                    }
                    length++;
                })
                firstItem.select.value = '0';
                let event = new Event('change');
                firstItem.select.dispatchEvent(event);

                firstItem.selectOptions.forEach(function (option){
                    if(option.value>0){
                        let newItem = firstItem.wrap.cloneNode(true);
                        firstItem.wrap.after(newItem);
                        let id = Math.random();

                        self.items[id] = new BeProduct({
                            'wrap': newItem,
                            'id': id,
                            'itemType': 'beproduct-item',
                            'parent': self
                        })

                        self.items[id].addInput.disabled = true;
                        self.items[id].deleteInput.disabled = true;
                        self.items[id].select.disabled = true;
                        self.items[id].select.value = option.value;
                        self.items[id].select.dispatchEvent(event);
                    }
                })

                firstItem.deleteItem();

                self.checkDistribution();
                break;
            case 'noP':
                length = 0;
                this.callbackItems(function (item){
                    if(length === 0){
                        item.select.value = 0;
                        let event = new Event('change');
                        item.select.dispatchEvent(event);
                        item.sumInputEditor.input.value = 0;
                        setValueEditor(item.sumInputEditor);
                        item.percentInputEditor.input.value = 0;
                        setValueEditor(item.percentInputEditor);
                        item.select.disabled = true;
                        item.addInput.disabled = true;
                        item.deleteInput.disabled = true;

                    }else{
                        item.deleteItem();
                    }
                    length++;
                })
                break;
            case 'userP':
                this.callbackItems(function (item){
                    item.select.disabled = false;
                    item.addInput.disabled = false;
                    item.deleteInput.disabled = false;
                    item.checkCanAddAndDelete();
                })
                break;
        }
        this.checkCanAddAndDelete();
    }

    getFreeSum(nowId) {
        let allSum = this.sumInputEditor.value;

        let sum = 0;

        this.callbackItems(function (item) {
            if (item.id !== nowId) {
                sum += parseFloat(item.sumInputEditor.value);
            }
        })

        return parseFloat((allSum - sum).toFixed(2));
    }

    percentChanger(editor){
        if(this.parent.startInit){
            if(parseFloat(editor.value) > 0){
                if(editor.value > this.parent.getFreePercent(this.id)){
                    editor.input.value = this.parent.getFreePercent(this.id);
                    setValueEditor(editor);
                }
                this.setSum();
            }else{
                editor.input.value = 0;
            }

            setValueEditor(editor);
            this.getAllData();
        }
    }

    sumChanger(editor) {
        if(this.parent.startInit){
            if(parseFloat(editor.value) > 0){
                if(editor.value > this.parent.getFreeSum(this.id)){
                    editor.input.value = this.parent.getFreeSum(this.id);
                    setValueEditor(editor);
                }
                this.setPercent();
            }else{
                editor.input.value = 0;
            }

            setValueEditor(editor);
            this.callbackItems(function (item){
                item.setSum();
            })
            this.getAllData();
        }


    }

    setSum(){
        if(parseFloat(this.percentInputEditor.value) > 0){
            this.sumInputEditor.input.value = this.parent.sumInputEditor.value / 100 * this.percentInputEditor.value;
            setValueEditor(this.sumInputEditor);
        }

        this.callbackItems(function (item){
            item.setSum();
        })

        this.getAllData();
    }

    setPercent(){
        if(parseFloat(this.sumInputEditor.value) > 0){
            this.percentInputEditor.input.value = this.getFloat(100 / this.parent.sumInputEditor.value * this.sumInputEditor.value);
            setValueEditor(this.percentInputEditor);
        }

        this.getAllData();
    }

    checkDistribution(){
        let self = this;
        if(this.distribution.checked){
            let length = 0;
            this.callbackItems(function (item){
                if(item.id > 1){
                    length++;
                }
            })

            this.callbackItems(function (item){
                item.sumInputEditor.input.value = self.getFloat(self.sumInputEditor.value / length);
                setValueEditor(item.sumInputEditor);
            })

            this.callbackItems(function (item){
                item.setPercent();
            })
        }
        this.callbackItems(function (item){
            item.checkInputs();
        })

        this.getAllData();
    }
}