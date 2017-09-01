/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

'use strict';
var pw_app;

$(document).ready(function(){
    pw_app = new PHPWorkController();
});

var PHPWorkController = function() {
    this.apiPost = function(dom, params, callback = null) {
        var controller = $(dom).attr('pw-controller');
        var action = $(dom).attr('pw-action');
        var url = apiUrl(controller, action);

        postApi(url, params, callback);
    }

    /**
    * URL生成    
    *
    * @param 
    * @return String
    **/
    function http_base() {
        var domain = location.hostname;
        var url;
        if (base_url) {
            url = base_url;
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
      var base_url = http_base();
      var url = base_url;
      if (project_name) {
          url+= project_name + '/';
      }
      return url;
    }

    /**
    * API URL
    *
    * @param string controller
    * @param string action
    * @return string
    **/
    function apiUrl(controller, action) {
        var url = projectUrl() + controller + '/' + action;
        return url;
    }

    /**
    * post api
    *
    * @param string url
    * @param object params
    * @param function callback
    * @return void
    **/
    function postApi(url, params, callback) {
        //showIndicator();
        $.ajax({
            type: 'POST',
            cache: false,
            url: url,
            data: params,
            dataType: 'html',
            success: function(data) {
                //hideIndicator();
                if (callback) callback(data);
            },
            error:function() {
                //hideIndicator();
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

    function controllerAction(obj) {
        var controller_name = $(obj).attr('pw-controller') + 'Controller';
        var action = $(obj).attr('pw-action');

        if (!controller_name) console.log('Not found pw-controller');
        if (!action) console.log('Not found pw-action');

        controller_name = upperTopString(controller_name);
        if (controller_name in window) {
            var controller = new window[controller_name]();
            if (action in controller) {
                controller[action](obj);
            }
        }
    }

    $(document).on('click', 'a.pw-app', function() {
        controllerAction(this);
    }); 

    $(document).on('click', '.btn.pw-app', function() {
        controllerAction(this);
    }); 

    /**
     * change action
     *
     * TODO public click action
     * 
     * @param  {[type]} 
     * @return {[type]}
     */
    $(document).on('change', 'select.pw-app', function() {
        controllerAction(this);
    }); 

}
