/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
var log_file_name = '';
var pw_log;

document.addEventListener('DOMContentLoaded', function() {
    pw_log = new PwLog();
    pw_log.base_url = pw_app.projectUrl()
});

var PwLog = function () {
    this.url_params = {};
    this.filename;
    this.html_id = '#log-list';
    this.name = 'pw_admin';
    this.action_list = 'log_list';
    this.action_detail = 'log_file';
    this.base_url = '';

    this.loadList = function() {
        var url = pw_log.base_url + this.name + '/' + this.action_list;

        $(this.html_id).html('');
        pw_app.urlPost(url, this.url_params, callback);

        function callback(json) {
            if (!json) return;
            var values = JSON.parse(json);
            renderHtml(values);
        }

        function renderHtml(values) {
            $(pw_log.html_id).append('<dl>');

            //TODO dom function
            for (var key in values) {
                var log = values[key];
                var attribute = '';
                attribute+= ' href="#"';
                attribute+= ' class="pw-lib"';
                attribute+= ' pw-lib="PwLog"';
                attribute+= ' pw-action="detail"';
                attribute+= ' filename="' + log.filename + '"';
                var dd = '<dd><a' + attribute + '>' + log.basename + '</dd>';
                $(pw_log.html_id).append(dd);
            }

            $(pw_log.html_id).append('</dl>');
        }
    }
    this.detail = function(dom) {
        var url = pw_log.base_url + 'pw_admin/log_file';
        this.filename = $(dom).attr('filename');
        pw_log.filename = this.filename;
        this.url_params.filename = this.filename;

        pw_app.urlPost(url, this.url_params, showLog);
        function showLog(data) {
            var values = nl2br(data);
            $('#log_title').html(pw_log.filename);
            $('#log-contents').html(values);
            $('#log-window').modal('show');
        }
    }
    this.reload = function(dom) {
        if (!pw_log.filename) return;
        var url = pw_log.base_url + 'pw_admin/log_file';
        this.url_params.filename = pw_log.filename;
        pw_app.urlPost(url, this.url_params, callback);
        pw_app.showLoading();
        function callback(data) {
            pw_app.hideLoading();
            var values = nl2br(data);
            $('#log-contents').html(values);
        }
    }
    this.delete = function(dom) {
        if (window.confirm('delete log?')) {
            var url = pw_log.base_url + 'pw_admin/delete_log';
            this.url_params.filename = pw_log.filename;

            pw_app.urlPost(url, this.url_params, callback);
            pw_app.showLoading();
            function callback(data) {
                pw_app.hideLoading();
                $('#log_title').html('');
                $('#log-contents').html('');
                $('#log-window').modal('hide');
            }
        }
    }

    /**
     * return to BR tag
     * 
     * @param string str
     */
    var nl2br = function (str) {
        return str.replace(/\n/g, '<br>');
    };

}