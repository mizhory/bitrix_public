(function (window, BX) {
    if (typeof window.InternalLoadFile !== 'function') {
        /**
         * @description Функция для работы с скачиванием файла в окне
         * @param src {string}
         * @param fileName {string}
         * @param onloadCallback {Function}
         * @param extraParams
         */
        let internalLoadFile = function (src = '', fileName = '', onloadCallback = null, extraParams = {}) {
            this.src = src;
            this.fileName = fileName;
            this.onloadCallback = onloadCallback;
            this.extraParams = extraParams;
            this.downloader = {}
        }

        /**
         *
         * @type {{destroyDownloader: internalLoadFile.destroyDownloader, load: internalLoadFile.load, createRequest: (function(): XMLHttpRequest), openInternal: internalLoadFile.openInternal, createDownloader: (function(*, *): *|{}|HTMLAnchorElement|{}), open: (function(Object): undefined)}}
         */
        internalLoadFile.prototype = {
            /**
             * @description Метод загрузки файла
             */
            load: function () {
                let request = this.createRequest();
                request.open('POST', this.src, true);
                request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                request.responseType = 'blob';
                request.onload = this.open.bind(this, request);
                request.send(BX.ajax.prepareData(this.extraParams));
            },

            /**
             * @description Метод открытия файла
             * @param request {Object}
             */
            open: function (request) {
                if (request.status !== 200) {
                    console.error('Ошибка открытия файла!');
                    return;
                }

                const blob = request.response;
                if (!!window.navigator.msSaveOrOpenBlob) {
                    window.navigator.msSaveBlob(blob, this.fileName);
                } else {
                    this.openInternal(request, blob);
                }

                if (!!this.onloadCallback) {
                    this.onloadCallback();
                }
            },

            /**
             * @description Никзоуровневый метод открытия файла
             * @param request {Object}
             * @param blob {BlobPart}
             */
            openInternal: function (request, blob) {
                const downloader = this.createDownloader(
                    window.URL.createObjectURL(
                        new Blob([blob],
                            { type: request.getResponseHeader("Content-Type") })
                    ),
                    this.fileName
                );

                downloader.click();

                this.destroyDownloader();
            },

            /**
             * @description Создает и возвращает экземпляр элемента ссылки
             * @param href
             * @param fileName
             * @return {*|{}|HTMLAnchorElement|{}}
             */
            createDownloader: function (href, fileName) {
                this.downloader = window.document.createElement('a', {});
                this.downloader.href = href;
                this.downloader.download = fileName;
                document.body.appendChild(this.downloader);
                return this.downloader;
            },

            /**
             * @description Уничтожает экземпляр элемента ссылки
             */
            destroyDownloader: function () {
                window.URL.revokeObjectURL(this.downloader.href);
                document.body.removeChild(this.downloader);
                this.downloader = {};
            },

            /**
             * @description Возвращает экземпляр реквеста
             * @return {XMLHttpRequest}
             */
            createRequest: function () {
                return new XMLHttpRequest();
            }
        };

        window.InternalLoadFile = internalLoadFile;
    }
})(window, BX);