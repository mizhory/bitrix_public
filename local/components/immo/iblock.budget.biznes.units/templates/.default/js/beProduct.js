class BeProduct extends Be {
    init() {
        let self = this;
        this.baseItemInit();
        this.initEditors();
        this.checkInputs();
        this.initSelects();

        this.addInput.addEventListener('click', function () {
            self.addItem();
        })

        this.fromChanger = false;

        this.deleteInput.addEventListener('click', function () {
            self.deleteItem();
        })
    }

    hideSelects() {
        let self = this;
        let length = 0;

        this.selectOptions.forEach(function (option) {
            if (self.parent.selectedItems[option.value]) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
            if (option.value > 0) {
                length++;
            }
        })

        return length;
    }

    deleteItem() {
        let self = this;
        this.wrap.remove();
        delete this.parent.items[this.id];
        if(this.parent.selectedItems[this.id]){
            delete this.parent.selectedItems[this.id];
        }


        this.parent.callbackItems(function (item){
            self.parent.maxItems = item.hideSelects();
        })
        this.checkCanAddAndDelete();
    }

    initSelects() {
        let self = this;
        this.select.addEventListener('change', function () {
            let value = this.value;

            if (value < 1) {
                value = Math.random();
            }

            if(self.parent.selectedItems[self.id]){
                delete self.parent.selectedItems[self.id];
            }

            self.parent.callbackItems(function (item){
                self.parent.maxItems = item.hideSelects();
            })

            self.parent.resetKey(self.id, value);

            self.id = value;

            self.checkInputs();

            if (value < 1) {
                self.percentInputEditor.formatValue();
            }

            if(value > 1){
                self.parent.selectedItems[self.id] = true;
            }

            self.setSum();
            self.parent.checkDistribution();
            self.getAllData();

            self.parent.callbackItems(function (item) {
                item.hideSelects();
            })
        })
    }


    getAllData() {
        this.parent.getAllData();
    }


    setSum() {
        if(parseFloat(this.percentInputEditor.value) > 0){
            this.sumInputEditor.input.value = this.parent.sumInputEditor.value / 100 * this.percentInputEditor.value;
            setValueEditor(this.sumInputEditor);
        }

        this.getAllData();
    }

    setPercent() {
        if(this.sumInputEditor){
            if(parseFloat(this.sumInputEditor.value) > 0){
                this.percentInputEditor.input.value = 100 / this.parent.sumInputEditor.value * this.sumInputEditor.value;
                setValueEditor(this.percentInputEditor);
            }
        }

        this.getAllData();
    }

    checkCanAddAndDelete(){
        let length = 0;

        this.parent.callbackItems(function () {
            length++;
        })

        let disDelete = false;
        if(length <=1 || this.parent.mode === 'allP' || this.parent.mode === 'noP'){
            disDelete = true;
        }

        this.parent.callbackItems(function (item) {
            item.deleteInput.disabled = disDelete;
        })

        let addDis = false;

        if(length >= this.parent.maxItems || this.parent.mode === 'allP' || this.parent.mode === 'noP'){
            addDis = true;
        }

        this.parent.callbackItems(function (item) {
            item.addInput.disabled = addDis;
        })

        this.parent.checkDistribution();
    }

    addItem(returned) {
        let newItem = this.wrap.cloneNode(true);
        this.wrap.after(newItem);
        let id = Math.random();

        let self = this;

        this.parent.items[id] = new BeProduct({
            'wrap': newItem,
            'id': id,
            'itemType': 'beproduct-item',
            'parent': self.parent
        })
        
        this.parent.callbackItems(function (item){
            self.parent.maxItems = item.hideSelects();
        })

        this.parent.items[id].select.disabled = false;
        if(returned){
            return this.parent.items[id];
        }

        this.checkCanAddAndDelete();
    }

}
