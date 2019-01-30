/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */
class PwNode {
    public params :any;
    public dom :HTMLFormElement;

    public constructor(params: any) {
        this.params = params;
    }

    static instance = function(params: any) {
        var instance = new PwNode(params);
        return instance;
    }
    static new = function(params: any) {
        var instance = new PwNode(params);
        return instance;
    }
    public init = function() {
        if (this.params.id) {
            this.dom = document.getElementById(this.params.id);
        } else if (this.params.dom) {
            this.dom = this.params.dom;
        }
    }
    public setValue = function(value: any) {
        if (this.dom) {
            this.dom.setAttribute('value', value);
            return this;
        }
    }
    public setAttr = function(key: string, value: any) {
        if (this.dom) {
            this.dom.setAttribute(key, value);
            return this;
        }
    }
    public bind = function(params: any) {
        if (!params) return;
        if (this.dom) Object.keys(params).map(function(key) { this.dom.setAttribute(key, params[key]) });
    }
    public value = function() {
        if (this.dom) return this.dom.getAttribute('value');
    }
    public attr = function(selector: string) {
        if (!selector) return;
        if (this.dom) return this.dom.getAttribute(selector);
    }
    public html = function(html: string) {
        if (this.dom) return this.dom.innerHTML = html;
    }
    public toggle = function(class_name: string) {
        if (!class_name) return;
        if (this.dom) {
            this.dom.classList.toggle(class_name);
            return this;
        }
    }
    public controller = function() {
        if (this.dom) return this.attr('pw-controller');
    }
    public action = function() {
        if (this.dom) return this.attr('pw-action');
    }

    /**
     * controller class name
     * 
     * @return string
     */
    public controllerClassName = function() {
        if (!this.dom) return;
        var controller_name = this.controller();
        if (!controller_name) return;

        var class_name = '';
        var names = controller_name.split('_');
        if (!names) return;
        names.forEach(function(name: string) {
            class_name += PwNode.upperTopString(name);
        });
        class_name += 'Controller';
        return class_name;
    }

    /**
    * upper string for top
    *
    * @param value string
    * @return string
    **/
    static upperTopString = function(value: string) {
        if (!value) return;
        var value = value.charAt(0).toUpperCase() + value.slice(1);
        var value = value.substring(0, 1).toUpperCase() + value.substring(1);
        var value = value.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    }
}