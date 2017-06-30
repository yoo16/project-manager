<?php
/**
 * ApplicationLoader 
 *
 * @author  Yohei Yoshikawa
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */
class ApplicationLoader {

    /**
     * [autoload_model description]
     * 
     * @return [type] [description]
     */
    function autoload_model() {
        $model_path = BASE_DIR."app/models/*.php";
        foreach (glob($model_path) as $model) {
            require_once $model;
        }
    }

    /**
     * [load_model description]
     * 
     * @param  string $file_path          [description]
     * @param  string $project_name [description]
     * @return string               [description]
     */
    function load_model($file_path, $project_name) {
        $models = self::rows($file_path);
        if (!$models) return;
        if ($project_name) {
            $model_path = BASE_DIR."app/{$project_name}/";
            set_include_path(get_include_path().PATH_SEPARATOR.$model_path);
        }
        foreach ($models as $project_name => $model) {
            $path = "{$model_path}models/{$model}.php";
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    /**
     * options
     *
     * @param String $file_path
     * @return Array
     **/
    function rows($file_path) {
        if (!file_exists($file_path)) {
            $file_path = BASE_DIR."db/model/{$file_path}.csv";
        }
        if (file_exists($file_path)) {
            $fp = fopen($file_path, "r");
            while ($value = fgetcsv($fp, 1024, ",")) {
                $results[] = $value[0];
            }
            fclose($fp); 
        }
        return $results;
    }

}

?>