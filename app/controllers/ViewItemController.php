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
                                              ->idIndex()
                                              ->order('name')
                                              ->all();


        $this->localize_string = $this->project->relationMany('LocalizeString')
                                              ->idIndex()
                                              ->all();

        $this->view->view_item = $this->view
                                      ->relationMany('ViewItem')
                                      ->idIndex()
                                      ->all();

        $this->pages = $this->project
                            ->relationMany('Page')
                            ->idIndex()
                            ->all()
                            ->values;

        $this->view_item_group = $this->view
                                        ->relationMany('ViewItemGroup')
                                        ->idIndex()
                                        ->all();

        $this->form['view_item_group']['name'] = 'view_item_group_member[view_item_group_id]';
        $this->form['view_item_group']['values'] = $this->view_item_group->values;
        $this->form['view_item_group']['value'] = 'id';
        $this->form['view_item_group']['label'] = 'name';
        $this->form['view_item_group']['class'] = 'form-control col-12';
        $this->form['view_item_group']['unselect'] = true;
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

        if ($attribute->value['csv']) {
            $posts['csv'] = $attribute->value['csv'];
        }
        if ($attribute->value['type'] == 'bool') {
            $posts['csv'] = 'active';
            $posts['form_type'] = 'radio';
        }
   
        $view_item = DB::table('ViewItem')->insert($posts);

        if ($view_item->errors) {
            var_dump($view_item->sql);
            var_dump($view_item->sql_error);
            var_dump($view_item->errors);
            exit;
        }

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
            if (!in_array($attribute['name'], Entity::$app_columns)) {
                $view_item = DB::table('ViewItem')
                            ->where("view_id = {$this->view->value['id']}")
                            ->where("attribute_id = {$attribute['id']}")
                            ->one();

                $posts = null;
                if (!$view_item->value['id']) {
                    $posts['view_id'] = $this->view->value['id'];
                    $posts['attribute_id'] = $attribute['id'];

                    if ($this->view->value['name'] == 'edit') {
                        if ($attribute['type'] == 'bool') {
                            $posts['csv'] = 'active';
                            $posts['form_type'] = 'radio';
                        }
                    }
                    //TODO define label
                    //$posts['label'] = $attribute['label'];

                    DB::table('ViewItem')->insert($posts);
                }
            }
        }
        $this->redirect_to('list');
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_remove_all() {
        //if (!isPost()) exit;
        $view_item = $this->view->relationMany('ViewItem')->all();

        foreach ($view_item->values as $view_item) {
            DB::table('ViewItem')->delete($view_item['id']);
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

        $this->redirect_to('edit', $this->params['id']);
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
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_labels() {
        $view_item = $this->view->relationMany('ViewItem')->all();
        foreach ($view_item->values as $value) {
            $attribute = DB::table('ViewItem')->fetch($value['attribute_id']);
            $posts['label'] = $attribute->value['label'];
            DB::table('ViewItem')->update($posts, $value['id']);
        }
        $this->redirect_to('list');
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update_view_item_group() {
        if (!isPost()) exit;
        $posts = $_REQUEST["view_item_group_member"];
        $view_item_group_member = DB::table('ViewItemGroupMember')
                            ->where("view_item_group_id = {$posts['view_item_group_id']}")
                            ->where("view_item_id = {$posts['view_item_id']}")
                            ->one();
        if (!$view_item_group_member->value) {
            $view_item_group_member = DB::table('ViewItemGroupMember')->insert($posts);
        }
        $this->redirect_to('list');
    }


}