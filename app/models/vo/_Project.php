<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'SqlEntity.php';

class _Project extends SqlEntity {

    var $id_column = 'id';
    var $name = 'projects';
    var $entity_name = 'project';

    var $columns = array(
        'created_at' => array('type' => 'timestamp', 'option' => 'NULL DEFAULT CURRENT_TIMESTAMP'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'length' => 256, 'required' => true),
        'database_id' => array('type' => 'int4', 'required' => true),
        'entity_name' => array('type' => 'varchar', 'length' => 256),
        'url' => array('type' => 'varchar', 'length' => 256),
        'external_project_id' => array('type' => 'int4'),
        'is_export_external_model' => array('type' => 'bool'),
    );

    function __construct($params=null) {
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