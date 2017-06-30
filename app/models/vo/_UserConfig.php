<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'PgsqlEntity.php';

class _UserConfig extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'user_configs';
    static $entity_name = 'user_config';

    var $columns = array(
        'created_at' => array('type'=> 't'),
        'updated_at' => array('type'=> 't'),
        'user_id' => array('type' => 'i'),
        'default_dev_url' => array('type' => 's'),
        'default_project_path' => array('type' => 's'),
        'default_db_host' => array('type' => 's'),
    );

    function __construct($params=null) {
        parent::__construct();        
        if ($params['pg_info']) $this->pg_info = $params['pg_info'];
    }

   /**
    * validate
    *
    * @param
    * @return void
    */
    function validate() {
        parent::validate();
    }

}

?>