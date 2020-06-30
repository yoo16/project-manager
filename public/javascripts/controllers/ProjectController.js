/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2019 Yohei Yoshikawa (http://yoo-s.com/)
 */

var ProjectController = function () {
    var _this = this;
    this.name = 'project';

    this.export_php = function (node) {
        let api_action = node.attr('api-action');
        let api_controller = node.controller();
        let user_project_setting_id = node.attr('user_project_setting_id');
        let project_id = PwNode.id('project_id').value();
        //TODO
        if (!project_id) project_id = node.attr('project_id');
        let model_id = node.attr('model_id');
        var is_overwrite = 0;
        if (PwNode.byName('is_overwrite').checked()) is_overwrite = 1;

        if (!project_id) window.alert('Not found project_id');
        if (!user_project_setting_id) window.alert('Not found user_project_setting_id');
        if (api_controller && api_action) {
            let message = node.attr('message');
            if (window.confirm(message)) {
               var params = {};
               params.project_id = project_id;
               params.user_project_setting_id = user_project_setting_id;
               params.model_id = model_id;
               params.is_overwrite = is_overwrite;

               pw_app.postHtml({controller: api_controller, action: api_action}, params, {callback: callback, is_show_loading: true});
               function callback(data) {
                   console.log(data);
               }
            }
        }
    }
}

var project = new ProjectController();