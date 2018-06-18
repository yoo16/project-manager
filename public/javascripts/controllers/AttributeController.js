/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var AttributeController = function() {

    this.relation_model_list = function(dom) {
        var params = {};
        params.attribute_id = $(dom).attr('attribute_id');

        pw_app.post(dom, params, callback);

        function callback(data) {
            $('#relation_list').html(data);
        }
    }

    this.relation_attribute_list = function(dom) {
        var params = {};
        params.fk_model_id = $(dom).attr('fk_model_id');
        params.attribute_id = $(dom).attr('attribute_id');

        pw_app.post(dom, params, callback);

        function callback(data) {
            $('#relation_list').html(data);
        }
    }

    this.unique_attribute_list = function(dom) {
        var params = {};
        params.model_id = $(dom).attr('model_id');

        pw_app.post(dom, params, callback);

        function callback(data) {
            $('#unique_attribute_list').html(data);
        }
    }

    this.old_attribute_list = function(dom) {
        var params = {};
        params.attribute_id = $(dom).attr('attribute_id');
        params.model_id = $(dom).attr('model_id');
        pw_app.post(dom, params, callback);

        function callback(data) {
            $('#old_attribute_list').html(data);
        }
    }

}