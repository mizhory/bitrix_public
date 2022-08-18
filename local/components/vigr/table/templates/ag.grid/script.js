class CustomLoadingOverlay {
    init(params) {
        this.eGui = this.createLoader();
    }

    createLoader() {
        return BX.create("div", {
            props: {
                className: "side-panel-default-loader-container"
            },
            html:
                '<svg class="side-panel-default-loader-circular" viewBox="25 25 50 50">' +
                '<circle ' +
                'class="side-panel-default-loader-path" ' +
                'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"' +
                '/>' +
                '</svg>'
        });
    }

    getGui() {
        return this.eGui;
    }

    refresh(params) {
        return false;
    }
}

class ArticleCell {
    // init method gets the details of the cell to be renderer
    init(params) {
        this.eGui = BX.create('span', {
            attrs: {className: 'vertical-cell'},
            children: [
                BX.create('a', {
                    text: params.value,
                    attrs: {
                        className: 'min-line-height',
                        href: `/budget/edit/${params.data.beId ?? ''}/${params.data.articleId ?? ''}/${params.data.year ?? ''}/`
                    },
                })
            ]
        });
    }

    getGui() {
        return this.eGui;
    }

    refresh(params) {
        return true;
    }
}
class VerticalCell {
    // init method gets the details of the cell to be renderer
    init(params) {
        this.eGui = BX.create('span', {
            attrs: {className: 'vertical-cell'},
            children: [
                BX.create('span', {text: params.value, attrs: {className: 'min-line-height'},})
            ]
        });
    }

    getGui() {
        return this.eGui;
    }

    refresh(params) {
        return true;
    }
}
class BudgetCell {
    // init method gets the details of the cell to be renderer
    init(params) {
        let children = [];
        for (let key in params.value) {
            children.push(BX.create('span', {text: params.value[key]}));
        }

        this.eGui = BX.create('span', {attrs: {className: 'vertical-cell budget-cell'}, children: children});
    }

    getGui() {
        return this.eGui;
    }

    refresh(params) {
        return true;
    }
}

(function (window, BX) {
    if (typeof window.BudgetList !== 'function') {
        window.BudgetList = function (params = {}) {
            this.params = params;
        }
    }

    window.BudgetList.prototype = {
        init: function () {
            this.initWrap();
            this.initTable();
            this.initFilter();
            this.initSlider();
            this.initExcelDownload()
        },

        initWrap: function () {
            this.wrap = BX(this.params.id);
        },

        getColumns: function () {
            return !!this.params.columns ? this.params.columns : [];
        },

        getData: function () {
            return !!this.params.data ? this.params.data : [];
        },

        initTable: function () {
            if (!this.wrap) {
                return;
            }

            const columns = this.getColumns();
            if (columns.length <= 0) {
                return;
            }

            const data = this.getData();

            this.gridOption = {
                suppressRowTransform: true,
                defaultColDef: {
                    width: 170,
                },
                columnDefs: columns,
                rowData: data,
                components: {
                    BudgetCell: BudgetCell,
                    VerticalCell: VerticalCell,
                    ArticleCell: ArticleCell,
                },
                rowHeight: 240,
                animateRows: true,
                getRowHeight: this.getRowHeight.bind(this),
                loadingOverlayComponent: CustomLoadingOverlay,
                loadingOverlayComponentParams: {
                    loadingMessage: 'One moment please...',
                },
            };

            this.grid = new agGrid.Grid(this.wrap, this.gridOption);
            if (data.length <= 0) {
                this.loadData();
            }
        },

        showLoader: function () {
            this.gridOption.api.showLoadingOverlay();
        },

        hideLoader: function () {
            this.gridOption.api.hideOverlay();
        },

        getRowHeight: function (params) {
            const length = Object.keys(params.data.budget).length;
            return (length == 1) ? 60 : length * 40;
        },

        initFilter: function () {
            BX.addCustomEvent('BX.Main.Filter:apply', this.loadData.bind(this));
        },

        loadData: function () {
            this.showLoader();

            BX.ajax.runComponentAction('vigr:table', 'loadData', {
                mode: 'class',
                data: {
                    componentName: this.params.componentName,
                    params: this.params.jsParams ?? {}
                },
                signedParameters: this.params.signedParams
            })
                .then(({data = []}) => {
                    this.updateGridData(data);
                    this.hideLoader();
                })
                .catch((response) => {
                    console.log(response);
                    this.hideLoader();
                })
        },

        updateGridData: function (data = []) {
            this.gridOption.api.setRowData(data);
        },

        initSlider: function () {
            BX.SidePanel.Instance.bindAnchors({rules: [{
                condition: ["/budget/edit"],
                options: {
                    allowChangeHistory: false,
                    onclose: this.loadData.bind(this),
                    cacheable: false,
                    animationDuration: 100,
                    width: 540
                }
            }]});
        },

        initExcelDownload: function () {
            this.excelBtn = BX('excel-download');
            if (!this.excelBtn) {
                return;
            }

            if (!window.InternalLoadFile) {
                return;
            }

            this.excelProccess = false;
            BX.bind(this.excelBtn, 'click', this.loadExcel.bind(this));
        },

        loadExcel: function () {
            if (this.excelProccess) {
                return;
            }

            this.excelProccess = true;
            this.showLoader();

            const loader = new window.InternalLoadFile(
                '/local/ajax/xml.php',
                'Выгрузка бюджета.xlsx', () => {
                    this.excelProccess = false;
                    this.hideLoader();
                },
                JSON.stringify(this.params.excelParams ?? {})
            );
            loader.load();
        },
    };
}) (window, BX);
