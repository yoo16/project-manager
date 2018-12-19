<?php
/**
 * Model 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _Model extends PwPgsql {

    public $id_column = 'id';
    public $name = 'models';
    public $entity_name = 'model';

    public $columns = array(
        'class_name' => array('type' => 'varchar', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'csv' => array('type' => 'varchar', 'length' => 256),
        'entity_name' => array('type' => 'varchar', 'is_required' => true),
        'id_column_name' => array('type' => 'varchar'),
        'is_lock' => array('type' => 'bool'),
        'is_none_id_column' => array('type' => 'bool'),
        'is_unenable' => array('type' => 'bool'),
        'label' => array('type' => 'varchar'),
        'name' => array('type' => 'varchar', 'is_required' => true),
        'note' => array('type' => 'text'),
        'old_database_id' => array('type' => 'int4'),
        'old_name' => array('type' => 'varchar', 'length' => 256),
        'pg_class_id' => array('type' => 'int4'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'relfilenode' => array('type' => 'int4'),
        'sort_order' => array('type' => 'int4'),
        'sub_table_name' => array('type' => 'varchar'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'models_pkey';
    public $foreign = array(
            'models_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
    );

    public $unique = array(
            'models_name_project_id_key' => [
                        'name',
                        'project_id',
                        ],
    );



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