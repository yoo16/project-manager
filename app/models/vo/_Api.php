<?php
/**
 * Api 
 * 
 * @create  2017-11-07 17:52:14 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Api extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'apis';
    public $entity_name = 'api';

    public $columns = array(
        'api_group_id' => array('type' => 'int4'),
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'apis_pkey';
    public $foreign = array(
            'apis_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'apis_api_group_id_fkey' => [
                                  'column' => 'api_group_id',
                                  'class_name' => 'ApiGroup',
                                  'foreign_table' => 'api_groups',
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