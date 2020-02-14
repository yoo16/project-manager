/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (http://yoo-s.com/)
 */

var PageController = function() {
    var _this = this;
    this.name = 'page';

    this.showArtisan = function(node) {
        var controller_name = node.attr('page_name') + 'Controller';
        var page_id = node.attr('page_id');

        var artisan_name_node = PwNode.id('artisan_name');
        artisan_name_node.html(controller_name);

        var page_id_node = PwNode.id('page_id');
        page_id_node.setValue(page_id);

        pw_ui.showModal('artisan_controller_window');

        this.changePathForArtisan(node);
    }

    this.hideArtisan = function(node) {
        pw_ui.hideModal('artisan_controller_window');
    }

    this.changePathForArtisan = function(node) {
        var params = {};
        params.id = node.attr('page_id');

        let user_project_setting = PwNode.id('artisan_controller_user_project_setting_id');
        params.user_project_setting_id = user_project_setting.selected();

        pw_app.postHtml({controller: this.name, action: 'artisan_controller_command'}, params, {callback: callback});
        function callback(json) {
            let values = JSON.parse(json);
            PwNode.id('artisan_make_controller_cmd').html(values.cmd);
        }
    }
}

var page = new PageController();