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
        artisan_name_node.setValue(controller_name);

        var page_id_node = PwNode.id('page_id');
        page_id_node.setValue(page_id);

        pw_ui.showModal('artisan-window');
    }

    this.hideArtisan = function(node) {
        pw_ui.hideModal('artisan-window');
    }
}

var page = new PageController();