/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var log_file_name = '';

/**
 * Log List
 *
 * @return void
 */
$(document).on('click', '.action-log_list', function() {
    var html_id = '#log-list';
    var url = projectUrl() + 'admin/log_list';
    var params = {};

    $(html_id).html('');
    postApi(url, params, showLog);

    function showLog(json) {
        values = JSON.parse(json);
        renderHtml(values);
    }

    function renderHtml(values) {

        $(html_id).append('<dl>');

        for (var key in values) {
            var log = values[key];
            var filename = 'filename=' + log.filename;;
            var dd = '<dd><a href="#" class="action-show_log" ' + filename  + '>' + log.name + '</dd>';
            $(html_id).append(dd);
        }

        $(html_id).append('</dl>'); 
    }
}); 

/**
 * Log File
 *
 * @return void
 */
$(document).on('click', '.action-show_log', function() {
    var url = projectUrl() + 'admin/log_file';
    var params = {};
    log_file_name = $(this).attr('filename');

    params.filename = log_file_name;
    postApi(url, params, showLog);

    function showLog(data) {
        values = nl2br(data);
        $('#log-contents').html(values);
        $('#log-window').fadeIn(700);
    }
}); 

/**
 * Delete Log File
 *
 * @return void
 */
$(document).on('click', '.action-delete-log', function() {
    if (window.confirm('delete log?')) {
        var url = projectUrl() + 'admin/delete_log';
        var params = {};
        params.filename = log_file_name;
        jsonApi(url, params, success);

        function success(data) {
            if (data.success) {
                $('#log-window').fadeOut(700);
                $('.action-log_list').trigger('click');
            }
        }
    }
}); 

