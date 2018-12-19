/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

var api;

$(document).ready(function(){
    api = new ApiController();
});

var ApiController = function() {
    this.params = {};
    this.name = 'api';

    this.client_secret = function(dom) {
        pw_app.post(dom, this.params, callback);

        function callback(json) {
            $('#client_secret_value').html(json);

            var values = jQuery.parseJSON(json);
            $('#client_secret').val(values.client_secret);
        }
    }

    this.staff_client_secret = function(dom) {
        this.params.login_name = $('#login_name').val();
        this.params.password = $('#password').val();

        pw_app.post(dom, this.params, callback);

        function callback(json) {
            $('#staff_client_secret_value').html(json);
        }
    }

    this.update_spot_token = function(dom) {
        this.params.client_secret = $('#client_secret').val();
        this.params.spot_id = $('#update_spot_token-spot_id').val();

        pw_app.post(dom, this.params, callback);

        function callback(json) {
            $('#update_spot_token_value').html(json);
            $('#spot_token-spot_id').val($('#update_spot_token-spot_id').val());

            var values = jQuery.parseJSON(json);
            if (values.spot_token) $('.spot_token').val(values.spot_token);
        }
    }

    this.spot_tokens = function(dom) {
        pw_app.post(dom, this.params, callback);

        function callback(json) {
            $('#spot_tokens_value').html(json);
        }
    }

    this.spot_token = function(dom) {
        this.params.spot_id = $('#spot_token-spot_id').val();

        pw_app.post(dom, this.params, callback);

        function callback(json) {
            $('#spot_token_value').html(json);

            var values = jQuery.parseJSON(json);
            if (values.spot_token) $('.spot_token').val(values.spot_token);
        }
    }

}
