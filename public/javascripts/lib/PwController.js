/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';

var PwController = function () {
    this.init = function(params) {

    }

    this.isIE = function() {
        var userAgent = window.navigator.userAgent.toLowerCase();
        if( userAgent.match(/(msie|MSIE)/) || userAgent.match(/(T|t)rident/) ) return true;
        return false;
    }

    this.ieVersion = function() {
        var userAgent = window.navigator.userAgent.toLowerCase();
        if (this.isIE()) return userAgent.match(/((msie|MSIE)\s|rv:)([\d\.]+)/)[3];
        return '';
    }

    this.isEdge = function() {
        var userAgent = window.navigator.userAgent.toLowerCase();
        if (userAgent.indexOf('edge') != -1) {
            return true;
        }
    }

    this.currentController = function() {
        return PwNode.id('pw-current-controller').value();
    }
    this.dom = function(params) {
        var instance = new PwNode(params);
        instance.init();
        return instance;
    }
    this.urlQuery = function(params) {
        return query(params);
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
    this.headerGet = function() {
        var header = { 
            method: 'GET',
            headers: {'Content-Type': 'application/xhtml+xml'},
            credentials: 'include',
            body: body,
            mode: 'cors',
            cache: 'default',
        };
        return header;
    }
    this.headerPostValues = function(values) {
        var header = { 
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            credentials: 'include',
            body: query(values),
            mode: 'cors',
            cache: 'default',
        };
        return header;
    }
    this.headerPostJson = function(json) {
        var header = { 
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            credentials: 'include',
            body: json,
            mode: 'cors',
            cache: 'default',
        };
        return header;
    }
    this.getHtml = function (params, values, options) {
        var url = this.urlFor(params, values);
        if (this.isIE()) {
            ajaxGet(url, values, options);
        } else {
            fetchRequest(url, this.headerGet(), options);
        }
    }
    this.post = function (node, values, callback) {
        var params = {
            controller: node.controller(),
            action: node.action(),
        };
        if (pw_multi_sid) values.pw_multi_sid = pw_multi_sid;
        pw_app.postHtml(params, values, {callback: callback});
    }
    this.postHtml = function (params, values, options) {
        var url = this.urlFor(params);
        if (this.isIE()) {
            ajaxPost(url, values, options);
        } else {
            fetchRequest(url, this.headerPostValues(values), options);
        }
    }
    this.postJson = function (params, json, options) {
        if (!params) return;
        if (!params.controller) return;
        if (!params.action) return;
        if (!json) return;
        var url = this.urlFor(params);
        if (this.isIE()) {
            //options.data_format = 'json';
            ajaxPost(url, json, options);
        } else {
            fetchRequest(url, this.headerPostJson(json), options);
        }
    }
    this.postByUrl = function (url, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var options = {callback:callback, data_format:data_format};
        ajaxPost(url, params, options);
    }
    this.controllerPost = function (controller, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var url = this.urlFor({controller: controller, action: action});
        var options = {callback:callback, data_format:data_format};
        ajaxPost(url, params, options);
    }
    this.actionGet = function (node, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var url = this.urlFor({controller: node.controller(), action: action});
        var options = {callback:callback, data_format:data_format};
        ajaxGet(url, params, options);
    }
    this.download = function (url, file_name, params, callback) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        download(url, file_name, params, callback);
    }

    /**
     * fetch request
     * 
     * @param string url 
     * @param Object header_options 
     * @param Object options 
     */
    function fetchRequest(url, header_options, options) {
        var callback = options.callback;
        var error_callback = options.error_callback;
        var is_show_loading = false;
        if (options.is_show_loading) is_show_loading = options.is_show_loading;
        if (is_show_loading) pw_app.showLoading(options.loading_selector);

        fetch(url, header_options).catch(function(err) {
            if (is_show_loading) pw_app.hideLoading();
            throw new Error('post error')
        }).then(function(response) {
            if (is_show_loading) pw_app.hideLoading();
            //const promise = response.text();
            if (response.ok) {
                return response.text().then(function(text) {
                    return text;
                });
            } else {
                if (error_callback) error_callback(response);
            }
        }).then(function(text) {
            if (callback) callback(text);
        }); 
    }

    this.pwLoad = function(params) {
        var $pw_load = document.getElementsByClassName('pw-load');
        for (var $i = 0; $i < $pw_load.length; $i++) {
            var element = $pw_load[$i];
            var pw_node = PwNode.byElement(element);
            var controller_name = pw_node.controller();
            if (!controller_name) return;
    
            var function_name = pw_node.functionName();
            var controller_class_name = pw_node.controllerClassName();
            if (controller_class_name in window) {
                var controller = new window[controller_class_name]();
                if (function_name && (function_name in controller)) {
                    var is_run = true;
                    if (params) is_run = (params.controller == controller_name && params.function == function_name);
                    if (is_run) controller[function_name](pw_node);
                }
            }
        }
        //TODO remove jquery
        $('#pw-error').modal('show');
    }

    /**
     * confirm dialog
     * 
     */
    this.confirmDialog = function() {
        document.querySelectorAll('.confirm-dialog').forEach(function(element) {
            element.addEventListener('click', function(event) {
                var message = '';
                if (element.getAttribute('message')) message = element.getAttribute('message');
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });
    }

    /**
     * pw-click
     * 
     */
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('pw-click')) eventAction(event);
    });

    /**
     * pw-change
     * 
     */
    document.addEventListener('change', function(event) {
        if (event.target.classList.contains('pw-change')) eventAction(event);
    });


    //TODO remove jquery
    document.addEventListener('change', function(event) {
        if (event.target.id == 'pw_upload_file') {
            var pw_node = PwNode.byElement(event.target);
            var label = pw_node.value().replace(/\\/g, '/').replace(/.*\//, '');
            PwNode.id('pw_upload_file_text').setValue(label);
        }
    });

    /**
     * confirm delete
     * 
     */
    document.addEventListener('click', function(event) {
        if(event.target.classList.contains('confirm-delete')) {
            var delete_id = PwNode.byElement(event.target).attr('delete_id');
            if (!delete_id) return;
            PwNode.id('from_delete_id').setValue(delete_id);
            //remove jquery
            $('.delete-window').modal();
        }
    });

    /**
     * loading
     * 
     */
    document.addEventListener('click', function(event) {
        if(event.target.classList.contains('action-loading')) {
            pw_app.showLoading();
        }
    });

    this.multiSessionLink = function(fileName, content) {
        var pw_multi_session_id = PwNode.id('pw-multi-session-id');
        if (!pw_multi_session_id) return;
        pw_multi_sid = pw_multi_session_id.value();
        if (!pw_multi_sid) return;

        [].forEach.call(document.getElementsByTagName('a'), function(element) {
            var node = PwNode.byElement(element);
            var link = '';
            if (node.attr('is_not_pw_multi_sid')) return;
            if (link = node.attr('href')) {
                if (link.indexOf('pw_multi_sid') == -1) {
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
    this.showLoading = function(selector_name) {
        if (!selector_name) selector_name = pw_loading_selector;
        var selector_node = PwNode.id(selector_name);
        if (selector_node) {
            //TODO selector object
            if (selector_name != 'body') selector_name = this.jqueryId(selector_name);
            $(selector_name).LoadingOverlay('show');
        } else {
            $.LoadingOverlay('show');
        }
    }
    this.hideLoading = function(selector_name) {
        if (!selector_name) selector_name = pw_loading_selector;
        var selector_node = PwNode.id(selector_name);
        if (selector_node) {
            //TODO selector object
            if (selector_name != 'body') selector_name = this.jqueryId(selector_name);
            $(selector_name).LoadingOverlay('hide');
        } else {
            $.LoadingOverlay('hide');
        }
    }
    /*
     * convert jQuery id
     */
    this.jqueryId = function(id) {
        if (id) {
            var start = id.slice(0, 1)
            if (start != '#') id = '#' + id;
        }
        return id;
    }
    this.checkImageLoading = function(class_name, count) {
        console.log(class_name);
        var displayed_count = 0;

        // $(class_name).off('load');
        // $(class_name).off('error');
        // $(class_name).on('error', function(e) {
        //     $(this).hide();
        //     pw_app.hideLoading();
        // });
        // $(class_name).on('load', function() {
        //     $(this).show();
        //     if (count) {
        //         displayed_count++;
        //         if (count == displayed_count) {
        //             pw_app.hideLoading();
        //         }
        //     } else {
        //         pw_app.hideLoading();
        //     }
        // });
        
        pw_app.hideLoading();
        // var pw_node = PwNode.byClass(class_name);
        // let loadHandler = function(event) {
        //     pw_app.hideLoading();
        //     pw_node.element.removeEventListener('load', loadHandler);
        // }
        // pw_node.element.addEventListener('load', loadHandler, false)
    }
    this.loadingDom = function(node, callback, error_callback) {
        var selector = '';
        if (node.getID()) selector = this.jqueryId(node.getID());
        pw_app.showLoading(selector);

        let loadHandler = function(event) {
            pw_app.hideLoading(selector);
            if (callback) callback();
            node.element.removeEventListener('load', loadHandler);
        }
        node.element.addEventListener('load', loadHandler, false);

        let errorHandler = function(event) {
            pw_app.hideLoading(selector);
            if (callback) error_callback();
            node.element.removeEventListener('error', errorHandler);
        }
        node.element.addEventListener('error', errorHandler, false);
    }
    this.loadImage = function(url, node, callback, error_callback) {
        url+= '&serial=' + new Date().getTime();
        node.setAttr('src', url);
        pw_app.loadingDom(node);

        var selector = '';
        if (node.getID()) selector = this.jqueryId(node.getID());
        let loadHandler = function(event) {
            pw_app.hideLoading(selector);
            node.show();
            if (callback) callback();
            node.element.removeEventListener('load', loadHandler);
        }
        node.element.addEventListener('load', loadHandler, false);

        let errorHandler = function(event) {
            pw_app.hideLoading(selector);
            node.attr('src', null);
            node.hide();
            if (callback) error_callback();
            node.element.removeEventListener('error', errorHandler);
        }
        node.element.addEventListener('error', errorHandler, false);
    }
    this.confirmDeleteImage = function(controller, node, delete_id_column) {
        var link_delete_image = PwNode.id('link_delete_image');
        link_delete_image.setAttr('pw-controller', controller);
        link_delete_image.setAttr('pw-action', 'delete_image');
        link_delete_image.setAttr(delete_id_column, node.attr(delete_id_column));
        //TODO remove jquery
        $('.delete-file-window').modal('show');
    }
    this.deleteImage = function(params) {
        //TODO remove jqeury
        $('.delete-file-window').modal('hide');
        var delete_id_column = params.delete_id_column;
        var url = pw_app.urlFor(
            {controller: params.controller, action: 'delete_image'},
            {delete_id_column: params.node.attr(delete_id_column)}
            );

        pw_app.postByUrl(url, null, 
            function (data, status, xhr) {
                if (params.image && params.callback) params.callback(params.image);
            }
        );
    }
    this.showDeleteConfirmImage = function() {
        PwNode.id('link_confirm_delete_image').show();
    }
    this.hideDeleteConfirmImage = function() {
        PwNode.id('link_confirm_delete_image').hide();
    }
    //TODO remove jquery
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

    this.openWindow = function(url, params) {
        var queryArray = [];
        Object.keys(params).forEach(function (key) {
            queryArray.push(key + '=' + params[key]);
        });
        var query = queryArray.join(',');
        if (url) window.open(url, 'new', query);
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
        //remove jquery
        $.each(names, function (index, value) {
            class_name += upperTopString(value);
        });
        class_name += 'Controller';
        return class_name;
    }

    this.loadPopup = function() {
        var popupEvent = function(event) {
            var option = this.href.replace(/^[^\?]+\??/,'').replace(/&/g, ',');
            window.open(this.href, this.rel, option).focus();
            event.preventDefault();
            event.stopPropagation();
        }
        //TODO remove jquery
        $("a.pw-popup").each(function(i) {
            $(this).click(popupEvent);
            $(this).keypress(popupEvent);
        });
    }

    /**
     * query
     * 
     * @param  array params
     * @return string
     */
    function query(params) {
        if (!params) return;
        //var esc = encodeURIComponent;
        var queryArray = [];
        Object.keys(params).forEach(function (key) { return queryArray.push(key + '=' + encodeURIComponent(params[key])); });
        var query = queryArray.join('&');
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
        //TODO remove jQuery
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
        if (pw_project_name) url += pw_project_name + '/';
        return url;
    }

    /**
    * post api
    *
    * @param string url
    * @param object data 
    * @param object options 
    * @return void
    **/
    function ajaxPost(url, data, options) {
        options.method = 'POST';
        ajaxRequest(url, data, options);
    }

    /**
    * ajax Get    
    *
    * @param string url
    * @param object data
    * @param object options 
    * @return void
    **/
    function ajaxGet(url, data, options) {
        options.method = 'GET';
        ajaxRequest(url, data, options);
    }

    /**
    * ajax Get    
    *
    * @param string url
    * @param object data
    * @param object options 
    * @return void
    **/
   function ajaxRequest(url, data, options) {
        var data_format = 'html';
        var callback;
        var method = 'GET';
        if (options) {
            if (options.data_format) data_format = options.data_format;
            if (options.callback) callback = options.callback;
            if (options.method) method = options.method;
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
                if (callback) callback(result);
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
    * event action
    *
    * @param Event event
    * @return void
    **/
    function eventAction(event) {
        var pw_node = PwNode.byElement(event.target);
        var lib_name = pw_node.attr('pw-lib');
        if (lib_name) return libAction(event);

        var name = pw_node.controller();
        if (!name) return;

        var action = pw_node.action();
        if (!action) return;

        var controller_name = pw_node.controllerClassName();
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller) controller[action](pw_node);
        } 
    }

    function libAction(event) {
        var pw_node = PwNode.byElement(event.target);
        var lib_name = pw_node.attr('pw-lib');
        if (!lib_name) return;

        var action = pw_node.action();
        if (!action) return

        if (lib_name in window) {
            var controller = new window[lib_name]();
            if (action in controller) controller[action](pw_node);
        }
    }

}

//TODO remove jquery
$.support.cors = true;

var pw_app = new PwController();
var pw_base_url = '';
var pw_current_controller = '';
var pw_current_action = '';
var pw_loading_selector = 'main';
var pw_multi_sid = '';

document.addEventListener('DOMContentLoaded', function() {
    pw_app.multiSessionLink();
    pw_app.pwLoad(); 
    //TODO
    pw_app.loadPopup();
    if (PwNode.id('pw-current-controller').value()) pw_app.pw_current_controller = PwNode.id('pw-current-controller').value();
    if (PwNode.id('pw-current-action').value()) pw_app.pw_current_action = PwNode.id('pw-current-action').value();
    pw_app.confirmDialog();
});
