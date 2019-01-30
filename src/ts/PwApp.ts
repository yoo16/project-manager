/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
var pw_app: any;
var pw_sortable: any;
var pw_form: any;
var pw_base_url = '';
var pw_current_controller = '';
var pw_current_action = '';
var pw_loading_selector = '#main';
var pw_multi_sid = '';

//TODO remove jquery
//$.support.cors = true;

document.addEventListener('DOMContentLoaded', function() {
    pw_app = new PwApp();
    pw_app.multiSessionLink();
    pw_app.pwLoad(); 

    pw_sortable = new PwSortable();
    pw_form = new PwForm();

    pw_current_controller = pw_app.dom({id: 'pw-current-controller'}).value();
    pw_current_action = pw_app.dom({id: 'pw-current-action'}).value();
});


//TODO remove jquery
$(document).on('change', ':file', function () {
    var input = pw_app.dom(this);
    //numFiles = input.get(0).files ? input.get(0).files.length : 1,
    var pattern1 = "/\\/g";
    var pattern2 = "/.*\//";
    var label = input.value().replace(pattern1, '/').replace(pattern2, '');
    input.parent().parent().next(':text').val(label);
});

class PwApp {
    public pw_project_name = '';

    public init = function(params: object) {

    }

    public dom = function(params: object) {
        var instance = new PwNode(params);
        instance.init();
        return instance;
    }
    public urlFor(params: {controller: string, action: string, id: string}) {
        var url_queries = [];
        if (params.controller) url_queries.push(params.controller);
        if (params.action) url_queries.push(params.action);
        if (params.id) url_queries.push(params.id);

        var url_query = url_queries.join('/');
        var url = this.projectUrl() + url_query;
        return url;
    }
    public urlForDom(dom: HTMLFormElement) {
        var pw_node = PwNode.instance({dom: dom});
        var params = {
            controller: pw_node.controller(),
            action: pw_node.action(),
            id: '',
        };
        var url = this.urlFor(params);
        return url;
    }
    public urlQuery(url: string, url_params: any) {
        if (pw_multi_sid) url_params.pw_multi_sid = pw_multi_sid;
        if (url_params) url = url + '?' + this.query(url_params);
        return url;
    }
    private headerGetHtml() {
        const header = new Headers();
        header.append('Content-Type', 'application/xhtml+xml');
        var header_options = { 
            method: 'GET',
            headers: header,
            mode: 'cors',
            cache: 'default',
        };
        return header_options;
    }
    private headerPostForm(values: any) {
        if (pw_multi_sid) values.pw_multi_sid = pw_multi_sid;
        const header = new Headers();
        header.append('Content-Type', 'application/x-www-form-urlencoded');
        var header_options = { 
            method: 'POST',
            headers: header,
            body: this.query(values),
            mode: 'cors',
            cache: 'default',
        };
        return header_options;
    }
    private headerPostJson(json: string) {
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
        return header_options;
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
   public requestGet(url: string, params: any, callback: any, data_type: any) {
    if (!data_type) data_type = 'html';
        $.ajax({
            type: 'GET',
            cache: false,
            url: url,
            data: params,
            dataType: data_type,
            success: function (data :any) {
                if (callback) callback(data);
            },
            error: function () {
            }
        });
    }
    public getHtml(params: any, url_params: any, options: any) {
        var url = this.urlQuery(this.urlFor(params), url_params);
        PwApp.fetchResponse(url, this.headerGetHtml(), options);
    }
    public getByUrl(url: string, url_params: any, options: any) {
        PwApp.fetchResponse(this.urlQuery(url, url_params), this.headerGetHtml(), options);
    }
    public getByDom (dom: HTMLFormElement, url_params: any, options: any) {
        var url = this.urlForDom(dom);
        PwApp.fetchResponse(this.urlQuery(url, url_params), this.headerGetHtml(), options);
    }
    public postHtml (params: any, values: any, options: any) {
        PwApp.fetchResponse(this.urlFor(params), this.headerPostForm(values), options);
    }
    public postByUrl(url: string, values: any, options: any) {
        PwApp.fetchResponse(url, this.headerPostForm(values), options);
    }
    public postByDom (dom: HTMLFormElement, values: any, options: any) {
        PwApp.fetchResponse(this.urlForDom(dom), this.headerPostForm(values), options);
    }
    public postJson (params: any, json: string, options: any) {
        if (!params) return;
        if (!params.controller) return;
        if (!params.action) return;
        if (!json) return;
        PwApp.fetchResponse(this.urlFor(params), this.headerPostJson(json), options);
    }
    //TODO remove function
    public controllerPost (controller: string, action: string, options: any, callback: any, data_format: string) {
        var params = {
            controller: controller,
            action: action,
            pw_multi_sid: '',
        };
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        this.postHtml(params, options, callback);
    }
    //TODO remove function
    public actionGet (dom: HTMLFormElement, action: string, url_params: any, callback: any, data_format: string) {
        if (pw_multi_sid) url_params.pw_multi_sid = pw_multi_sid;
        var options = {callback: callback};
        if (data_format == 'json') {

        } else {
            this.getByUrl(this.actionUrl(dom, action), url_params, options);
        }
    }

    /**
     * 
     * @param string url 
     * @param Object options 
     */
    static fetchResponse (url: string, header_options: any, options: any) {
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

    public pwLoad () {
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
    public multiSessionLink () {
        var dom = pw_app.dom({id: 'pw-multi-session-id'});
        if (!dom) return;
        pw_multi_sid = dom.value('value');
        if (!pw_multi_sid) return;

        [].forEach.call(document.getElementsByTagName('a'), function(element: HTMLFormElement) {
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
    public downloadAsFile (file_name: string, content: string) {
        var a = document.createElement('a');
        a.download = file_name;
        a.href = 'data:application/octet-stream,' + encodeURIComponent(content);
        a.click();
    };
    public requestPage (url: string, params: any) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        window.location.href = pw_app.generateUrl(url, params);
    }
    public download (url: string, file_name: string, url_params: any, options: any) {
        if (pw_multi_sid) url_params.pw_multi_sid = pw_multi_sid;
        var callback = options.callback;
        var error_callback = options.error_callback;
        var is_show_loading = false;

        if (url_params) url+= this.query(url_params);
        if (options.is_show_loading) is_show_loading = options.is_show_loading;
        if (is_show_loading) pw_app.showLoading();

        const header = new Headers();
        header.append('Content-Type', 'application/xhtml+xml');
        var header_options = { 
            method: 'GET',
            headers: header,
            mode: 'cors',
            cache: 'default',
        };
        PwApp.fetchResponse(url, header_options, options);
    }
    public generateUrl (url: string, params: any) {
        var url_param = this.query(params);
        url = url + '?' + url_param;
        return url;
    }
    public generateProjectUrl = function(url: string, params: any) {
        url = pw_app.projectUrl() + url;
        var url_param = this.query(params);
        url = url + '?' + url_param;
        return url;
    }
    public setSession (key: string, value: any) {
        value = JSON.stringify(value);
        localStorage.setItem(key, value);
    }
    public getSession (key: string) {
        var value = localStorage.getItem(key)
        value = JSON.parse(value);
        return value;
    }
    public showLoading = function(selector: string) {
        if (selector) {
            $(selector).LoadingOverlay("show");
        } else {
            $(pw_loading_selector).LoadingOverlay("show");
        }
    }
    public hideLoading = function(selector: string) {
        if (selector) {
            $(selector).LoadingOverlay("hide");
        } else {
            $(pw_loading_selector).LoadingOverlay("hide");
        }
    }
    public checkImageLoading = function(class_name: string, count: number) {
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
    public loadingDom (dom: HTMLFormElement, callback: any, error_callback: any) {
        var selector = '';
        var selector = '';
        var node = PwNode.instance({dom: dom});
        if (selector = node.attr('id')) {};
        pw_app.showLoading(selector);
        node.dom.addEventListener('error', function(e) {
            pw_app.hideLoading(selector);
            //$(dom).off('error');
            if (error_callback) error_callback();
        });
        node.dom.addEventListener('load', function() {
            pw_app.hideLoading(selector);
            //$(dom).off('load');
            if (callback) callback();
        });
    }
    public loadImage = function(url: string, dom: HTMLFormElement, callback: any, error_callback: any) {
        var node = PwNode.instance({dom: dom});
        node.setAttr('src', url);

        pw_app.loadingDom(dom, callback, error_callback);
        var selector = '';
        //if ($(dom).attr('id')) selector = '#' + $(dom).attr('id');
        node.dom.addEventListener('error', function(e) {
            pw_app.hideLoading(selector);
            node.setAttr('src', null);
            //node.hide();
            //node.off('error');
            if (error_callback) error_callback();
        });

        node.dom.addEventListener('load', function() {
            pw_app.hideLoading(selector);
            //node.show();
            //node.off('load');
            if (callback) callback();
        });
    }
    public fileUpload = function(url: string, form_id: string, callback: any, error_callback: any)
    {
        var node = PwNode.instance({id: form_id});
        var form_data = new FormData(node.dom);

        pw_app.showLoading();
        pw_app.postHtml();
    }
    /**
     * controller class name
     * 
     * @param  string name
     * @return string
     */
    static controllerClassName = function(name: string) {
        var class_name = '';
        var names = name.split('_');
        names.forEach(function(index: any) {
            class_name += this.upperTopString(names[index]);
        });
        class_name += 'Controller';
        return class_name;
    }

    //TODO remove jquery
    $(document).on('click', '.pw-click', function () {
        var pw_node = PwNode.instance({dom: this});
        var controller_name = pw_node.controllerClassName();
        if (!controller_name) return;
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller) controller[action](pw_node.dom);
        }
    });


    $(document).on('change', '.pw-change', function () {
        var pw_node = PwNode.instance({dom: this});
        var controller_name = pw_node.controllerClassName();
        if (!controller_name) return;
        var action = pw_node.action();
        if (!action) return;
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

    $(document).on('click', '.action-loading', function() {
        pw_app.showLoading();
    });

    /**
     * query
     * 
     * @param  array params
     * @return string
     */
    public query = function(params: any) {
        if (!params) return;
        var esc = encodeURIComponent;
        var query = Object.keys(params).map(k => esc(k) + '=' + esc(params[k])).join('&');
        return query;
    }

    /**
    * http base
    *
    * @param 
    * @return string
    **/
   public httpBase = function() {
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
   public projectUrl = function() {
        var pw_base_url = this.httpBase();
        var url = pw_base_url;
        if (this.pw_project_name) url += this.pw_project_name + '/';
        return url;
    }

    /**
    * controller URL
    *
    * @param controller
    * @param action
    * @return string
    **/
   public controllerUrl = function(controller: string, action: string) {
        if (!controller) return;
        if (!action) return;
        var url = this.projectUrl() + controller + '/' + action;
        return url;
    }

    /**
    * action URL
    *
    * @param action
    * @return string
    **/
    public actionUrl = function(dom: HTMLFormElement, action: string) {
        var controller = $(dom).attr('pw-controller');
        if (!controller) return;
        if (!action) return;
        var url = this.projectUrl() + controller + '/' + action;
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
    public ajaxPost = function(url: string, params: any, callback: any, data_format: string) {
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
            success: function (data: any) {
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
    public upperTopString(string: string) {
        var value = string.charAt(0).toUpperCase() + string.slice(1);
        var value = string.substring(0, 1).toUpperCase() + string.substring(1);
        var value = string.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    }

}