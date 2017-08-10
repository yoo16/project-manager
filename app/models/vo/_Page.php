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
        'created_at' => array('type' => 'timestamp', 'option' => 'NULL DEFAULT CURRENT_TIMESTAMP'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'model_id' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'entity_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'class_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'label' => array('type' => 'varchar', 'length' => 256),
        'is_overwrite' => array('type' => 'bool'),
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