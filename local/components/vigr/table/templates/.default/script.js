BX.ready(function (){
    document.addEventListener('click',function (e){
        let target = e.target;
        let tr = target.closest('tr');
        if (!tr) {
           return;
        }
        let yearNode = tr.querySelector('.year');
        if (!yearNode) {
            return;
        }
        let year = yearNode.getAttribute('data-year');
        if(target.classList.contains('clicker')){
            BX.SidePanel.Instance.open(
                "/budget/edit/"+target.getAttribute('data-be')+'/'+target.getAttribute('data-articleid')+
                '/' + target.getAttribute('data-articleHash') + '/?year=' + year,
                {
                    cacheable:false,
                    animationDuration: 100,
                    width: 500
                }
            );
        }
    })

    document.addEventListener('click',function (e){
        let target = e.target;

        if(target.classList.contains('download')){
            BX.showWait(document.getElementById ('wrapD'))
            var request = new XMLHttpRequest();
            request.open('POST', '/local/ajax/xml.php?'  , true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.responseType = 'blob';
            let fileName = 'Выгрузка бюджета.xlsx';
            request.onload = function(e) {
                if (this.status === 200) {
                    var blob = this.response;
                    if(window.navigator.msSaveOrOpenBlob) {
                        window.navigator.msSaveBlob(blob, fileName);
                    }
                    else{
                        var downloadLink = window.document.createElement('a');
                        var contentTypeHeader = request.getResponseHeader("Content-Type");
                        downloadLink.href = window.URL.createObjectURL(new Blob([blob], { type: contentTypeHeader }));
                        downloadLink.download = fileName;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                        BX.closeWait(document.getElementById ('wrapD'))
                    }
                }
            };
            request.send(JSON.stringify({'type':document.getElementById('type').value,'id':location.pathname.split('/')[3]}));
        }

    })

})