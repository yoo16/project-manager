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

    }

    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

    function action_list() {
        $this->model = $this->project
                            ->relationMany('Model')
                            ->order('name')
                            ->all();
    }

    function action_attribute_list() {
        $this->model = DB::table('Model')
                            ->fetch($_REQUEST['model_id']);

        $this->attribute = $this->model
                            ->relationMany('Attribute')
                            ->order('name')
                            ->all();
    }

    function action_edit() {
        $this->model = DB::table('Model')->fetch($this->params['id']);
    }

    function action_update_model() {
        if (!isPost()) exit;

        $posts = $this->posts['model'];
        $model = DB::table('Model')->fetch($this->params['id']);
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

        $attribute = DB::table('Attribute')->update($posts, $this->params['id']);

        $params['model_id'] = $attribute->value['model_id'];
        $this->redirect_to('attribute_list', $params);
    }

    function action_page() {
        $this->page = $this->project->relationMany('Page')->all();

        foreach ($this->page->values as $page_index => $page) {
            $this->view = DB::table('View')->where("page_id = {$page['id']}")->all();
            foreach ($this->view->values as $view_index => $view) {
                $view['view_item'] = DB::table('ViewItem')->where("view_id = {$view['id']}")->all()->values;
                $page['view'][] = $view;
            }
            $this->pages[] = $page;
        }

        $this->attributes = DB::table('Attribute')
                                ->join('Model', 'id', 'model_id')
                                ->where("models.project_id = {$this->project->value['id']}")
                                ->idIndex()
                                ->all()
                                ->values;

    }

}