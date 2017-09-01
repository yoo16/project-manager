/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var database;
var action = '';

$(document).ready(function(){
    database = new DatabaseController();
});

var DatabaseController = function() {
    this.params = {};
    this.name = 'database';

    this.import_list = function(dom) {
        var params = {};
        params.host = $(dom).val();

        console.log(params);
        pw_app.apiPost(dom, params, callback);

        function callback(data) {
            $('#import_list').html(data);
        }
    }

    this.list = function(dom) {
        postApi(apiUrl(this.name, 'list'), this.params, callback);

        function callback(data) {
            $('#database_list').html(data);
        }
    }

    this.new = function(dom) {
        postApi(apiUrl(this.name, 'new'), this.params, callback);

        function callback(data) {
            $('#database_edit').html(data);
        }
    }

    this.edit = function(dom) {
        this.params.id = $(dom).attr('edit-id');
        postApi(apiUrl(this.name, 'edit'), this.params, callback);

        function callback(data) {
            $('#database_edit').html(data);
        }
    }

    this.update = function(dom) {
        if (!window.confirm('update user?')) return;

        this.params.user = formParseJson('#form-edit');

        postApi(apiUrl(this.name, 'update'), this.params, callback);

        function callback(json) {
            data = $.parseJSON(json)
            if (data.errors) {
                //window.alert(data);
            } else {
                $('#edit_modal').modal('hide');
                database.list();
            }
        }
    }

    this.delete = function(dom) {
        if (!window.confirm('delete user?')) return;

        this.params.user = formParseJson('#form-edit');

        postApi(apiUrl(this.name, 'delete'), this.params, callback);

        function callback(data) {
            $('#edit_modal').modal('hide');
            database.list();
        }
    }


    this.columns = function(dom) {
        this.params.database_id = $(dom).attr('database_id');
        this.params.table_name = $(dom).attr('table_name');

        var url = apiUrl(this.name, 'columns');

        postApi(url, this.params, callback);

        function callback(data) {
            $('#columns').html(data);
        }
    }

    this.updateTableComment = function(dom) {
        this.params.database_id = $(dom).attr('database_id');
        this.params.table_name = $(dom).attr('table_name');

        var comment_id = '#' + $(dom).attr('comment-id');
        this.params.comment = $(comment_id).val();

        var url = apiUrl(this.name, 'update_table_comment');
        postApi(url, this.params, callback);

        function callback(data) {
            //$('#edit_modal').modal('hide');
            //database.list();
            //database.columns(dom);
        }
    }

    this.updateColumnComment = function(dom) {
        this.params.database_id = $(dom).attr('database_id');
        this.params.table_name = $(dom).attr('table_name');
        this.params.column_name = $(dom).attr('column_name');

        var comment_id = '#' + $(dom).attr('comment-id');
        this.params.comment = $(comment_id).val();

        var url = apiUrl(this.name, 'update_column_comment');
        postApi(url, this.params, callback);

        function callback(data) {
            //$('#edit_modal').modal('hide');
            //database.list();
            //database.columns(dom);
        }
    }

    this.closeColumns = function(dom) {
        $('#columns').html('');
    }

    $(document).on('click', '.action', function() {
        params = {};
        action = $(this).attr('action');
        if (!action) {
            window.alert('not found action');
            return;
        }
        database[action](this);
    }); 

}
