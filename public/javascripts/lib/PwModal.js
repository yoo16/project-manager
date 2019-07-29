/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

var PwModal = function() {
    var _this = this;

    this.backdrop_id = 'pw_backdrop';
    this.load_show_class_name = 'pw-modal';
    this.show_class_name = 'pw-modal-show';
    this.close_class_name = 'pw-modal-close';
    this.pw_backdrop_element;
    
    this.createBackground = function() {
        if (!_this.pw_backdrop_element) {
            _this.pw_backdrop_element = document.createElement('div');
            _this.pw_backdrop_element.id = 'pw_backdrop';
            _this.pw_backdrop_element.classList.add('pw_backdrop');
            document.body.appendChild(_this.pw_backdrop_element);
        }
    }

    this.show = function(selector) {
        if (!selector) return;
        _this.createBackground();

        var background_node = PwNode.id(_this.backdrop_id);
        background_node.show();

        var node = PwNode.id(selector);
        if (node) node.showInline();
    }
    this.hide = function(selector) {
        var background_node = PwNode.id(_this.backdrop_id);
        background_node.hide();

        var node = PwNode.id(selector);
        if (node) node.hide();
        PwNode.byClass(_this.load_show_class_name).hide();
    }
    this.showHandler = function(event) {
        _this.show(this.getAttribute('selector'));
    }
    this.hideHandler = function(event) {
        _this.hide(this.getAttribute('selector'));
    }
    this.reloadEvents = function() {
        //IE dosen't work elements.forEach()
        let load_show_elements = document.getElementsByClassName(_this.load_show_class_name);
        [].forEach.call(load_show_elements, function(element) {
            let event = element.getAttribute('event');
            let action = element.getAttribute('action');
            if (element.id && event && action) {
                if (event == 'load' && action == 'show') {
                    _this.show(element.id);
                }
            }
        });
        let show_elements = document.getElementsByClassName(_this.show_class_name);
        [].forEach.call(show_elements, function(element) {
            element.addEventListener('click', _this.showHandler, false);
        });
        let close_elements = document.getElementsByClassName(_this.close_class_name);
        [].forEach.call(close_elements, function(element) {
            element.addEventListener('click', _this.hideHandler, false);
        });
    }
}

var pw_modal = new PwModal();
document.addEventListener('DOMContentLoaded', function() {
    pw_modal.reloadEvents();
});
