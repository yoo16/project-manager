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
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'name' => array('type' => 's', 'required' => true),
        'hostname' => array('type' => 's'),
        'user_name' => array('type' => 's'),
        'port' => array('type' => 'i'),
        'current_version' => array('type' => 'i'),
        'type' => array('type' => 's'),
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