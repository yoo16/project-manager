/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var contact;

$(document).ready(function(){
    contact = new ContactController();
});

var ContactController = function() {
    this.params = {};
    this.name = 'contact';

    this.staff_list = function(dom) {
        pw_app.post(dom, this.params, callback);

        function callback(data) {
            $('#add_staff_list').html(data);
        }
    }


}
