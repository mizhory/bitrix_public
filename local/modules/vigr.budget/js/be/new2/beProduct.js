class BeProduct extends Be{
    /**
     * Стартовый инит
     * */
    startInit(){
        this.baseBeAndProductInit();
        this.beProductAdditionalInit();
        if(this.id > 1){
            this.initEditors();
        }
    }

    /**
     * Добавление продукта БЕ
     * */

    addItem() {
        let newItem;

        let oldItem;

        this.parent.callbackItems(function (item){
            try {
                newItem = item.wrap.cloneNode(true);
                oldItem = item;
                throw 'w';
            }catch (error){

            }
        })
        let id = Math.random();
        newItem.querySelector('.select-item').value = '0';
        newItem.setAttribute('data-id','0');

        this.wrap.after(newItem);


        let self = this;

        this.parent.items[id] = new BeProduct({
            'wrap': newItem,
            'id': id,
            'itemType': 'be-item',
            'parent': self.parent
        })

        this.parent.items[id].percentInput.value = 0;
        this.parent.items[id].sumInput.value = 0;

        this.parent.hideSelects();
        this.parent.checkCanAddAndDelete();
        this.parent.checkDistribution();
    }

    /**
     * Проверка возможности добавления удаления
     * */

    checkCanAddAndDelete() {
        let length = 0;

        this.parent.callbackItems(function (item) {
            length++;
        })

        let disDelete = false;

        if (length <= 1 || this.parent.mode === 'allP') {
            disDelete = true;
        }

        this.parent.callbackItems(function (item) {
            item.deleteInput.disabled = disDelete;
        })

        let addDis = false;

        if (length >= this.parent.maxItems || this.parent.mode === 'allP') {
            addDis = true;
        }

        this.parent.callbackItems(function (item) {
            item.addInput.disabled = addDis;
        })

        this.getAllData();
        this.checkInputs();
    }


    /**
     * коллбэк при изменении суммы
     * */

    sumChanger(fromParent) {
        if(!this.parent.distribution.checked){
            if (parseFloat(this.sumInputEditor.unmaskedValue) > this.parent.getFreeSum(this.id) && !this.parent.distribution.checked) {
                this.sumInputEditor.value = ''+this.parent.getFreeSum(this.id);
            }

            this.NS = true;
            this.percentInputEditor.value = ''+this.getFloat(100 /
                this.parent.sumInputEditor.unmaskedValue * this.sumInputEditor.unmaskedValue);
            this.NS = false;
        }
        this.parent.setFreeSum();
        this.getAllData();
    }

    /**
     * коллбэк при изменении процента
     * */

    percentChanger(fromParent){
        if(!this.parent.distribution.checked){
            if (parseFloat(this.percentInputEditor.unmaskedValue) > this.parent.getFreePercent(this.id)) {
                this.percentInputEditor.value = ''+this.parent.getFreePercent(this.id);
            }
        }

        if(!this.NS){
            this.sumInputEditor.value = '' + (this.parent.sumInputEditor.unmaskedValue /
                100 * this.percentInputEditor.unmaskedValue).toFixed(2);
        }
        this.getAllData();
    }

    beProductAdditionalInit(){
        this.checkCanAddAndDelete();
        this.initSelectors();
    }

    /**
     * инит селекторов
     * */

    initSelectors() {
        let self = this;
        this.select.addEventListener('change', function () {
            let value = this.value;

            if (value < 1) {
                value = Math.random();
            }

            if(self.parent.selectedItems[self.id]){
                delete self.parent.selectedItems[self.id];
            }

            self.parent.resetKey(self.id, value);
            self.id = value;

            if(value > 1){
                self.initEditors();
                self.parent.selectedItems[self.id] = true;
            }else{
                self.parent.items[value].percentInput.value = 0;
                self.parent.items[value].sumInput.value = 0;
            }
            self.parent.hideSelects();
            self.parent.checkDistribution();
        })
    }
}
