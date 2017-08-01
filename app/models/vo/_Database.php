<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'PgsqlEntity.php';

class _Database extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'databases';
    var $entity_name = 'database';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'updated_at' => array('type' => 'timestamp'),
        'name' => array('type' => 'varchar', 'required' => true),
        'hostname' => array('type' => 'varchar', 'required' => true),
        'user_name' => array('type' => 'varchar', 'required' => true),
        'port' => array('type' => 'int4', 'required' => true),
        'type' => array('type' => 'varchar'),
        'current_version' => array('type' => 'int4'),
        'is_lock' => array('type' => 'bool'),
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