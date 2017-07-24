/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var database;
var action = '';

$(document).ready(function(){
    model = new ModelController();
});

var ModelController = function() {
    this.params = {};
    this.name = 'model';

    this.relation_list = function(dom) {
        alert('list');
        postApi(apiUrl(this.name, 'list'), this.params, callback);

        function callback(data) {
            $('#database_list').html(data);
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
        model[action](this);
    }); 

}
