/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var attribute;
var action = '';
var relation_attribute_id;

$(document).ready(function(){
    attribute = new AttributeController();
});

var AttributeController = function() {
    this.params = {};
    this.name = 'attribute';

    this.relation_model_list = function(dom) {
        var params = {};
        params.attribute_id = $(dom).attr('attribute-id');
        postApi(apiUrl(this.name, action), params, callback);

        function callback(data) {
            $('#relation_list').html(data);
        }
    }

    this.relation_attribute_list = function(dom) {
        var params = {};
        params.fk_model_id = $(dom).attr('fk_model_id');
        params.attribute_id = $(dom).attr('attribute_id');
        postApi(apiUrl(this.name, action), params, callback);

        function callback(data) {
            $('#relation_list').html(data);
        }
    }

    $(document).on('click', '.action', function() {
        params = {};
        action = $(this).attr('action');
        if (!action) {
            window.alert('not found action');
            return;
        }
        attribute[action](this);
    }); 

}