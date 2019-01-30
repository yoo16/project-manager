class PwSortable {

    constructor(){}

    public init = function() {
        this.is_show_sortable = false;
        this.table_id = 'sortable-table';
        this.api_uri = '';
        this.selector = '';
        this.tr_selector = '';
        this.sort_orders;
        this.model_name = '';
        this.before_rows;
        this.row_id_column = 'row-id';
        this.callback;
        this.is_use_loading = true;
        this.sortable_table_tr_selector = '';

        this.selector = this.table_id + ' tbody';
        this.sortable_table_tr_selector = this.selector + ' tr';
        this.before_rows = document.getElementById(this.sortable_table_tr_selector);
        this.before_rows.forEach(function(index: number) {
            var row = this.before_rows[index];
            row.setAttribute('order', index);
        });
    }

    public set = function(params: {table_id: string, api_uri: string, callback: (data: string) => void, is_use_loading: boolean}) {
        if (params) {
            if (params.hasOwnProperty('table_id')) this.table_id = params.table_id;
            if (params.hasOwnProperty('api_uri')) this.api_uri = params.api_uri;
            if (params.hasOwnProperty('callback')) this.callback = params.callback;
            if (params.hasOwnProperty('is_use_loading')) this.is_use_loading = params.is_use_loading;
        }
    }

}