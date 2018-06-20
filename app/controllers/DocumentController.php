<?php
/**
 * ModelController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class DocumentController extends ProjectController {

    var $name = 'document';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!$this->project->value['id'] || !$this->database->value['id']) {
            $this->redirect_to('project/index');
            exit;
        }
    }
    
   /**
    * before_rendering
    *
    * @param string $action
    * @return void
    */ 
    function before_rendering($action) {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function action_index() {
        $this->type = $this->params['type'];
        if (!$this->type) $this->type = 'php';
        if ($this->type == 'db') {
            $this->database_documents = $this->_getModelDocuments();
        } elseif ($this->type == 'js') {
            $this->_getJsDocuments();
        } elseif ($this->type == 'css') {
            $this->_getCssDocuments();
        } elseif ($this->type == 'php') {
            $this->_getPhpDocuments();
        } elseif ($this->type == 'view') {
            $this->_getViewDocuments();
        }
    }

    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

    function action_list() {
        $this->redirect_to('model');
    }

    function action_attribute_list() {
        $this->model = DB::model('Model')
                            ->fetch($_REQUEST['model_id']);

        $this->attribute = $this->model
                            ->relationMany('Attribute')
                            ->order('name')
                            ->all();
    }

    function action_edit() {
        $this->model = DB::model('Model')->fetch($this->params['id']);
    }

    function action_update_model() {
        if (!isPost()) exit;

        $posts = $this->posts['model'];
        $model = DB::model('Model')->fetch($this->params['id']);
        if ($model->value) {
            if ($model->value['label'] != $posts['label']) {
                $results = $this->database->pgsql()->updateTableComment($model->value['name'], $posts['label']);
            }
            $model = $model->update($posts);
        }
        if ($model->errors) {
            $this->flash['errors'] = $model->errors;
        } else {
            unset($this->session['posts']);
        }
        $this->redirect_to('list');
    }


    function action_update_attribute() {
        if (!isPost()) exit;
        $posts = $this->posts['attribute'];

        $pgsql = $this->database->pgsql();
        $pg_class = $pgsql->pgClassById($this->model->value['pg_class_id']);
        $pgsql->updateColumnComment($pg_class['relname'], $attribute->value['name'], $posts['label']);

        $attribute = DB::model('Attribute')->update($posts, $this->params['id']);

        $params['model_id'] = $attribute->value['model_id'];
        $this->redirect_to('attribute_list', $params);
    }

    function action_model() {
        $this->model = $this->project
                            ->relationMany('Model')
                            ->order('name')
                            ->all();
    }

    function action_page() {
        $this->page = $this->project->relationMany('Page')->all();

        foreach ($this->page->values as $page_index => $page) {
            $this->view = DB::model('View')->where("page_id = {$page['id']}")->all();
            foreach ($this->view->values as $view_index => $view) {
                $view['view_item'] = DB::model('ViewItem')->where("view_id = {$view['id']}")->all()->values;
                $page['view'][] = $view;
            }
            $this->pages[] = $page;
        }

        $this->attributes = DB::model('Attribute')
                                ->join('Model', 'id', 'model_id')
                                ->where("models.project_id = {$this->project->value['id']}")
                                ->idIndex()
                                ->all()
                                ->values;

    }

    function _getPhpDocuments() {
        $user_project_setting = DB::model('UserProjectSetting');
        $user_project_setting->where("project_id = {$this->project->value['id']}")->all();

        $user_project_setting->value = $user_project_setting->values[0];

        $document = new DynamicPHPDocument();
        $document->getDocuments($user_project_setting->value['project_path']);
        $document->sort();
        $this->controller_files = $document->_document_files;
        $this->total_row_count = $document->total_row_count;
        $this->total_file_count = $document->total_file_count;
        $this->funcition_count = $document->funcition_count;

        foreach ($this->controller_files as $path => $controller_file) {
            $this->generatePhpDocuments($controller_file);
        }
    }

    function generatePhpDocuments() {
        foreach ($controller_file as $key => $_controller) {
            $function_documents = $_controller['documents']['function_documents'];
            $file_name = $_controller['file_name'];
            if (!$function_documents) {
                $this->none_function_files[] = $file_name;
            } else {
                foreach ($function_documents as $function_document) {
                    $function_name = trim($function_document['function_name']);
                    $first_explains = trim($function_document['first_explains']);
                    if (!$first_explains) {
                        $this->none_first_explains[$file_name][] = $function_name;
                    }
                    $params = $function_document['params'];
                    $function_params = $function_document['function_params'];
                    if ($params && is_array($function_params)) {
                        foreach ($function_params as $function_param) {
                        $param = self::checkFunctionParam($function_param, $params);
                        if ($param) $this->missmatch_function_params[$file_name][$function_name][] = $param;
                        }
                    }
                }
            }
        }
    }

    function _getViewDocuments() {
        $project = Project::_getValue($this->project_id); 
        $user_project_setting = UserProjectSetting::getForUserAndProject($this->user, $project);
        $document = new DynamicDocument('view');
        $document->getDocuments($user_project_setting['project_path']);
        $document->sort();
        $this->view_files = $document->_document_files;
        $this->total_row_count = $document->total_row_count;
        $this->total_file_count = $document->total_file_count;
    }

    function _getJsDocuments() {
        $project = Project::_getValue($this->project_id); 
        $public_path = "{$this->user_config['default_project_path']}{$project['name']}/public/";
        $js_path = "{$public_path}javascripts/";

        $document = new DynamicJSDocument();
        $document->getDocuments($js_path);
        $document->sort();
        $this->js_files = $document->_document_files;
        $this->total_row_count = $document->total_row_count;
        $this->total_file_count = $document->total_file_count;
    }

    function _getCssDocuments() {
        $project = Project::_getValue($this->project_id); 
        $public_path = "{$this->user_config['default_project_path']}{$project['name']}/public/";
        $css_path = "{$public_path}stylesheets/";

        $document = new DynamicCssDocument();
        $document->getDocuments($css_path);
        $document->sort();
        $this->css_files = $document->_document_files;
        $this->total_row_count = $document->total_row_count;
        $this->total_file_count = $document->total_file_count;
    }

    function _getModelDocuments() {
        $model = $this->project->relationMany('Model')->all();
        if (!$model->values) return;

        foreach ($model->values as $model_value) {
            $model->value = $model_value;
            $attribute = $model->relationMany('Attribute')->all();

            $value['model'] = $model->value;
            $value['attributes'] = $attribute->values;
            $values[$model->value['id']] = $value;
        }
        return $values;
    }


    function checkFunctionParam($function_param, $params) {
        if (!is_array($params)) return;

        $pos = strpos($function_param, '$');
        $function_param = substr($function_param, $pos);
        $function_param = explode('=', $function_param);
        $function_param = $function_param[0];
        $function_param = trim($function_param);
        foreach ($params as $param) {
            $pos = strpos($param, '$');
            $param = substr($param, $pos);
            if ($param == $function_param) return;
        }
        return $function_param;
    }

    function functionParam($function_param, $params) {
        if (!is_array($params)) return;

        $pos = strpos($function_param, '$');
        $function_param = substr($function_param, $pos);

        $function_param = explode('=', $function_param);
        $values['is_param_default'] = $function_param[1];
        $function_param = $function_param[0];
        $function_param = trim($function_param);

        foreach ($params as $param) {
            $pos = strpos($param, '$');

            $type = substr($param, 0, $pos);
            $param = substr($param, $pos);

            if ($param == $function_param) {
                $values['type'] = $type;
                $values['name'] = $param;
                return $values;
            }
        }
        return $function_param;
    }

}