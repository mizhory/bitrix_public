BX.ready(function (){
    class Select{
        constructor(selector){
            this.card = new Usercard();
            this.select = this.card.createSelect({
                select:selector,
                searchPlaceholder:"Введите название БЕ",
                placeholder:"Выберите значение БЕ",
            });
        }
    }

    class Selects{
        selects = {};
        mainSelect;

        constructor() {
            this.mainSelect = new Select('.main_user');

            let self = this;

            document.querySelector('.main_user').addEventListener('change',function (){
                self.getData();
                self.getAjax();
            })


            this.initAdditional();
        }

        initAdditional(){
            let self = this;

            for (let key in this.selects){
                delete this.selects[key];
            }

            document.querySelectorAll('.additional').forEach(function (item){
                let selector = item.getAttribute('data-selector');

                self.selects[selector] = new Select(selector);

                item.addEventListener('change',function (){
                    self.getData();
                })
            })
            setTimeout(function (){
                self.getData();
            },500)

        }

        getAjax(){
            let self = this;
            BX.ajax.runComponentAction('vigr:usertype.ulandheads', 'getUr', {
                mode: 'class',
                data: {
                    'userId': this.mainSelect.select.selected()
                }
            }).then(function (response){
                document.querySelector('.body').innerHTML = response.data;
                self.initAdditional();
                self.getData();
            })
        }

        getData(){
            let self = this;
            let data = {
                'data_user': this.mainSelect.select.selected(),
                'values':{

                }
            }
            let rows = document.querySelectorAll('.urL');

            rows.forEach(function (row) {
                let id = row.getAttribute('data-id');

                let head = self.selects['.user_h_'+id].select.selected();
                let isp = self.selects['.user_i_'+id].select.selected();

                data['values'][id] = {
                    'id':id,
                    'head':head,
                    'isp':isp,
                }

                if(head <=0 || isp <=0){
                    window.checker.setStatus(id,false);
                }else{
                    window.checker.setStatus(id,true);
                }

            })

            window.checker.checkStatus();

            document.getElementById('mainFieldUl').value = JSON.stringify(data);
        }

    }

    (new Selects());

})