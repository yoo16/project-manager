/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

'use strict';
var pw_app;
var pw_base_url = '';

$(document).ready(function(){
    pw_app = new PwController();
});

var PwController = function() {
    this.post = function(dom, params, callback, data_format = 'html') {
        post(postUrl(dom), params, callback, data_format);
    }
    this.urlPost = function(url, params, callback, data_format = 'html') {
        post(url, params, callback, format);
    }
    this.projectUrl = function() {
        return projectUrl();
    }

    $(document).on('click', '.pw-app', function() {
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

    $(document).on('click', '.pw-lib', function() {
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
     * @param  String name
     * @return String
     */
    function controllerClassName(name) {
        var class_name = '';
        var names = name.split('_');
        $.each(names, function(index, value) {
            class_name+= upperTopString(value);
        });
        class_name+= 'Controller';
        return class_name;
    }

    /**
    * URL生成    
    *
    * @param 
    * @return String
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
    * プロジェクトURL生成    
    *
    * @param 
    * @return String
    **/
    function projectUrl() {
      var pw_base_url = httpBase();
      var url = pw_base_url;
      if (pw_project_name) {
          url+= pw_project_name + '/';
      }
      return url;
    }

    /**
    * Post URL
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
    * @param String data_type
    * @return void
    **/
    function post(url, params, callback, data_type = 'html') {
        $.ajax({
            type: 'POST',
            cache: false,
            url: url,
            data: params,
            dataType: data_type,
            success: function(data) {
                if (callback) callback(data);
            },
            error:function() {
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
        var value = string.replace(/^[a-z]/g, function (val) {return val.toUpperCase();});
        return value;
    }

    var formParseJson = function(form_id) {
        var form = $(form_id);
        var values = {};
        $(form.serializeArray()).each(function(i, v) {
            values[v.name] = v.value;
        });
        var json = JSON.stringify(values)
        return json;
    }

    var requestAjax = function(values){
        var $ajax = $.ajax(values);
        var defer = new $.Deferred();
        $ajax.done(function(data, status, $ajax) {
            defer.resolveWith(this, arguments);
        });
        $ajax.fail(function(data, status, $ajax) {
            defer.resolveWith(this, arguments);
        });
        return $.extend({}, $ajax, defer.promise());
    };

    /**
     * render html
     * 
     * @param  String html_id [description]
     * @param  String data    [description]
     * @return void
     */
     function renderHtml(html_id, data) {
        $(html_id).html(data);
    }

    function parallelAjax(requests, callback) {
        var results = [];
        $.each (requests, function(index, value) {
            var $ajax = requestAjax({url: value.url, data: value.params}).done(function(res, status) {
                if (value.callback) {
                    value.callback(res);
                }
            });
            results.push($ajax);
        });
        $.when.apply(null, results).done(function(){
            if (callback) callback(results);
        });
        $.when.apply(null, results).fail(function(){
        });
    }

    function parallelRequest(requests, callback) {
        var XHRList = [];
        $.each (requests, function(index, value) {
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
