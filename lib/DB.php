<?php
/**
 * DB 
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class DB {

    /**
     * constructor
     *
     * @param array $params
     */
    function __construct($params = null) {
    }

   /**
    * table
    *
    * Deprecated
    *
    * @param string $name
    * @return PwEntity
    */
    static function table($name) {
        if (!class_exists($name)) exit;
        $instance = new $name();
        return $instance;
    }

   /**
    * model
    *
    * @param string $name
    * @return PwEntity
    */
    static function model($name) {
        if (!class_exists($name)) echo('Not found class');
        $instance = new $name();
        return $instance;
    }

    /**
     * setting pgsql file path
     * 
     * @param UserProjectSetting $user_project_setting
     * @param Database $database
     * @param string $base_dir
     * @return string
     */
    static function settingPgsqlPath($user_project_setting, $database, $base_dir = 'app/settings/pgsql/') {
        if (!$user_project_setting->value) return;
        if (!$database->value['name']) return;
        if (!file_exists($user_project_setting->value['project_path'])) return;

        $file_name = 'default'.EXT_PHP;
        $path = $user_project_setting->value['project_path']."{$base_dir}{$file_name}";
        return $path;
    }

    /**
     * template path For PostgreSQL
     * 
     * @param array $model
     * @return string
     */
    static function templatePgFilePath() {
        $path = BASE_DIR.'lib/templates/settings/pgsql.phtml';
        return $path;
    }


    public function fetch($id) {}
    public function select($columns = null, $as_columns = null) {}
    public function insert($posts = null) {}
    public function inserts($rows) {}
    public function update($posts = null, $id = null) {}
    public function delete($id = null) {}
    public function upsert($posts, $upsert_constraint = null) {}
    public function fetchRows($sql) {}
    public function refresh() {}
    static function initDb() {}
}