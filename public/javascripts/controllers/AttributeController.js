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

        function callback(html) {
            PwNode.id('relation_list').html(html);
        }
    }

    this.relation_attribute_list = function(node) {
        console.log(node);
        var params = {};
        params.fk_model_id = node.attr('fk_model_id');
        params.attribute_id = node.attr('attribute_id');

        pw_app.post(node, params, callback);

        function callback(html) {
            PwNode.id('relation_list').html(html);
        }
    }

    this.index_list = function(node) {
        var params = {};
        params.model_id = node.attr('model_id');

        pw_app.post(node, params, callback);

        function callback(html) {
            PwNode.id('index_list').html(html);
        }
    }

    this.unique_attribute_list = function(node) {
        var params = {};
        params.model_id = node.attr('model_id');

        pw_app.post(node, params, callback);

        function callback(html) {
            PwNode.id('unique_attribute_list').html(html);
        }
    }

    this.old_attribute_list = function(node) {
        var params = {};
        params.attribute_id = node.attr('attribute_id');
        params.model_id = node.attr('model_id');
        pw_app.post(node, params, callback);

        function callback(html) {
            PwNode.id('old_attribute_list').html(html);
        }
    }

}