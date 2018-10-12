/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
var pw_app;
var pw_base_url = '';
var pw_current_controller = '';
var pw_current_action = '';
var pw_loadin_selector = '#main';
var pw_multi_sid = '';

$.support.cors = true;
$(document).ready(function () {
    pw_app = new PwController();
    pw_current_controller = $('#pw-current-controller').val();
    pw_current_action = $('#pw-current-action').val();
    pw_app.multiSessionLink();
});

var PwController = function () {
    this.multiSessionLink = function(fileName, content) {
        pw_multi_sid = $('#pw-multi-session-id').val();

        if (!pw_multi_sid) return;

        jQuery('a').each(function() {
            var link = $(this).attr("href");
            if (link) {
                if (link.indexOf('pw_multi_sid') > 0) {

                } else {
                    link = link + "&pw_multi_sid=" + pw_multi_sid;
                    $(this).attr('href', link);
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
    this.post = function (dom, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        post(postUrl(dom), params, callback, data_format);
    }
    this.urlPost = function (url, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        post(url, params, callback, data_format);
    }
    this.controllerPost = function (controller, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        post(controllerUrl(controller, action), params, callback, data_format);
    }
    this.actionPost = function (dom, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        post(actionUrl(dom, action), params, callback, data_format);
    }
    this.controllerGet = function (controller, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        requestGet(controllerUrl(controller, action), params, callback, data_format);
    }
    this.actionGet = function (dom, action, params, callback, data_format) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        requestGet(actionUrl(dom, action), params, callback, data_format);
    }
    this.download = function (url, file_name, params, callback) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        download(url, file_name, params, callback);
    }
    this.generateUrl = function(url, params) {
        var url_param = $.param(params);
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
    this.showLoading = function() {
        $(pw_loadin_selector).LoadingOverlay("show");
    }
    this.hideLoading = function() {
        $(pw_loadin_selector).LoadingOverlay("hide");
    }
    this.checkImageLoading = function(class_name, count) {
        var displayed_count = 0;

        $(class_name).off('load');
        $(class_name).off('error');
        $(class_name).on('error', function(e) {
            pw_app.hideLoading();
        });
        $(class_name).on('load', function() {
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
    $(document).on('click', '.pw-click', function () {
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
        var url_param = $.param(params);
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
    * post api
    *
    * @param string url
    * @param object params
    * @param function callback
    * @param String data_type
    * @return void
    **/
   function requestGet(url, params, callback, data_type) {
    if (!data_type) data_type = 'html';
        $.ajax({
            type: 'GET',
            cache: false,
            url: url,
            data: params,
            dataType: data_type,
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