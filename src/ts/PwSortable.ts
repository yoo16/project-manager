/**
 * PwSortable
 * 
 * ver 0.0.1
 * required PwNode.js
 * 
 * @author  Yohei Yoshikawa
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */
class PwSortable {
    static pw_row_id_column = 'pw_sortable_';

    public is_show_sortable = false;
    public table_id = 'sortable-table';
    public api_uri = '';
    public selector = '';
    public tr_selector = '';
    public sort_orders: any = [];
    public model_name = '';
    public before_rows: any = [];
    public row_id_column = 'row-id';
    public callback: any;
    public is_use_loading = true;
    public drag_item: any;
    public target_item: any;
    public drag_element: any;

    constructor (options:any){
        this.bindOptions(options);
    }
    public bindOptions = function(options:any) {
        pw_sortable.options = options;
        if (!options) return;
        Object.keys(options).forEach(function(key) {
            pw_sortable[key] = this[key];
        }, pw_sortable.options);
    }
    public init = function(node: PwNode) {
        pw_sortable.is_show_sortable = true;
        this.loadTableId(node);
        this.enableDrag();

        pw_sortable.is_show_sortable = true;
        pw_sortable.before_rows = [];
        pw_sortable.selector = '#' + pw_sortable.table_id + ' tbody';
        pw_sortable.sortable_table_tr_selector = pw_sortable.selector + ' tr';
        pw_sortable.before_rows = [];
        [].forEach.call(this.getElements(), function(element:HTMLFormElement, index:Number) {
            pw_sortable.before_rows.push(element);
            var row_node = PwNode.byElement(element);
            if (row_node.attr('row-id')) {
                row_node.setAttr('order', index);
                row_node.setAttr('draggable', true);
            }
        });
        this.enableDrag();
    }
    public enableDrag = function() {
        if (!this.table_id) return;
        var row_id;
        [].forEach.call(this.getElements(), function(element:any, index:Number) {
            pw_sortable.before_rows.push(element);
            var row_node = PwNode.byElement(element);
            if (row_id = row_node.attr('row-id')) {
                row_node.setAttr('id', pw_sortable.pw_row_id_column + row_id);
                row_node.setAttr('order', index);
                row_node.setAttr('draggable', true);
            }
        });
        function handleDragStart(event:any) {
            this.style.opacity = '0.4';
            let tr = event.target.closest('tr');
            if (tr) pw_sortable.drag_item = event.target.closest('tr');
            event.stopPropagation();
        }
        function handleDrag(event:any) {
        }
        function handleDragEnter(event:any) {
        }
        function handleDragOver(event:any) {
            if (event.preventDefault) event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            return false;
        }
        function handleDragLeave(event:any) {
            var tr = event.target.closest('tr');
            if (tr && pw_sortable.drag_item != tr) {
                pw_sortable.target_item = tr;
            }
        }
        function handleDrop(event:any) {
        }
        function handleDragEnd(event:any) {
            event.stopPropagation();
            this.style.opacity = '1.0'
            let row_id = pw_sortable.drag_item.getAttribute('row-id');
            if (row_id && pw_sortable.target_item && pw_sortable.drag_item != pw_sortable.target_item) {
                var tbody = PwNode.byQuery(pw_sortable.body_selector).first();
                let drag_order = pw_sortable.drag_item.getAttribute('order');
                let target_order = pw_sortable.target_item.getAttribute('order');
                if (drag_order > target_order) {
                    tbody.insertBefore(pw_sortable.drag_item, pw_sortable.target_item);
                } else if (drag_order < target_order) {
                    tbody.insertBefore(pw_sortable.drag_item, pw_sortable.target_item.nextElementSibling);
                }
                pw_sortable.reloadRowIds();
            }
        }
        function addEvents() {
            let elements = document.querySelectorAll(pw_sortable.sortable_tr_selector);
            [].forEach.call(elements, function(element:any) {
                element.addEventListener('dragstart', handleDragStart, false);
                element.addEventListener('drag', handleDrag, false);
                element.addEventListener('dragenter', handleDragEnter, false)
                element.addEventListener('dragover', handleDragOver, false);
                element.addEventListener('dragleave', handleDragLeave, false);
                element.addEventListener('drop', handleDrop, false);
                element.addEventListener('dragend', handleDragEnd, false);
            });
        }
        addEvents();
    }
    public loadTableId = function(node:PwNode) {
        if (node && node.attr('pw_sortable_table_id')) {
            pw_sortable.table_id = node.attr('pw_sortable_table_id');
        } else {
            pw_sortable.table_id = 'sortable-table';
        }
    }
    public getElements = function() {
        let elements = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).elements;
        return elements;
    }
    public reloadRowIds = function() {
        pw_sortable.sort_orders = [];
        [].forEach.call(this.getElements(), function(element:HTMLElement) {
            let row_id = element.getAttribute(pw_sortable.row_id_column);
            pw_sortable.sort_orders.push(row_id);
        });
    }
    public getOrders = function() {
        var orders:Array<Object> = [];
        var order = 0;
        [].forEach.call(pw_sortable.sort_orders, function(id:Number) {
            if (id > 0) {
                order++;
                orders.push({id: id, order: order});
            }
        });
        return orders;
    }
    public set = function(params:any) {
        if (params) {
            if (params.hasOwnProperty('table_id')) pw_sortable.table_id = params.table_id;
            if (params.hasOwnProperty('api_uri')) pw_sortable.api_uri = params.api_uri;
            if (params.hasOwnProperty('callback')) pw_sortable.callback = params.callback;
            if (params.hasOwnProperty('is_use_loading')) pw_sortable.is_use_loading = params.is_use_loading;
        }
    }
    public reset = function(node:PwNode) {
        pw_sortable.close(node);
    }
    public edit = function(node:PwNode) {
        if (pw_sortable.is_show_sortable) return;
        this.init(node);
        PwNode.byClass('pw-sortable-control').show();
        pw_sortable.showControl(node);
    }
    public update_sort = function(node:PwNode) {
        this.reloadRowIds();
        if (!pw_sortable.sort_orders) return;
        pw_app.postJson( { controller: node.controller(), action: node.action() },
            JSON.stringify(this.getOrders()),
            {callback: callback, is_show_loading: true}
        );
        function callback(data:any) {
            pw_sortable.before_rows = [];
            pw_sortable.is_show_sortable = false;
            pw_sortable.close(node);
            if (pw_sortable.callback) pw_sortable.callback(data);
        }
    }
    public close = function(node:PwNode) {
        pw_sortable.is_show_sortable = false;
        PwNode.byClass('pw-sortable-control').hide();
        PwNode.byClass('sortable-control').remove();
        this.cursorChange('default');

        [].forEach.call(this.getElements(), function(element:HTMLElement, index:Number) {
            var row_node:PwNode = PwNode.byElement(element);
            if (row_node.attr('row-id')) { row_node.setAttr('draggable', false); }
        });
    }
    public cursorChange = function(value:any) {
        var tbody = PwNode.byQuery(pw_sortable.selector).first();
        tbody.style.cursor = value;
    }
    public showControl = function(node:PwNode) {
        PwNode.byClass('sortable-control').show();

        var header_selector = '#' + pw_sortable.table_id + ' tr';
        var header_tr_element = PwNode.byQuery(header_selector).first();
        var sortable_control_header_element = document.createElement('th')
        sortable_control_header_element.classList.add('sortable-control');
        header_tr_element.insertBefore(sortable_control_header_element, header_tr_element.children[0]);

        [].forEach.call(this.getElements(), function(element:HTMLElement, index:Number) {
            let tr = PwNode.byElement(element);
            var row_id:any;
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
    public top = function(node:PwNode) {
        var tbody = PwNode.byQuery(pw_sortable.selector).first();
        var top_tr = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).first()
        var tr = node.element.closest('tr');
        tbody.insertBefore(tr, top_tr);

        pw_sortable.reloadRowIds();
    }
    public bottom = function(node:PwNode) {
        var tbody = PwNode.byQuery(pw_sortable.selector).first();
        var last_tr = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).last()
        var tr = node.element.closest('tr');
        tbody.insertBefore(tr, last_tr);

        pw_sortable.reloadRowIds();
    }
}
var pw_sortable = new PwSortable();