/**
 * Класс для продуктов БЕ. Наследуется от класса БЕ, для передачи некоторых методов
 */
class BeProduct extends Be{
    /**
     * Переопределяем метод для инициализации
     */
    startInit(){
        this.baseBeAndProductInit();
        this.beProductAdditionalInit();
        if(this.id > 1){
            this.initEditors();
        }
    }

    /**
     * Добавление нового продукта
     */
    addItem() {
        let newItem;

        let oldItem;

        /**
         * @todo создает новый элемент продукта, путем клонирования
         */
        this.parent.callbackItems(function (item){
            try {
                newItem = item.wrap.cloneNode(true);
                oldItem = item;
                throw 'w';
            }catch (error){

            }
        })
        const id = this.generateItemId();
        newItem.querySelector('.select-item').value = '0';
        newItem.setAttribute('data-id','0');

        this.wrap.after(newItem);


        let self = this;

        /**
         * @type {BeProduct}
         */
        this.parent.items[id] = new BeProduct({
            'wrap': newItem,
            'id': id,
            'itemType': 'be-item',
            'parent': self.parent,
            mainWrap: this.mainWrap
        })

        this.parent.items[id].percentInput.value = 0;
        this.parent.items[id].sumInput.value = 0;

        this.parent.hideSelects();
        this.parent.checkCanAddAndDelete();
        this.parent.checkDistribution();
    }

    /**
     * Проверяет и скрывает кнопки добавления или удаления (если выбрано "все продукты")
     */
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
     * Дополнительная инициализация продуктов
     */
    beProductAdditionalInit(){
        this.checkCanAddAndDelete();
        this.initSelectors();
    }

    /**
     * Инициализация селекторов. Регистрирует обработчик события для изменения селектора с выбором продукта
     */
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
