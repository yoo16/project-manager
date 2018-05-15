/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var admin_user;
var action = '';

$(document).ready(function(){
    admin_user = new AdminUserController();
    admin_user.list();
});

var AdminUserController = function() {
    this.params = {};
    this.name = 'user';

    this.new = function(dom) {
        postApi(apiUrl(this.name, 'new'), this.params, callback);

        function callback(data) {
            $('#user_edit').html(data);
        }
    }

    this.edit = function(dom) {
        this.params.id = $(dom).attr('edit-id');
        postApi(apiUrl(this.name, 'edit'), this.params, callback);

        function callback(data) {
            $('#user_edit').html(data);
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
                admin_user.list();
            }
        }
    }

    $(document).on('click', '.action', function() {
        params = {};
        action = $(this).attr('action');
        if (!action) {
            window.alert('not found action');
            return;
        }
        admin_user[action](this);
    }); 

}
