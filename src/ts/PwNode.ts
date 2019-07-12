/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */
class PwNode {
    public params :any;
    public dom :HTMLFormElement;
    public elements: any;
    public element: any;
    public controller_column: string = 'pw-controller';
    public action_column: string = 'pw-action';
    public function_column: string = 'pw-function';

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
    public init = function () {
        if (this.params.id) {
            this.element = document.getElementById(this.params.id);
        } else if (this.params.element) {
            this.element = this.params.element;
        }
        if (this.params.name) {
            this.setName(this.params.name);
            this.node_list = document.getElementsByName(this.params.name);
        }
        if (this.params.class_name) {
            this.setClassName(this.params.class_name);
            //TODO node_list type
            this.node_list = document.getElementsByClassName(this.params.class_name);
            this.html_collection = document.getElementsByClassName(this.params.class_name);
            //this.elements = Array.from(this.html_collection);
            this.elements = [].slice.call(this.html_collection);
        }
        if (this.params.query) {
            this.elements = document.querySelectorAll(this.params.query);
        }
        return this;
    };
    public setValue = function (value:any) {
        if (this.element) {
            this.element.value = value;
        }
        return this;
    }
    public getValue = function() {
        return this.value();
    }
    public value = function () {
        if (this.element) return this.element.value;
    }
    public setValues = function (value:any) {
        var elements;
        if (this.node_list) {
            elements = this.node_list;
        } else if (this.elements) {
            elements = this.elements;
        }
        if (elements) [].forEach.call(elements, function(element:any) {
            element.value = value;
        });
    }
    public first = function() {
        if (this.elements) return this.elements[0];
    }
    public last = function() {
        if (this.elements) {
            var index = this.length - 1;
            if (index <= 0) index = 0
            return this.elements[index];
        }
    }
    public name = function () {
        if (this.name) return this.element.name;
    }
    public setName = function (name:string) {
        if (name) this.name = name;
    }
    public setClassName = function (name:string) {
        if (name) this.class_name = name;
    }
    public selected = function () {
        if (this.element) return this.element.value;
    }
    public check = function(is_checked:boolean) {
        if (is_checked) {
            this.element.checked = 1;
        } else {
            this.element.checked = null;
        }
    }
    public checked = function () {
        var elements;
        if (this.node_list) {
            elements = this.node_list;
        } else if (this.elements) {
            elements = this.elements;
        }
        if (elements) {
            var checked = null;
            [].forEach.call(elements, function (element:any) {
                if (element.checked) return checked = element.value;
            });
            return checked;
        } else if (this.element) {
            return this.element.checked;
        }
    }
    public toggleCheckAll = function(is_checked:boolean) {
        if (is_checked) {
            this.checkAll();
        } else {
            this.uncheckAll();
        }
    }
    public checkAll = function () {
        var elements = null;
        if (this.node_list) elements = this.node_list;
        if (this.elements) elements = this.elements;
        if (elements) elements.forEach(function (element:any) { element.checked = 1; });
    }
    public html = function (html:string) {
        if (this.element) return this.element.innerHTML = html;
        if (this.elements) [].forEach.call(this.elements, function(element:any) { element.innerHTML = html });
    };
    public setAttr = function (key:string, value:any) {
        if (this.element) {
            var node = PwNode.byElement(this.element);
            node.element.setAttribute(key, value);
            this.element.setAttribute(key, value);
            return this;
        }
    }
    public getAttr = function (selector:string) {
        return this.attr(selector);
    }
    public attr = function (selector:string) {
        if (!selector) return;
        if (this.element) return this.element.getAttribute(selector);
    }
    public bind = function (params:any) {
        if (!params) return;
        if (this.element) {
            Object.keys(params).map(function (key) {
                this.element.setAttribute(key, params[key]);
            });
        }
    }
    public parent = function() {
        if (this.element && this.element.parentNode) {
            return PwNode.byElement(this.element.parentNode);
        }
    }
    public remove = function() {
        if (this.element) this.element.remove();
        if (this.elements) {
            [].forEach.call(this.elements, function(element:HTMLElement) {
                element.parentNode.removeChild(element);
            });
        }
    }
    public show = function () {
        if (this.element) PwNode.setDisplay(this.element, '');
        if (this.elements) [].forEach.call(this.elements, function(element:HTMLElement) {
            this.setDisplay(element, '')
        });
    }
    public hide = function () {
        if (this.element) PwNode.setDisplay(this.element, 'none');
        if (this.elements) [].forEach.call(this.elements, function(element:HTMLElement) {
            this.setDisplay(element, 'none')
        });
    }
    static setDisplay(element:HTMLElement, value:any) {
        element.style.display = value
    }
    public disabled = function () {
        if (this.element) PwNode.setDisabled(this.element.disabled, true);
        if (this.elements) [].forEach.call(this.elements, function(element:any) { 
            this.setDisabled(element.disabled, true)
        });
    }
    public abled = function () {
        if (this.element) PwNode.setDisabled(this.element.disabled, false);
        if (this.elements) [].forEach.call(this.elements, function(element:any) {
            this.setDisabled(element.disabled, false)
        });
    }
    static setDisabled(element:any, value:any) {
        element.style.display = value
    }
    public getID = function () {
        if (this.element) return this.element.id;
    }
    public toggle = function (class_name:string) {
        if (!class_name) return;
        if (this.element) {
            this.element.classList.toggle(class_name);
            return this;
        }
    };
    public controller = function () {
        if (this.element) return this.attr(this.controller_column);
    };
    public functionName = function () {
        if (this.element) return this.attr(this.function_column);
    };
    public action = function () {
        if (this.element) return this.attr(this.action_column);
    }
    public controllerClassName = function() {
        if (!this.dom) return;
        var controller_name = this.controller();
        if (!controller_name) return;

        var class_name = '';
        var names = controller_name.split('_');
        if (!names) return;
        names.forEach(function(name:string) {
            class_name += PwNode.upperTopString(name);
        });
        class_name += 'Controller';
        return class_name;
    }
    //static create node functions
    static id = function (id:string) {
        if (!id) return;
        var instance = new PwNode({id: id});
        instance.init();
        return instance;
    }
    static byName = function (name:string) {
        var instance = new PwNode({name: name});
        instance.init();
        return instance;
    }
    static byClass = function (class_name:string) {
        var instance = new PwNode({class_name: class_name});
        instance.init();
        return instance;
    }
    static byElement = function (element:any) {
        var instance = new PwNode({element: element});
        instance.init();
        return instance;
    }
    static byQuery = function (query:string) {
        var instance = new PwNode({query: query});
        instance.init();
        return instance;;
    }
    //static function
    static upperTopString = function(value:string) {
        if (!value) return;
        var value = value.charAt(0).toUpperCase() + value.slice(1);
        value = value.substring(0, 1).toUpperCase() + value.substring(1);
        value = value.replace(/^[a-z]/g, function (val) { return val.toUpperCase(); });
        return value;
    }
}