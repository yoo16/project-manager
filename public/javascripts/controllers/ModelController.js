/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

var ModelController = function() {
    var _this = this;
    this.name = 'model';

    this.relation_list = function(node) {
        var params = {};
        params.model_id = node.attr('model_id')
        pw_app.postHtml({controller: this.name, action: 'list'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('database_list').html(data);
        }
    }

    this.old_table_list = function(node) {
        var params = {};
        params.model_id = node.attr('model_id');
        pw_app.postHtml({controller: _this.name, action: 'old_list'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('old_table_list').html(data);
        }
    }

}
