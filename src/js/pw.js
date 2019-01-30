'use strict';
var pw_app;
var pw_base_url = '';
var pw_current_controller = '';
var pw_current_action = '';
var pw_loading_selector = '#main';
var pw_multi_sid = '';
document.addEventListener('DOMContentLoaded', function () {
    pw_app = new PwApp();
    pw_app.multiSessionLink();
    pw_app.pwLoad();
});
window.onload = function () {
    pw_current_controller = pw_app.dom({ id: 'pw-current-controller' }).value();
    pw_current_action = pw_app.dom({ id: 'pw-current-action' }).value();
};
$(document).on('change', ':file', function () {
    var input = pw_app.dom(this);
    var pattern1 = "/\\/g";
    var pattern2 = "/.*\//";
    var label = input.value().replace(pattern1, '/').replace(pattern2, '');
    input.parent().parent().next(':text').val(label);
});
var PwApp = function () {
    this.pw_project_name;
    this.init = function (params) {
    };
    this.dom = function (params) {
        var instance = new PwNode(params);
        instance.init();
        return instance;
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
        var url = projectUrl() + url_query;
        if (options)
            url = url + '?' + query(options);
        return url;
    };
    this.getHtml = function (params, values, options) {
        var url = this.urlFor(params, values);
        var header = new Headers();
        header.append('Content-Type', 'application/xhtml+xml');
        var header_options = {
            method: 'GET',
            headers: header,
            mode: 'cors',
            cache: 'default'
        };
        fetchResponse(url, header_options, options);
    };
    this.post = function (dom, values, callback, data_format) {
        var pw_dom = pw_app.dom({ dom: dom });
        var params = {
            controller: pw_dom.controller(),
            action: pw_dom.action()
        };
        if (pw_multi_sid)
            values.pw_multi_sid = pw_multi_sid;
        pw_app.postHtml(params, values, { callback: callback });
    };
    this.postHtml = function (params, values, options) {
        var url = this.urlFor(params);
        var header = new Headers();
        header.append('Content-Type', 'application/x-www-form-urlencoded');
        var header_options = {
            method: 'POST',
            headers: header,
            body: query(values),
            mode: 'cors',
            cache: 'default'
        };
        fetchResponse(url, header_options, options);
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
        var header = new Headers();
        header.append('Content-Type', 'application/x-www-form-urlencoded');
        var header_options = {
            method: 'POST',
            headers: header,
            body: json,
            mode: 'cors',
            cache: 'default'
        };
        fetchResponse(url, header_options, options);
    };
    function fetchResponse(url, header_options, options) {
        var callback = options.callback;
        var error_callback = options.error_callback;
        var is_show_loading = false;
        if (options.is_show_loading)
            is_show_loading = options.is_show_loading;
        if (is_show_loading)
            pw_app.showLoading();
        fetch(url, header_options)["catch"](function (err) {
            if (is_show_loading)
                pw_app.hideLoading();
            throw new Error('post error');
        })
            .then(function (response) {
            if (is_show_loading)
                pw_app.hideLoading();
            var promise = response.text();
            return promise.then(function (body) { return ({ body: body, response: response }); });
        }).then(function (_a) {
            var body = _a.body, response = _a.response;
            if (response.ok) {
                return body;
            }
            else {
                if (error_callback)
                    error_callback(response);
            }
        }).then(function (text) {
            callback(text);
        });
    }
    this.pwLoad = function () {
        var $pw_load = document.getElementsByClassName('pw-load');
        for (var $i = 0; $i < $pw_load.length; $i++) {
            var dom = $pw_load[$i];
            var name = dom.getAttribute('pw-controller');
            if (!name)
                return;
            var function_name = dom.getAttribute('pw-function');
            var action = dom.getAttribute('pw-action');
            var controller_name = pw_app.controllerClassName(name);
            if (controller_name in window) {
                var controller = new window[controller_name]();
                if (action && (action in controller))
                    controller[action](dom);
                if (function_name && (function_name in controller))
                    controller[function_name]();
            }
        }
        $('#pw-error').modal('show');
    };
    $(document).on('click', '.pw-click', function () {
        var pw_dom = pw_app.dom({ dom: this });
        var name = pw_dom.controller();
        if (!name)
            return;
        var action = pw_dom.action();
        if (!action)
            return;
        var controller_name = pw_dom.controllerClassName();
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller)
                controller[action](pw_dom.dom);
        }
    });
    this.multiSessionLink = function () {
        var dom = pw_app.dom({ id: 'pw-multi-session-id' });
        if (!dom)
            return;
        pw_multi_sid = dom.value('value');
        if (!pw_multi_sid)
            return;
        [].forEach.call(document.getElementsByTagName('a'), function (element) {
            var node = PwNode.instance({ dom: element });
            var link = '';
            if (node.attr('is_not_pw_multi_sid'))
                return;
            if (link = node.attr('href')) {
                if (link.indexOf('pw_multi_sid') > 0) {
                }
                else {
                    link = link + "&pw_multi_sid=" + pw_multi_sid;
                    node.setAttr('href', link);
                }
            }
        });
    };
    this.downloadAsFile = function (file_name, content) {
        var a = document.createElement('a');
        a.download = file_name;
        a.href = 'data:application/octet-stream,' + encodeURIComponent(content);
        a.click();
    };
    this.requestPage = function (url, params) {
        if (pw_multi_sid)
            params.pw_multi_sid = pw_multi_sid;
        window.location.href = pw_app.generateUrl(url, params);
    };
    this.postByUrl = function (url, params, callback, data_format) {
        if (pw_multi_sid)
            params.pw_multi_sid = pw_multi_sid;
        post(url, params, callback, data_format);
    };
    this.controllerPost = function (controller, action, params, callback, data_format) {
        if (pw_multi_sid)
            params.pw_multi_sid = pw_multi_sid;
        post(controllerUrl(controller, action), params, callback, data_format);
    };
    this.actionPost = function (dom, action, params, callback, data_format) {
        if (pw_multi_sid)
            params.pw_multi_sid = pw_multi_sid;
        post(actionUrl(dom, action), params, callback, data_format);
    };
    this.controllerGet = function (controller, action, params, callback, data_format) {
        if (pw_multi_sid)
            params.pw_multi_sid = pw_multi_sid;
        requestGet(controllerUrl(controller, action), params, callback, data_format);
    };
    this.actionGet = function (dom, action, params, callback, data_format) {
        if (pw_multi_sid)
            params.pw_multi_sid = pw_multi_sid;
        requestGet(actionUrl(dom, action), params, callback, data_format);
    };
    this.download = function (url, file_name, url_params, options) {
        if (pw_multi_sid)
            url_params.pw_multi_sid = pw_multi_sid;
        download(url, file_name, url_params, options);
    };
    this.generateUrl = function (url, params) {
        var url_param = query(params);
        url = url + '?' + url_param;
        return url;
    };
    this.generateProjectUrl = function (url, params) {
        url = pw_app.projectUrl() + url;
        var url_param = query(params);
        url = url + '?' + url_param;
        return url;
    };
    this.projectUrl = function () {
        return projectUrl();
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
    this.showLoading = function (selector) {
        if (selector) {
            $(selector).LoadingOverlay("show");
        }
        else {
            $(pw_loading_selector).LoadingOverlay("show");
        }
    };
    this.hideLoading = function (selector) {
        if (selector) {
            $(selector).LoadingOverlay("hide");
        }
        else {
            $(pw_loading_selector).LoadingOverlay("hide");
        }
    };
    this.checkImageLoading = function (class_name, count) {
        var displayed_count = 0;
        $(class_name).off('load');
        $(class_name).off('error');
        $(class_name).on('error', function (e) {
            $(this).hide();
            pw_app.hideLoading();
        });
        $(class_name).on('load', function () {
            $(this).show();
            if (count) {
                displayed_count++;
                if (count == displayed_count) {
                    pw_app.hideLoading();
                }
            }
            else {
                pw_app.hideLoading();
            }
        });
    };
    this.loadingDom = function (dom, callback, error_callback) {
        var selector = '';
        var selector = '';
        var node = PwNode.instance({ dom: dom });
        if (selector = node.attr('id')) { }
        ;
        pw_app.showLoading(selector);
        node.dom.addEventListener('error', function (e) {
            pw_app.hideLoading(selector);
            if (error_callback)
                error_callback();
        });
        node.dom.addEventListener('load', function () {
            pw_app.hideLoading(selector);
            if (callback)
                callback();
        });
    };
    this.loadImage = function (url, dom, callback, error_callback) {
        var node = PwNode.instance({ dom: dom });
        node.setAttr('src', url);
        pw_app.loadingDom(dom, callback, error_callback);
        var selector = '';
        node.dom.addEventListener('error', function (e) {
            pw_app.hideLoading(selector);
            node.setAttr('src', null);
            if (error_callback)
                error_callback();
        });
        node.dom.addEventListener('load', function () {
            pw_app.hideLoading(selector);
            if (callback)
                callback();
        });
    };
    this.fileUpload = function (url, form_id, callback, error_callback) {
        var node = PwNode.instance({ id: form_id });
        var form_data = new FormData(node.dom);
        console.log(form_data);
        pw_app.showLoading();
        pw_app.postHtml();
    };
    this.controllerClassName = function (name) {
        var class_name = '';
        var names = name.split('_');
        names.forEach(function (index) {
            class_name += upperTopString(names[index]);
        });
        class_name += 'Controller';
        return class_name;
    };
    $(document).on('change', '.pw-change', function () {
        var name = $(this).attr('pw-controller');
        if (!name)
            return;
        var action = $(this).attr('pw-action');
        if (!action)
            return;
        var controller_name = controllerClassName(name);
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller) {
                controller[action](this);
            }
        }
    });
    $(document).on('click', '.pw-lib', function () {
        var lib_name = $(this).attr('pw-lib');
        if (!lib_name)
            return;
        var action = $(this).attr('pw-action');
        if (!action)
            return;
        if (lib_name in window) {
            var controller = new window[lib_name]();
            if (action in controller) {
                controller[action](this);
            }
        }
    });
    $(document).on('click', '.action-loading', function () {
        pw_app.showLoading();
    });
    function query(params) {
        if (!params)
            return;
        var esc = encodeURIComponent;
        var query = Object.keys(params).map(function (k) { return esc(k) + '=' + esc(params[k]); }).join('&');
        return query;
    }
    function controllerClassName(name) {
        var class_name = '';
        var names = name.split('_');
        $.each(names, function (index, value) {
            class_name += upperTopString(value);
        });
        class_name += 'Controller';
        return class_name;
    }
    function httpBase() {
        var domain = location.hostname;
        var url;
        if (pw_base_url) {
            url = pw_base_url;
        }
        else {
            url = '//' + domain + '/';
        }
        return url;
    }
    function projectUrl() {
        var pw_base_url = httpBase();
        var url = pw_base_url;
        if (pw_app.pw_project_name)
            url += this.pw_project_name + '/';
        return url;
    }
    function controllerUrl(controller, action) {
        if (!controller)
            return;
        if (!action)
            return;
        var url = projectUrl() + controller + '/' + action;
        return url;
    }
    function actionUrl(dom, action) {
        var controller = $(dom).attr('pw-controller');
        if (!controller)
            return;
        if (!action)
            return;
        var url = projectUrl() + controller + '/' + action;
        return url;
    }
    function postUrl(dom) {
        var controller = $(dom).attr('pw-controller');
        var action = $(dom).attr('pw-action');
        if (!controller)
            return;
        if (!action)
            return;
        var url = projectUrl() + controller + '/' + action;
        return url;
    }
    function post(url, params, callback, data_format) {
        if (!data_format)
            data_format = 'html';
        $.ajax({
            type: 'POST',
            cache: false,
            url: url,
            data: params,
            dataType: data_format,
            xhrFields: {
                withCredentials: true
            },
            success: function (data) {
                if (callback)
                    callback(data);
            },
            error: function () {
            }
        });
    }
    function download(url, file_name, url_params, options) {
        var callback = options.callback;
        var error_callback = options.error_callback;
        var is_show_loading = false;
        if (url_params)
            url += query(url_params);
        if (options.is_show_loading)
            is_show_loading = options.is_show_loading;
        if (is_show_loading)
            pw_app.showLoading();
        var header = new Headers();
        header.append('Content-Type', 'application/xhtml+xml');
        var header_options = {
            method: 'GET',
            headers: header,
            mode: 'cors',
            cache: 'default'
        };
        fetchResponse(url, header_options, options);
    }
    function requestGet(url, params, callback, data_type) {
        if (!data_type)
            data_type = 'html';
        $.ajax({
            type: 'GET',
            cache: false,
            url: url,
            data: params,
            dataType: data_type,
            success: function (data) {
                if (callback)
                    callback(data);
            },
            error: function () {
            }
        });
    }
    function upperTopString(string) {
        var value = string.charAt(0).toUpperCase() + string.slice(1);
        var value = string.substring(0, 1).toUpperCase() + string.substring(1);
        var value = string.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    }
};
var PwNode = (function () {
    function PwNode(params) {
        this.init = function () {
            if (this.params.id) {
                this.dom = document.getElementById(this.params.id);
            }
            else if (this.params.dom) {
                this.dom = this.params.dom;
            }
        };
        this.setValue = function (value) {
            if (this.dom) {
                this.dom.setAttribute('value', value);
                return this;
            }
        };
        this.setAttr = function (key, value) {
            if (this.dom) {
                this.dom.setAttribute(key, value);
                return this;
            }
        };
        this.bind = function (params) {
            if (!params)
                return;
            if (this.dom)
                Object.keys(params).map(function (key) { this.dom.setAttribute(key, params[key]); });
        };
        this.value = function () {
            if (this.dom)
                return this.dom.getAttribute('value');
        };
        this.attr = function (selector) {
            if (!selector)
                return;
            if (this.dom)
                return this.dom.getAttribute(selector);
        };
        this.html = function (html) {
            if (this.dom)
                return this.dom.innerHTML = html;
        };
        this.toggle = function (class_name) {
            if (!class_name)
                return;
            if (this.dom) {
                this.dom.classList.toggle(class_name);
                return this;
            }
        };
        this.controller = function () {
            if (this.dom)
                return this.attr('pw-controller');
        };
        this.action = function () {
            if (this.dom)
                return this.attr('pw-action');
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
    PwNode.instance = function (params) {
        var instance = new PwNode(params);
        return instance;
    };
    PwNode["new"] = function (params) {
        var instance = new PwNode(params);
        return instance;
    };
    PwNode.upperTopString = function (value) {
        if (!value)
            return;
        var value = value.charAt(0).toUpperCase() + value.slice(1);
        var value = value.substring(0, 1).toUpperCase() + value.substring(1);
        var value = value.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    };
    return PwNode;
}());
var PwSortable = (function () {
    function PwSortable() {
        this.init = function () {
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
            this.before_rows.forEach(function (index) {
                var row = this.before_rows[index];
                row.setAttribute('order', index);
            });
        };
        this.set = function (params) {
            if (params) {
                if (params.hasOwnProperty('table_id'))
                    this.table_id = params.table_id;
                if (params.hasOwnProperty('api_uri'))
                    this.api_uri = params.api_uri;
                if (params.hasOwnProperty('callback'))
                    this.callback = params.callback;
                if (params.hasOwnProperty('is_use_loading'))
                    this.is_use_loading = params.is_use_loading;
            }
        };
    }
    return PwSortable;
}());
var pw_sortable;
document.addEventListener('DOMContentLoaded', function () {
    pw_sortable = new PwSortable();
});
var pw_form;
document.addEventListener('DOMContentLoaded', function () {
    pw_form = new PwForm();
});
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
            $(selector).each(function () {
                var type = $(this).attr('type');
                var name = $(this).attr('name');
                var default_value = $(this).attr('default_value');
                if (name) {
                    if (type == 'checkbox') {
                        $(this).val('');
                        if (default_value)
                            $(this).val(default_value);
                    }
                    else if (type == 'radio') {
                        $(this).val('');
                        if (default_value)
                            $(this).val(default_value);
                    }
                    else {
                        $(this).val('');
                        if (default_value)
                            $(this).val(default_value);
                    }
                }
            });
        };
        this.initSelect = function (selector_id) {
            var selector = selector_id + ' select option';
            $(selector).each(function () {
                var name = $(this).parent().attr('name');
                var default_value = $(this).attr('default_value');
                if (name) {
                    $(this).parent().val('');
                    if (default_value)
                        $(this).parent().val(default_value);
                }
            });
        };
        this.initTextarea = function (selector_id) {
            var selector = selector_id + ' textarea';
            $(selector).each(function () {
                var name = $(this).attr('name');
                var default_value = $(this).attr('default_value');
                if (name) {
                    $(this).val('');
                    if (default_value)
                        $(this).val(default_value);
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
            $(selector).each(function () {
                var type = $(this).attr('type');
                var name = $(this).attr('name');
                name = pw_form.checkName(name);
                if (name && values[name]) {
                    var value = values[name];
                    if (type == 'checkbox') {
                    }
                    else if (type == 'radio') {
                        $(this).val(value);
                    }
                    else {
                        $(this).val(value);
                    }
                }
            });
        };
        this.bindSelect = function (selector_id, values) {
            var selector = selector_id + ' select option';
            $(selector).each(function () {
                var name = $(this).parent().attr('name');
                name = pw_form.checkName(name);
                if (name && values[name]) {
                    var value = values[name];
                    $(this).parent().val(value);
                }
            });
        };
        this.bindTextarea = function (selector_id, values) {
            var selector = selector_id + ' textarea';
            $(selector).each(function () {
                var name = $(this).attr('name');
                name = pw_form.checkName(name);
                if (name && values[name]) {
                    var value = values[name];
                    $(this).val(value);
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
            $(selector).each(function () {
                var type = $(this).attr('type');
                var name = $(this).attr('name');
                if (name) {
                    if (type == 'checkbox') {
                        pw_form.value[name] = pw_form.checkboxValues(name);
                    }
                    else if (type == 'radio') {
                        var checked = $(this).prop('checked');
                        if (checked)
                            pw_form.value[name] = $(this).val();
                    }
                    else {
                        pw_form.value[name] = $(this).val();
                    }
                }
            });
        };
        this.loadSelect = function (selector_id) {
            var selector = selector_id + ' select option';
            $(selector).each(function () {
                var name = $(this).parent().attr('name');
                if (name)
                    pw_form.value[name] = $(this).val();
            });
        };
        this.loadTextarea = function (selector_id) {
            var selector = selector_id + ' textarea';
            $(selector).each(function () {
                var name = $(this).attr('name');
                if (name)
                    pw_form.value[name] = $(this).val();
            });
        };
        this.checkName = function (name) {
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
        this.checkboxValues = function (name) {
            var column = '[name="' + name + '"]:checked';
            var checks = [];
            $(column).each(function () {
                var checked = $(this).prop('checked');
                if (checked) {
                    checks.push($(this).val());
                }
            });
            return checks;
        };
    }
    return PwForm;
}());
var pw_date;
document.addEventListener('DOMContentLoaded', function () {
    pw_date = new PwDate();
});
var PwDate = function () {
    this.from_at_selector = '#from-at';
    this.to_at_selector = '#to-at';
    this.unixToString = function (time) {
        var date = new Date(time);
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var day = date.getDate();
        var hour = ('0' + date.getHours()).slice(-2);
        var min = ('0' + date.getMinutes()).slice(-2);
        var sec = ('0' + date.getSeconds()).slice(-2);
        return (year + '/' + month + '/' + day + ' ' + hour + ':' + min);
    };
    this.unixToDateString = function (time) {
        var date = new Date(time);
        var year = date.getFullYear() - 100;
        var month = date.getMonth() + 1;
        var day = date.getDate();
        return (year + '/' + month + '/' + day);
    };
    this.nextDate = function (value) {
        if (!value)
            return;
        var date = new Date(value);
        date.setDate(date.getDate() + 1);
        var value = this.string(date);
        return value;
    };
    this.prevDate = function (value) {
        var date = new Date(value);
        date.setDate(date.getDate() - 1);
        var value = this.string(date);
        return value;
    };
    this.replaceAllHyphen = function (value) {
        if (!value)
            return;
        value = value.toString();
        value = value.replace(/-/g, "/");
        return value;
    };
    this.string = function (value) {
        if (!value)
            return;
        value = this.replaceAllHyphen(value);
        var year = 0;
        var month = 0;
        var day = 0;
        var hour = 0;
        var minute = 0;
        var date = new Date(value);
        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
        hour = date.getHours();
        minute = date.getMinutes();
        if (!(year > 1900))
            return;
        if (!(month > 0))
            return;
        if (!(day > 0))
            return;
        var year_string = ('0000' + year).slice(-4);
        var month_string = ('00' + month).slice(-2);
        var day_string = ('00' + day).slice(-2);
        var hour_string = ('00' + hour).slice(-2);
        var minute_string = ('00' + minute).slice(-2);
        var number = year_string + '/' + month_string + '/' + day_string + ' ' + hour_string + ':' + minute_string;
        return number;
    };
    this.number = function (value) {
        if (!value)
            return;
        value = this.replaceAllHyphen(value);
        var date = new Date(value);
        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
        hour = date.getHours();
        minute = date.getMinutes();
        var year = ('0000' + year).slice(-4);
        var month = ('00' + month).slice(-2);
        var day = ('00' + day).slice(-2);
        var hour = ('00' + hour).slice(-2);
        var minute = ('00' + minute).slice(-2);
        var number = year + month + day + hour + minute;
        return number;
    };
    this.zeroMinute = function (value) {
    };
    this.limitToday = function (value) {
        if (!value)
            return;
        value = this.replaceAllHyphen(value);
        var date = new Date(value);
        var today = new Date();
        if (date.getTime() > today.getTime()) {
            today.setMinutes(0);
            return this.string(today);
        }
        else {
            return this.string(value);
        }
    };
    this.updateFromToComponent = function (value) {
        if (value.from_at) {
            var from_at = this.string(value.from_at);
            $(this.from_at_selector).val(from_at);
        }
        if (value.to_at) {
            var to_at = this.string(value.to_at);
            $(this.to_at_selector).val(to_at);
        }
    };
    this.convertGraphDate = function (value) {
        var date = new Date(value * 1000);
        var year = date.getFullYear() - 100;
        var month = date.getMonth() + 1;
        var day = date.getDate();
        var date_string = year + '/' + month + '/' + day;
        return date_string;
    };
};
//# sourceMappingURL=pw.js.map