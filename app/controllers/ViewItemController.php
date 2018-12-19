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

        $this->page = DB::model('Page')->requestSession();
        $this->view = DB::model('View')->requestSession();

        if (!$this->project || !$this->page || !$this->view) {
            $this->redirectTo(['controller' => 'view']);
        }
    }

   /**
    * index
    *
    * @param
    * @return void
    */
    function index() {
        $this->clearPwPosts();
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        $this->clearPwPosts();
        $this->redirectTo(['action' => 'list']);;
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
        $this->view_item = DB::model('ViewItem')->takeValues($this->session['posts']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->view_item = DB::model('ViewItem')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->session['posts']);

        if ($this->view_item->value['attribute_id']) {
            $this->attribute = DB::model('Attribute')->fetch($this->view_item->value['attribute_id']);
        }
    }

    function action_add_for_attribute() {
        if (!isPost()) return;
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        $posts['view_id'] = $this->view->value['id'];

        $attribute = DB::model('Attribute')->fetch($posts['attribute_id']);
        $posts['label'] = $attribute->value['label'];

        if ($attribute->value['csv']) {
            $posts['csv'] = $attribute->value['csv'];
        }
        if ($attribute->value['type'] == 'bool') {
            $posts['csv'] = 'active';
            $posts['form_type'] = 'radio';
        }
   
        $view_item = DB::model('ViewItem')->insert($posts);

        if ($view_item->errors) {
            var_dump($view_item->sql);
            var_dump($view_item->sql_error);
            var_dump($view_item->errors);
            exit;
        }

        $this->redirectTo(['action' => 'list']);;
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
        DB::model('ViewItem')->insert($posts);

        if ($view_item->errors) {
            $this->redirectTo(['action' => 'new']);;
        } else {
            $this->redirectTo();
        }
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add_all() {
        $this->page->bindBelongsTo('Model');
        $attribute = $this->page->model
                                ->relationMany('Attribute')
                                ->idIndex()
                                ->all();

        foreach ($attribute->values as $attribute) {
            if (!in_array($attribute['name'], PwEntity::$app_columns)) {
                $view_item = DB::model('ViewItem');
                $view_item->where('view_id', $this->view->value['id'])
                          ->where('attribute_id', $attribute['id'])
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

                    DB::model('ViewItem')->insert($posts);
                }
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_remove_all() {
        $view_item = $this->view->relationMany('ViewItem')->all();

        foreach ($view_item->values as $view_item) {
            DB::model('ViewItem')->delete($view_item['id']);
        }
        $this->redirectTo(['action' => 'list']);;
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
        $view_item = DB::model('ViewItem')->update($posts, $this->pw_params['id']);

        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('ViewItem')->delete($this->pw_params['id']);
        $this->redirectTo();
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
            $attribute = DB::model('ViewItem')->fetch($value['attribute_id']);
            $posts['label'] = $attribute->value['label'];
            DB::model('ViewItem')->update($posts, $value['id']);
        }
        $this->redirectTo(['action' => 'list']);;
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
        $view_item_group_member = DB::model('ViewItemGroupMember')
                            ->where("view_item_group_id = {$posts['view_item_group_id']}")
                            ->where("view_item_id = {$posts['view_item_id']}")
                            ->one();
        if (!$view_item_group_member->value) {
            $view_item_group_member = DB::model('ViewItemGroupMember')->insert($posts);
        }
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('ViewItem');
    }
}