'use strict';
var PwNode = (function () {
    function PwNode(params) {
        this.controller_column = 'pw-controller';
        this.action_column = 'pw-action';
        this.function_column = 'pw-function';
        this.init = function () {
            if (this.params.id) {
                this.element = document.getElementById(this.params.id);
            }
            else if (this.params.element) {
                this.element = this.params.element;
            }
            if (this.params.name) {
                this.setName(this.params.name);
                this.node_list = document.getElementsByName(this.params.name);
            }
            if (this.params.class_name) {
                this.setClassName(this.params.class_name);
                this.node_list = document.getElementsByClassName(this.params.class_name);
                this.html_collection = document.getElementsByClassName(this.params.class_name);
                this.elements = [].slice.call(this.html_collection);
            }
            if (this.params.query) {
                this.elements = document.querySelectorAll(this.params.query);
            }
            return this;
        };
        this.setValue = function (value) {
            if (this.element) {
                this.element.value = value;
            }
            return this;
        };
        this.getValue = function () {
            return this.value();
        };
        this.value = function () {
            if (this.element)
                return this.element.value;
        };
        this.setValues = function (value) {
            var elements;
            if (this.node_list) {
                elements = this.node_list;
            }
            else if (this.elements) {
                elements = this.elements;
            }
            if (elements)
                [].forEach.call(elements, function (element) {
                    element.value = value;
                });
        };
        this.first = function () {
            if (this.elements)
                return this.elements[0];
        };
        this.last = function () {
            if (this.elements) {
                var index = this.length - 1;
                if (index <= 0)
                    index = 0;
                return this.elements[index];
            }
        };
        this.name = function () {
            if (this.name)
                return this.element.name;
        };
        this.setName = function (name) {
            if (name)
                this.name = name;
        };
        this.setClassName = function (name) {
            if (name)
                this.class_name = name;
        };
        this.selected = function () {
            if (this.element)
                return this.element.value;
        };
        this.check = function (is_checked) {
            if (is_checked) {
                this.element.checked = 1;
            }
            else {
                this.element.checked = null;
            }
        };
        this.checked = function () {
            var elements;
            if (this.node_list) {
                elements = this.node_list;
            }
            else if (this.elements) {
                elements = this.elements;
            }
            if (elements) {
                var checked = null;
                [].forEach.call(elements, function (element) {
                    if (element.checked)
                        return checked = element.value;
                });
                return checked;
            }
            else if (this.element) {
                return this.element.checked;
            }
        };
        this.toggleCheckAll = function (is_checked) {
            if (is_checked) {
                this.checkAll();
            }
            else {
                this.uncheckAll();
            }
        };
        this.checkAll = function () {
            var elements = null;
            if (this.node_list)
                elements = this.node_list;
            if (this.elements)
                elements = this.elements;
            if (elements)
                elements.forEach(function (element) { element.checked = 1; });
        };
        this.html = function (html) {
            if (this.element)
                return this.element.innerHTML = html;
            if (this.elements)
                [].forEach.call(this.elements, function (element) { element.innerHTML = html; });
        };
        this.setAttr = function (key, value) {
            if (this.element) {
                var node = PwNode.byElement(this.element);
                node.element.setAttribute(key, value);
                this.element.setAttribute(key, value);
                return this;
            }
        };
        this.getAttr = function (selector) {
            return this.attr(selector);
        };
        this.attr = function (selector) {
            if (!selector)
                return;
            if (this.element)
                return this.element.getAttribute(selector);
        };
        this.bind = function (params) {
            if (!params)
                return;
            if (this.element) {
                Object.keys(params).map(function (key) {
                    this.element.setAttribute(key, params[key]);
                });
            }
        };
        this.parent = function () {
            if (this.element && this.element.parentNode) {
                return PwNode.byElement(this.element.parentNode);
            }
        };
        this.remove = function () {
            if (this.element)
                this.element.remove();
            if (this.elements) {
                [].forEach.call(this.elements, function (element) {
                    element.parentNode.removeChild(element);
                });
            }
        };
        this.show = function () {
            if (this.element)
                PwNode.setDisplay(this.element, '');
            if (this.elements)
                [].forEach.call(this.elements, function (element) {
                    this.setDisplay(element, '');
                });
        };
        this.hide = function () {
            if (this.element)
                PwNode.setDisplay(this.element, 'none');
            if (this.elements)
                [].forEach.call(this.elements, function (element) {
                    this.setDisplay(element, 'none');
                });
        };
        this.disabled = function () {
            if (this.element)
                PwNode.setDisabled(this.element.disabled, true);
            if (this.elements)
                [].forEach.call(this.elements, function (element) {
                    this.setDisabled(element.disabled, true);
                });
        };
        this.abled = function () {
            if (this.element)
                PwNode.setDisabled(this.element.disabled, false);
            if (this.elements)
                [].forEach.call(this.elements, function (element) {
                    this.setDisabled(element.disabled, false);
                });
        };
        this.getID = function () {
            if (this.element)
                return this.element.id;
        };
        this.toggle = function (class_name) {
            if (!class_name)
                return;
            if (this.element) {
                this.element.classList.toggle(class_name);
                return this;
            }
        };
        this.controller = function () {
            if (this.element)
                return this.attr(this.controller_column);
        };
        this.functionName = function () {
            if (this.element)
                return this.attr(this.function_column);
        };
        this.action = function () {
            if (this.element)
                return this.attr(this.action_column);
        };
        this.controllerClassName = function () {
            if (!this.dom)
                return;
            var controller_name = this.controller();
            if (!controller_name)
                return;
            var class_name = '';
            var names = controller_name.split('_');
            if (!names)
                return;
            names.forEach(function (name) {
                class_name += PwNode.upperTopString(name);
            });
            class_name += 'Controller';
            return class_name;
        };
        this.params = params;
    }
    PwNode.setDisplay = function (element, value) {
        element.style.display = value;
    };
    PwNode.setDisabled = function (element, value) {
        element.style.display = value;
    };
    PwNode.instance = function (params) {
        var instance = new PwNode(params);
        return instance;
    };
    PwNode["new"] = function (params) {
        var instance = new PwNode(params);
        return instance;
    };
    PwNode.id = function (id) {
        if (!id)
            return;
        var instance = new PwNode({ id: id });
        instance.init();
        return instance;
    };
    PwNode.byName = function (name) {
        var instance = new PwNode({ name: name });
        instance.init();
        return instance;
    };
    PwNode.byClass = function (class_name) {
        var instance = new PwNode({ class_name: class_name });
        instance.init();
        return instance;
    };
    PwNode.byElement = function (element) {
        var instance = new PwNode({ element: element });
        instance.init();
        return instance;
    };
    PwNode.byQuery = function (query) {
        var instance = new PwNode({ query: query });
        instance.init();
        return instance;
        ;
    };
    PwNode.upperTopString = function (value) {
        if (!value)
            return;
        var value = value.charAt(0).toUpperCase() + value.slice(1);
        value = value.substring(0, 1).toUpperCase() + value.substring(1);
        value = value.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    };
    return PwNode;
}());
var PwUI = (function () {
    function PwUI() {
        this.delete_file_window_name = 'delete-file-window';
        this.error_window_name = 'pw-error';
        this.popup_name = 'pw-popup';
        this.confirm_dialog_name = 'confirm-dialog';
        this.confirm_delete_name = 'confirm-delete';
        this.delete_checkbox_name = 'delete_checkbox';
        this.jqueryId = function (id) {
            if (id) {
                var start = id.slice(0, 1);
                if (start != '#')
                    id = '#' + id;
            }
            return id;
        };
        this.showModal = function (selector) {
            selector = this.jqueryId(selector);
        };
        this.hideModal = function (selector) {
            selector = this.jqueryId(selector);
        };
        this.openWindow = function (url, params) {
            var queryArray = [];
            [].forEach.call(params, function (key) {
                queryArray.push(key + '=' + params[key]);
            });
            var query = queryArray.join(',');
            if (url)
                window.open(url, 'new', query);
        };
        this.deleteConfirmHandler = function (event) {
            var delete_id = this.getAttribute('delete_id');
            if (!delete_id)
                return;
            PwNode.id('from_delete_id').setValue(delete_id);
            pw_ui.showModal('delete-window');
        };
        this.reloadDeleteConfirm = function () {
            var elements = document.getElementsByClassName(pw_ui.confirm_delete_name);
            [].forEach.call(elements, function (element) {
                element.addEventListener('click', pw_ui.deleteConfirmHandler, false);
            });
        };
        this.popupEventHandler = function (event) {
            var window_name = '_blank';
            var window_option = null;
            if (this.getAttribute('window_name'))
                window_name = this.getAttribute('window_name');
            if (this.getAttribute('window_option'))
                window_option = this.getAttribute('window_option');
            window.open(this.href, window_name, window_option).focus();
            event.preventDefault();
            event.stopPropagation();
        };
        this.reloadPopupEvent = function () {
            var elements = document.getElementsByClassName(pw_ui.popup_name);
            [].forEach.call(elements, function (element) {
                element.addEventListener('click', pw_ui.popupEventHandler, false);
            });
        };
        this.confirmDialogHandler = function (event) {
            var message = '';
            if (this.getAttribute('message'))
                message = this.getAttribute('message');
            pw_ui.reloadConfirmDialogEvent();
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        };
        this.reloadConfirmDialogEvent = function () {
            var elements = document.getElementsByClassName(pw_ui.confirm_dialog_name);
            [].forEach.call(elements, function (element) {
                element.addEventListener('click', pw_ui.confirmDialogHandler, false);
            });
        };
        this.deleteCheckbox = function () {
            var elements = document.getElementsByClassName(pw_ui.delete_checkbox_name);
            [].forEach.call(elements, function (element) {
                element.addEventListener('change', function (event) {
                    var pw_node = PwNode.byElement(element);
                    var delete_link_node = PwNode.id('delete_link');
                    var is_checked = pw_node.checked();
                    if (is_checked) {
                        delete_link_node.abled();
                    }
                    else {
                        delete_link_node.disabled();
                    }
                });
            });
        };
    }
    return PwUI;
}());
var pw_ui = new PwUI();
document.addEventListener('DOMContentLoaded', function () {
    pw_ui.deleteCheckbox();
    pw_ui.reloadDeleteConfirm();
    pw_ui.reloadConfirmDialogEvent();
    pw_ui.reloadPopupEvent();
});
var PwSortable = (function () {
    function PwSortable(options) {
        this.is_show_sortable = false;
        this.table_id = 'sortable-table';
        this.api_uri = '';
        this.selector = '';
        this.tr_selector = '';
        this.sort_orders = [];
        this.model_name = '';
        this.before_rows = [];
        this.row_id_column = 'row-id';
        this.is_use_loading = true;
        this.bindOptions = function (options) {
            pw_sortable.options = options;
            if (!options)
                return;
            Object.keys(options).forEach(function (key) {
                pw_sortable[key] = this[key];
            }, pw_sortable.options);
        };
        this.init = function (node) {
            pw_sortable.is_show_sortable = true;
            this.loadTableId(node);
            this.enableDrag();
            pw_sortable.is_show_sortable = true;
            pw_sortable.before_rows = [];
            pw_sortable.selector = '#' + pw_sortable.table_id + ' tbody';
            pw_sortable.sortable_table_tr_selector = pw_sortable.selector + ' tr';
            pw_sortable.before_rows = [];
            [].forEach.call(this.getElements(), function (element, index) {
                pw_sortable.before_rows.push(element);
                var row_node = PwNode.byElement(element);
                if (row_node.attr('row-id')) {
                    row_node.setAttr('order', index);
                    row_node.setAttr('draggable', true);
                }
            });
            this.enableDrag();
        };
        this.enableDrag = function () {
            if (!this.table_id)
                return;
            var row_id;
            [].forEach.call(this.getElements(), function (element, index) {
                pw_sortable.before_rows.push(element);
                var row_node = PwNode.byElement(element);
                if (row_id = row_node.attr('row-id')) {
                    row_node.setAttr('id', pw_sortable.pw_row_id_column + row_id);
                    row_node.setAttr('order', index);
                    row_node.setAttr('draggable', true);
                }
            });
            function handleDragStart(event) {
                this.style.opacity = '0.4';
                var tr = event.target.closest('tr');
                if (tr)
                    pw_sortable.drag_item = event.target.closest('tr');
                event.stopPropagation();
            }
            function handleDrag(event) {
            }
            function handleDragEnter(event) {
            }
            function handleDragOver(event) {
                if (event.preventDefault)
                    event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
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
                this.style.opacity = '1.0';
                var row_id = pw_sortable.drag_item.getAttribute('row-id');
                if (row_id && pw_sortable.target_item && pw_sortable.drag_item != pw_sortable.target_item) {
                    var tbody = PwNode.byQuery(pw_sortable.body_selector).first();
                    var drag_order = pw_sortable.drag_item.getAttribute('order');
                    var target_order = pw_sortable.target_item.getAttribute('order');
                    if (drag_order > target_order) {
                        tbody.insertBefore(pw_sortable.drag_item, pw_sortable.target_item);
                    }
                    else if (drag_order < target_order) {
                        tbody.insertBefore(pw_sortable.drag_item, pw_sortable.target_item.nextElementSibling);
                    }
                    pw_sortable.reloadRowIds();
                }
            }
            function addEvents() {
                var elements = document.querySelectorAll(pw_sortable.sortable_tr_selector);
                [].forEach.call(elements, function (element) {
                    element.addEventListener('dragstart', handleDragStart, false);
                    element.addEventListener('drag', handleDrag, false);
                    element.addEventListener('dragenter', handleDragEnter, false);
                    element.addEventListener('dragover', handleDragOver, false);
                    element.addEventListener('dragleave', handleDragLeave, false);
                    element.addEventListener('drop', handleDrop, false);
                    element.addEventListener('dragend', handleDragEnd, false);
                });
            }
            addEvents();
        };
        this.loadTableId = function (node) {
            if (node && node.attr('pw_sortable_table_id')) {
                pw_sortable.table_id = node.attr('pw_sortable_table_id');
            }
            else {
                pw_sortable.table_id = 'sortable-table';
            }
        };
        this.getElements = function () {
            var elements = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).elements;
            return elements;
        };
        this.reloadRowIds = function () {
            pw_sortable.sort_orders = [];
            [].forEach.call(this.getElements(), function (element) {
                var row_id = element.getAttribute(pw_sortable.row_id_column);
                pw_sortable.sort_orders.push(row_id);
            });
        };
        this.getOrders = function () {
            var orders = [];
            var order = 0;
            [].forEach.call(pw_sortable.sort_orders, function (id) {
                if (id > 0) {
                    order++;
                    orders.push({ id: id, order: order });
                }
            });
            return orders;
        };
        this.set = function (params) {
            if (params) {
                if (params.hasOwnProperty('table_id'))
                    pw_sortable.table_id = params.table_id;
                if (params.hasOwnProperty('api_uri'))
                    pw_sortable.api_uri = params.api_uri;
                if (params.hasOwnProperty('callback'))
                    pw_sortable.callback = params.callback;
                if (params.hasOwnProperty('is_use_loading'))
                    pw_sortable.is_use_loading = params.is_use_loading;
            }
        };
        this.reset = function (node) {
            pw_sortable.close(node);
        };
        this.edit = function (node) {
            if (pw_sortable.is_show_sortable)
                return;
            this.init(node);
            PwNode.byClass('pw-sortable-control').show();
            pw_sortable.showControl(node);
        };
        this.update_sort = function (node) {
            this.reloadRowIds();
            if (!pw_sortable.sort_orders)
                return;
            pw_app.postJson({ controller: node.controller(), action: node.action() }, JSON.stringify(this.getOrders()), { callback: callback, is_show_loading: true });
            function callback(data) {
                pw_sortable.before_rows = [];
                pw_sortable.is_show_sortable = false;
                pw_sortable.close(node);
                if (pw_sortable.callback)
                    pw_sortable.callback(data);
            }
        };
        this.close = function (node) {
            pw_sortable.is_show_sortable = false;
            PwNode.byClass('pw-sortable-control').hide();
            PwNode.byClass('sortable-control').remove();
            this.cursorChange('default');
            [].forEach.call(this.getElements(), function (element, index) {
                var row_node = PwNode.byElement(element);
                if (row_node.attr('row-id')) {
                    row_node.setAttr('draggable', false);
                }
            });
        };
        this.cursorChange = function (value) {
            var tbody = PwNode.byQuery(pw_sortable.selector).first();
            tbody.style.cursor = value;
        };
        this.showControl = function (node) {
            PwNode.byClass('sortable-control').show();
            var header_selector = '#' + pw_sortable.table_id + ' tr';
            var header_tr_element = PwNode.byQuery(header_selector).first();
            var sortable_control_header_element = document.createElement('th');
            sortable_control_header_element.classList.add('sortable-control');
            header_tr_element.insertBefore(sortable_control_header_element, header_tr_element.children[0]);
            [].forEach.call(this.getElements(), function (element, index) {
                var tr = PwNode.byElement(element);
                var row_id;
                if (row_id = tr.attr(pw_sortable.row_id_column)) {
                    var sortable_control_element = document.createElement('td');
                    sortable_control_element.classList.add('sortable-control');
                    sortable_control_element.setAttribute('row_id', row_id);
                    sortable_control_element.setAttribute('nowrap', 'nowrap');
                    element.insertBefore(sortable_control_element, element.children[0]);
                }
            });
            var link_tag = '';
            link_tag += '<a><i class="fa fa-align-justify"></i></a>';
            link_tag += '<a class="btn btn-sm pw-click" pw-lib="PwSortable" pw-action="top"><i class="fa fa-angle-double-up pw-click" pw-lib="PwSortable" pw-action="top"></i></a>';
            link_tag += '<a class="bottom btn btn-sm pw-click" pw-lib="PwSortable" pw-action="bottom"><i class="fa fa-angle-double-down pw-click" pw-lib="PwSortable" pw-action="bottom"></i></a>';
            PwNode.byQuery('td.sortable-control').html(link_tag);
        };
        this.top = function (node) {
            var tbody = PwNode.byQuery(pw_sortable.selector).first();
            var top_tr = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).first();
            var tr = node.element.closest('tr');
            tbody.insertBefore(tr, top_tr);
            pw_sortable.reloadRowIds();
        };
        this.bottom = function (node) {
            var tbody = PwNode.byQuery(pw_sortable.selector).first();
            var last_tr = PwNode.byQuery(pw_sortable.sortable_table_tr_selector).last();
            var tr = node.element.closest('tr');
            tbody.insertBefore(tr, last_tr);
            pw_sortable.reloadRowIds();
        };
        this.bindOptions(options);
    }
    PwSortable.pw_row_id_column = 'pw_sortable_';
    return PwSortable;
}());
var pw_sortable = new PwSortable();
var PwForm = (function () {
    function PwForm() {
        this.value = {};
        this.init = function (selector_id) {
            this.initInput(selector_id);
            this.initSelect(selector_id);
            this.initTextarea(selector_id);
        };
        this.initInput = function (selector_id) {
            var selector = selector_id + ' input';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var type = node.attr('type');
                var name = node.attr('name');
                var default_value = node.attr('default_value');
                if (name) {
                    if (type == 'checkbox') {
                        node.setValue('');
                        if (default_value)
                            node.setValue(default_value);
                    }
                    else if (type == 'radio') {
                        node.setValue('');
                        if (default_value)
                            node.setValue(default_value);
                    }
                    else {
                        node.setValue('');
                        if (default_value)
                            node.setValue(default_value);
                    }
                }
            });
        };
        this.initSelect = function (selector_id) {
            var selector = selector_id + ' select option';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var name = node.parent().attr('name');
                var default_value = element.attr('default_value');
                if (name) {
                    node.parent().setValue('');
                    if (default_value)
                        node.parent().setValue(default_value);
                }
            });
        };
        this.initTextarea = function (selector_id) {
            var selector = selector_id + ' textarea';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var name = node.attr('name');
                var default_value = node.attr('default_value');
                if (name) {
                    node.setValue('');
                    if (default_value)
                        node.setValue(default_value);
                }
            });
        };
        this.bind = function (selector_id, values) {
            this.init(selector_id);
            this.bindInput(selector_id, values);
            this.bindSelect(selector_id, values);
            this.bindTextarea(selector_id, values);
        };
        this.bindInput = function (selector_id, values) {
            var selector = selector_id + ' input';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var type = node.attr('type');
                var name = node.attr('name');
                name = this.checkName(name);
                if (name && values[name]) {
                    var value = values[name];
                    if (type == 'checkbox') {
                    }
                    else if (type == 'radio') {
                        node.setValue(value);
                    }
                    else {
                        node.setValue(value);
                    }
                }
            });
        };
        this.bindSelect = function (selector_id, values) {
            var selector = selector_id + ' select option';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var name = node.parent().attr('name');
                name = this.checkName(name);
                if (name && values[name]) {
                    node.parent().setValue(values[name]);
                }
            });
        };
        this.bindTextarea = function (selector_id, values) {
            var selector = selector_id + ' textarea';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var name = node.attr('name');
                name = this.checkName(name);
                if (name && values[name]) {
                    node.setValue(values[name]);
                }
            });
        };
        this.loadForm = function (selector_id) {
            this.loadInput(selector_id);
            this.loadSelect(selector_id);
            this.loadTextarea(selector_id);
        };
        this.loadInput = function (selector_id) {
            var selector = selector_id + ' input';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var type = node.attr('type');
                var name = node.attr('name');
                if (name) {
                    if (type == 'checkbox') {
                        pw_form.value[name] = pw_form.checkboxValues(name);
                    }
                    else if (type == 'radio') {
                        var checked = node.checked();
                        if (checked)
                            pw_form.value[name] = node.value();
                    }
                    else {
                        pw_form.value[name] = node.value();
                    }
                }
            });
        };
        this.loadSelect = function (selector_id) {
            var selector = selector_id + ' select option';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var name = node.parent().attr('name');
                if (name)
                    pw_form.value[name] = node.value();
            });
        };
        this.loadTextarea = function (selector_id) {
            var selector = selector_id + ' textarea';
            var elements = PwNode.byQuery(selector).elements;
            if (!elements)
                return;
            [].forEach(elements, function (element) {
                var node = PwNode.byElement(element);
                var name = node.attr('name');
                if (name)
                    pw_form.value[name] = node.value();
            });
        };
    }
    PwForm.prototype.checkName = function (name) {
        if (!name)
            return;
        var names;
        if (name.indexOf('[') != -1) {
            names = name.split('[');
            name = names[1];
        }
        if (name.indexOf(']') != -1) {
            names = name.split(']');
            name = names[0];
        }
        return name;
    };
    PwForm.prototype.checkboxValues = function (name) {
        var selector = '[name="' + name + '"]:checked';
        var elements = PwNode.byQuery(selector).elements;
        if (!elements)
            return;
        var checks = [];
        [].forEach(elements, function (element) {
            var node = PwNode.byElement(element);
            var checked = node.checked();
            if (checked) {
                checks.push(node.value());
            }
        });
        return checks;
    };
    PwForm.getByName = function (name) {
        var elements = document.forms[name].elements;
        if (!elements)
            return;
        var length = elements.length;
        var model_name = '';
        var values;
        for (var i = 0; i <= length; i++) {
            var element = PwNode.byElement(elements[i]);
            if (model_name = element.attr('pw-model')) {
                var name = element.name();
                values[name] = element.value();
            }
        }
        return values;
    };
    return PwForm;
}());
var pw_form = new PwForm();
document.addEventListener('DOMContentLoaded', function () {
});
var PwApp = (function () {
    function PwApp() {
        this.pw_current_controller = '';
        this.pw_current_action = '';
        this.pw_loading_selector = 'main';
        this.init = function () {
        };
        this.isIE = function () {
            var userAgent = window.navigator.userAgent.toLowerCase();
            if (userAgent.match(/(msie|MSIE)/) || userAgent.match(/(T|t)rident/))
                return true;
            return false;
        };
        this.ieVersion = function () {
            var userAgent = window.navigator.userAgent.toLowerCase();
            if (this.isIE())
                return userAgent.match(/((msie|MSIE)\s|rv:)([\d\.]+)/)[3];
            return '';
        };
        this.isEdge = function () {
            var userAgent = window.navigator.userAgent.toLowerCase();
            if (userAgent.indexOf('edge') != -1) {
                return true;
            }
        };
        this.currentController = function () {
            return PwNode.id('pw-current-controller').value();
        };
        this.dom = function (params) {
            var instance = new PwNode(params);
            instance.init();
            return instance;
        };
        this.urlQuery = function (params) {
            return this.query(params);
        };
        this.urlFor = function (params, options) {
            var url_queries = [];
            if (params.controller)
                url_queries.push(params.controller);
            if (params.action)
                url_queries.push(params.action);
            if (params.id)
                url_queries.push(params.id);
            var url_query = url_queries.join('/');
            var url = PwApp.projectUrl() + url_query;
            if (options)
                url = url + '?' + this.query(options);
            return url;
        };
        this.headerGet = function (body) {
            var header = {
                method: 'GET',
                headers: { 'Content-Type': 'application/xhtml+xml' },
                credentials: 'include',
                body: body,
                mode: 'cors',
                cache: 'default'
            };
            return header;
        };
        this.headerPostValues = function (values) {
            var header = {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'include',
                body: this.query(values),
                mode: 'cors',
                cache: 'default'
            };
            return header;
        };
        this.headerPostJson = function (json) {
            var header = {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'include',
                body: json,
                mode: 'cors',
                cache: 'default'
            };
            return header;
        };
        this.getHtml = function (params, values, options) {
            var url = this.urlFor(params, values);
            if (this.isIE()) {
                this.ajaxGet(url, values, options);
            }
            else {
                this.fetchRequest(url, this.headerGet(), options);
            }
        };
        this.post = function (node, values, callback) {
            var params = {
                controller: node.controller(),
                action: node.action()
            };
            if (pw_multi_sid)
                values.pw_multi_sid = pw_multi_sid;
            pw_app.postHtml(params, values, { callback: callback });
        };
        this.postHtml = function (params, values, options) {
            var url = this.urlFor(params);
            if (this.isIE()) {
                this.ajaxPost(url, values, options);
            }
            else {
                this.fetchRequest(url, this.headerPostValues(values), options);
            }
        };
        this.postJson = function (params, json, options) {
            if (!params)
                return;
            if (!params.controller)
                return;
            if (!params.action)
                return;
            if (!json)
                return;
            var url = this.urlFor(params);
            if (this.isIE()) {
                this.ajaxPost(url, json, options);
            }
            else {
                this.fetchRequest(url, this.headerPostJson(json), options);
            }
        };
        this.postByUrl = function (url, params, callback, data_format) {
            if (pw_multi_sid)
                params.pw_multi_sid = pw_multi_sid;
            var options = { callback: callback, data_format: data_format };
            this.ajaxPost(url, params, options);
        };
        this.controllerPost = function (controller, action, params, callback, data_format) {
            if (pw_multi_sid)
                params.pw_multi_sid = pw_multi_sid;
            var url = this.urlFor({ controller: controller, action: action });
            var options = { callback: callback, data_format: data_format };
            this.ajaxPost(url, params, options);
        };
        this.actionGet = function (node, action, params, callback, data_format) {
            if (pw_multi_sid)
                params.pw_multi_sid = pw_multi_sid;
            var url = this.urlFor({ controller: node.controller(), action: action });
            var options = { callback: callback, data_format: data_format };
            this.ajaxGet(url, params, options);
        };
        this.download = function (url, file_name, params, callback) {
            if (pw_multi_sid)
                params.pw_multi_sid = pw_multi_sid;
            var url_param = this.query(params);
            url = url + '?' + url_param;
            $.ajax({
                download: file_name,
                href: url,
                success: function (data) {
                    if (callback)
                        callback(data);
                },
                error: function () {
                }
            });
        };
        this.pwLoad = function (params) {
            var pw_load = document.getElementsByClassName('pw-load');
            for (var i = 0; i < pw_load.length; i++) {
                var element = pw_load[i];
                var pw_node = PwNode.byElement(element);
                var controller_name = pw_node.controller();
                if (!controller_name)
                    return;
                var function_name = pw_node.functionName();
                var controller_class_name = pw_node.controllerClassName();
                if (controller_class_name in window) {
                    var controller = new window[controller_class_name]();
                    if (function_name && (function_name in controller)) {
                        var is_run = true;
                        if (params)
                            is_run = (params.controller == controller_name && params["function"] == function_name);
                        if (is_run)
                            controller[function_name](pw_node);
                    }
                }
            }
        };
        this.pwClickHandler = function (event) {
            pw_app.eventAction(this);
            event.preventDefault();
        };
        this.confirmDialog = function () {
            var elements = document.querySelectorAll('.confirm-dialog');
            [].forEach.call(elements, function (element) {
                element.addEventListener('click', function (event) {
                    var message = '';
                    if (element.getAttribute('message'))
                        message = element.getAttribute('message');
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                }, false);
            });
        };
        this.deleteCheckbox = function () {
            var elements = document.querySelectorAll('.delete_checkbox');
            [].forEach.call(elements, function (element) {
                element.addEventListener('change', function (event) {
                    var pw_node = PwNode.byElement(element);
                    var delete_link_node = PwNode.id('delete_link');
                    var is_checked = pw_node.checked();
                    if (is_checked) {
                        delete_link_node.abled();
                    }
                    else {
                        delete_link_node.disabled();
                    }
                });
            });
        };
        this.fetchRequest = function (url, header_options, options) {
            var callback = options.callback;
            var error_callback = options.error_callback;
            var is_show_loading = false;
            if (options.is_show_loading)
                is_show_loading = options.is_show_loading;
            if (is_show_loading)
                pw_app.showLoading(options.loading_selector);
            fetch(url, header_options)["catch"](function (err) {
                if (is_show_loading)
                    pw_app.hideLoading(null);
                throw new Error('post error');
            }).then(function (response) {
                if (is_show_loading)
                    pw_app.hideLoading(null);
                if (response.ok) {
                    return response.text().then(function (text) {
                        return text;
                    });
                }
                else {
                    if (error_callback)
                        error_callback(response);
                }
            }).then(function (text) {
                if (callback)
                    callback(text);
            });
        };
        this.multiSessionLink = function () {
            var pw_multi_session_id = PwNode.id('pw-multi-session-id');
            if (!pw_multi_session_id)
                return;
            pw_multi_sid = pw_multi_session_id.value();
            if (!pw_multi_sid)
                return;
            [].forEach.call(document.getElementsByTagName('a'), function (element) {
                var node = PwNode.byElement(element);
                var link = '';
                if (node.attr('is_not_pw_multi_sid'))
                    return;
                if (link = node.attr('href')) {
                    if (link.indexOf('pw_multi_sid') == -1) {
                        link = link + "&pw_multi_sid=" + pw_multi_sid;
                        node.setAttr('href', link);
                    }
                }
            });
        };
        this.downloadAsFile = function (fileName, content) {
            var a = document.createElement('a');
            a.download = fileName;
            a.href = 'data:application/octet-stream,' + encodeURIComponent(content);
            a.click();
        };
        this.requestPage = function (url, params) {
            if (pw_multi_sid)
                params.pw_multi_sid = pw_multi_sid;
            window.location.href = pw_app.generateUrl(url, params);
        };
        this.generateUrl = function (url, params) {
            var url_param = this.query(params);
            url = url + '?' + url_param;
            return url;
        };
        this.generateProjectUrl = function (url, params) {
            url = pw_app.projectUrl() + url;
            var url_param = this.query(params);
            url = url + '?' + url_param;
            return url;
        };
        this.projectUrl = function () {
            return PwApp.projectUrl();
        };
        this.setSession = function (key, value) {
            value = JSON.stringify(value);
            localStorage.setItem(key, value);
        };
        this.getSession = function (key) {
            var value = localStorage.getItem(key);
            value = JSON.parse(value);
            return value;
        };
        this.showLoading = function (selector_name) {
            if (!selector_name)
                selector_name = this.pw_loading_selector;
            var selector_node = PwNode.id(selector_name);
            console.log(selector_node);
            if (selector_node) {
                if (selector_name != 'body')
                    selector_name = this.jqueryId(selector_name);
                $(selector_name).LoadingOverlay('show');
            }
            else {
                $.LoadingOverlay('show');
            }
        };
        this.hideLoading = function (selector_name) {
            if (!selector_name)
                selector_name = this.pw_loading_selector;
            var selector_node = PwNode.id(selector_name);
            if (selector_node) {
                if (selector_name != 'body')
                    selector_name = this.jqueryId(selector_name);
                $(selector_name).LoadingOverlay('hide');
            }
            else {
                $.LoadingOverlay('hide');
            }
        };
        this.jqueryId = function (id) {
            if (id) {
                var start = id.slice(0, 1);
                if (start != '#')
                    id = '#' + id;
            }
            return id;
        };
        this.checkImageLoading = function (class_name, count) {
            pw_app.hideLoading(null);
        };
        this.loadingDom = function (node, callback, error_callback) {
            var selector = '';
            if (node.getID())
                selector = node.getID();
            pw_app.showLoading(selector);
            var loadHandler = function (event) {
                pw_app.hideLoading(selector);
                if (callback)
                    callback();
                node.element.removeEventListener('load', loadHandler);
            };
            node.element.addEventListener('load', loadHandler, false);
            var errorHandler = function (event) {
                pw_app.hideLoading(selector);
                if (callback)
                    error_callback();
                node.element.removeEventListener('error', errorHandler);
            };
            node.element.addEventListener('error', errorHandler, false);
        };
        this.loadImage = function (url, node, callback, error_callback, loading_node) {
            url += '&serial=' + new Date().getTime();
            node.setAttr('src', url);
            if (loading_node)
                pw_app.loadingDom(loading_node, null, null);
            var selector = '';
            var loadHandler = function (event) {
                if (loading_node)
                    pw_app.hideLoading(loading_node);
                node.show();
                if (callback)
                    callback();
                node.element.removeEventListener('load', loadHandler);
            };
            node.element.addEventListener('load', loadHandler, false);
            var errorHandler = function (event) {
                pw_app.hideLoading(selector);
                node.setAttr('src', null);
                node.hide();
                if (callback)
                    error_callback();
                node.element.removeEventListener('error', errorHandler);
            };
            node.element.addEventListener('error', errorHandler, false);
        };
        this.confirmDeleteImage = function (controller, node, delete_id_column) {
            var link_delete_image = PwNode.id('link_delete_image');
            link_delete_image.setAttr('pw-controller', controller);
            link_delete_image.setAttr('pw-action', 'delete_image');
            link_delete_image.setAttr(delete_id_column, node.attr(delete_id_column));
            pw_ui.showModal('delete-file-window');
        };
        this.deleteImage = function (params) {
            pw_ui.hideModal('delete-file-window');
            var delete_id_column = params.delete_id_column;
            var url = pw_app.urlFor({ controller: params.controller, action: 'delete_image' }, { delete_id_column: params.node.attr(delete_id_column) });
            pw_app.postByUrl(url, null, callback, null);
            function callback(data, status, xhr) {
                if (params.image && params.callback)
                    params.callback(params.image);
            }
        };
        this.showDeleteConfirmImage = function () {
            PwNode.id('link_confirm_delete_image').show();
        };
        this.hideDeleteConfirmImage = function () {
            PwNode.id('link_confirm_delete_image').hide();
        };
        this.fileUpload = function (url, form_id, callback, error_callback) {
            if (!$(form_id))
                return;
            if (!$(form_id).get(0))
                return;
            var form_data = new FormData($(form_id).get(0));
            pw_app.showLoading(null);
            $.ajax({
                url: url,
                type: 'POST',
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'html'
            })
                .done(function (data, status, xhr) {
                pw_app.hideLoading(null);
                callback(data, status, xhr);
            })
                .fail(function (xhr, status, errorThrown) {
                pw_app.hideLoading(null);
                error_callback(xhr, status, errorThrown);
            });
        };
        this.openWindow = function (url, params) {
            var queryArray = [];
            [].forEach.call(params, function (key) {
                queryArray.push(key + '=' + params[key]);
            });
            var query = queryArray.join(',');
            if (url)
                window.open(url, 'new', query);
        };
        this.loadPopup = function () {
            var popupEvent = function (event) {
                var option = this.href.replace(/^[^\?]+\??/, '').replace(/&/g, ',');
                window.open(this.href, this.rel, option).focus();
                event.preventDefault();
                event.stopPropagation();
            };
            var elements = PwNode.byQuery('a.pw-popup').elements;
            [].forEach.call(elements, function (element) {
                element.addEventListener('click', popupEvent, true);
            });
        };
    }
    PwApp.prototype.query = function (params) {
        if (!params)
            return;
        var queryArray = [];
        Object.keys(params).forEach(function (key) { return queryArray.push(key + '=' + encodeURIComponent(params[key])); });
        var query = queryArray.join('&');
        return query;
    };
    PwApp.httpBase = function () {
        var domain = location.hostname;
        var url;
        if (pw_base_url) {
            url = pw_base_url;
        }
        else {
            url = '//' + domain + '/';
        }
        return url;
    };
    PwApp.projectUrl = function () {
        var pw_base_url = PwApp.httpBase();
        var url = pw_base_url;
        if (pw_project_name)
            url += pw_project_name + '/';
        return url;
    };
    PwApp.prototype.ajaxPost = function (url, data, options) {
        options.method = 'POST';
        this.ajaxRequest(url, data, options);
    };
    PwApp.prototype.ajaxGet = function (url, data, options) {
        options.method = 'GET';
        this.ajaxRequest(url, data, options);
    };
    PwApp.prototype.ajaxRequest = function (url, data, options) {
        var is_show_loading = false;
        if (options.is_show_loading)
            is_show_loading = options.is_show_loading;
        if (is_show_loading)
            pw_app.showLoading(options.loading_selector);
        var data_format = 'html';
        var callback;
        var method = 'GET';
        if (options) {
            if (options.data_format)
                data_format = options.data_format;
            if (options.callback)
                callback = options.callback;
            if (options.method)
                method = options.method;
        }
        $.ajax({
            type: method,
            cache: false,
            url: url,
            data: data,
            dataType: data_format,
            xhrFields: {
                withCredentials: true
            },
            success: function (result) {
                if (is_show_loading)
                    pw_app.hideLoading(options.loading_selector);
                if (callback)
                    callback(result);
            },
            error: function () {
            }
        });
    };
    PwApp.prototype.eventAction = function (element) {
        var pw_node = PwNode.byElement(element);
        var lib_name = pw_node.attr('pw-lib');
        if (lib_name)
            return this.libAction(element);
        var name = pw_node.controller();
        if (!name)
            return;
        var action = pw_node.action();
        if (!action)
            return;
        var controller_name = pw_node.controllerClassName();
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller)
                controller[action](pw_node);
        }
    };
    PwApp.prototype.libAction = function (element) {
        var pw_node = PwNode.byElement(element);
        var lib_name = pw_node.attr('pw-lib');
        if (!lib_name)
            return;
        var action = pw_node.action();
        if (!action)
            return;
        if (lib_name in window) {
            var controller = new window[lib_name]();
            if (action in controller)
                controller[action](pw_node);
        }
    };
    return PwApp;
}());
var pw_app = new PwApp();
var pw_base_url = '';
var pw_multi_sid = '';
var pw_base_url = '';
var pw_project_name = '';
document.addEventListener('DOMContentLoaded', function () {
    pw_app.multiSessionLink();
    pw_app.pwLoad(null);
    if (PwNode.id('pw-current-controller').value())
        pw_app.pw_current_controller = PwNode.id('pw-current-controller').value();
    if (PwNode.id('pw-current-action').value())
        pw_app.pw_current_action = PwNode.id('pw-current-action').value();
    pw_app.loadPopup();
    pw_app.confirmDialog();
    pw_app.deleteCheckbox();
});
document.addEventListener('click', function (event) {
    var element = event.target;
    if (element.classList.contains('pw-click')) {
        pw_app.eventAction(event.target);
    }
    else if (element.parentNode) {
        if (element.parentNode.classList.contains('pw-click')) {
            pw_app.eventAction(element.parentNode);
        }
    }
}, false);
document.addEventListener('change', function (event) {
    var element = event.target;
    if (element.classList.contains('pw-change')) {
        pw_app.eventAction(event.target);
    }
}, false);
document.addEventListener('change', function (event) {
    var element = event.target;
    if (element.id == 'pw_upload_file') {
        var pw_node = PwNode.byElement(element);
        var label = pw_node.value().replace(/\\/g, '/').replace(/.*\//, '');
        PwNode.id('pw_upload_file_text').setValue(label);
    }
}, false);
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('confirm-delete')) {
        var delete_id = PwNode.byElement(event.target).attr('delete_id');
        if (!delete_id)
            return;
        PwNode.id('from_delete_id').setValue(delete_id);
        pw_ui.showModal('delete-window');
    }
}, false);
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('action-loading')) {
        pw_app.showLoading(null);
    }
}, false);
//# sourceMappingURL=pw.js.map