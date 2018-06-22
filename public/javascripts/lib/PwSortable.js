/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (http://yoo-s.com/)
 */

var pw_sortable;
$(document).ready(function(){
    pw_sortable = new PwSortable();
});

var PwSortable = function() {
    this.is_show_sortable = false;
    this.sortable_table_id = '';
    this.sortable_selector = '';
    this.sortable_table_tr_selector = '';
    this.sort_orders = {};
    this.model_name = '';
    this.before_rows;

    this.init = function() {
        pw_sortable.is_show_sortable = true;
        sort_order = 1;
        pw_sortable.sortable_table_id = $(pw_sortable).attr('table-id');
        if (!pw_sortable.sortable_table_id) pw_sortable.sortable_table_id = 'sortable-table';
        pw_sortable.sortable_selector = '#' + pw_sortable.sortable_table_id + ' tbody';
        pw_sortable.sortable_table_tr_selector = pw_sortable.sortable_selector + ' tr';

        pw_sortable.before_rows = $(pw_sortable.sortable_table_tr_selector);
        $.each(pw_sortable.before_rows, function(index, row) {
            $(row).attr('order', index);
        });
    }
    this.reset = function(dom) {
        if (pw_sortable.before_rows) {
            $(pw_sortable.sortable_selector).html(pw_sortable.before_rows);
        }
        pw_sortable.close(dom);
    }
    this.edit = function(dom) {
        if (pw_sortable.is_show_sortable) return;

        this.init();

        $('.pw-sortable-control').show();

        $(pw_sortable.sortable_selector).sortable({
            cursor: 'move',
            opacity: 0.8,
            placeholder: 'sortable-selected',
            scroll: true,
            axis: 'y',
            delay: 100
        });
        $(pw_sortable.sortable_selector).sortable('enable');
        $(pw_sortable.sortable_selector).sortable({
            update: function(ev, ui) {
                pw_sortable.sort_orders = $(pw_sortable.sortable_selector).sortable('toArray');
            }
        });
        pw_sortable.before_orders = $(pw_sortable.sortable_selector).sortable('toArray');
        pw_sortable.showControl(dom);
    }
    this.showControl = function(dom) {
        table_id = pw_sortable.sortable_table_id;
        $('.sortable-control').show();

        var header_selector = '#' + table_id + ' tr';
        var header_tr = $(header_selector).first();

        var sortable_control_header_tag = '<th class="sortable-control"></th>';
        if (header_tr) header_tr.prepend(sortable_control_header_tag);

        $(pw_sortable.sortable_table_tr_selector).map(function() {
            row_id = $(this).attr('id');
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
        
        var params = {};
        var sort_order = [];
        $.each(pw_sortable.sort_orders, function(index, id) {
            sort_order.push({id: id, order: index});
        });
        params.sort_order = sort_order;
        //params.sort_order = pw_sortable.sort_orders;

        var table_id = '#' + pw_sortable.sortable_table_id;
        if ($(table_id).attr('model-name')) {
            params.model_name = $(table_id).attr('model-name');
        }

        showLoading();
        pw_app.post(dom, params, callback);
    
        function callback(data) {
            pw_sortable.before_rows = null;
            pw_sortable.is_show_sortable = false;
            pw_sortable.close(dom);
            hideLoading();
        }
    }
    this.top = function(dom) {
        var first_tr = $(pw_sortable.sortable_table_tr_selector).first();
        if (first_tr) {
            var row = $(dom).closest('tr');
            row.insertBefore(first_tr);
            pw_sortable.sort_orders = $(pw_sortable.sortable_selector).sortable('toArray');
        }
    }
    this.bottom = function(dom) {
        var last_tr = $(pw_sortable.sortable_table_tr_selector).last();
        if (last_tr) {
            var row = $(dom).closest('tr');
            row.insertAfter(last_tr);
            pw_sortable.sort_orders = $(pw_sortable.sortable_selector).sortable('toArray');
        }
    }
    this.close = function(dom) {
        pw_sortable.is_show_sortable = false;
        $(pw_sortable.sortable_selector).sortable('disable');
        $('.pw-sortable-control').hide();
        $('.sortable-control').hide();
        $('.sortable-control').remove();
    }
}
