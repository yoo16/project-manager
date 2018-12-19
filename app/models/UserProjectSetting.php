<?php
/**
 * UserProjectSetting 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */
require_once 'vo/_UserProjectSetting.php';

class UserProjectSetting extends _UserProjectSetting {

    function __construct($params=null) {
        parent::__construct($params);        
    }
    
    function validate() {
        parent::validate();
    }

    static function gitCloneCommand($value) {
    	$path = $value['project_path'];
        $url = PHP_WORK_GIT_URL;
        $cmd = "git clone {$url} {$path}";
        return $cmd;
    }
}

?>