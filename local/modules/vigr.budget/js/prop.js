class allSort{
    allFields = [];

    arrayForJson = {};

    setArrayForJson(propId,userObject){
        if(!this.arrayForJson[propId]){
            this.arrayForJson[propId] = {
                'lastId':0,
                'users':{

                },
                'usersIdsByPlace':{

                }
            };
        }

        let forJson = this.arrayForJson[propId];

        userObject['arSelected'].forEach(function (object){
            if(object !== null){
                if(typeof forJson['usersIdsByPlace'][object['id']] === 'undefined'){
                    forJson['usersIdsByPlace'][object['id']] = forJson['lastId'];
                    forJson['users'][forJson['lastId']] = object;
                    forJson['lastId']++;
                }
            }
        })

        let needResort = false;

        for(let key in forJson['users']){
            if(userObject['arSelected'][forJson['users'][key]['id']] === null){
                delete forJson['usersIdsByPlace'][[forJson['users'][key]['id']]];
                delete forJson['users'][key];
                if(!needResort){
                    needResort = true;
                }
            }
        }

        if(needResort){
            let newObject = {};

            let length = 0;

            let oldData = Object.assign({},forJson);

            forJson = {
                'users':{

                },
                'usersIdsByPlace':{

                }
            };

            for(let key in oldData['users']){
                forJson['users'][length] = oldData['users'][key];
                forJson['usersIdsByPlace'][oldData['users'][key]['id']] = length;
                length++;
            }
            forJson['lastId'] = length;
        }

        this.arrayForJson[propId] = forJson;

        this.setFieldAll();
    }

    setFieldAll()
    {
        //this.propInput.value = JSON.stringify(this.arrayForJson);
    }

    initField(input){
        let inputs = document.querySelectorAll('.bx-field-value input');
        let propInput;

        inputs.forEach(function (input){
            if(input.closest('tr').querySelector('.bx-field-name')){
                if(input.closest('tr').querySelector('.bx-field-name').textContent === 'Техническое поле сортировки:') {
                    propInput = document.querySelector('[name = "' + input.name + '"]');
                }
            }
        })

        this.propInput = propInput;

        let idProp = document.querySelector('[name="PROPERTY_'+input.value+'[]"]').closest('span').id;
        idProp = idProp.replace('Multiple_','');
        idProp = idProp.replace('_hids','');
        let resField = input.closest('tr').querySelector('.res');
        let valueField = input.closest('tr').querySelector('.propName');
        this.allFields.push(
            new sortField(
                (window['O_Multiple_'+idProp]),idProp,this,resField,input.value,valueField
            )
        );
    }
}


class sortField{

    constructor(userObject,idProp,parent,valueInput,realId,valueField) {
        this.propId = idProp;
        this.propObj = userObject;
        this.parent = parent;
        this.valueWrap = valueInput;
        this.realId = realId;
        this.valueField = valueField;

        if(this.valueField.value !== ''){

            if(this.valueField.value){
                this.parent.arrayForJson[this.realId] = JSON.parse(this.valueField.value);
            }
            this.setField();
        }

        this.initMutation();

    }

    initMutation(){
        let id = '#Multiple_'+this.propId+'_res';
        let res = document.querySelector(id);
        const config = {
            attributes: true,
            childList: true,
            subtree: true
        };

        let self = this;

        const callback = function(mutationsList, observer) {
            for (let mutation of mutationsList) {
                if (mutation.type === 'childList') {
                    self.onChange();
                }
            }
        };

        const observer = new MutationObserver(callback);

        observer.observe(res, config);
    }

    onChange(){
        this.parent.setArrayForJson(this.realId,this.propObj);
        this.setField();

    }

    setField(){
        let users = this.parent.arrayForJson[this.realId];
        let html = '';
        for (let key in users['users']){
            html += users['users'][key]['name'] + '<br>';
        }

        this.valueWrap.innerHTML = html;
        this.valueField.value = JSON.stringify(users);
    }
}

document.addEventListener('DOMContentLoaded',function (){
    let allSortClass = new allSort();
    let inputs = document.querySelectorAll('[name=sortField]');

    inputs.forEach(function (input){
        allSortClass.initField(input);
    })
})