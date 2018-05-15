<?php
/**
 * Record 
 *
 * @create   
 */

//namespace project_manager;

require_once 'models/vo/_Record.php';

class Record extends _Record {

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
     * project path
     * 
     * @param array $user_project_setting
     * @param array $record
     * @return string
     */
    static function csvFilePath($user_project_setting, $record, $lang = 'ja') {
        if (!$user_project_setting) return;
        if (!$record['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $file_name = "{$record['name']}.csv";
        $path = $user_project_setting['project_path']."db/records/{$lang}/{$file_name}";
        return $path;
    }

}