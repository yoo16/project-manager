<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'PgsqlEntity.php';

class _User extends PgsqlEntity {
    
    var $id_column = 'id';
    var $name = 'users';
    var $entity_name = 'user';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'login_name' => array('type' => 's', 'required' => true),
        'htaccess_name' => array('type' => 's'),
        'last_name' => array('type' => 's'),
        'first_name' => array('type' => 's'),
        'password' => array('type' => 's'),
        'email' => array('type' => 's'),
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