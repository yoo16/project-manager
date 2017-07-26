<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'PgsqlEntity.php';

class _Admin extends PgsqlEntity {
    
    var $id_column = 'id';
    var $name = 'admins';
    var $entity_name = 'admin';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'login_name' => array('type' => 's', 'required' => true),
        'email' => array('type' => 's'),
        'last_name' => array('type' => 's'),
        'first_name' => array('type' => 's'),
        'password' => array('type' => 's'),
        'tmp_password' => array('type' => 's'),
        'tmp_password' => array('type' => 's'),
        'memo' => array('type' => 's'),
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