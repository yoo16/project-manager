/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
var pw_app;
var pw_base_url = '';
var pw_current_controller = '';
var pw_current_action = '';
var pw_loading_selector = '#main';
var pw_multi_sid = '';

//TODO remove jquery
$.support.cors = true;

document.addEventListener('DOMContentLoaded', function() {
    pw_app = new PwController();
    pw_app.multiSessionLink();
    pw_app.pwLoad(); 
});

window.onload = function () {
    pw_current_controller = pw_app.dom({id: 'pw-current-controller'}).value();
    pw_current_action = pw_app.dom({id: 'pw-current-action'}).value();
};

//TODO remove jquery
$(document).on('change', ':file', function () {
    var input = $(this),
    //numFiles = input.get(0).files ? input.get(0).files.length : 1,
    label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.parent().parent().next(':text').val(label);
});

var PwController = function () {
    this.init = function(params) {

    }

    this.dom = function(params) {
        var instance = new PwNode(params);
        instance.init();
        return instance;
    }
    this.urlFor = function (params, options) {
        var url_queries = [];
        if (params.controller) url_queries.push(params.controller);
        if (params.action) url_queries.push(params.action);
        if (params.id) url_queries.push(params.id);

        var url_query = url_queries.join('/');
        var url = projectUrl() + url_query;

        if (options) url = url + '?' + query(options);
        return url;
    }
    this.getHtml = function (params, values, options) {
        var url = this.urlFor(params, values);
        const header = new Headers();
        header.append('Content-Type', 'application/xhtml+xml');
        var header_options = { 
            method: 'GET',
            headers: header,
            mode: 'cors',
            cache: 'default',
        };
        fetchResponse(url, header_options, options);
    }
    this.post = function (dom, values, callback, data_format) {
        var pw_dom = pw_app.dom({dom: dom});
        var params = {
            controller: pw_dom.controller(),
            action: pw_dom.action(),
        };
        if (pw_multi_sid) values.pw_multi_sid = pw_multi_sid;
        pw_app.postHtml(params, values, {callback: callback});
    }
    this.postHtml = function (params, values, options) {
        var url = this.urlFor(params);
        const header = new Headers();
        header.append('Content-Type', 'application/x-www-form-urlencoded');
        var header_options = { 
            method: 'POST',
            headers: header,
            body: query(values),
            mode: 'cors',
            cache: 'default',
        };
        fetchResponse(url, header_options, options);
    }
    this.postJson = function (params, json, options) {
        if (!params) return;
        if (!params.controller) return;
        if (!params.action) return;
        if (!json) return;
        var url = this.urlFor(params);
        const header = new Headers();
        header.append('Content-Type', 'application/x-www-form-urlencoded');
        //TODO PHP header: not work with 'application/json'
        //header.append('Content-Type', 'application/json');

        var header_options = { 
            method: 'POST',
            headers: header,
            body: json,
            mode: 'cors',
            cache: 'default',
        };
        fetchResponse(url, header_options, options);
    }

    /**
     * 
     * @param string url 
     * @param Object options 
     */
    function fetchResponse(url, header_options, options) {
        var callback = options.callback;
        var error_callback = options.error_callback;
        var is_show_loading = false;
        if (options.is_show_loading) is_show_loading = options.is_show_loading;
        if (is_show_loading) pw_app.showLoading();

        fetch(url, header_options)
        .catch(err => {
            if (is_show_loading) pw_app.hideLoading();
            throw new Error('post error')
        })
        .then(function(response) {
            if (is_show_loading) pw_app.hideLoading();
            const promise = response.text();
            return promise.then(body => ({ body: body, response: response }))
        }).then(({ body, response }) => {
            if (response.ok) {
                return body
            } else {
                if (error_callback) error_callback(response);
            }
        }).then(text => {
            callback(text);
        }); 
    }

    this.pwLoad = function() {
        var $pw_load = document.getElementsByClassName('pw-load');
        for (var $i = 0; $i < $pw_load.length; $i++) {
            var dom = $pw_load[$i];
            var name = dom.getAttribute('pw-controller');
            if (!name) return;
    
            var function_name = dom.getAttribute('pw-function');
            var action = dom.getAttribute('pw-action');
    
            var controller_name = pw_app.controllerClassName(name);
            if (controller_name in window) {
                var controller = new window[controller_name]();
                if (action && (action in controller)) controller[action](dom);
                if (function_name && (function_name in controller)) controller[function_name]();
            }
        }
        //document.getElementById('pw-error').modal('show');
        //TODO remove jquery
        $('#pw-error').modal('show');
    }

    //TODO remove jquery
    $(document).on('click', '.pw-click', function () {
        var pw_dom = pw_app.dom({dom: this});
        var name = pw_dom.controller();
        if (!name) return;

        var action = pw_dom.action();
        if (!action) return;

        var controller_name = pw_dom.controllerClassName();
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller) controller[action](pw_dom.dom);
        }
    });

    //TODO remove jquery
    this.multiSessionLink = function(fileName, content) {
        var dom = pw_app.dom({id: 'pw-multi-session-id'});
        if (!dom) return;
        pw_multi_sid = dom.value('value');
        if (!pw_multi_sid) return;

        [].forEach.call(document.getElementsByTagName('a'), function(element) {
            var node = PwNode.instance({dom: element});
            var link = '';
            if (node.attr('is_not_pw_multi_sid')) return;
            if (link = node.attr('href')) {
                if (link.indexOf('pw_multi_sid') > 0) {

                } else {
                    link = link + "&pw_multi_sid=" + pw_multi_sid;
                    node.setAttr('href', link);
                }
            }
        });
    };
    this.downloadAsFile = function(fileName, content) {
        var a = document.createElement('a');
        a.download = fileName;
        a.href = 'data:application/octet-stream,' + encodeURIComponent(content);
        a.click();
    };
    this.requestPage = function (url, params, callback) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        window.location.href = pw_app.generateUrl(url, params);
    }
    this.postByUrl = function (url, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        post(url, params, callback, data_format);
    }
    this.controllerPost = function (controller, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        post(controllerUrl(controller, action), params, callback, data_format);
    }
    this.actionGet = function (dom, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        requestGet(actionUrl(dom, action), params, callback, data_format);
    }
    this.download = function (url, file_name, params, callback) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        download(url, file_name, params, callback);
    }
    this.generateUrl = function (url, params) {
        var url_param = query(params);
        url = url + '?' + url_param;
        return url;
    }
    this.generateProjectUrl = function(url, params) {
        url = pw_app.projectUrl() + url;
        var url_param = query(params);
        url = url + '?' + url_param;
        return url;
    }
    this.projectUrl = function () {
        return projectUrl();
    }
    this.setSession = function (key, value) {
        value = JSON.stringify(value);
        localStorage.setItem(key, value);
    }
    this.getSession = function (key) {
        var value = localStorage.getItem(key)
        value = JSON.parse(value);
        return value;
    }
    this.showLoading = function(selector) {
        if (selector) {
            $(selector).LoadingOverlay("show");
        } else {
            $(pw_loading_selector).LoadingOverlay("show");
        }
    }
    this.hideLoading = function(selector) {
        if (selector) {
            $(selector).LoadingOverlay("hide");
        } else {
            $(pw_loading_selector).LoadingOverlay("hide");
        }
    }
    this.checkImageLoading = function(class_name, count) {
        var displayed_count = 0;

        $(class_name).off('load');
        $(class_name).off('error');
        $(class_name).on('error', function(e) {
            $(this).hide();
            pw_app.hideLoading();
        });
        $(class_name).on('load', function() {
            $(this).show();
            if (count) {
                displayed_count++;
                if (count == displayed_count) {
                    pw_app.hideLoading();
                }
            } else {
                pw_app.hideLoading();
            }
        });
    }
    this.loadingDom = function(dom, callback, error_callback) {
        var selector = '';
        if ($(dom).attr('id')) selector = '#' + $(dom).attr('id');
        pw_app.showLoading(selector);
        $(dom).on('error', function(e) {
            pw_app.hideLoading(selector);
            $(dom).off('error');
            if (error_callback) error_callback();
        });
        $(dom).on('load', function() {
            pw_app.hideLoading(selector);
            $(dom).off('load');
            if (callback) callback();
        });
    }
    this.loadImage = function(url, dom, callback, error_callback) {
        $(dom).attr('src', url);
        pw_app.loadingDom(dom, callback, error_callback);

        var selector = '';
        if ($(dom).attr('id')) selector = '#' + $(dom).attr('id');
        $(dom).on('error', function(e) {
            pw_app.hideLoading(selector);
            $(dom).attr('src', null);
            $(dom).hide();
            $(dom).off('error');
            if (error_callback) error_callback();
        });
        $(dom).on('load', function() {
            pw_app.hideLoading(selector);
            $(dom).show();
            $(dom).off('load');
            if (callback) callback();
        });
    }
    this.fileUpload = function(url, form_id, callback, error_callback)
    {
        if (!$(form_id)) return;
        if (!$(form_id).get(0)) return;
        var form_data = new FormData($(form_id).get(0));

        pw_app.showLoading();

        $.ajax({
            url  : url,
            type : 'POST',
            data : form_data,
            cache       : false,
            contentType : false,
            processData : false,
            dataType    : 'html'
        })
        .done(function(data, status, xhr) {
            pw_app.hideLoading();
            callback(data, status, xhr);
        })
        .fail(function(xhr, status, errorThrown){
            pw_app.hideLoading();
            error_callback(xhr, status, errorThrown);
        });
    }
    /**
     * controller class name
     * 
     * @param  string name
     * @return string
     */
    this.controllerClassName = function(name) {
        var class_name = '';
        var names = name.split('_');
        $.each(names, function (index, value) {
            class_name += upperTopString(value);
        });
        class_name += 'Controller';
        return class_name;
    }

    $(document).on('change', '.pw-change', function () {
        var name = $(this).attr('pw-controller');
        if (!name) return;

        var action = $(this).attr('pw-action');
        if (!action) return;

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
        if (!lib_name) return;

        var action = $(this).attr('pw-action');
        if (!action) return;

        if (lib_name in window) {
            var controller = new window[lib_name]();
            if (action in controller) {
                controller[action](this);
            }
        }
    });

    $(document).on('click', '.confirm-delete', function() {
        $('#from_delete_id').val($(this).attr('delete_id'));
        var title = $(this).attr('title');
        if (title) $('#from-delete-title').html(title);
        $('.delete-window').modal();
    });

    /**
     * confirm dialog
     */
    $(document).on('click', '.confirm-dialog', function() {
        var message = '';
        if ($(this).attr('message')) message = $(this).attr('message');
        return (window.confirm(message));
    });

    $(document).on('click', '.action-loading', function() {
        pw_app.showLoading();
    });

    /**
     * query
     * 
     * @param  array params
     * @return string
     */
    function query(params) {
        if (!params) return;
        var esc = encodeURIComponent;
        var query = Object.keys(params).map(k => esc(k) + '=' + esc(params[k])).join('&');
        return query;
    }

    /**
     * controller class name
     * 
     * @param  string name
     * @return string
     */
    function controllerClassName(name) {
        var class_name = '';
        var names = name.split('_');
        $.each(names, function (index, value) {
            class_name += upperTopString(value);
        });
        class_name += 'Controller';
        return class_name;
    }

    /**
    * http base
    *
    * @param 
    * @return string
    **/
    function httpBase() {
        var domain = location.hostname;
        var url;
        if (pw_base_url) {
            url = pw_base_url;
        } else {
            url = '//' + domain + '/';
        }
        return url;
    }

    /**
    * project url
    *
    * @param 
    * @return string
    **/
    function projectUrl() {
        var pw_base_url = httpBase();
        var url = pw_base_url;
        if (pw_project_name) {
            url += pw_project_name + '/';
        }
        return url;
    }

    /**
    * controller URL
    *
    * @param controller
    * @param action
    * @return string
    **/
    function controllerUrl(controller, action) {
        if (!controller) return;
        if (!action) return;
        var url = projectUrl() + controller + '/' + action;
        return url;
    }

    /**
    * action URL
    *
    * @param action
    * @return string
    **/
    function actionUrl(dom, action) {
        var controller = $(dom).attr('pw-controller');
        if (!controller) return;
        if (!action) return;
        var url = projectUrl() + controller + '/' + action;
        return url;
    }

    /**
    * Post URL for dom
    *
    * @return string
    **/
    function postUrl(dom) {
        var controller = $(dom).attr('pw-controller');
        var action = $(dom).attr('pw-action');
        if (!controller) return;
        if (!action) return;
        var url = projectUrl() + controller + '/' + action;
        return url;
    }

    /**
    * post api
    *
    * @param string url
    * @param object params
    * @param function callback
    * @param string data_type
    * @return void
    **/
    function post(url, params, callback, data_format) {
        if (!data_format) data_format = 'html';
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
                if (callback) callback(data);
            },
            error: function () {
            }
        });
    }

    /**
    * post api
    *
    * @param string url
    * @param string file_name
    * @param object params
    * @param function callback
    * @return void
    **/
   function download(url, file_name, params, callback) {
        var url_param = query(params);
        url = url + '?' + url_param;
        $.ajax({
            download: file_name,
            href: url,
            success: function (data) {
                if (callback) callback(data);
            },
            error: function () {
            }
        });
    }

    /**
    * upper string for top
    *
    * @param string string
    * @return string
    **/
    function upperTopString(string) {
        var value = string.charAt(0).toUpperCase() + string.slice(1);
        var value = string.substring(0, 1).toUpperCase() + string.substring(1);
        var value = string.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    }

    /**
    * request ajax
    *
    * @param array values
    * @return object
    **/
    var requestAjax = function (values) {
        var $ajax = $.ajax(values);
        var defer = new $.Deferred();
        $ajax.done(function (data, status, $ajax) {
            defer.resolveWith(this, arguments);
        });
        $ajax.fail(function (data, status, $ajax) {
            defer.resolveWith(this, arguments);
        });
        return $.extend({}, $ajax, defer.promise());
    };

    function parallelAjax(requests, callback) {
        var results = [];
        $.each(requests, function (index, value) {
            var $ajax = requestAjax({ url: value.url, data: value.params }).done(function (res, status) {
                if (value.callback) {
                    value.callback(res);
                }
            });
            results.push($ajax);
        });
        $.when.apply(null, results).done(function () {
            if (callback) callback(results);
        });
        $.when.apply(null, results).fail(function () {
        });
    }

    function parallelRequest(requests, callback) {
        var XHRList = [];
        $.each(requests, function (index, value) {
            XHRList.push($.ajax({
                type: "POST",
                cache: false,
                url: value.url,
                data: value.params,
            }));
        });
        $.when.apply($, XHRList).done(function () {
            callback(arguments);
        }).fail(function (ex) {
            alert('ajax error');
        });
    }

}