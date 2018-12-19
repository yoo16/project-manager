/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

var ModelController = function() {

    this.relation_list = function(dom) {
        postApi(apiUrl(this.name, 'list'), this.params, callback);

        function callback(data) {
            $('#database_list').html(data);
        }
    }

    this.old_table_list = function(dom) {
        var params = {};
        params.model_id = $(dom).attr('model_id');
        pw_app.apiPost(dom, params, callback);

        function callback(data) {
            $('#old_table_list').html(data);
        }
    }

}
