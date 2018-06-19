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
        if ($_REQUEST['user_project_setting_id']) {
            $this->project->user_project_setting = DB::table('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        }

        if ($_REQUEST['model_id']) {
            $model = DB::table('Model')->fetch($_REQUEST['model_id']);
            LocalizeString::importByModel($model, $this->project);
        } else {
            $model = $this->project->hasMany('Model');
            LocalizeString::importsByModel($model, $this->project);
        }

        if ($_REQUEST['redirect']) {
            $this->redirect_to($_REQUEST['redirect']);
        } else {
            $this->redirect_to('list');
        }
    }

    /**
     * csv import
     *
     * @return void
     */
    function action_csv_import() {
        if ($this->project->value['id']) {
            $file_path = FileManager::uploadFilePath();
            $csv = new CsvLite($file_path);
            $csv->from_encode = 'AUTO';
            $csv->to_encode = 'UTF-8';
            $csv_values = $csv->results();

            $localize_string = DB::table('LocalizeString')->where("project_id = {$this->project->value['id']}")
                                                          ->all();

            foreach ($csv_values as $csv_value) {
                if ($key = $csv_value['key']) {
                    $csv_labels[$key] = $csv_value;
                }
            }

            foreach ($localize_string->values as $localize_string_value) {
                $name = $localize_string_value['name'];
                $csv_label = $csv_labels[$name];

                if ($csv_label['en']) {
                    $labels = json_decode($localize_string_value['label'], true);
                    if ($labels['en'] != $csv_label['en']) {
                        $labels['en'] = $csv_label['en'];
                        $posts['label'] = json_encode($labels);

                        $localize_string->update($posts, $localize_string_value['id']);
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
    * add
    *
    * @param
    * @return void
    */
    function action_duplicate() {
        $localize_string = DB::table('LocalizeString')->fetch($this->params['id']);

        if ($localize_string->value) {
            $posts = $localize_string->value;
            $posts['name'] = "{$posts['name']}_1";
            unset($posts['id']);

            $localize_string->insert($posts);
            if ($localize_string->errors) {
                $this->addErrors($localize_string->errors);
            }
        }
        $this->redirect_to('index');
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

}