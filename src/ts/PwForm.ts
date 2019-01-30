/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwForm {
    public value = {}

    public init = function(selector_id: string) {
        this.initInput(selector_id)
        this.initSelect(selector_id)
        this.initTextarea(selector_id)
    }
    public initInput = function(selector_id: string) {
        var selector = selector_id + ' input';
        $(selector).each(function() {
            var type = $(this).attr('type');
            var name = $(this).attr('name');
            var default_value = $(this).attr('default_value');
            if (name) {
                if (type == 'checkbox') {
                    $(this).val('');
                    if (default_value) $(this).val(default_value);
                } else if (type == 'radio') {
                    $(this).val('');
                    if (default_value) $(this).val(default_value);
                } else {
                    $(this).val('');
                    if (default_value) $(this).val(default_value);
                }
            }
        });
    }
    public initSelect = function(selector_id: string) {
        var selector = selector_id + ' select option';
        $(selector).each(function() {
            var name = $(this).parent().attr('name');
            var default_value = $(this).attr('default_value');
            if (name) {
                $(this).parent().val('');
                if (default_value) $(this).parent().val(default_value);
            }
        });
    }
    public initTextarea = function(selector_id: string) {
        var selector = selector_id + ' textarea';
        $(selector).each(function() {
            var name = $(this).attr('name');
            var default_value = $(this).attr('default_value');
            if (name) {
                $(this).val('');
                if (default_value) $(this).val(default_value);
            }
        });
    }
    public bind = function(selector_id: string, values: any) {
        this.init(selector_id);
        this.bindInput(selector_id, values);
        this.bindSelect(selector_id, values);
        this.bindTextarea(selector_id, values);
    }
    public bindInput = function(selector_id: string, values: any) {
        var selector = selector_id + ' input';
        $(selector).each(function() {
            var type = $(this).attr('type');
            var name = $(this).attr('name');
            name = pw_form.checkName(name);
            if (name && values[name]) {
                var value = values[name];
                if (type == 'checkbox') {

                } else if (type == 'radio') {
                    $(this).val(value);
                } else {
                    $(this).val(value);
                }
            }
        });
    }
    public bindSelect = function(selector_id: string, values: any) {
        var selector = selector_id + ' select option';
        $(selector).each(function() {
            var name = $(this).parent().attr('name');
            name = pw_form.checkName(name);
            if (name && values[name]) {
                var value = values[name];
                $(this).parent().val(value);
            }
        });
    }
    public bindTextarea = function(selector_id: string, values: any) {
        var selector = selector_id + ' textarea';
        $(selector).each(function() {
            var name = $(this).attr('name');
            name = pw_form.checkName(name);
            if (name && values[name]) {
                var value = values[name];
                $(this).val(value);
            }
        });
    }
    public loadForm = function(selector_id: string) {
        this.loadInput(selector_id);
        this.loadSelect(selector_id);
        this.loadTextarea(selector_id);
    }
    public loadInput = function(selector_id: string) {
        var selector = selector_id + ' input';
        $(selector).each(function() {
            var type = $(this).attr('type');
            var name = $(this).attr('name');

            if (name) {
                if (type == 'checkbox') {
                    pw_form.value[name] = pw_form.checkboxValues(name);
                } else if (type == 'radio') {
                    var checked = $(this).prop('checked');
                    if (checked) pw_form.value[name] = $(this).val();
                } else {
                    pw_form.value[name] = $(this).val();
                }
            }
        });
    }
    public loadSelect = function(selector_id: string) {
        var selector = selector_id + ' select option';
        $(selector).each(function() {
            var name = $(this).parent().attr('name');
            if (name) pw_form.value[name] = $(this).val();
        });
    }
    public loadTextarea = function(selector_id: string) {
        var selector = selector_id + ' textarea';
        $(selector).each(function() {
            var name = $(this).attr('name');
            if (name) pw_form.value[name] = $(this).val();
        });
    }
    /**
     * check name
     * 
     * @param  string name
     * @return string
     */
    public checkName = function (name: string) {
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
    public checkboxValues = function(name: string) {
        var column = '[name="' + name + '"]:checked';
        var checks = [];
        $(column).each(function() {
            var checked = $(this).prop('checked');
            if (checked) {
                checks.push($(this).val());
            }
        });
        return checks;
    }

}