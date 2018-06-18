<?php
/**
 * LocalizeString 
 *
 * @create   
 */

//namespace project_manager;

require_once 'models/vo/_LocalizeString.php';

class LocalizeString extends _LocalizeString {

   /**
    * validate
    *
    * @access public
    * @param
    * @return void
    */ 
    function validate() {
        parent::validate();
    }

    /**
     * import By model
     *
     * @param Model $model
     * @return void
     */
    function importsByModel($model, $project) {
        foreach ($model->values as $model->value) {
            $this->importByModel($model);
        }
        if ($project->user_project_setting->value) {
            $project->exportAttributeLabels();
        }
    }

    /**
     * import By model
     *
     * @param Model $model
     * @return void
     */
    function importByModel($model) {
        if (!$model->value) return;
        $attribute = DB::table('Attribute')->where("model_id = {$model->value['id']}")->all();

        $model_name = strtoupper($model->value['name']);
        $posts['name'] = "LABEL_{$model_name}";
        $posts['lang'] = 'ja';
        $posts['project_id'] = $model->value['project_id'];
        $label['ja'] = $model->value['label'];
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

        foreach ($attribute->values as $attribute->value) {
            if (in_array($attribute->value['name'], Entity::$app_columns)) {

            } else {
                if (mb_substr($attribute->value['name'], -3) != '_id') {
                    $label['ja'] = $attribute->value['label'];

                    $attribute_name = strtoupper($attribute->value['name']);
                    $posts['name'] = "LABEL_{$model_name}_{$attribute_name}";
                    $posts['lang'] = 'ja';
                    $posts['project_id'] = $model->value['project_id'];
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
}