/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';

var PwDom = function (params) {
    this.params = params;
    this.init = function() {
        if (this.params.id) {
            this.dom = document.getElementById(this.params.id);
        } else if (this.params.dom) {
            this.dom = this.params.dom;
        }
    }
    this.value = function() {
        if (this.dom) return this.dom.getAttribute('value');
    }
    this.attr = function(selector) {
        if (this.dom) return this.dom.getAttribute(selector);
    }
    this.html = function(html) {
        if (this.dom) return this.dom.innerHTML = html;
    }
    this.toggle = function(class_name) {
        if (this.dom) return this.dom.classList.toggle(class_name);
    }
    this.controller = function() {
        if (this.dom) return this.attr('pw-controller');
    }
    this.action = function() {
        if (this.dom) return this.attr('pw-action');
    }

    /**
     * controller class name
     * 
     * @return string
     */
    this.controllerClassName = function() {
        if (!this.dom) return;
        var controller_name = this.controller();
        if (!controller_name) return;

        var class_name = '';
        var names = controller_name.split('_');
        $.each(names, function (index, value) {
            class_name += upperTopString(value);
        });
        class_name += 'Controller';
        return class_name;
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
}