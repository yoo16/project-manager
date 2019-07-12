/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

var DatabaseController = function() {
    var _this = this;
    this.name = 'database';

    this.import_list = function(node) {
        var params = {};
        params.host = PwNode.id(node).value();
        pw_app.postHtml({controller: this.name, action: 'import_list'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('import_list').html(data);
        }
    }

    this.list = function(node) {
        var params = {};
        pw_app.postHtml({controller: this.name, action: 'list'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('database_list').html(data);
        }
    }

    this.new = function(node) {
        var params = {};
        pw_app.postHtml({controller: this.name, action: 'new'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('database_edit').html(data);
        }
    }

    this.edit = function(node) {
        var params = {};
        params.id = node.attr('user_id');
        pw_app.postHtml({controller: this.name, action: 'edit'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('database_edit').html(data);
        }
    }

    this.update = function(node) {
        if (!window.confirm('update user?')) return;
        var params = {};
        params.id = node.attr('user_id');
        //TODO params
        pw_app.postHtml({controller: this.name, action: 'update'}, params, {callback: callback});
        function callback(json) {
            data = JSON.parse(json)
            if (data.errors) {
                console.log(data.errors);
            } else {
                PwNode.id('edit_modal').modal('hide');
                database.list();
            }
        }
    }

    this.delete = function(node) {
        if (!window.confirm('delete user?')) return;

        var params = {};
        params.id = node.attr('user_id');
        pw_app.postHtml({controller: this.name, action: 'delete'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('edit_modal').modal('hide');
            database.list();
        }
    }


    this.columns = function(node) {
        var params = {};
        params.database_id = PwNode.id(node).attr('database_id');
        params.table_name = PwNode.id(node).attr('table_name');

        pw_app.postHtml({controller: this.name, action: 'columns'}, params, {callback: callback});
        function callback(data) {
            PwNode.id('columns').html(data);
        }
    }

    this.updateTableComment = function(node) {
        var params = {};
        params.database_id = PwNode.id(node).attr('database_id');
        params.table_name = PwNode.id(node).attr('table_name');
        params.comment = PwNode.id(node.attr('comment-id')).value();
        pw_app.postHtml({controller: this.name, action: 'update_table_comment'}, params, {callback: callback});
        function callback(data) {
        }
    }

    this.updateColumnComment = function(node) {
        var params = {};
        params.database_id = PwNode.id(node).attr('database_id');
        params.table_name = PwNode.id(node).attr('table_name');
        params.column_name = PwNode.id(node).attr('column_name');
        params.comment = PwNode.id(node.attr('comment-id')).value();
        pw_app.postHtml({controller: this.name, action: 'update_column_comment'}, params, {callback: callback});
        function callback(data) {
        }
    }

    this.closeColumns = function(node) {
        PwNode.id('columns').html('');
    }

}

var database = new DatabaseController();