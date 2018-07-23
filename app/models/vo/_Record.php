<?php
/**
 * Record 
 * 
 * @create  2017-09-20 17:10:43 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Record extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'records';
    public $entity_name = 'record';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'laben_en' => array('type' => 'bool'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'records_pkey';
    public $foreign = array(
            'records_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
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