<?php
/**
 * Project 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Project extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'projects';
    public $entity_name = 'project';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'database_id' => array('type' => 'int4', 'is_required' => true),
        'entity_name' => array('type' => 'varchar', 'length' => 256),
        'external_project_id' => array('type' => 'int4'),
        'is_export_external_model' => array('type' => 'bool'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'url' => array('type' => 'varchar', 'length' => 256),
    );

    public $primary_key = 'projects_pkey';




    function __construct($params = null) {
        parent::__construct($params);
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