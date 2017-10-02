<?php
/**
 * ViewItemController 
 *
 * @create  2017-07-31 15:26:54 
 */

require_once 'ProjectController.php';

class ViewItemController extends ProjectController {

    var $name = 'view_item';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);

        $this->page = DB::table('Page')->requestSession();
        $this->view = DB::table('View')->requestSession();

        if (!$this->project || !$this->page || !$this->view) {
            $this->redirect_to('view/');
            exit;
        }
    }

   /**
    * index
    *
    * @param
    * @return void
    */
    function index() {
        $this->clearPosts();
        $this->redirect_to('list');
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        $this->clearPosts();
        $this->redirect_to('list');
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->page->bindBelongsTo('Model');
        $this->page->model->attribute = $this->page
                                              ->model
                                              ->relationMany('Attribute')
                                              ->order('name')
                                              ->idIndex()
                                              ->all();

        $this->view->view_item = $this->view->relationMany('ViewItem')->all();

        $this->pages = $this->project
                            ->relationMany('Page')
                            ->idIndex()
                            ->all()
                            ->values;

    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->view_item = DB::table('ViewItem')->takeValues($this->session['posts']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->view_item = DB::table('ViewItem')
                    ->fetch($this->params['id'])
                    ->takeValues($this->session['posts']);

        if ($this->view_item->value['attribute_id']) {
            $this->attribute = DB::table('Attribute')->fetch($this->view_item->value['attribute_id']);
        }
    }

    function action_add_for_attribute() {
        if (!isPost()) return;
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        $posts['view_id'] = $this->view->value['id'];

        $attribute = DB::table('Attribute')->fetch($posts['attribute_id']);
        $posts['label'] = $attribute->value['label'];

        $view_item = DB::table('ViewItem')->insert($posts);

        $this->redirect_to('list');
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        DB::table('ViewItem')->insert($posts);

        if ($view_item->errors) {
            $this->redirect_to('new');
        } else {
            $this->redirect_to('index');
        }
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add_all() {
        //if (!isPost()) exit;
        //$view_item = $this->view->hasMany('ViewItem');
        $this->page->bindBelongsTo('Model');
        $attribute = $this->page->model
                                ->relationMany('Attribute')
                                ->idIndex()
                                ->all();

        foreach ($attribute->values as $attribute) {
            if (!in_array($attribute['name'], Entity::$except_columns)) {
                $view_item = DB::table('ViewItem')
                            ->where("view_id = {$this->view->value['id']}")
                            ->where("attribute_id = {$attribute['id']}")
                            ->one();

                if (!$view_item->value['id']) {
                    $posts['view_id'] = $this->view->value['id'];
                    $posts['attribute_id'] = $attribute['id'];
                    $posts['label'] = $attribute['label'];

                    DB::table('ViewItem')->insert($posts);
                }
            }
        }
        $this->redirect_to('list');
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update() {
        if (!isPost()) exit;
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        $view_item = DB::table('ViewItem')->update($posts, $this->params['id']);

        if ($view_item->errors) {
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->redirect_to('index');
        }
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::table('ViewItem')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        $this->view_item = DB::table('ViewItem')->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;

        $view_item = DB::table('ViewItem')->updateSortOrder($_REQUEST['sort_order']);
        $this->redirect_to('list');
    }

}