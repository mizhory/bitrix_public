/*function ____bizUnitForm(optionsbizUnitForm){

    this.prefix = optionsbizUnitForm.prefix;
    this.allSum = optionsbizUnitForm.allSum;
    this.allPsnt = optionsbizUnitForm.allPsnt;
    this.ietmMaxPsnt = optionsbizUnitForm.ietmMaxPsnt;
    this.ietmMaxSum = optionsbizUnitForm.ietmMaxSum;
    this.countItems = optionsbizUnitForm.countItems;
    this.key = optionsbizUnitForm.key;    
    this.budgetBizUnit = optionsbizUnitForm.budgetBizUnit;
    var self = this;   
    
    console.log(this.budgetBizUnit);
    self.hideSelect = function(){
        
        //var options = document.querySelectorAll('.' +self.prefix + '-option-' + self.countItems);
        
        var options = document.querySelectorAll('option');
        var selects = document.querySelectorAll('.select-bizunit');
        
        var arHideOptionsValue = [];
        for(var k = 0; k < selects.length; k++){
            if(selects[k].value !== ""){
                arHideOptionsValue.push(selects[k].value);
            }
        }
        
        
        for(var k = 0; k < options.length; k++){
            if(options[k].value !== "" && options[k].parentNode.value != options[k].value){
                if(arHideOptionsValue.includes(options[k].value)){
                    options[k].style.display = 'none';
                } else {
                    options[k].style.display = 'block';
                }                
            }
        }       
        
        var blockId = 'biz_unit_product_item_' + self.key;        
        
        if(blockId){
                
            var options = document.querySelectorAll("#" + blockId + ' .' +self.prefix + '-options');
            var selects = document.querySelectorAll("#" + blockId + ' .select-productbizunit');

            var arHideOptionsValue = [];
            for(var k = 0; k < selects.length; k++){
                if(selects[k].value !== ""){
                    arHideOptionsValue.push(selects[k].value);
                }
            }
            //console.log("#" + blockId + ' .select-productbizunit');
            //console.log("#" + blockId + ' .' +self.prefix + '-options');
            for(var k = 0; k < options.length; k++){
                if(options[k].value !== "" && options[k].parentNode.value != options[k].value){
                    if(arHideOptionsValue.includes(options[k].value)){
                        options[k].style.display = 'none';
                    } else {
                        options[k].style.display = 'block';
                    }                
                }
            }
        }        
    }
    
    
    self.loadBizUnitProducts = function(select, key){
        
        console.log("select.value = " + select.value);
        console.log("key = " + key);
        
        var sum =  document.getElementById('biz_unit_sum_' + key).value;
        console.log("Sum = " + sum);
        
        
        
        var ajaxLink = '/local/components/de/bizunit.products.widgetform.edit/templates/.default/ajax.php';
        var postData = {BIZ_UNIT_ID : select.value, BIZ_UNIT_KEY : key, BIZ_UNIT_SUM : sum};    
        BX.ajax({
            url: ajaxLink,
            method: 'POST',
            dataType: 'html',
            data: postData,
            async: true,
            onsuccess: function(res){                    
                document.getElementById('biz_unit_product_item_' + key).innerHTML = res;
                self.save();
            }
        });        
        
    }
    
    self.setCurrency = function(value){
        var elemOpportynity = document.getElementsByName('CURRENCY_ID');
        for(var i = 0; i < elemOpportynity.length; i++){
            console.log(value);
            elemOpportynity[i].value = value;
        }        
    };
    
    
    self.setOpportynity = function(sum){
        var elemOpportynity = document.getElementsByName('OPPORTUNITY');
        for(var i = 0; i < elemOpportynity.length; i++){
            console.log(sum);
            elemOpportynity[i].value = sum;
        }
        self.setAllSum(sum);
    };
        
    
    self.setAllSum = function(sum){        
        self.allSum = sum;
        self.recountAllSumItems();
        self.ietmMaxSum[0] = sum;
    };
    
    self.recountAllSumItems = function(){
        var itemId = "";
        for(var i = 0; i < self.countItems; i++){
            
            itemId = self.prefix + "_psnt_" + i;
            var item = document.getElementById(itemId);
            var itemSelect = document.getElementById('biz_unit_selbi_' + i);            
            
            console.log("self.countItems = " + self.countItems);
            console.log("self.countItems = " + self.countItems);
            var newItemSum = self.setSumByPsnt(item);
            
            if(window['bizUnitWidgetForm_biz_productunit' + itemSelect.value] && self.prefix.indexOf('productunit') === -1){
                var objProductItem = window['bizUnitWidgetForm_biz_productunit' + itemSelect.value];
                
                if(objProductItem.prefix != self.prefix){
                    objProductItem.setAllSum(newItemSum);
                }
            }
            
        }        
    };
    
    self.prepareSumToInput = function(sum){
        sum = +sum;
        sum = self.roundValue(sum);
        sum = sum.toFixed(2);
        return sum;
    };
    
    self.prepareSumToCount = function(sumTxt){
        sumTxt = sumTxt.replace(",", ".");
        sumTxt = +sumTxt;
        var sum = self.roundValue(sumTxt);
        return sum;
    }

    self.setSumByPsnt = function (item){
        var numItem = +item.dataset.num;
        item.value = self.prepareSumToCount(item.value);
        
        var value = +item.value;

        var nextPsnt = self.getNextPsnt();
        if(nextPsnt < 0){
           value = nextPsnt + value;          
           item.value = value;
        }        
        var sumItemId = self.prefix + "_sum_" + numItem;
        var sumItem = document.getElementById(sumItemId);
        var sumValue = self.roundValue(self.allSum*value/100);
        
               
        sumItem.value = self.prepareSumToInput(sumValue);

        self.recountMaxSumPsnt(numItem, sumValue, value);
        self.setActiveSaveBtn(item);
        
        
        var selectValue = document.getElementById('biz_unit_selbi_' + numItem).value;
        console.log('----bizUnitWidgetForm_biz_productunit' + selectValue);
                 
        if(window['bizUnitWidgetForm_biz_productunit' + selectValue] && self.prefix.indexOf('productunit') === -1){
            var obj = window['bizUnitWidgetForm_biz_productunit' + selectValue];
            obj.setAllSum(sumValue);
            console.log(obj);
        }
        item.value = self.prepareSumToInput(item.value);
        
        return sumValue;
    };
    
    self.getMaxBudgetSum = function(idBizUnit){
        var maxSum = -1;
        if(self.budgetBizUnit[idBizUnit]){
            maxSum = self.budgetBizUnit[idBizUnit].TOTAL.TOTAL;
            
            console.log("MAX_SUM");
            console.log(maxSum);
        }
        return maxSum;
        
    };
    
    self.checkBudgetSum = function(item){       
        
        var numItem = +item.dataset.num;
        var sum = +item.value;
        var selectValue = document.getElementById('biz_unit_selbi_' + numItem).value;
        console.log("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
        console.log(numItem);
        console.log(sum);
        
        var maxBudgetSum = self.getMaxBudgetSum(selectValue);        
        
        if(sum >= maxBudgetSum && maxBudgetSum != -1){
            item.style.backgroundColor = '#f5ff5c';
            console.log(sum + " сумма больше бюджета " + maxBudgetSum);
            
            item.value = self.prepareSumToInput(maxBudgetSum);
            
        } else {
            item.style.backgroundColor = '#ffffff';
            console.log(sum + " сумма не првевышает бюджет " + maxBudgetSum);
            item.value = self.prepareSumToInput(sum);                       
        }       
    };


    self.setPsntBySum = function (item){
        var numItem = +item.dataset.num;
        item.value = self.prepareSumToCount(item.value);
        
        var value = +item.value; 
        
        // проверка по бюджету суммы
        
        self.checkBudgetSum(item);
                

        var nextSum = self.getNextSum();
        if(nextSum < 0){
           value = nextSum + value;
           item.value = self.prepareSumToInput(value);
        }
        

        var psntItemId = self.prefix + "_psnt_" + numItem;
        var psntItem = document.getElementById(psntItemId);
        var psntValue = self.roundValue(value/(self.allSum/100));
        psntItem.value = psntValue;
        self.setActiveSaveBtn(item);
        
        var selectValue = document.getElementById('biz_unit_selbi_' + numItem).value;
        console.log('----bizUnitWidgetForm_biz_productunit' + selectValue);
                 
        if(window['bizUnitWidgetForm_biz_productunit' + selectValue] && self.prefix.indexOf('productunit') === -1){
            var obj = window['bizUnitWidgetForm_biz_productunit' + selectValue];
            obj.setAllSum(value);
            console.log(obj);
        }
        item.value = self.prepareSumToInput(item.value);
    };


    self.saveItem = function (item){
        var numItem = +item.dataset.num;
        if(document.getElementById(self.prefix + "_savebi_" + numItem).classList.contains("ui-btn-disabled")){
            return;
        }

        var nextItemId = self.prefix + "_item_" + (numItem + 1);
        var psntItemId = self.prefix + "_psnt_" + numItem;
        var sumItemId = self.prefix + "_sum_" + numItem;
        var psntItem = document.getElementById(psntItemId).value;
        var sumItem = document.getElementById(sumItemId).value;


        if(sumItem < self.ietmMaxSum[numItem]){

            while(document.getElementById(nextItemId)){
                document.getElementById(psntItemId).value;
                numItem += 1;
                nextItemId = self.prefix + "_item_" + (numItem + 1);
            }

            var nextPsnt = self.getNextPsnt();
            var nextSum = self.getNextSum();

            console.log("nextPsnt=" + nextPsnt);
            console.log("sumItem=" + nextSum);

            if(nextPsnt > 0 && nextSum > 0){
                self.addNewItem(numItem + 1);
                self.updateItem(numItem + 1, nextPsnt, nextSum);
            }
        }
    };

    self.getNextSum = function(){
        var nodeNum = 0;
        var sumItem_ = 0;
        while(document.getElementById(self.prefix + "_item_" + nodeNum)){
            sumItem_ += +document.getElementById(self.prefix + "_sum_" + nodeNum).value;
            nodeNum += 1;
            self.countItems = nodeNum;
        }
        return self.allSum - sumItem_;
    };

    self.getNextPsnt = function(){
        var nodeNum = 0;
        var psntItem_ = 0;
        while(document.getElementById(self.prefix + "_item_" + nodeNum)){
            psntItem_ += +document.getElementById(self.prefix + "_psnt_" + nodeNum).value;
            nodeNum += 1;
            self.countItems = nodeNum;
        }
        return self.allPsnt - psntItem_;
    };

    self.updateItem = function (numItem, psnt, sum){
        var psntItemId = self.prefix + "_psnt_" + numItem;
        var sumItemId = self.prefix + "_sum_" + numItem;

        var psntItem = document.getElementById(psntItemId);
        var sumItem = document.getElementById(sumItemId);

        self.ietmMaxPsnt[numItem] = psnt;
        self.ietmMaxSum[numItem] = sum;
              
        psntItem.value = self.prepareSumToInput(psnt);        
        sumItem.value = self.prepareSumToInput(sum);       
    };


    self.addNewItem = function (numItem){
        var beforeElem = document.getElementById(self.prefix + "_wrap_item_{num}");
        var itemHtml = beforeElem.innerHTML.replace(/{num}/gi, numItem);
        itemHtml = itemHtml.replace("{product}", "");
        var newItem = document.createElement('div');
        
        newItem.id = self.prefix + "_item_" + numItem;
        
        
        if(self.prefix.indexOf("product") === -1){
            newItem.classList.add('be-item');
        }               
        

        document.getElementById(self.prefix + "-choose-be-form").insertBefore(newItem, beforeElem);
        newItem.innerHTML = itemHtml;
        
        self.hideFerstDelBtn(firstDelBtnItemId = self.prefix + "_delbi_0");
    };


    self.setActiveSaveBtn = function (item){
        var numItem = +item.dataset.num;
        var selectItemId = self.prefix + "_selbi_" + numItem;
        var psntItemId = self.prefix + "_psnt_" + numItem;
        var sumItemId = self.prefix + "_sum_" + numItem;


        var valSelect = document.getElementById(selectItemId).value;
        var valPsnt = document.getElementById(psntItemId).value;
        var valSum = document.getElementById(sumItemId).value;

        if(valSelect == "" || valPsnt == "" || valSum == ""){
            document.getElementById(self.prefix + "_savebi_" + numItem).classList.remove("ui-btn-success");
            document.getElementById(self.prefix + "_savebi_" + numItem).classList.add("ui-btn-disabled");
        } else {
            document.getElementById(self.prefix + "_savebi_" + numItem).classList.remove("ui-btn-disabled");
            document.getElementById(self.prefix + "_savebi_" + numItem).classList.add("ui-btn-success");
        }
               
        self.hideSelect();
        self.save(); 
    };

    self.deleteItem = function(item){
        var numItem = +item.dataset.num;

        document.getElementById(self.prefix + "_item_" + numItem).remove();

        while(document.getElementById(self.prefix + "_item_" + (numItem + 1))){
            var itemId = self.prefix + "_item_" + (numItem + 1);

            var selectItemId = self.prefix + "_selbi_" + (numItem + 1);
            var psntItemId = self.prefix + "_psnt_" + (numItem + 1);
            var sumItemId = self.prefix + "_sum_" + (numItem + 1);

            var delBtnItemId = self.prefix + "_delbi_" + (numItem + 1);
            var saveBtnItemId = self.prefix + "_savebi_" + (numItem + 1);

            document.getElementById(selectItemId).dataset.num = numItem;
            document.getElementById(psntItemId).dataset.num = numItem;
            document.getElementById(sumItemId).dataset.num = numItem;
            document.getElementById(saveBtnItemId).dataset.num = numItem;
            document.getElementById(delBtnItemId).dataset.num = numItem;

            self.ietmMaxPsnt[numItem + 1] = self.ietmMaxPsnt[numItem];
            self.ietmMaxSum[numItem + 1] = self.ietmMaxSum[numItem];


            // set id last operation with node !!!
            document.getElementById(itemId).id = self.prefix + "_item_" + numItem;
            document.getElementById(selectItemId).id = self.prefix + "_selbi_" + numItem;
            document.getElementById(psntItemId).id = self.prefix + "_psnt_" + numItem;
            document.getElementById(sumItemId).id = self.prefix + "_sum_" + numItem;
            document.getElementById(saveBtnItemId).id = self.prefix + "_savebi_" + numItem;
            document.getElementById(delBtnItemId).id = self.prefix + "_delbi_" + numItem;

            numItem += 1;
        }
               
        self.hideFerstDelBtn(self.prefix + "_delbi_0");     
    };
    
    self.hideFerstDelBtn = function(id){
        document.getElementById(id).style.setProperty('display', 'none', 'important');
    }

    self.recountMaxSumPsnt = function (numItem, sum, psnt){
        var currentMaxSum = self.ietmMaxSum[numItem];
        var currentMaxPsnt = self.ietmMaxPsnt[numItem];

        numItem += 1;
        while(document.getElementById(self.prefix + "_item_" + numItem)){
            console.log(numItem);
            self.ietmMaxPsnt[numItem] = currentMaxPsnt - psnt;
            self.ietmMaxSum[numItem] = currentMaxSum - sum;
            currentMaxPsnt += psnt;            
            numItem += 1;
        }
    };

    self.roundValue = function (val){
        return Math.ceil((val)*100)/100;
    };
    
    
    self.save = function(){
        
        var widgetData = {biunit : []};
        var allSum = 0;
                       
        var biUnits = document.querySelectorAll(".select-bizunit");
        for(var i=0; i < biUnits.length; i++){
            if(biUnits[i].value != ""){            
                if(!widgetData.biunit[i]){
                    widgetData.biunit[i] = {BI_ID : 0, SUM : 0, PSNT : 0, DATA_BIZ_UNIT_PRODUCTS : []};
                }         
                               
                var biId = +biUnits[i].value;                
                
                widgetData.biunit[i].BI_ID = biId;
                if(document.querySelector("#biz_unit_psnt_" + i)){
                    widgetData.biunit[i].PSNT = +document.querySelector("#biz_unit_psnt_" + i).value;
                }
                
                if(document.querySelector("#biz_unit_sum_" + i)){
                    widgetData.biunit[i].SUM = +document.querySelector("#biz_unit_sum_" + i).value;
                    allSum += widgetData.biunit[i].SUM;                    
                }                                 
                
                var products = document.querySelectorAll("select[id^='biz_productunit" + biId + "_selbi_']");
                
                if(products){
                    for(var k = 0; k < products.length; k++){
                        if(products[k].value != ""){
                            if(!widgetData.biunit[i].DATA_BIZ_UNIT_PRODUCTS[k]){
                                widgetData.biunit[i].DATA_BIZ_UNIT_PRODUCTS[k] = {BIZ_UNIT_PRODUCT_ID : 0, PSNT : 0, SUM : 0};
                            }
                            
                            widgetData.biunit[i].DATA_BIZ_UNIT_PRODUCTS[k].BIZ_UNIT_PRODUCT_ID = +products[k].value;
                            
                            if(document.querySelector("#biz_productunit" + biId + "_psnt_" + k)){
                                widgetData.biunit[i].DATA_BIZ_UNIT_PRODUCTS[k].PSNT = +document.querySelector("#biz_productunit" + biId + "_psnt_" + k).value;
                            }
                            
                            if(document.querySelector("#biz_productunit" + biId + "_sum_" + k)){
                                widgetData.biunit[i].DATA_BIZ_UNIT_PRODUCTS[k].SUM = +document.querySelector("#biz_productunit" + biId + "_sum_" + k).value;
                            }                         
                        }
                    }
                }             
            }
        }       
        
        
        var widgetDataInput = document.querySelector("input[name='UF_CRM_BIZUINIT_WIDGET_DATA']");        
        widgetDataInput.value = JSON.stringify(widgetData.biunit);
        
        console.log(widgetDataInput.value);
        //console.log(widgetData);
    }
    
    self.save();
    self.hideSelect();
};*/