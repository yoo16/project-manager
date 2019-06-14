/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
class PwApp {
    public pw_current_controller = '';
    public pw_current_action = '';
    public pw_loading_selector = 'main';

    public init = function() {

    }

    public isIE = function() {
        var userAgent = window.navigator.userAgent.toLowerCase();
        if( userAgent.match(/(msie|MSIE)/) || userAgent.match(/(T|t)rident/) ) return true;
        return false;
    }

    public ieVersion = function() {
        var userAgent = window.navigator.userAgent.toLowerCase();
        if (this.isIE()) return userAgent.match(/((msie|MSIE)\s|rv:)([\d\.]+)/)[3];
        return '';
    }

    public isEdge = function() {
        var userAgent = window.navigator.userAgent.toLowerCase();
        if (userAgent.indexOf('edge') != -1) {
            return true;
        }
    }

    public currentController = function() {
        return PwNode.id('pw-current-controller').value();
    }
    public dom = function(params:any) {
        var instance = new PwNode(params);
        instance.init();
        return instance;
    }
    public urlQuery = function(params:any) {
        return this.query(params);
    }
    public urlFor = function (params:any, options:Object) {
        var url_queries = [];
        if (params.controller) url_queries.push(params.controller);
        if (params.action) url_queries.push(params.action);
        if (params.id) url_queries.push(params.id);

        var url_query = url_queries.join('/');
        var url = PwApp.projectUrl() + url_query;

        if (options) url = url + '?' + this.query(options);
        return url;
    }
    public headerGet = function(body:any) {
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
    public headerPostValues = function(values:any) {
        var header = { 
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            credentials: 'include',
            body: this.query(values),
            mode: 'cors',
            cache: 'default',
        };
        return header;
    }
    public headerPostJson = function(json:any) {
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
    public getHtml = function (params:any, values:any, options:any) {
        var url = this.urlFor(params, values);
        if (this.isIE()) {
            this.ajaxGet(url, values, options);
        } else {
            this.fetchRequest(url, this.headerGet(), options);
        }
    }
    public post = function (node:PwNode, values:any, callback:any) {
        var params = {
            controller: node.controller(),
            action: node.action(),
        };
        if (pw_multi_sid) values.pw_multi_sid = pw_multi_sid;
        pw_app.postHtml(params, values, {callback: callback});
    }
    public postHtml = function (params:any, values:any, options:any) {
        var url = this.urlFor(params);
        if (this.isIE()) {
            this.ajaxPost(url, values, options);
        } else {
            this.fetchRequest(url, this.headerPostValues(values), options);
        }
    }
    public postJson = function (params:any, json:JSON, options:any) {
        if (!params) return;
        if (!params.controller) return;
        if (!params.action) return;
        if (!json) return;
        var url = this.urlFor(params);
        if (this.isIE()) {
            this.ajaxPost(url, json, options);
        } else {
            this.fetchRequest(url, this.headerPostJson(json), options);
        }
    }
    public postByUrl = function (url:String, params:any, callback:any, data_format:String) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var options = {callback:callback, data_format:data_format};
        this.ajaxPost(url, params, options);
    }
    public controllerPost = function (controller:String, action:String, params:any, callback:any, data_format:String) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var url = this.urlFor({controller: controller, action: action});
        var options = {callback:callback, data_format:data_format};
        this.ajaxPost(url, params, options);
    }
    public actionGet = function (node:PwNode, action:String, params:any, callback:any, data_format:String) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var url = this.urlFor({controller: node.controller(), action: action});
        var options = {callback:callback, data_format:data_format};
        this.ajaxGet(url, params, options);
    }
    public download = function (url:String, file_name:String, params:any, callback:any) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        var url_param = this.query(params);
        url = url + '?' + url_param;

        $.ajax({
            download: file_name,
            href: url,
            success: function (data:any) {
                if (callback) callback(data);
            },
            error: function () {
            }
        });
    }

    public pwLoad = function(params:any) {
        var pw_load = document.getElementsByClassName('pw-load');
        for (var i = 0; i < pw_load.length; i++) {
            var element:any = pw_load[i];
            var pw_node = PwNode.byElement(element);
            var controller_name:string = pw_node.controller();
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
        //$('#pw-error').modal('show');
    }

    /**
     * pw-click handler
     *
     */
    public pwClickHandler = function(event:any) {
        pw_app.eventAction(this);
        event.preventDefault();
    }

    /**
     * confirm dialog
     * 
     */
    public confirmDialog = function() {
        //IE dosen't work elements.forEach()
        let elements = document.querySelectorAll('.confirm-dialog');
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('click', function(event) {
                var message = '';
                if (element.getAttribute('message')) message = element.getAttribute('message');
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            }, false);
        });
    }

    /**
     * delete
     * 
     */
    public deleteCheckbox = function() {
        //IE dosen't work elements.forEach()
        let elements = document.querySelectorAll('.delete_checkbox');
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('change', function(event) {
                let pw_node = PwNode.byElement(element);
                let delete_link_node = PwNode.id('delete_link');
                let is_checked = pw_node.checked();
                if (is_checked) {
                    delete_link_node.abled();                    
                } else {
                    delete_link_node.disabled();                    
                }
            });
        });
    }

    /**
     * fetch request
     * 
     * @param string url 
     * @param Object header_options 
     * @param Object options 
     */
    public fetchRequest = function(url:any, header_options:any, options:any) {
        var callback = options.callback;
        var error_callback = options.error_callback;
        var is_show_loading = false;
        if (options.is_show_loading) is_show_loading = options.is_show_loading;
        if (is_show_loading) pw_app.showLoading(options.loading_selector);

        fetch (url, header_options).catch(function(err:Error) {
            if (is_show_loading) pw_app.hideLoading(null);
            throw new Error('post error')
        }).then(function(response:Response) {
            if (is_show_loading) pw_app.hideLoading(null);
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

    public multiSessionLink = function() {
        var pw_multi_session_id = PwNode.id('pw-multi-session-id');
        if (!pw_multi_session_id) return;
        pw_multi_sid = pw_multi_session_id.value();
        if (!pw_multi_sid) return;

        [].forEach.call(document.getElementsByTagName('a'), function(element:HTMLElement) {
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

    public downloadAsFile = function(fileName:string, content:any) {
        var a:any = document.createElement('a');
        a.download = fileName;
        a.href = 'data:application/octet-stream,' + encodeURIComponent(content);
        a.click();
    };
    public requestPage = function (url:string, params:any) {
        if (pw_multi_sid) params.pw_multi_sid = pw_multi_sid;
        window.location.href = pw_app.generateUrl(url, params);
    }
    public generateUrl = function (url:string, params:any) {
        var url_param = this.query(params);
        url = url + '?' + url_param;
        return url;
    }
    public generateProjectUrl = function(url:string, params:any) {
        url = pw_app.projectUrl() + url;
        var url_param = this.query(params);
        url = url + '?' + url_param;
        return url;
    }
    public projectUrl = function () {
        return PwApp.projectUrl();
    }
    public setSession = function (key:any, value:any) {
        value = JSON.stringify(value);
        localStorage.setItem(key, value);
    }
    public getSession = function (key:any) {
        var value = localStorage.getItem(key)
        value = JSON.parse(value);
        return value;
    }
    public showLoading = function(selector_name:string) {
        if (!selector_name) selector_name = this.pw_loading_selector;
        var selector_node = PwNode.id(selector_name);
            console.log(selector_node);
        if (selector_node) {
            //TODO selector object
            if (selector_name != 'body') selector_name = this.jqueryId(selector_name);
            $(selector_name).LoadingOverlay('show');
        } else {
            $.LoadingOverlay('show');
        }
    }
    public hideLoading = function(selector_name:string) {
        if (!selector_name) selector_name = this.pw_loading_selector;
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
    public jqueryId = function(id:String) {
        if (id) {
            var start = id.slice(0, 1)
            if (start != '#') id = '#' + id;
        }
        return id;
    }
    //TODO
    public checkImageLoading = function(class_name:String, count:Number) {
        pw_app.hideLoading(null);
        //pw_app.hideLoading();
        // var pw_node = PwNode.byClass(class_name);
        // let loadHandler = function(event) {
        //     pw_app.hideLoading();
        //     pw_node.element.removeEventListener('load', loadHandler);
        // }
        // pw_node.element.addEventListener('load', loadHandler, false)
    }
    public loadingDom = function(node:PwNode, callback:any, error_callback:any) {
        var selector = '';
        if (node.getID()) selector = node.getID();
        pw_app.showLoading(selector);

        let loadHandler = function(event:Event) {
            pw_app.hideLoading(selector);
            if (callback) callback();
            node.element.removeEventListener('load', loadHandler);
        }
        node.element.addEventListener('load', loadHandler, false);

        let errorHandler = function(event:Event) {
            pw_app.hideLoading(selector);
            if (callback) error_callback();
            node.element.removeEventListener('error', errorHandler);
        }
        node.element.addEventListener('error', errorHandler, false);
    }
    public loadImage = function(url:String, node:PwNode, callback:any, error_callback:any, loading_node:any) {
        url+= '&serial=' + new Date().getTime();
        node.setAttr('src', url);
        if (loading_node) pw_app.loadingDom(loading_node, null, null);

        var selector = '';
        let loadHandler = function(event:Event) {
            if (loading_node) pw_app.hideLoading(loading_node);
            node.show();
            if (callback) callback();
            node.element.removeEventListener('load', loadHandler);
        }
        node.element.addEventListener('load', loadHandler, false);

        let errorHandler = function(event:Event) {
            pw_app.hideLoading(selector);
            node.setAttr('src', null);
            node.hide();
            if (callback) error_callback();
            node.element.removeEventListener('error', errorHandler);
        }
        node.element.addEventListener('error', errorHandler, false);
    }
    public confirmDeleteImage = function(controller:String, node:PwNode, delete_id_column:any) {
        var link_delete_image = PwNode.id('link_delete_image');
        link_delete_image.setAttr('pw-controller', controller);
        link_delete_image.setAttr('pw-action', 'delete_image');
        link_delete_image.setAttr(delete_id_column, node.attr(delete_id_column));
        pw_ui.showModal('delete-file-window');
    }
    public deleteImage = function(params:any) {
        pw_ui.hideModal('delete-file-window');
        var delete_id_column = params.delete_id_column;
        var url = pw_app.urlFor(
            {controller: params.controller, action: 'delete_image'},
            {delete_id_column: params.node.attr(delete_id_column)}
            );

        pw_app.postByUrl(url, null, callback, null);
        function callback(data:any, status:any, xhr:XMLHttpRequest) {
            if (params.image && params.callback) params.callback(params.image);
        }
    }
    public showDeleteConfirmImage = function() {
        PwNode.id('link_confirm_delete_image').show();
    }
    public hideDeleteConfirmImage = function() {
        PwNode.id('link_confirm_delete_image').hide();
    }
    //TODO remove jquery
    public fileUpload = function(url:string, form_id:string, callback:any, error_callback:any)
    {
        if (!$(form_id)) return;
        if (!$(form_id).get(0)) return;
        var form_data = new FormData($(form_id).get(0));

        pw_app.showLoading(null);
        $.ajax({
            url  : url,
            type : 'POST',
            data : form_data,
            cache       : false,
            contentType : false,
            processData : false,
            dataType    : 'html'
        })
        .done(function(data:any, status:any, xhr:XMLHttpRequest) {
            pw_app.hideLoading(null);
            callback(data, status, xhr);
        })
        .fail(function(xhr:XMLHttpRequest, status:any, errorThrown:any){
            pw_app.hideLoading(null);
            error_callback(xhr, status, errorThrown);
        });
    }

    public openWindow = function(url:any, params:any) {
        var queryArray:Array<String> = [];
        [].forEach.call(params, function(key:any) {
            queryArray.push(key + '=' + params[key]);
        });
        var query = queryArray.join(',');
        if (url) window.open(url, 'new', query);
    }

    public loadPopup = function() {
        var popupEvent = function(event:Event) {
            var option = this.href.replace(/^[^\?]+\??/,'').replace(/&/g, ',');
            window.open(this.href, this.rel, option).focus();
            event.preventDefault();
            event.stopPropagation();
        }
        let elements = PwNode.byQuery('a.pw-popup').elements;
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('click', popupEvent, true);
        });
    }

    /**
     * query
     * 
     * @param  array params
     * @return string
     */
    public query(params:any) {
        if (!params) return;
        var queryArray:Array<String> = [];
        Object.keys(params).forEach(function (key) { return queryArray.push(key + '=' + encodeURIComponent(params[key])); });
        var query = queryArray.join('&');
        return query;
    }

    /**
    * http base
    *
    * @param 
    * @return string
    **/
    static httpBase() {
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
    static projectUrl() {
        var pw_base_url = PwApp.httpBase();
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
    public ajaxPost(url:String, data:any, options:any) {
        options.method = 'POST';
        this.ajaxRequest(url, data, options);
    }

    /**
    * ajax Get    
    *
    * @param string url
    * @param object data
    * @param object options 
    * @return void
    **/
    public ajaxGet(url:String, data:any, options:any) {
        options.method = 'GET';
        this.ajaxRequest(url, data, options);
    }

    /**
    * ajax Get    
    *
    * @param string url
    * @param object data
    * @param object options 
    * @return void
    **/
   public ajaxRequest(url:String, data:any, options:any) {
        var is_show_loading = false;
        if (options.is_show_loading) is_show_loading = options.is_show_loading
        if (is_show_loading) pw_app.showLoading(options.loading_selector);

        var data_format = 'html';
        var callback:any;
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
            success: function (result:any) {
                if (is_show_loading) pw_app.hideLoading(options.loading_selector);
                if (callback) callback(result);
            },
            error: function () {
            }
        });
    }

    /**
    * event action
    *
    * @param Element element
    * @return void
    **/
    public eventAction(element:any) {
        var pw_node = PwNode.byElement(element);
        var lib_name = pw_node.attr('pw-lib');
        if (lib_name) return this.libAction(element);

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

    /**
    * lib action
    *
    * @param Element element
    * @return void
    **/
    public libAction(element:HTMLElement) {
        var pw_node = PwNode.byElement(element);
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
//$.support.cors = true;

var pw_app = new PwApp();
var pw_base_url = '';
var pw_multi_sid = '';
var pw_base_url = '';
var pw_project_name = '';


document.addEventListener('DOMContentLoaded', function() {
    pw_app.multiSessionLink();
    pw_app.pwLoad(null); 
    //TODO
    if (PwNode.id('pw-current-controller').value()) pw_app.pw_current_controller = PwNode.id('pw-current-controller').value();
    if (PwNode.id('pw-current-action').value()) pw_app.pw_current_action = PwNode.id('pw-current-action').value();
    pw_app.loadPopup();
    pw_app.confirmDialog();
    pw_app.deleteCheckbox();
});

/**
 * pw-click
 * 
 */
document.addEventListener('click', function(event:any) {
    let element = event.target;
    if (element.classList.contains('pw-click')) {
        pw_app.eventAction(event.target);
    //TODO child click event or use PwClick()
    } else if (element.parentNode) {
        if (element.parentNode.classList.contains('pw-click')) {
            pw_app.eventAction(element.parentNode);
        }
    }
}, false);

/**
 * pw-change
 * 
 */
document.addEventListener('change', function(event:any) {
    let element = event.target;
    if (element.classList.contains('pw-change')) {
        pw_app.eventAction(event.target);
    }
}, false);


//TODO remove jquery
document.addEventListener('change', function(event:any) {
    let element = event.target;
    if (element.id == 'pw_upload_file') {
        var pw_node = PwNode.byElement(element);
        var label = pw_node.value().replace(/\\/g, '/').replace(/.*\//, '');
        PwNode.id('pw_upload_file_text').setValue(label);
    }
}, false);

/**
 * confirm delete
 * 
 */
document.addEventListener('click', function(event:any) {
    if(event.target.classList.contains('confirm-delete')) {
        var delete_id = PwNode.byElement(event.target).attr('delete_id');
        if (!delete_id) return;
        PwNode.id('from_delete_id').setValue(delete_id);
        pw_ui.showModal('delete-window');
    }
}, false);

/**
 * loading
 * 
 */
document.addEventListener('click', function(event:any) {
    if(event.target.classList.contains('action-loading')) {
        pw_app.showLoading(null);
    }
}, false);