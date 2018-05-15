/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var admin_user;

$(document).ready(function(){
    admin_user = new AdminController();
    admin_user.list();
});

var AdminUserController = function() {
    this.params = {};
    this.name = 'user';

    this.edit = function(dom) {

    }

    this.update = function(dom) {

    }

}
