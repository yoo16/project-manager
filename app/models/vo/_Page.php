<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'SqlEntity.php';

class _Page extends SqlEntity {

    var $id_column = 'id';
    var $name = 'pages';
    var $entity_name = 'page';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'project_id' => array('type' => 'i', 'required' => true),
        'model_id' => array('type' => 'i'),
        'name' => array('type' => 's', 'required' => true),
        'entity_name' => array('type' => 's', 'required' => true),
        'class_name' => array('type' => 's', 'required' => true),
        'label' => array('type' => 's'),
        'is_force_write' => array('type' => 'b'),
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