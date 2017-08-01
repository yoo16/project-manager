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
        'created_at' => array('type' => 'timestamp', 'option' => 'NULL DEFAULT CURRENT_TIMESTAMP'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'login_name' => array('type' => 'varchar', 'length' => 256, 'required' => true),
        'htaccess_name' => array('type' => 'varchar', 'length' => 256),
        'last_name' => array('type' => 'varchar', 'length' => 256),
        'first_name' => array('type' => 'varchar', 'length' => 256),
        'password' => array('type' => 'varchar', 'length' => 256),
        'email' => array('type' => 'varchar', 'length' => 256),
        'default_dev_url' => array('type' => 'varchar', 'length' => 256),
        'default_project_path' => array('type' => 'varchar', 'length' => 256),
        'default_db_host' => array('type' => 'varchar', 'length' => 256),
    );

    function __construct($params = null) {
        parent::__construct();        
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