<?php
/**
 * RelationDatabase 
 * 
 * @create  2017-09-04 15:05:48 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _RelationDatabase extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'relation_databases';
    public $entity_name = 'relation_database';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'old_database_id' => array('type' => 'int4', 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'relation_databases_pkey';
    public $foreign = array(
            'relation_databases_old_database_id_fkey' => [
                                  'column' => 'old_database_id',
                                  'class_name' => 'Database',
                                  'foreign_table' => 'databases',
                                  'foreign_column' => 'id',
                                  ],
            'relation_databases_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
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