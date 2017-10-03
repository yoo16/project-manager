<?php
/**
 * LocalizeStringController 
 *
 * @create  2017-10-03 01:27:12 
 */

require_once 'ProjectController.php';

class LocalizeStringController extends ProjectController {

    var $name = 'localize_string';


   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
    }

   /**
    * index
    *
    * @param
    * @return void
    */
    function index() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

   /**
    * export
    *
    * @param
    * @return void
    */
    function action_export() {
        $this->project->user_project_setting = DB::table('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $this->project->exportAttributeLabels();
        $this->redirect_to('localize_string/list');
    }

   /**
    * import
    *
    * @param
    * @return void
    */
    function action_import_from_model() {
        $models = $this->project->hasMany('Model')->values;
        foreach ($models as $model) {
            $attributes = DB::table('Attribute')
                                    ->where("model_id = {$model['id']}")
                                    ->all()
                                    ->values;

            $model_name = strtoupper($model['name']);
            $posts['name'] = "LABEL_{$model_name}";
            $posts['lang'] = 'ja';
            $posts['project_id'] = $this->project->value['id'];
            $label['ja'] = $model['label'];
            $posts['label'] = json_encode($label);

            $localize_string = DB::table('LocalizeString')
                                       ->where("name = '{$posts['name']}'")
                                       ->where("project_id = '{$posts['project_id']}'")
                                       ->one();
            if ($localize_string->value['id']) {
                $localize_string->update($posts);
            } else {
                DB::table('LocalizeString')->insert($posts);
            }

            foreach ($attributes as $attribute) {

                if (in_array($attribute['name'], Entity::$app_columns)) {

                } else {
                    if (mb_substr($attribute['name'], -3) != '_id') {
                        $label['ja'] = $attribute['label'];

                        $attribute_name = strtoupper($attribute['name']);
                        $posts['name'] = "LABEL_{$model_name}_{$attribute_name}";
                        $posts['lang'] = 'ja';
                        $posts['project_id'] = $this->project->value['id'];
                        $posts['label'] = json_encode($label);

                        $localize_string = DB::table('LocalizeString')
                                                   ->where("name = '{$posts['name']}'")
                                                   ->where("project_id = '{$posts['project_id']}'")
                                                   ->one();
                        if ($localize_string->value['id']) {
                            $localize_string->update($posts);
                        } else {
                            DB::table('LocalizeString')->insert($posts);
                        }
                    }
                }
            }
        }
        $this->redirect_to('list');
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_remove_illegal() {
        $localize_strings = $this->project->hasMany('LocalizeString')->values;
        foreach ($localize_strings as $localize_string) {
            $search_words = ['_OLD_DB', '_OLD_HOST'];

            foreach ($search_words as $search_word) {
                if (strpos($localize_string['name'], $search_word)) {
                    DB::table('LocalizeString')->delete($localize_string['id']);
                }
            }
        }
        $this->redirect_to('list');
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->localize_string = DB::table('LocalizeString')
                                    ->where("project_id = {$this->project->value['id']}")
                                    ->order('name')
                                    ->all();
        $this->lang = DB::table('Lang')->all();
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->localize_string = DB::table('LocalizeString')->takeValues($this->session['posts']);
        $this->lang = DB::table('Lang')->all();
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->localize_string = DB::table('LocalizeString')
                    ->fetch($this->params['id'])
                    ->takeValues($this->session['posts']);
        //TODO entity
        $this->localize_string->value['label'] = json_decode($this->localize_string->value['label'], true);

        $this->lang = DB::table('Lang')->all();
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["localize_string"];
        $posts['label'] = json_encode($posts['label']);

        $localize_string = DB::table('LocalizeString')->insert($posts);

        if ($localize_string->errors) {
            $this->redirect_to('new');
        } else {
            $this->redirect_to('index');
        }
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update() {
        if (!isPost()) exit;
        $posts = $this->posts["localize_string"];
        $posts['label'] = json_encode($posts['label']);

        $localize_string = DB::table('LocalizeString')->update($posts, $this->params['id']);

        if ($localize_string->errors) {
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
        DB::table('LocalizeString')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        $this->localize_string = DB::table('LocalizeString')->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;
        DB::table('LocalizeString')->updateSortOrder($_REQUEST['sort_order']);
    }

}