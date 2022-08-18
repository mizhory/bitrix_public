class bizUnitForm {

    constructor(optionsBizUnitForm) {
        this.prefix = optionsBizUnitForm.prefix;
        this.allSum = optionsBizUnitForm.allSum;
        this.allPsnt = optionsBizUnitForm.allPsnt;
        this.ietmMaxPsnt = optionsBizUnitForm.ietmMaxPsnt;
        this.ietmMaxSum = optionsBizUnitForm.ietmMaxSum;
        this.countItems = optionsBizUnitForm.countItems;
        this.key = optionsBizUnitForm.key;
        this.budgetBizUnit = optionsBizUnitForm.budgetBizUnit;

        this.editor = optionsBizUnitForm.editor;

        this.mainDestrChecked = false;

        this.lastItemId = 0;

        this.lastProductItems = {};

        this.valuesSelect = {};

        this.valuesSelectMain = {};

        this.beData = {};

        this.mode = 'item';

        document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET]')[0].value = '10000';
    }


    init() {
        let self = this;
        if (document.querySelectorAll('.ui-entity-section-control')) {
            this.mainInit(true);
        } else {
            BX.addCustomEvent(
                'BX.UI.EntityEditorField:onLayout',
                function (event) {
                    if (event._id === 'UF_CRM_BIZUINIT_WIDGET') {
                        self.mainInit(true);
                    }
                }
            );
        }
    }


    mainInit() {
        this.hideFirstDelBtn('biz_unit_delbi_0');
        this.initStartData();
        this.initButtons();
        this.initChangeBItems();
        this.initEvenDistribution();
    }

    initEvenDistribution() {
        let self = this;
        document.getElementById('distribution_start').addEventListener('change', function () {
            let input = this;
            let set = 'set';

            if (!input.checked) {
                set = 'unset';
            }

            if (!self.setEvenDistributionItem(set)) {
                input.checked = false;
            }

            self.mainDestrChecked = input.checked;
        })
    }

    hideFirstDelBtn(id) {
        document.getElementById(id).style.setProperty('display', 'none', 'important');
    }

    initButtons() {
        let self = this;
        document.querySelectorAll('[data-cid=UF_CRM_BIZUINIT_WIDGET]')[0].addEventListener('click', function (e) {
            let eTargetClassList = e.target.classList;

            if (eTargetClassList.contains('ui-btn-disabled')) {
                return;
            }

            let mainItem = '';

            if (e.target.closest('.beproduct-item')) {
                self.mode = 'product-item';
                mainItem = e.target.closest('.be-item');
            } else {
                self.mode = 'item';
            }

            if (eTargetClassList.contains('save')) {
                self.save();
            } else if (eTargetClassList.contains('add')) {
                self.add(mainItem);
            } else if (eTargetClassList.contains('delete')) {
                if (e.target.closest('.beproduct-item')) {
                    self.delete(e.target.closest('.beproduct-item'));
                } else {
                    self.delete(e.target.closest('.be-item'));
                }
            }
        })
    }

    save() {

    }

    add(mainItem = '') {
        switch (this.mode) {
            case 'item':
                this.addItem();
                break;
            case 'product-item':
                this.addProductItem(mainItem);
                break;
        }
    }

    delete(element) {
        let idNow = element.dataset.id;

        switch (this.mode) {
            case 'item':
                delete this.beData[idNow];
                if (parseInt(idNow) === parseInt(this.lastItemId)) {
                    this.lastItemId--;
                    this.swtichButtonStatus('biz_unit_addbi_' + this.lastItemId, 'enable');
                }

                if (this.mainDestrChecked) {
                    //this.setEvenDistributionItem('set')
                }

                element.remove();

                break;
            case 'product-item':
                let parentId = element.closest('.be-item').dataset.id

                delete this.beData[element.closest('.be-item').dataset.num]['products'][idNow];

                this.lastProductItems[parentId]--;
                this.swtichButtonStatus('biz_productunit' + this.valuesSelect[parentId] + '_addbi_' + this.lastProductItems[parentId], 'enable');

                if (this.beData[element.closest('.be-item').dataset.num]['itemsDistribution']) {
                    //this.setEvenDistributionItemProducts('set', parentId, 'integer')
                }

                element.remove();

                break;
        }

        this.updateMainValue();

    }

    addItem() {
        this.lastItemId++;
        let hideItem = document.querySelectorAll(".be-item.be-item-hide")[0];
        let itemHtml = hideItem.innerHTML.replace(/{num}/gi, this.lastItemId);
        itemHtml = itemHtml.replace("{product}", "");

        let newItem = document.createElement('div');

        newItem.id = this.prefix + "_item_" + this.lastItemId;
        newItem.dataset.id = this.lastItemId;
        newItem.dataset.num = this.lastItemId;
        newItem.classList.add('be-item');
        document.getElementById(this.prefix + "-choose-be-form").insertBefore(newItem, hideItem);

        newItem.innerHTML = itemHtml;
        this.swtichButtonStatus('biz_unit_addbi_' + (this.lastItemId - 1));

        this.beData[this.lastItemId] = {
            'inputs': {
                'sum': document.getElementById('biz_unit_sum_' + this.lastItemId),
                'psnt': document.getElementById('biz_unit_psnt_' + this.lastItemId),
            },
            'products': {}
        }

        if (document.getElementById('distribution_start').checked) {
            this.beData[this.lastItemId]['inputs']['sum'].disabled = true;
            this.beData[this.lastItemId]['inputs']['psnt'].disabled = true;
            this.setEvenDistributionItem('set');
        }

        console.log(this.beData);
    }

    addProductItem(mainItem) {
        let mainItemId = mainItem.dataset.id;

        if (this.lastProductItems[mainItemId] !== undefined) {
            this.lastProductItems[mainItem.dataset.id]++;

            let hideItem = mainItem.querySelectorAll(".beproduct-item.be-item-hide")[0];

            let newItem = document.createElement('div');

            let itemHtml = hideItem.innerHTML.replace(/{num}/gi, this.lastProductItems[this.valuesSelectMain[mainItem.dataset.num]]);

            newItem.classList.add('beproduct-item');
            newItem.dataset.id = this.lastProductItems[mainItemId];
            newItem.dataset.num = this.lastProductItems[mainItemId];
            newItem.id = 'biz_productunit' + this.valuesSelect[mainItemId] + "_item_" + this.lastProductItems[mainItemId];

            mainItem.querySelectorAll('.be_product-form')[0].insertBefore(newItem, hideItem);

            newItem.innerHTML = itemHtml;

            newItem.querySelector('.distribution').remove();

            this.swtichButtonStatus('biz_productunit' + this.valuesSelect[mainItem.dataset.id] + '_addbi_' + (this.lastProductItems[this.valuesSelectMain[mainItem.dataset.num]] - 1));

            console.log('last' + this.lastProductItems[mainItemId])

            this.addInProductsData(mainItemId, this.lastProductItems[mainItemId])

            if (this.beData[mainItem.dataset.num]['itemsDistribution']) {
                this.setEvenDistributionItemProducts('set', newItem);
            }
            this.updateMainValue();

        }
    }

    setEvenDistributionItem(type = 'set') {
        let data = this.beData;

        switch (type) {
            case 'set':
                let allSumInput = document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET]')[0];

                let allSum = parseInt(allSumInput.value);

                if (allSum <= 0 || allSumInput.value === '') {
                    alert('Введите сумму!');
                    return false;
                }
                console.log(this.lastItemId);
                let percentByOne = parseInt(100 / (this.lastItemId + 1));

                for (let key in data) {
                    if (key !== 'test') {
                        data[key]['inputs']['psnt'].disabled = true;
                        data[key]['inputs']['sum'].disabled = true;


                        let valueByOne = percentByOne / 100 * allSum;

                        data[key]['inputs']['psnt'].value = percentByOne;
                        data[key]['inputs']['sum'].value = valueByOne;

                        data[key]['sum'] = valueByOne;
                        data[key]['psnt'] = percentByOne;

                        if (data[key]['itemsDistribution']) {
                            this.setEvenDistributionItemProducts('set', key, 'integer');
                        }

                        this.updateMainValue();
                    }
                }
                break;
            case 'unset':
                console.log(data);
                for (let key in data) {
                    if (key !== 'test') {
                        data[key]['inputs']['psnt'].disabled = false;
                        data[key]['inputs']['sum'].disabled = false;
                    }
                }
                break;
        }

        this.beData = data;
        return true;
    }

    setEvenDistributionItemProducts(type = 'set', event, typeE = 'element') {
        let data = this.beData;

        let mainId = event;

        if (typeE === 'element') {
            mainId = event.closest('.be-item').dataset.num;
            switch (type) {
                case 'set':
                    data[mainId]['itemsDistribution'] = true;

                    let percentOne = 100 / (this.lastProductItems[this.valuesSelectMain[mainId]] + 1);
                    let valueOne = percentOne / 100 * data[mainId]['inputs']['sum'].value;

                    for (let key in data[mainId]['products']) {
                        data[mainId]['products'][key]['inputs']['psnt'].disabled = true;
                        data[mainId]['products'][key]['inputs']['sum'].disabled = true;

                        data[mainId]['products'][key]['inputs']['psnt'].value = percentOne;
                        data[mainId]['products'][key]['inputs']['sum'].value = valueOne;

                        data[mainId]['products'][key]['psnt'] = percentOne;
                        data[mainId]['products'][key]['sum'] = valueOne;
                    }

                    this.updateMainValue();
                    break;
                case 'unset':
                    data[mainId]['itemsDistribution'] = false;

                    for (let key in data[mainId]['products']) {
                        data[mainId]['products'][key]['inputs']['psnt'].disabled = false;
                        data[mainId]['products'][key]['inputs']['sum'].disabled = false;
                    }

                    break;
            }

            return true;
        }
    }

    addInProductsData(itemId, productId = 0) {
        let data = this.beData;

        if (data[itemId]) {
            if (!data[itemId]['products']) {
                data[itemId]['products'] = {}
            }

            let parentItem = document.getElementById('biz_unit_item_' + itemId);


            let inputPercentSelector = '#biz_productunit' + this.valuesSelect[itemId] + '_psnt_0';
            let inputSumSelector = '#biz_productunit' + this.valuesSelect[itemId] + '_sum_0';

            if (productId > 0) {
                inputPercentSelector = '#biz_productunit' + this.valuesSelect[itemId] + '_psnt_' + productId;
                inputSumSelector = '#biz_productunit' + this.valuesSelect[itemId] + '_sum_' + productId;
            }

            data[itemId]['products'][productId] = {
                'inputs': {
                    'psnt': parentItem.querySelectorAll(inputPercentSelector)[0],
                    'sum': parentItem.querySelectorAll(inputSumSelector)[0]
                }
            }
        }
        this.beData = data;
        this.updateMainValue();
    }

    swtichButtonStatus(id, type = 'disable') {
        return;
        let button = document.getElementById(id);
        switch (type) {
            case 'disable':
                button.classList.add('ui-btn-disabled');
                button.classList.remove('ui-btn-success');
                break;
            case 'enable':
                button.classList.remove('ui-btn-disabled');
                button.classList.add('ui-btn-success');
                break;
        }
    }

    initChangeBItems() {
        let self = this;
        document.querySelectorAll('[data-cid=UF_CRM_BIZUINIT_WIDGET]')[0].addEventListener('change', function (e) {
            if (e.target.classList.contains('select-bizunit')) {
                self.getProducts(e.target);
                self.setItem(e.target);
            } else if (e.target.classList.contains('distribution')) {
                if (e.target.checked) {
                    self.setEvenDistributionItemProducts('set', e.target);
                } else {
                    self.setEvenDistributionItemProducts('unset', e.target);
                }
            } else if (e.target.classList.contains('select-productbizunit')) {
                self.setProductItem(e.target);
            }
        })
    }

    setItem(element) {
        let ItemWrapper = element.closest('.be-item');

        ItemWrapper.dataset.id = element.value;

        this.beData[ItemWrapper.dataset.num]['value'] = element.value;
        this.beData[ItemWrapper.dataset.num]['name'] = element.selectedOptions[0].text;

        this.valuesSelectMain[ItemWrapper.dataset.num] = ItemWrapper.dataset.id;

        this.updateMainValue();
    }

    setProductItem(element) {
        let productWrapper = element.closest('.beproduct-item');
        let ItemWrapper = productWrapper.closest('.be-item ');

        if(productWrapper.dataset.num === ''){
            productWrapper.dataset.num = 0;
        }

        if (!this.beData[ItemWrapper.dataset.num]['products'][productWrapper.dataset.num]) {
            this.beData[ItemWrapper.dataset.num]['products'][productWrapper.dataset.num] = {
                'inputs': {
                    'sum': document.getElementById('biz_productunit' +
                        this.beData[ItemWrapper.dataset.num]['value'] + '_sum_' + productWrapper.dataset.num),
                    'psnt': document.getElementById('biz_productunit' +
                        this.beData[ItemWrapper.dataset.num]['value'] + '_psnt_' + productWrapper.dataset.num)
                }
            };
        }

        this.beData[ItemWrapper.dataset.num]['products'][productWrapper.dataset.num]['value'] = element.value;
        this.beData[ItemWrapper.dataset.num]['products'][productWrapper.dataset.num]['name'] = element.selectedOptions[0].text;

        this.updateMainValue();
    }

    getProducts(selector) {
        let key = selector.closest('.be-item').dataset.id;

        if (!selector.value) {
            document.getElementById('biz_unit_product_item_' + key).innerHTML = '';
            if (this.lastProductItems[key]) {
                delete this.lastProductItems[key];
            }
            return;
        }

        let self = this;
        var ajaxLink = '/local/components/de/bizunit.products.widgetform.edit/templates/.default/ajax.php';
        var postData = {BIZ_UNIT_ID: selector.value, BIZ_UNIT_KEY: key, BIZ_UNIT_SUM: 0, 'ajax': 'Y'};
        BX.ajax({
            url: ajaxLink,
            method: 'POST',
            dataType: 'html',
            data: postData,
            async: true,
            onsuccess: function (res) {
                document.getElementById('biz_unit_product_item_' + key).innerHTML = res;
                if (document.getElementById('biz_unit_product_item_' + key).querySelectorAll('.de-inline-bl')[0]) {
                    self.hideFirstDelBtn('biz_productunit' + selector.value + '_delbi_0');
                    //self.addInProductsData(key);
                } else {

                }
            }
        });

        this.lastProductItems[selector.value] = 0;

        this.valuesSelect[key] = selector.value;

        //this.hildeSelects()
    }

    updateMainValue() {
        console.log(this);
        this.beData['test'] = 'y';
        document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET_DATA]')[0].value = JSON.stringify(this.beData);
    }

    initStartData() {
        let data = this.beData;

        let length = 0;

        let self = this;

        document.querySelectorAll('.be-item:not(.be-item-hide)').forEach(function (elem, index) {
            if (index > 0) {
                let num = elem.dataset.num;
                let id = elem.dataset.id;

                self.valuesSelectMain[num] = id;

                data[num] = {
                    'inputs': {
                        'sum': document.getElementById('biz_unit_sum_' + id),
                        'psnt': document.getElementById('biz_unit_psnt_' + id),
                    },
                    'value': id,
                    'products': {}
                };

                let pLength = 0;
                elem.querySelectorAll('.beproduct-item:not(.be-item-hide)').forEach(function (elem) {
                    let pId = elem.dataset.id;
                    data[num]['products'][pId] = {
                        'inputs': {
                            'sum': document.getElementById('biz_productunit' + id + '_sum_' + pId),
                            'psnt': document.getElementById('biz_productunit' + id + '_psnt_' + pId),
                        },
                    };
                    pLength++;
                })

                elem.querySelectorAll('.distribution').forEach(function (elem,index){
                    if(index>1){
                        elem.remove();
                    }
                })

                self.lastProductItems[id] = pLength - 1;
                if (self.lastProductItems[num] < 0) {
                    self.lastProductItems[num] = 0;
                }
                length++;
            }
        })


        let nodesItem = document.querySelectorAll('.be-item:not(.be-item-hide)')

        this.lastItemId = nodesItem.length - 2;

        document.querySelectorAll('.be-item:not(.be-item-hide)').forEach(function (elem, index) {
            if (index > 0) {
                let num = elem.dataset.num;

                if (parseInt(num) !== parseInt(self.lastItemId)) {
                    self.swtichButtonStatus('biz_unit_addbi_' + num);
                }

            }
        });

        this.beData = data;

        console.log(this.beData);
    }

}









































