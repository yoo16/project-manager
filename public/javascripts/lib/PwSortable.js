/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

var PwSortable = function() {
    this.is_show_sortable = false;
    this.table_id = 'sortable-table';
    this.api_uri = '';
    this.selector = '';
    this.tr_selector = '';
    this.sort_orders;
    this.model_name = '';
    this.before_rows = [];
    this.row_id_column = 'row-id';
    this.callback;
    this.is_use_loading = true;
    this.drag_item;
    this.target_item;

    this.drag_element;
    this.init = function(node) {
        pw_sortable.is_show_sortable = true;
        sort_order = 1;
        this.loadTableId(node);
        pw_sortable.selector = '#' + pw_sortable.table_id + ' tbody';
        pw_sortable.sortable_table_tr_selector = pw_sortable.selector + ' tr';
        pw_sortable.before_rows = [];
        [].forEach.call(this.getElements(), function(element, index) {
            pw_sortable.before_rows.push(element);
            row_node = PwNode.byElement(element);
            if (row_node.attr('row-id')) {
                row_node.setAttr('order', index);
                row_node.setAttr('draggable', true);
            }
        });
        this.enableDrag();
    }
    this.enableDrag = function() {
        function handleDragStart(event) {
            this.style.opacity = '0.4';
            pw_sortable.drag_item = event.target;
            event.stopPropagation();
        }
        function handleDrag(event) {
        }
        function handleDragEnter(event) {
        }
        function handleDragOver(event) {
            if (event.preventDefault) {
                event.preventDefault();
            }
            event.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.
            return false;
        }
        function handleDragLeave(event) {
            var tr = event.target.closest('tr');
            if (tr && pw_sortable.drag_item != tr) {
                pw_sortable.target_item = tr;
            }
        }
        function handleDrop(event) {
        }
        function handleDragEnd(event) {
            event.stopPropagation();
            this.style.opacity = '1.0'
            if (pw_sortable.target_item && pw_sortable.drag_item != pw_sortable.target_item) {
                var tbody = PwNode.byQuery(pw_sortable.selector).first();
                //console.log(pw_sortable.target_item.previousSibling);
                //let prev_node = pw_sortable.target_item.previousSibling;
                tbody.insertBefore(pw_sortable.drag_item, pw_sortable.target_item);
            }
        }
        var cols = document.querySelectorAll(pw_sortable.sortable_table_tr_selector);
        [].forEach.call(cols, function(col) {
            col.addEventListener('dragstart', handleDragStart, false);
            col.addEventListener('drag', handleDrag, false);
            col.addEventListener('dragenter', handleDragEnter, false)
            col.addEventListener('dragover', handleDragOver, false);
            col.addEventListener('dragleave', handleDragLeave, false);
            col.addEventListener('drop', handleDrop, false);
            col.addEventListener('dragend', handleDragEnd, false);
        });
    }
    this.loadTableId = function(node) {
        if (node && node.attr('pw_sortable_table_id')) {
            pw_sortable.table_id = node.attr('pw_sortable_table_id');
        } else {
            pw_sortable.table_id = 'sortable-table';
        }
    }
    this.getElements = function() {
        let elements = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).elements;
        return elements;
    }
    this.reloadRowIds = function() {
        pw_sortable.sort_orders = [];
        [].forEach.call(this.getElements(), function(element) {
            let row_id = element.getAttribute(pw_sortable.row_id_column);
            pw_sortable.sort_orders.push(row_id);
        });
    }
    this.getOrders = function() {
        var orders = [];
        var order = 0;
        [].forEach.call(pw_sortable.sort_orders, function(id) {
            if (id > 0) {
                order++;
                orders.push({id: id, order: order});
            }
        });
        return orders;
    }
    this.set = function(params) {
        if (params) {
            if (params.hasOwnProperty('table_id')) pw_sortable.table_id = params.table_id;
            if (params.hasOwnProperty('api_uri')) pw_sortable.api_uri = params.api_uri;
            if (params.hasOwnProperty('callback')) pw_sortable.callback = params.callback;
            if (params.hasOwnProperty('is_use_loading')) pw_sortable.is_use_loading = params.is_use_loading;
        }
    }
    this.reset = function(node) {
        pw_sortable.close(node);
    }
    this.edit = function(node) {
        if (pw_sortable.is_show_sortable) return;
        this.init(node);
        PwNode.byClass('pw-sortable-control').show();
        pw_sortable.showControl(node);
    }
    this.update_sort = function(node) {
        this.reloadRowIds();
        if (!pw_sortable.sort_orders) return;
        pw_app.postJson( { controller: node.controller(), action: node.action() },
            JSON.stringify(this.getOrders()),
            {callback: callback, is_show_loading: true}
        );
        function callback(data) {
            pw_sortable.before_rows = [];
            pw_sortable.is_show_sortable = false;
            pw_sortable.close(node);
            if (pw_sortable.callback) pw_sortable.callback(data);
        }
    }
    this.close = function(node) {
        pw_sortable.is_show_sortable = false;
        PwNode.byClass('pw-sortable-control').hide();
        PwNode.byClass('sortable-control').remove();
        this.cursorChange('default');

        [].forEach.call(this.getElements(), function(element, index) {
            row_node = PwNode.byElement(element);
            if (row_node.attr('row-id')) { row_node.setAttr('draggable', false); }
        });
    }
    this.jqueryEdit = function() {
        //TODO remove jquery
        // $(pw_sortable.selector).sortable({
        //     cursor: 'move',
        //     opacity: 0.8,
        //     placeholder: 'sortable-selected',
        //     scroll: true,
        //     axis: 'y',
        //     delay: 100
        // });
        // $(pw_sortable.selector).sortable('enable');
        // $(pw_sortable.selector).sortable({
        //     update: function(ev, ui) {
        //         pw_sortable.reloadRowIds();
        //     }
        // });
    }
    this.jqueryClose = function() {
        //TODO remove jquery
        //$(pw_sortable.selector).sortable('disable');
    }
    this.cursorChange = function(value) {
        var tbody = PwNode.byQuery(pw_sortable.selector).first();
        tbody.style.cursor = value;
    }
    this.showControl = function(node) {
        PwNode.byClass('sortable-control').show();

        var header_selector = '#' + pw_sortable.table_id + ' tr';
        var header_tr_element = PwNode.byQuery(header_selector).first();
        var sortable_control_header_element = document.createElement('th')
        sortable_control_header_element.classList.add('sortable-control');
        header_tr_element.insertBefore(sortable_control_header_element, header_tr_element.children[0]);

        [].forEach.call(this.getElements(), function(element, index) {
            let tr = PwNode.byElement(element);
            if (row_id = tr.attr(pw_sortable.row_id_column)) {
                var sortable_control_element = document.createElement('td');
                sortable_control_element.classList.add('sortable-control');
                sortable_control_element.setAttribute('row_id', row_id);
                sortable_control_element.setAttribute('nowrap', 'nowrap');
                element.insertBefore(sortable_control_element, element.children[0]);
            }
        });

        var link_tag = '';
        link_tag+= '<a><i class="fa fa-align-justify"></i></a>';
        link_tag+= '<a class="btn btn-sm pw-click" pw-lib="PwSortable" pw-action="top"><i class="fa fa-angle-double-up pw-click" pw-lib="PwSortable" pw-action="top"></i></a>';
        link_tag+= '<a class="bottom btn btn-sm pw-click" pw-lib="PwSortable" pw-action="bottom"><i class="fa fa-angle-double-down pw-click" pw-lib="PwSortable" pw-action="bottom"></i></a>';

        PwNode.byQuery('td.sortable-control').html(link_tag);
    }
    this.top = function(node) {
        var tbody = PwNode.byQuery(pw_sortable.selector).first();
        var top_tr = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).first()
        var tr = node.element.closest('tr');
        tbody.insertBefore(tr, top_tr);

        pw_sortable.reloadRowIds();
    }
    this.bottom = function(node) {
        var tbody = PwNode.byQuery(pw_sortable.selector).first();
        var last_tr = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).last()
        var tr = node.element.closest('tr');
        tbody.insertBefore(tr, last_tr);

        pw_sortable.reloadRowIds();
    }
}

var pw_sortable = new PwSortable();
document.addEventListener('DOMContentLoaded', function() {
    
});
