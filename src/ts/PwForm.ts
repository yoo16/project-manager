/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
class PwForm {
    public value:any = {};

    static getByName = function(name:any) {
        var elements = document.forms[name].elements;
        if (!elements) return;
        var length = elements.length;
        var model_name = '';
        var values:any;
        for (var i = 0; i <= length; i++) {
            var element = PwNode.byElement(elements[i]);
            if (model_name = element.attr('pw-model')) {
                var name = element.name();
                values[name] = element.value();
            }
        }
        return values;
    }
    public init = function(selector_id:string) {
        this.initInput(selector_id)
        this.initSelect(selector_id)
        this.initTextarea(selector_id)
    }
    public initInput = function(selector_id:string) {
        var selector = selector_id + ' input';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var type = node.attr('type');
            var name = node.attr('name');
            var default_value = node.attr('default_value');
            if (name) {
                if (type == 'checkbox') {
                    node.setValue('');
                    if (default_value) node.setValue(default_value);
                } else if (type == 'radio') {
                    node.setValue('');
                    if (default_value) node.setValue(default_value);
                } else {
                    node.setValue('');
                    if (default_value) node.setValue(default_value);
                }
            }
        });
    }
    public initSelect = function(selector_id:string) {
        var selector = selector_id + ' select option';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var name = node.parent().attr('name');
            var default_value = element.attr('default_value');
            if (name) {
                node.parent().setValue('');
                if (default_value) node.parent().setValue(default_value);
            }
        });
    }
    public initTextarea = function(selector_id:string) {
        var selector = selector_id + ' textarea';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var name = node.attr('name');
            var default_value = node.attr('default_value');
            if (name) {
                node.setValue('');
                if (default_value) node.setValue(default_value);
            }
        });
    }
    public bind = function(selector_id:string, values:any) {
        this.init(selector_id);
        this.bindInput(selector_id, values);
        this.bindSelect(selector_id, values);
        this.bindTextarea(selector_id, values);
    }
    public bindInput = function(selector_id:string, values:any) {
        var selector = selector_id + ' input';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var type = node.attr('type');
            var name = node.attr('name');
            name = this.checkName(name);
            if (name && values[name]) {
                var value = values[name];
                if (type == 'checkbox') {

                } else if (type == 'radio') {
                    //TODO
                    node.setValue(value);
                } else {
                    node.setValue(value);
                }
            }
        });
    }
    public bindSelect = function(selector_id:string, values:any) {
        var selector = selector_id + ' select option';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var name = node.parent().attr('name');
            name = this.checkName(name);
            if (name && values[name]) { node.parent().setValue(values[name]); }
        });
    }
    public bindTextarea = function(selector_id:string, values:any) {
        var selector = selector_id + ' textarea';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var name = node.attr('name');
            name = this.checkName(name);
            if (name && values[name]) { node.setValue(values[name]); }
        });
    }
    public loadForm = function(selector_id:string) {
        this.loadInput(selector_id);
        this.loadSelect(selector_id);
        this.loadTextarea(selector_id);
    }
    public loadInput = function(selector_id:string) {
        var selector = selector_id + ' input';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var type = node.attr('type');
            var name = node.attr('name');
            if (name) {
                if (type == 'checkbox') {
                    pw_form.value[name] = pw_form.checkboxValues(name);
                } else if (type == 'radio') {
                    var checked = node.checked();
                    if (checked) pw_form.value[name] = node.value();
                } else {
                    pw_form.value[name] = node.value();
                }
            }
        });
    }
    public loadSelect = function(selector_id:string) {
        var selector = selector_id + ' select option';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var name = node.parent().attr('name');
            if (name) pw_form.value[name] = node.value();
        });
    }
    public loadTextarea = function(selector_id:string) {
        var selector = selector_id + ' textarea';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var name = node.attr('name');
            if (name) pw_form.value[name] = node.value();
        });
    }
    /**
     * check name
     * 
     * @param  string name
     * @return string
     */
    public checkName(name:string) {
        if (!name) return;
        var names;
        if (name.indexOf('[') != -1) {
            names = name.split('[');
            name = names[1];
        }
        if (name.indexOf(']') != -1) {
            names = name.split(']');
            name = names[0];
        }
        return name;
    }
    /**
     * checkboxValues
     * 
     * @param  string name
     * @return string
     */
    public checkboxValues(name:string) {
        var selector = '[name="' + name + '"]:checked';
        let elements = PwNode.byQuery(selector).elements;
        if (!elements) return;
        var checks:Array<any> = [];
        [].forEach(elements, function(element:any) {
            var node = PwNode.byElement(element);
            var checked = node.checked();
            if (checked) {
                checks.push(node.value());
            }
        });
        return checks;
    }

}

var pw_form = new PwForm();
document.addEventListener('DOMContentLoaded', function() {
});
