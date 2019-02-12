/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

var AttributeController = function() {

    this.relation_model_list = function(node) {
        var params = {};
        params.attribute_id = node.attr('attribute_id');

        pw_app.post(node, params, callback);

        function callback(data) {
            $('#relation_list').html(data);
        }
    }

    this.relation_attribute_list = function(node) {
        console.log(node);
        var params = {};
        params.fk_model_id = node.attr('fk_model_id');
        params.attribute_id = node.attr('attribute_id');

        pw_app.post(node, params, callback);

        function callback(data) {
            $('#relation_list').html(data);
        }
    }

    this.unique_attribute_list = function(node) {
        var params = {};
        params.model_id = node.attr('model_id');

        pw_app.post(node, params, callback);

        function callback(data) {
            $('#unique_attribute_list').html(data);
        }
    }

    this.old_attribute_list = function(node) {
        var params = {};
        params.attribute_id = node.attr('attribute_id');
        params.model_id = node.attr('model_id');
        pw_app.post(node, params, callback);

        function callback(data) {
            $('#old_attribute_list').html(data);
        }
    }

}