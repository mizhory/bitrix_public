class DaData{
    checked;

    constructor() {
        let self = this;
        this.checkInput = $('#notResident');

        this.checked = this.checkInput.attr('checked');

        this.inn = $('#getterINNNalBeznal');

        this.name = $('#getterNalBeznal');

        this.plagin = $("#getterNalBeznal, #getterINNNalBeznal").suggestions({
            token: "689bf82ee3ac09f313bcda958e0efef80c39ab14",
            type: "PARTY",
            onSelect: function (suggestion) {
                $('#getterNalBeznal').val(suggestion.value.replaceAll('"',''));
                $('#getterINNNalBeznal').val(suggestion.data.inn);
                self.getAllData();
            }
        }).suggestions();

        this.inn.css('paddingLeft','0px');
        this.name.css('paddingLeft','0px');

        if(this.checked){
            this.plagin.disable();
            //this.inn.addClass('disabled');
        }else{
            //this.inn.removeClass('disabled');
            this.plagin.enable();
        }
        this.init();
        this.getAllData();
    }

    init(){
        let self = this;

        this.checkInput.on('input',function (){
            self.checked = $(this).attr('checked');
            if(self.checked){
                self.inn.val('');
                //self.inn.addClass('disabled');
                self.plagin.disable();
            }else{
                //self.inn.removeClass('disabled');
                self.plagin.enable();
            }
            self.getAllData();
        })

        this.inn.on('input',function (){
            self.getAllData();
        })

        this.name.on('input',function (){
            self.getAllData();
        })
    }

    getAllData(){

        let data = {
            'checked' : this.checked ?? 0,
            'inn' : this.inn.val(),
            'name' : this.name.val()
        }
        console.log(data);
        $('#UF_DADATA').val(JSON.stringify(data));
    }
}