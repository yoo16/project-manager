/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */
var PwNode = /** @class */ (function () {
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
        this.show = function () {
            if (this.dom) this.dom.style.display = '';
                //this.dom.style.visibility = 'visibility';
        }
        this.hide = function () {
            if (this.dom) this.dom.style.display = 'none';
                //this.dom.style.visibility = 'hidden';
        }
        this.controller = function () {
            if (this.dom)
                return this.attr('pw-controller');
        };
        this.action = function () {
            if (this.dom)
                return this.attr('pw-action');
        };
        this.fadeOut = function () {
            this.dom.classList.add('fadeout');
            var dom = this.dom;
            setTimeout(function() { dom.style.display = "none"; }, 1000);
        }
        this.fadeIn = function () {
            this.dom.classList.add('fadein');
            var dom = this.dom;
            setTimeout(function() { dom.style.display = "block"; }, 1000);
        }

        /**
         * controller class name
         *
         * @return string
         */
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
        instance.init();
        return instance;
    };
    /**
    * upper string for top
    *
    * @param value string
    * @return string
    **/
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
