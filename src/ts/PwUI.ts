/**
 * PwUI
 * 
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
class PwUI {
    public delete_file_window_name = 'delete-file-window';
    public error_window_name = 'pw-error';
    public popup_name = 'pw-popup';
    public confirm_dialog_name = 'confirm-dialog';
    public confirm_delete_name = 'confirm-delete';
    public delete_checkbox_name = 'delete_checkbox';

    /**
     * convert jQuery id
     */
    public jqueryId = function(id:string) {
        if (id) {
            let start = id.slice(0, 1)
            if (start != '#') id = '#' + id;
        }
        return id;
    }

    //remove jquery
    public showModal = function(selector:string) {
        selector = this.jqueryId(selector);
        //$(selector).modal('show');
    }
    //remove jquery
    public hideModal = function(selector:string) {
        selector = this.jqueryId(selector);
        //$(selector).modal('hide');
    }

    public openWindow = function(url:string, params:any) {
        var queryArray:Array<string> = [];
        [].forEach.call(params, function(key:any) {
            queryArray.push(key + '=' + params[key]);
        });
        var query = queryArray.join(',');
        if (url) window.open(url, 'new', query);
    }

/**
     * confirm delete
     */
    public deleteConfirmHandler = function(event:any) {
        var delete_id = this.getAttribute('delete_id');
        if (!delete_id) return;
        PwNode.id('from_delete_id').setValue(delete_id);
        pw_ui.showModal('delete-window');
    }
    public reloadDeleteConfirm = function() {
        //IE dosen't work elements.forEach()
        let elements = document.getElementsByClassName(pw_ui.confirm_delete_name);
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('click', pw_ui.deleteConfirmHandler, false);
        });
    }

    /**
     * popup
     */
    public popupEventHandler = function(event:any) {
        var window_name = '_blank';
        var window_option = null;
        if (this.getAttribute('window_name')) window_name = this.getAttribute('window_name');
        if (this.getAttribute('window_option')) window_option = this.getAttribute('window_option');
        window.open(this.href, window_name, window_option).focus();
        event.preventDefault();
        event.stopPropagation();
    }
    public reloadPopupEvent = function() {
        //IE dosen't work elements.forEach()
        let elements = document.getElementsByClassName(pw_ui.popup_name);
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('click', pw_ui.popupEventHandler, false);
        });
    }

    /**
     * confirm dialog
     */
    //TODO remove event?
    public confirmDialogHandler = function(event:any) {
        var message = '';
        if (this.getAttribute('message')) message = this.getAttribute('message');
        pw_ui.reloadConfirmDialogEvent();
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    }
    public reloadConfirmDialogEvent = function() {
        //IE dosen't work elements.forEach()
        let elements = document.getElementsByClassName(pw_ui.confirm_dialog_name);
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('click', pw_ui.confirmDialogHandler, false);
        });
    }

    /**
     * delete check
     */
    public deleteCheckbox = function() {
        //IE dosen't work elements.forEach()
        let elements = document.getElementsByClassName(pw_ui.delete_checkbox_name);
        [].forEach.call(elements, function(element:HTMLElement) {
            element.addEventListener('change', function(event:any) {
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

}

var pw_ui = new PwUI();
document.addEventListener('DOMContentLoaded', function() {
    pw_ui.deleteCheckbox();
    pw_ui.reloadDeleteConfirm();
    pw_ui.reloadConfirmDialogEvent();
    pw_ui.reloadPopupEvent();
});