/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

var pw_sortable;
document.addEventListener('DOMContentLoaded', function() {
    pw_sortable = new PwSortable();
});


var PwSortable = function() {
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

    this.init = function() {
        pw_sortable.is_show_sortable = true;
        sort_order = 1;
        if ($(pw_sortable).attr('table-id')) pw_sortable.table_id = $(pw_sortable).attr('table-id');
        pw_sortable.selector = '#' + pw_sortable.table_id + ' tbody';
        pw_sortable.sortable_table_tr_selector = pw_sortable.selector + ' tr';
        pw_sortable.before_rows = $(pw_sortable.sortable_table_tr_selector);
        $.each(pw_sortable.before_rows, function(index, row) {
            $(row).attr('order', index);
        });
    }
    this.set = function(params) {
        if (params) {
            if (params.hasOwnProperty('table_id')) pw_sortable.table_id = params.table_id;
            if (params.hasOwnProperty('api_uri')) pw_sortable.api_uri = params.api_uri;
            if (params.hasOwnProperty('callback')) pw_sortable.callback = params.callback;
            if (params.hasOwnProperty('is_use_loading')) pw_sortable.is_use_loading = params.is_use_loading;
        }
    }
    this.reset = function(dom) {
        if (pw_sortable.before_rows) {
            $(pw_sortable.selector).html(pw_sortable.before_rows);
        }
        pw_sortable.close(dom);
    }
    this.edit = function(dom) {
        if (pw_sortable.is_show_sortable) return;

        this.init();

        $('.pw-sortable-control').show();

        $(pw_sortable.selector).sortable({
            cursor: 'move',
            opacity: 0.8,
            placeholder: 'sortable-selected',
            scroll: true,
            axis: 'y',
            delay: 100
        });
        $(pw_sortable.selector).sortable('enable');
        $(pw_sortable.selector).sortable({
            update: function(ev, ui) {
                pw_sortable.sort_orders = $(pw_sortable.selector).sortable('toArray', { attribute: pw_sortable.row_id_column} );
            }
        });
        pw_sortable.showControl(dom);
    }
    this.showControl = function(dom) {
        table_id = pw_sortable.table_id;
        $('.sortable-control').show();

        var header_selector = '#' + table_id + ' tr';
        var header_tr = $(header_selector).first();
        var sortable_control_header_tag = '<th class="sortable-control"></th>';
        if (header_tr) header_tr.prepend(sortable_control_header_tag);

        $(pw_sortable.sortable_table_tr_selector).map(function() {
            row_id = $(this).attr(pw_sortable.row_id_column);
            var sortable_control_tag = '<td class="sortable-control" nowrap="nowrap" row-id="' + row_id + '"></td>';
            $(this).prepend(sortable_control_tag);
        });

        var link_tag = '';
        link_tag+= '<a><i class="fa fa-align-justify"></i></a>';
        link_tag+= '<a class="btn btn-sm pw-lib" pw-lib="PwSortable" pw-action="top"><i class="fa fa-angle-double-up"></i></a>';
        link_tag+= '<a class="bottom btn btn-sm pw-lib" pw-lib="PwSortable" pw-action="bottom"><i class="fa fa-angle-double-down"></i></a>';
        $('td.sortable-control').html(link_tag);
        $('.sortable-control').show();
    }
    this.update_sort = function(dom) {
        if (!pw_sortable.sort_orders) return;
        
        var orders = [];
        var order = 0;
        pw_sortable.sort_orders.forEach(function(id) {
            if (id > 0) {
                order++;
                orders.push({id: id, order: order});
            }
        });

        var pw_dom = pw_app.dom({dom: dom});
        pw_app.postJson(
            {
                controller: pw_dom.controller(),
                action: pw_dom.action()
            },
            JSON.stringify(orders),
            {callback: callback, is_show_loading: true}
        );
    
        function callback(data) {
            pw_sortable.before_rows = null;
            pw_sortable.is_show_sortable = false;
            pw_sortable.close(dom);

            if (pw_sortable.callback) {
                pw_sortable.callback(data);
            }
        }
    }
    this.top = function(dom) {
        var first_tr = $(pw_sortable.sortable_table_tr_selector).first();
        if (first_tr) {
            var row = $(dom).closest('tr');
            row.insertBefore(first_tr);
            pw_sortable.sort_orders = $(pw_sortable.selector).sortable('toArray', { attribute: pw_sortable.row_id_column} );
        }
    }
    this.bottom = function(dom) {
        var last_tr = $(pw_sortable.sortable_table_tr_selector).last();
        if (last_tr) {
            var row = $(dom).closest('tr');
            row.insertAfter(last_tr);
            pw_sortable.sort_orders = $(pw_sortable.selector).sortable('toArray', { attribute: pw_sortable.row_id_column} );
        }
    }
    this.close = function(dom) {
        pw_sortable.is_show_sortable = false;
        $(pw_sortable.selector).sortable('disable');
        $('.pw-sortable-control').hide();
        $('.sortable-control').hide();
        $('.sortable-control').remove();
    }
}