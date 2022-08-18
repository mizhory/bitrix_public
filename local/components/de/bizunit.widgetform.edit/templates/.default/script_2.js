class bizUnitFormInits {
    prefix;
    key;
    beData;
    lastItemId;
    mode;
    allSum;
    allPercents;

    constructor(optionsBizUnitForm) {
        this.prefix = optionsBizUnitForm.prefix;
        this.key = optionsBizUnitForm.key;

        this.initEvents = false;

        this.beData = {};

        this.lastItemId = 1;

        this.mode = 'item';

        this.allPercents = 100;

        this.allSum = document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET]')[0].value;
    }

    callbackMainItemMutation(mutationsList) {

    }

    initStartData() {
        let self = this;
        this.beData = {};

        let jsonInput = document.querySelector('.forJs');

        if(!jsonInput){
            document.querySelectorAll('.be-item:not(.be-item-hide)').forEach(function (elem) {
                if (elem.id !== 'biz_unit_sum') {
                    self.createNewItem(elem);

                    /*
                    elem.querySelectorAll('.beproduct-item').forEach(function (elemP){
                        self.setProductItem(elemP.querySelector('.select-productbizunit'),true);
                    })
                    */
                }
            })
        }else{
            let data = JSON.parse(jsonInput.value);

            console.log(data);

            document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET]')[0].value = data.allSum;

            this.allSum = data.allSum;

            if(data['mainEventDustrbution']){
                document.getElementById('distribution_start').checked = true;
            }

            document.querySelector('[name=OPPORTUNITY]').val = 'Y';

            for(let mainKey in data){

                if(typeof data[mainKey] === "object"){
                    data[mainKey]['inputs']['budget'] = document.getElementById('biz_unit_budget_'+mainKey);
                    data[mainKey]['inputs']['sum'] = document.getElementById('biz_unit_sum_'+mainKey);
                    data[mainKey]['inputs']['psnt'] = document.getElementById('biz_unit_psnt_'+mainKey);

                    if(data['mainEventDustrbution']){
                        data[mainKey]['inputs']['budget'].disabled = true;
                        data[mainKey]['inputs']['sum'].disabled = true;
                        data[mainKey]['inputs']['psnt'].disabled = true;
                    }else{
                        data[mainKey]['inputs']['budget'].disabled = false;
                        data[mainKey]['inputs']['sum'].disabled = false;
                        data[mainKey]['inputs']['psnt'].disabled = false;
                    }

                    data[mainKey]['inputs']['budget'].value = data[mainKey]['values']['budget'];
                    data[mainKey]['inputs']['sum'].value = data[mainKey]['values']['sum'];
                    data[mainKey]['inputs']['psnt'].value = data[mainKey]['values']['psnt'];


                    const config = {
                        attributes: true,
                        childList: true,
                        subtree: true,
                        characterData: true,
                        characterDataOldValue: true,
                        attributeOldValue: true
                    };

                    const observer = new MutationObserver(mutationRecords => {
                        this.callbackMainItemMutation(mutationRecords, 'item')
                    });

                    observer.observe(data[mainKey]['inputs']['budget'].closest('.be-item'), config);
                    let checks = 0;
                    for (let pKey in data[mainKey]['products']){

                        if(typeof data[mainKey]['products'][pKey] === "object"){
                            data[mainKey]['products'][pKey]['inputs']['budget'] = document.getElementById('biz_productunit'+data[mainKey]['value']+'_budget_'+pKey);
                            data[mainKey]['products'][pKey]['inputs']['sum'] = document.getElementById('biz_productunit'+data[mainKey]['value']+'_sum_'+pKey);
                            data[mainKey]['products'][pKey]['inputs']['psnt'] = document.getElementById('biz_productunit'+data[mainKey]['value']+'_psnt_'+pKey);

                            let checkedInput = data[mainKey]['products'][pKey]['inputs']['budget'].closest('.beproduct-item').querySelector('input.distribution');

                            const config = {
                                attributes: true,
                                childList: true,
                                subtree: true,
                                characterData: true,
                                characterDataOldValue: true,
                                attributeOldValue: true
                            };

                            const observer = new MutationObserver(mutationRecords => {
                                this.callbackMainItemMutation(mutationRecords, 'item')
                            });

                            observer.observe(data[mainKey]['products'][pKey]['inputs']['budget'].closest('.beproduct-item'), config);

                            if(data[mainKey]['products']['distr'] || data[mainKey]['products'][pKey].value<=0){


                                data[mainKey]['products'][pKey]['inputs']['budget'].disabled = true;
                                data[mainKey]['products'][pKey]['inputs']['sum'].disabled = true;
                                data[mainKey]['products'][pKey]['inputs']['psnt'].disabled = true;
                            }else{
                                data[mainKey]['products'][pKey]['inputs']['budget'].disabled = false;
                                data[mainKey]['products'][pKey]['inputs']['sum'].disabled = false;
                                data[mainKey]['products'][pKey]['inputs']['psnt'].disabled = false;
                            }

                            console.log('.beproduct-item:not(#biz_productunit'+data[mainKey]['value']+'_item_0)')

                            if(data[mainKey]['products']['allP']){
                                //document.querySelector('.beproduct-item:not(#biz_productunit'+data[mainKey]['value']+'_item_0) select').disabled = true;
                            }

                            if(checks > 0 && checkedInput){
                                checkedInput.closest('.field-wrap').remove();
                            }

                            checks++;
                        }
                    }
                }
            }

            data['update'] = 'y';

            this.beData = data;

            console.log(this.beData);

            this.hideSelectorsItems();
        }

        this.updateMainValue();



        if (this.beData['mainEventDustrbution']) {
            //this.setEventDistributionMain('set');
        }
    }
    updateMainValue(){

    }

    initButtons() {
        let self = this;

        this.counterEvent = 0;

        var listener = function (e){

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
                //self.save();
            } else if (eTargetClassList.contains('add')) {
                self.add(mainItem);
            } else if (eTargetClassList.contains('delete')) {
                if (e.target.closest('.beproduct-item')) {
                    self.delete(e.target.closest('.beproduct-item'));
                } else {
                    self.delete(e.target.closest('.be-item'));
                }
            }else{

            }
        }

        document.querySelectorAll('[data-cid=UF_CRM_BIZUINIT_WIDGET]')[0].removeEventListener('click',listener);

        document.querySelectorAll('[data-cid=UF_CRM_BIZUINIT_WIDGET]')[0].addEventListener('click', listener );
    }

    delete(item){

    }


    initInputs() {
        let self = this;
        document.querySelectorAll('[data-cid=UF_CRM_BIZUINIT_WIDGET]')[0].addEventListener('input', function (e) {
            let classList = e.target.classList;

            let mode = 'item';

            if(e.target.closest('.beproduct-item ')){
                mode = 'product';
            }

            if (classList.contains('sumInput')) {
                if(mode === 'item'){
                    self.countValuesMain('sum', e.target);
                }else{
                    self.countValuesProduct('sum', e.target);
                }

            } else if (classList.contains('percentInput')) {
                if(mode === 'item'){
                    self.countValuesMain('percent', e.target);
                }else{
                    self.countValuesProduct('percent', e.target);
                }
            } else if (classList.contains('mainSum')) {
                self.allSum = e.target.value;
                self.onModifyMainSum();
            }
        })
    }

    countValuesProduct(type,elem){

    }

    onModifyMainSum() {

    }

    initChangeBItems() {
        let self = this;
        document.querySelectorAll('[data-cid=UF_CRM_BIZUINIT_WIDGET]')[0].addEventListener('change', function (e) {
            if (e.target.classList.contains('select-bizunit')) {
                self.getProducts(e.target);
                //self.setItem(e.target);
            } else if (e.target.classList.contains('distribution')) {
                if (e.target.checked) {
                    self.setEvenDistributionItemProducts('set', e.target);
                } else {
                    self.setEvenDistributionItemProducts('unset', e.target);
                }
            } else if (e.target.classList.contains('select-productbizunit')) {
                self.setProductItem(e.target);
            } else if (e.target.classList.contains('distribution_start')) {
                self.beData['mainEventDustrbution'] = e.target.checked;
                if (e.target.checked) {
                    self.setEventDistributionMain('set', e.target);
                } else {
                    self.setEventDistributionMain('unset', e.target);
                }
            } else if (e.target.classList.contains('priority')) {
                self.priority = e.target.value;
            } else if (e.target.classList.contains('switchProduct')){

            }
        })
    }

    switchProducts(){

    }

    getProducts(item) {

    }

    setProductItem(select , newItem = false) {

    }

    setEventDistributionMain(type) {

    }

    setEvenDistributionItemProducts(type,input){

    }

    updateProduct(item, type) {

    }

    hideSelectorsItems(){

    }

    add(mainItem = '') {

    }

    createNewItem(elem) {

    }

    countValuesMain(type = '', input) {

    }
}


class bizUnitForm extends bizUnitFormInits {
    constructor(props) {
        super(props);
        let self = this;
        if (location.href.indexOf('details/0') > 0) {
            this.mainInit();
        } else {
            BX.addCustomEvent(
                'BX.UI.EntityEditorField:onLayout',
                function (event) {
                    if (event._id === 'UF_CRM_BIZUINIT_WIDGET' && event._mode === 1) {
                        self.initStartData();
                        self.mainInit();
                    }
                }
            );
        }
        document.querySelectorAll('.beproduct-item:not(.be-item-hide)').forEach(function (elem,index){
            elem.querySelectorAll('.distribution.field-wrap').forEach(function (itemD){
                if(elem.dataset.num > 0){
                    itemD.remove();
                }
            })
        })
        this.initStartData();
    }

    switchProducts(){

    }


    mainInit() {
        this.initChangeBItems();
        this.initButtons();
        this.initInputs();
        this.lastItemId = document.querySelectorAll('.be-item:not(#biz_unit_sum):not(.be-item-hide)').length;

        let self = this;

        document.querySelectorAll('.be-item:not(#biz_unit_sum):not(.be-item-hide)').forEach(function (elem){
            if(elem.dataset.num < self.lastItemId){
                elem.querySelectorAll('.add').forEach(function (button){
                    button.disabled = true;
                })
                elem.querySelectorAll('.delete').forEach(function (button){
                    button.closest('.de-inline-bl').style.display = 'none';
                })
            }
        })
    }

    delete(item) {
        if(this.mode === 'item'){
            delete this.beData[item.dataset.num];
            this.lastItemId--;

            document.querySelector('.be-item[data-num="'+(this.lastItemId)+'"] .add').disabled = false;
            if(this.lastItemId>1){
                document.querySelector('.be-item[data-num="'+(this.lastItemId)+'"] .delete').closest('.de-inline-bl').style.display = '';
            }


            item.remove();
            if(this.beData['mainEventDustrbution']){
                this.setEventDistributionMain('set');
            }
            this.updateMainValue();
        }else{
            let pNum = item.dataset.num;
            let num = item.closest('.be-item').dataset.num;

            delete this.beData[num]['products'][pNum];

            this.beData[num]['products']['lastProductItem']--;

            item.remove();
            this.updateMainValue();
        }
    }


    setProductItem(select , newItem = false) {
        let productWrap = select.closest('.beproduct-item');

        let self = this;

        let mainNum = select.closest('.be-item').dataset.num;

        let mainData = this.beData[mainNum];
        /*
        if(select.value === '99999999'){
            mainData['products']['allP'] = true;
            for (let key in mainData['products']){
                if(typeof mainData['products'][key] === "object" && key !== productWrap.dataset.num){
                    self.mode = 'product';
                    self.delete(mainData['products'][key]['inputs']['sum'].closest('.beproduct-item'));
                }
            }
            setTimeout(function (){
                select.querySelectorAll('option.biz_productunit'+mainData['value']).forEach(function (option){
                    if(option.value !== '99999991' && option.value !=='99999991' && option.value !=='0'){
                        self.add(mainData['inputs']['sum'].closest('.be-item'),function (item){
                            item.querySelector('select').value = option.value;
                            item.querySelector('select').disabled = true;
                        });
                    }

                    console.log(option);
                })
            },500)

        }else if (select.value ==='99999991'){
            mainData['products']['withoutP'] = true;
            select.closest('.beproduct-item').querySelector('.add').disabled = true;
            self.mode = 'product'
            for (let key in mainData['products']){
                if(typeof mainData['products'][key] === "object" && key !== productWrap.dataset.num){
                    self.mode = 'product';
                    self.delete(mainData['products'][key]['inputs']['sum'].closest('.beproduct-item'));
                }
            }
        }else{

        }
        */

        if(newItem){
            let data = this.getInputsByItem(productWrap, 'product');

            mainData['products'][productWrap.dataset.num] = data;

            if(mainData['lastProductItem'] < 1){
                mainData['lastProductItem'] = 0;
            }

        }

        productWrap.dataset.value = select.value;

        /*
        setTimeout(function (){
            self.hideSelectorsItemsProducts(mainNum,productWrap.dataset.num);
        },500)
        */



        this.updateMainValue();
    }

    setEvenDistributionItemProducts(type,input,mainId = 0){
        if(mainId === 0){
            mainId = input.closest('.be-item').dataset.num;
        }

        let productData = this.beData[mainId]['products'];

        switch (type){
            case 'set':
                this.beData[mainId]['products']['distr'] = true;
                
                let lastItemId = 0;
                for (let key in productData) {
                    if (productData[key].value > 0) {
                        lastItemId++;
                    }
                }
                for (let key in productData) {
                    if (productData[key].value > 0) {
                        let value = 100 / lastItemId;
                        let sumFromPercentage = this.beData[mainId]['values']['sum'] * value / 100;
                        productData[key]['inputs']['psnt'].disabled = true;
                        productData[key]['inputs']['sum'].disabled = true;

                        productData[key]['values']['psnt'] = value;
                        productData[key]['inputs']['psnt'].value = value;

                        productData[key]['values']['sum'] = sumFromPercentage;
                        productData[key]['inputs']['sum'].value = sumFromPercentage;

                    }
                }
                break;
            case 'unset':
                this.beData[mainId]['products']['distr'] = false;

                for (let key in productData) {
                    if (productData[key].value > 0) {
                        productData[key]['inputs']['psnt'].disabled = false;
                        productData[key]['inputs']['sum'].disabled = false;
                    }
                }

                break;
        }
        this.updateMainValue();
    }

    countValuesProduct(type = '',input){
        let mainNum = input.closest('.be-item').dataset.num;
        let productNum = input.closest('.beproduct-item').dataset.num;

        let data = this.beData[mainNum]['products'][productNum];

        switch (type){
            case 'sum':
                data['values']['sum'] = 0;
                this.updateSumMain('productItem',mainNum);

                let freeSum = this.beData[mainNum]['freeSum'];

                if (parseInt(data['inputs']['sum'].value) > parseInt(freeSum)) {
                    data['inputs']['sum'].value = freeSum;
                }

                data['values']['sum'] = data['inputs']['sum'].value;

                let percentFromSum = 100 / this.beData[mainNum]['values']['sum'] * data['values']['sum'];

                data['values']['psnt'] = percentFromSum;
                data['inputs']['psnt'].value = percentFromSum;

                break;
            case 'percent':
                data['values']['psnt'] = 0;
                this.updatePercentsMain('productItem',mainNum);

                let freePercent = this.beData[mainNum]['freePercent'];

                if (data['inputs']['psnt'].value > freePercent) {
                    data['inputs']['psnt'].value = freePercent;
                }

                let sumFromPercentage = this.beData[mainNum]['values']['sum'] * data['inputs']['psnt'].value / 100;
                data['inputs']['sum'].value = sumFromPercentage;

                data['values']['sum'] = data['inputs']['sum'].value;
                data['values']['psnt'] = data['inputs']['psnt'].value;
                break;
        }

        this.updateMainValue();
    }

    countValuesMain(type = '', input) {
        let num = input.closest('.be-item').dataset.num;
        let data = this.beData[num];
        switch (type) {
            case 'sum':
                data['values']['sum'] = 0;
                this.updateSumMain();
                let freeSum = document.querySelectorAll('[name=UF_CRM_BIZUINIT_FREE]')[0].value;

                if (parseInt(data['inputs']['sum'].value) > parseInt(freeSum)) {
                    data['inputs']['sum'].value = freeSum;
                }

                data['values']['sum'] = data['inputs']['sum'].value;

                let percentFromSum = 100 / this.allSum * data['values']['sum'];

                data['values']['psnt'] = percentFromSum;
                data['inputs']['psnt'].value = percentFromSum;

                break;
            case 'percent':
                data['values']['psnt'] = 0;
                this.updatePercentsMain();

                if (data['inputs']['psnt'].value > this.allPercents) {
                    data['inputs']['psnt'].value = this.allPercents;
                }

                let sumFromPercentage = this.allSum * data['inputs']['psnt'].value / 100;
                data['inputs']['sum'].value = sumFromPercentage;

                data['values']['sum'] = data['inputs']['sum'].value;
                data['values']['psnt'] = data['inputs']['psnt'].value;
                break;
        }
        this.updatePercentsMain();
        this.updateSumMain();
        this.updateMainValue();

        if(data['products']['distr']){
           this.setEvenDistributionItemProducts('set',0,num);
        }

        if(parseInt(data['values']['sum']) > parseInt(data['values']['budget'])){
            document.getElementById('biz_unit_warning_'+num).style.display = 'block';
            document.getElementById('biz_unit_warningsum_'+num).innerHTML = data['values']['budget'];
            document.querySelector('.ui-entity-section-control .ui-btn-success').disabled = true;
        }else{
            document.getElementById('biz_unit_warning_'+num).style.display = 'none';
            document.querySelector('.ui-entity-section-control .ui-btn-success').disabled = false
        }
    }

    onModifyMainSum() {
        this.updateSumMain();
        for (let key in this.beData) {
            if (this.beData[key].value > 0) {
                if(this.beData['mainEventDustrbution']){
                    this.setEventDistributionMain('set');
                }else{
                    let sumFromPercentage = this.allSum * this.beData[key]['inputs']['psnt'].value / 100;

                    this.beData[key]['values']['sum'] = sumFromPercentage;
                    this.beData[key]['inputs']['sum'].value = sumFromPercentage;
                }
                /*
                if (this.priority === 'percent') {
                    let sum = this.beData[key]['values']['sum'];

                    let percentageFromSum = 100 / this.allSum * this.beData[key]['values']['sum'];

                    this.beData[key]['values']['psnt'] = percentageFromSum;
                    this.beData[key]['inputs']['psnt'].value = percentageFromSum;
                } else {
                    let sumFromPercentage = this.allSum * this.beData[key]['inputs']['psnt'].value / 100;

                    this.beData[key]['values']['sum'] = sumFromPercentage;
                    this.beData[key]['inputs']['sum'].value = sumFromPercentage;
                }
                */
            }
        }
        this.updateMainValue();
    }

    setEventDistributionMain(type) {
        switch (type) {
            case 'set':
                let lastItemId = 0;
                for (let key in this.beData) {
                    if (this.beData[key].value > 0) {
                        lastItemId++;
                    }
                }
                for (let key in this.beData) {
                    if (this.beData[key].value > 0) {
                        let value = 100 / lastItemId;
                        let sumFromPercentage = this.allSum * value / 100;
                        this.beData[key]['inputs']['psnt'].disabled = true;
                        this.beData[key]['inputs']['sum'].disabled = true;

                        this.beData[key]['values']['psnt'] = value;
                        this.beData[key]['inputs']['psnt'].value = value;

                        this.beData[key]['values']['sum'] = sumFromPercentage;
                        this.beData[key]['inputs']['sum'].value = sumFromPercentage;

                        console.log(this.beData[key]['values']['budget']);

                        if(parseInt(this.beData[key]['values']['sum']) > parseInt(this.beData[key]['values']['budget'])){
                            document.getElementById('biz_unit_warning_'+key).style.display = 'block';
                            document.getElementById('biz_unit_warningsum_'+key).innerHTML = this.beData[key]['values']['budget'];
                            document.querySelector('.ui-entity-section-control .ui-btn-success').disabled = true;
                        }else{
                            document.getElementById('biz_unit_warning_'+key).style.display = 'none';
                            document.querySelector('.ui-entity-section-control .ui-btn-success').disabled = false;
                        }

                        if(this.beData[key]['products']['distr']){
                            this.setEvenDistributionItemProducts('set',0,key);
                            document.querySelector('.ui-entity-section-control .ui-btn-success').disabled = false;
                        }
                    }
                }
                break;
            case 'unset':
                for (let key in this.beData) {
                    if (typeof this.beData[key] === "object" && this.beData[key].value > 0) {
                        this.beData[key]['inputs']['psnt'].disabled = false;
                        this.beData[key]['inputs']['sum'].disabled = false;
                    }
                }
                break;
        }
        this.updateSumMain();
        this.updatePercentsMain();
        this.updateMainValue();
    }

    updateSumMain(type = 'item',mainId = 0) {
        let sum;
        switch (type){
            case 'item':
                sum = document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET]')[0].value;
                for (let key in this.beData) {
                    if (typeof this.beData[key] === "object") {
                        sum -= this.beData[key]['values']['sum'];
                    }
                }
                document.querySelectorAll('[name=UF_CRM_BIZUINIT_FREE]')[0].value = sum;
                break;
            case 'productItem':
                sum = this.beData[mainId]['values']['sum'];
                for (let key in this.beData[mainId]['products']) {
                    if (typeof this.beData[mainId]['products'][key] === "object") {
                        sum -= this.beData[mainId]['products'][key]['values']['sum'];
                    }
                }
                this.beData[mainId]['freeSum'] = sum;
                break;
        }

    }

    updatePercentsMain(type = 'item',mainId = 0) {
        let percent = 100;

        switch (type){
            case 'item':
                for (let key in this.beData) {
                    if (typeof this.beData[key] === "object") {
                        percent -= this.beData[key]['values']['psnt'];
                    }
                }
                this.allPercents = percent;
                break;
            case 'productItem':
                for (let key in this.beData[mainId]['products']) {
                    if (typeof this.beData[mainId]['products'][key] === "object") {
                        percent -= this.beData[mainId]['products'][key]['values']['psnt'];
                    }
                }
                this.beData[mainId]['freePercent'] = percent;
                break;
        }


    }

    callbackMainItemMutation(mutationsList, type = 'item') {
        for (let mutation of mutationsList) {
            if (mutation.type === 'childList') {
            } else if (mutation.type === 'attributes') {
                switch (mutation.attributeName) {
                    case 'data-value':
                        if (mutation.target.classList.contains('be-item')) {
                            this.updateProduct(mutation.target, 'value')
                        } else {
                            this.updateProductItem(mutation.target)
                        }
                        break;
                    case 'data-budget':
                        this.updateProduct(mutation.target, 'budget')
                        break;
                }
            }
        }
    }

    add(mainItem,callback) {
        switch (this.mode) {
            case 'item':
                this.addItem();
                break;
            case 'product-item':
                this.addProductItem(mainItem,callback);
                break;
        }
    }

    addProductItem(mainItem,callback){
        let mainItemNum = mainItem.dataset.num;

        this.beData[mainItemNum]['lastProductItem']++;

        let hideItem = mainItem.querySelectorAll(".beproduct-item.be-item-hide")[0];

        let newItem = document.createElement('div');

        let itemHtml = hideItem.innerHTML.replace(/{num}/gi, this.beData[mainItemNum]['lastProductItem']);

        newItem.classList.add('beproduct-item');

        newItem.dataset.num = this.beData[mainItemNum]['lastProductItem'];

        let prefix = 'biz_productunit'+this.beData[mainItemNum]['value'];

        newItem.id = prefix+'_item_'+this.beData[mainItemNum]['lastProductItem'];
        mainItem.querySelectorAll('.be_product-form')[0].insertBefore(newItem, hideItem);

        newItem.innerHTML = itemHtml;

        newItem.querySelector('.distribution').remove();

        if(typeof callback === "function"){
            callback(newItem);
        }

        this.setProductItem(newItem.querySelector('#'+prefix+'_selbi_'+this.beData[mainItemNum]['lastProductItem']),true)
    }

    updateProductItem(item) {
        let productWrap = item.closest('.beproduct-item');

        let mainProductWrap = productWrap.closest('.be-item');

        let num = mainProductWrap.dataset.num;

        let product = this.beData[num]['products'][productWrap.dataset.num];

        if (parseInt(item.dataset.value) < 1) {
            product['inputs']['sum'].disabled = true;
            product['inputs']['psnt'].disabled = true;

            product['inputs']['sum'].value = 0;
            product['inputs']['psnt'].value = 0;

            product['values']['sum'] = 0;
            product['values']['psnt'] = 0;
        }else{
            product['inputs']['sum'].disabled = false;
            product['inputs']['psnt'].disabled = false;
        }

        product['value'] = productWrap.dataset.value;
        this.updateMainValue();
    }

    addItem() {
        this.lastItemId++;

        let hideItem = document.querySelectorAll(".be-item.be-item-hide")[0];
        let itemHtml = hideItem.innerHTML.replace(/{num}/gi, this.lastItemId);

        itemHtml = itemHtml.replace(/{product}/gi, "");
        itemHtml = itemHtml.replace(/{budget}/gi, "");
        itemHtml = itemHtml.replace(/{number}/gi, this.lastItemId);

        let newItem = document.createElement('div');

        newItem.classList.add('be-item');

        newItem.dataset.budget = 0;
        newItem.dataset.num = this.lastItemId;
        newItem.dataset.value = 0;

        newItem.id = this.prefix + "_item_" + this.lastItemId;

        document.getElementById(this.prefix + "-choose-be-form").insertBefore(newItem, hideItem);

        newItem.innerHTML = itemHtml;

        this.createNewItem(newItem);

        if (this.beData['mainEventDustrbution']) {
            this.setEventDistributionMain('set');
        }

        document.querySelector('.be-item[data-num="'+(this.lastItemId-1)+'"] .add').disabled = true;
        document.querySelector('.be-item[data-num="'+(this.lastItemId-1)+'"] .delete').closest('.de-inline-bl').style.display = 'none';

        this.updateMainValue();
    }

    updateMainValue() {
        this.beData['allSum'] = this.allSum;

        if(this.beData['update']){
            var date = new Date();
            this.beData['update'] = date.getTime();
        }

        console.log(this.beData);

        document.querySelectorAll('[name=UF_CRM_BIZUINIT_WIDGET_DATA]')[0].value = JSON.stringify(this.beData);
    }

    createNewItem(elem) {
        let data = this.getInputsByItem(elem);

        data['products'] = {};
        data['lastProductItem'] = -1;
        this.beData[elem.dataset.num] = data;
        if (this.beData['mainEventDustrbution']) {
            this.setEventDistributionMain('set');
        }
        this.updateMainValue();
    }

    getInputsByItem(elem, type = 'item') {
        let inputSum = elem.querySelector('.sumInput');
        let inputPercent = elem.querySelector('.percentInput');
        let inputBudget = elem.querySelector('.budgetInput');

        if (elem.dataset.value < 1) {
            inputSum.disabled = true;
            inputPercent.disabled = true;
            inputBudget.disabled = true;
        } else {
            inputSum.disabled = false;
            inputPercent.disabled = false;
            inputBudget.disabled = false;
        }

        const config = {
            attributes: true,
            childList: true,
            subtree: true,
            characterData: true,
            characterDataOldValue: true,
            attributeOldValue: true
        };

        const observer = new MutationObserver(mutationRecords => {
            this.callbackMainItemMutation(mutationRecords, type)
        });

        observer.observe(elem, config);

        if (elem.dataset.num < 1) {
            if (elem.querySelector('.delete').length) {
                elem.querySelector('.delete').remove();
            }
        }

        if (parseInt(elem.dataset.value) === 0) {
            inputSum.value = 0;
            inputBudget.value = 0;
        }

        return {
            'value': elem.dataset.value,

            'inputs': {
                'sum': inputSum,
                'psnt': inputPercent,
                'budget': inputBudget
            },

            'values': {
                'sum': inputSum.value,
                'psnt': inputPercent.value,
                'budget': inputBudget.value
            }
        }
    }


    updateProduct(item, type) {
        let data = this.beData[item.dataset.num];
        let value = item.dataset.value;
        switch (type) {
            case 'value':
                data['value'] = item.dataset.value;
                break;
            case 'budget':
                if (value > 0) {
                    data['inputs']['sum'].disabled = false;
                    data['inputs']['psnt'].disabled = false;
                    data['inputs']['budget'].value = item.dataset.budget;

                    data['values']['budget'] = item.dataset.budget;

                } else {
                    data['inputs']['sum'].disabled = true;
                    data['inputs']['psnt'].disabled = true;
                    data['inputs']['budget'].value = 0;

                    data['values']['budget'] = 0;
                }

                data['inputs']['psnt'].value = 0;
                data['inputs']['sum'].value = 0;

                data['values']['psnt'] = 0;
                data['values']['sum'] = 0;

                break;
        }

        if (this.beData['mainEventDustrbution']) {
            this.setEventDistributionMain('set');
        }
        this.updateMainValue();
        this.hideSelectorsItems();
    }

    checkBudget() {

    }

    getAllBudgets(biItem = 0) {
        let budget = 0;
        if (biItem > 0) {

        } else {
            for (let key in this.beData) {
                budget += this.beData[key]['values']['budget'];
            }
        }
        return budget;
    }

    getProducts(selector) {
        let optionSelected = selector.querySelectorAll('option:checked')[0];
        let mainItem = selector.closest('.be-item');
        this.beData[mainItem.dataset.num].name = optionSelected.text;

        mainItem.dataset.value = selector.value;

        mainItem.dataset.budget = optionSelected.dataset.budget;

        let key = mainItem.dataset.num;

        let self = this;

        var ajaxLink = '/local/components/de/bizunit.products.widgetform.edit/templates/.default/ajax.php';
        var postData = {BIZ_UNIT_ID: selector.value, BIZ_UNIT_KEY: mainItem.dataset.key, BIZ_UNIT_SUM: 0, 'ajax': 'Y'};
        BX.ajax({
            url: ajaxLink,
            method: 'POST',
            dataType: 'html',
            data: postData,
            async: true,
            onsuccess: function (res) {
                document.getElementById('biz_unit_product_item_' + key).innerHTML = res;
                if (document.getElementById('biz_unit_product_item_' + key).querySelectorAll('.de-inline-bl')[0]) {
                    let select = document.getElementById('biz_productunit'+selector.value+'_selbi_0');
                    self.setProductItem(select,true);
                } else {

                }
            }
        });

    }

    hideSelectorsItems(mainNum,productNum) {
        let haveSelects = {};
        for (let key in this.beData) {
            haveSelects[this.beData[key]['value']] = true;
        }

        document.querySelectorAll('.select-bizunit option').forEach(function (elem) {
            if (typeof haveSelects[elem.value] !== 'undefined') {
                elem.style.display = 'none';
            } else {
                elem.style.display = '';
            }
        })
    }

    hideSelectorsItemsProducts(mainItem,productItem) {
        let haveSelects = {};

        let data = this.beData[mainItem]['products'];


        for (let key in data) {
            haveSelects[data[key]['value']] = true;
        }


        data[productItem]['inputs']['sum'].closest('.beproduct-item').querySelectorAll('.select-productbizunit option').forEach(function (elem) {
            if (typeof haveSelects[elem.value] !== 'undefined') {
                elem.style.display = 'none';
            } else {
                elem.style.display = '';
            }
        })
    }
}






