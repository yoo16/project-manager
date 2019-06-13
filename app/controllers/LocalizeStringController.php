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
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        unset($this->session['posts']);
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * export
    *
    * @param
    * @return void
    */
    function action_export() {
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $this->project->exportAttributeLabels();
        $this->redirectTo(['controller' => 'localize_string', 'action' => 'list']);
    }

   /**
    * import
    *
    * @param
    * @return void
    */
    function action_import_from_model() {
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        if ($_REQUEST['model_id']) {
            $model = DB::model('Model')->where('id', $_REQUEST['model_id'])->all();
        } else {
            $model = $this->project->relation('Model')->all();
        }
        DB::model('LocalizeString')->importsByModel($model, $this->project);

        $model = DB::model('Model')->fetch($_REQUEST['model_id']);
        LocalizeString::importByModel($model, $this->project);

        if ($_REQUEST['redirect']) {
            $this->redirectTo($this->redirectParams());
        } else {
            $this->redirectTo(['action' => 'list']);
        }
    }

    /**
     * csv import
     *
     * @return void
     */
    function action_csv_import() {
        if ($this->project->value['id']) {
            $file_path = PwFile::uploadFilePath();
            $csv = new PwCsv($file_path);
            $csv->from_encode = 'AUTO';
            $csv->to_encode = 'UTF-8';
            $csv_values = $csv->results();
            $localize_string = DB::model('LocalizeString')->where('project_id', $this->project->value['id'])->all();
            //TODO array_column()
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
        $this->redirectTo(['action' => 'list']);;
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
                    DB::model('LocalizeString')->delete($localize_string['id']);
                }
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->localize_string = DB::model('LocalizeString')
                                    ->where("project_id = {$this->project->value['id']}")
                                    ->order('name')
                                    ->all();
        $this->lang = DB::model('Lang')->all();
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->localize_string = DB::model('LocalizeString')->takeValues($this->session['posts']);
        $this->lang = DB::model('Lang')->all();
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->localize_string = DB::model('LocalizeString')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->session['posts']);
        //TODO entity
        $this->localize_string->value['label'] = json_decode($this->localize_string->value['label'], true);

        $this->lang = DB::model('Lang')->all();
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["localize_string"];
        $posts['label'] = json_encode($posts['label']);

        $localize_string = DB::model('LocalizeString')->insert($posts);

        if ($localize_string->errors) {
            $this->redirectTo(['action' => 'new']);;
        } else {
            $this->redirectTo();
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
        $posts = $this->pw_posts["localize_string"];
        $posts['label'] = json_encode($posts['label']);

        $localize_string = DB::model('LocalizeString')->update($posts, $this->pw_params['id']);

        if ($localize_string->errors) {
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
        } else {
            $this->redirectTo(['action' => 'list']);
        }
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_duplicate() {
        $localize_string = DB::model('LocalizeString')->fetch($this->pw_params['id']);

        if ($localize_string->value) {
            $posts = $localize_string->value;
            $posts['name'] = "{$posts['name']}_1";
            unset($posts['id']);

            $localize_string->insert($posts);
            if ($localize_string->errors) {
                $this->addErrors($localize_string->errors);
            }
        }
        $this->redirectTo();
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('LocalizeString')->delete($this->pw_params['id']);
        $this->redirectTo();
    }

}