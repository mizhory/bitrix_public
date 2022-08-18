BX.ready(function () {
    (new Uploader());
})

/**Класс загрузки файлов*/
class Uploader {
    constructor() {
        this.lastIndexFile = 0;
        this.fileUploader = document.getElementById('file-uploader');
        this.files = {};
        this.initDownload();
        this.initButtons();
    }


    /**Инциализация кнопок*/
    initButtons(){
        let self = this;
        document.addEventListener('click',(event)=>{
            if(event.target.classList.contains('deleteFile')){
                delete self.files[event.target.getAttribute('data-file')];
                let dt = new DataTransfer();

                let index = 0;

                for (let key in self.files){
                    dt.items.add(self.files[key]);
                }

                self.fileUploader.files = dt.files;

                event.target.closest('div').remove();
                document.getElementById('errors').innerHTML = '';
            }else if(event.target.classList.contains('saveFiles')){
                self.sendFiles();
            }
        })
    }
    /**Инциализация загрузки*/
    initDownload() {

        let self = this;

        this.fileUploader.addEventListener('input', (event) => {
            self.files = [];
            const files = event.target.files;

            let msg = '';

            const feedback = document.getElementById('files');

            let index = 0;
            let error = false;
            for (const file of files) {
                self.files[index] = file;
                const name = file.name;
                const type = file.type ? file.type : 'NA';
                const size = file.size;
                const lastModified = file.lastModified;

                if(type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
                    error = true;
                }

                console.log({file, name, type, size, lastModified});
                msg += `  <div> ${name} <button data-file="${index}" type="button" class="btn deleteFile btn-outline-danger">Удалить</button></div> `;
                index++
            }

            self.lastIndexFile = index;
            if(!error){
                msg += ''
                msg += '<button type="button" class="btn saveFiles btn-outline-success">Сохранить</button>';
            }else{
                msg += 'Возможно загружать только xlsx файлы!';
            }

            feedback.innerHTML = msg;
        });
    }


    /**Отправка файлов*/
    sendFiles() {
        const bxFormData = new BX.ajax.FormData();

        for (let key in this.files) {
            bxFormData.append(key, this.files[key])

        }
        bxFormData.append('sign',document.getElementById('sign').value);
        bxFormData.append('year',document.getElementById('year').value);

        bxFormData.send(
            '/local/ajax/file.php',
            function (jsonRes) {
                let res = JSON.parse(jsonRes);
                let errorMsg = '';
                if(res.status === 'error'){
                    if(res.response['stringInFile']){
                        console.log(res.response['stringInFile']);

                        res.response['stringInFile'].forEach(function (arValue){
                            errorMsg += '<div> Ошибка - дублирующиеся строки в файлах : '
                                + arValue['fileName1']
                                + ' строка - '
                                + arValue['string1']
                                + ', файл ' + arValue['fileName2']
                                + ' строка - ' + arValue['string2']
                        })
                    }else if(res.response['recalculateInFile']){
                        errorMsg += '<div>'+res.response['recalculateInFile'][0] + '</div>';
                    }
                    if(res.response['additionalError']){
                        res.response['additionalError'].forEach(function (arMessage){
                            errorMsg += '<div>'+arMessage['message'] + '</div>';
                        })
                    }
                }else{
                    errorMsg += '<div>Успешная загрузка! <a href="/budget/all/">Перейти в список бюджетов</a></div>';
                }
                document.getElementById('errors').innerHTML = errorMsg;
            }
        );
    }
}