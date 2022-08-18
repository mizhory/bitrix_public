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
        userSelect;


        constructor() {
            this.mainSelect = new Select('.ur');

            let self = this;

            document.querySelector('.ur').addEventListener('change',function (){
                self.getData();
            })

            this.userSelect = new Select('.user');

            document.querySelector('.user').addEventListener('change',function (){
                self.getAjax();
            })

            this.initAdditional();
        }

        initAdditional(){
            let self = this;

            for (let key in this.selects){
                delete this.selects[key];
            }
            delete this.mainSelect;

            this.mainSelect = new Select('.ur');

            document.querySelector('.ur').addEventListener('change',function (){
                self.getData();
            })

            if( document.querySelectorAll('.additional')){
                document.querySelectorAll('.additional').forEach(function (item){
                    let selector = item.getAttribute('data-selector');

                    self.selects[selector] = new Select(selector);

                    item.addEventListener('change',function (){
                        self.getData();
                    })
                })

                document.querySelectorAll('.delete').forEach(function (item){
                    item.addEventListener('click',function (item){
                        item.target.closest('table').style.display = 'none';
                        let newAr = [];
                        let ar = self.mainSelect.select.selected();
                        ar.forEach(function (value){
                            if(value !== item.target.closest('table').getAttribute('data-id')){
                                newAr.push(value);
                            }
                        })
                        self.mainSelect.select.setSelected(newAr);
                        self.getData();
                        return false;
                    })
                })

                document.querySelectorAll('.date').forEach(function (item){
                    item.addEventListener('change',function (){
                        self.getData();
                    })
                })

                this.getData();
            }
        }

        getAjax(){
            let self = this;

            BX.ajax.runComponentAction('vigr:usertype.headsandsuccessors', 'getUr', {
                mode: 'class',
                data: {
                    'userId': this.userSelect.select.selected()
                }
            }).then(function (response){
                document.querySelector('.body').innerHTML = response.data;
                self.initAdditional();
            })
        }

        getData(){
            let self = this;
            let data = {
                'values':{

                }
            }

            let haves = {};

            self.mainSelect.select.selected().forEach(function (value){
                haves[value] = true;
            })

            let rows = document.querySelectorAll('.urL');
            let error = false;
            rows.forEach(function (row) {
                let id = row.getAttribute('data-id');
                if(haves[id]){
                    let head = self.selects['.user_h_'+id].select.selected();
                    let pre = self.selects['.user_p_'+id].select.selected();
                    let date = document.getElementById('date_'+id).value;
                    row.closest('table').style.display = '';
                    data['values'][id] = {
                        'id':id,
                        'head':head,
                        'pre':pre,
                        'date':date
                    }

                    if(!error && head<=0 || pre<=0 || date === ''){
                        error = true;
                    }
                }else{
                    row.closest('table').style.display = 'none';
                }
            })

            console.log(data);

            //document.getElementById('blog-submit-button-save').disabled = error;

            document.getElementById('mainFieldUl').value = JSON.stringify(data);
        }

    }

    selects = new Selects();
})