<?php
/**
 * RelationDatabase 
 * 
 * @create  2017/09/04 15:05:48 
 */

require_once 'PwPgsql.php';

class _RelationDatabase extends PwPgsql {

    public $id_column = 'id';
    public $name = 'relation_databases';
    public $entity_name = 'relation_database';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'old_database_id' => ['type' => 'int4', 'is_required' => true],
        'project_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'relation_databases_pkey';
    public $foreign = [
            'relation_databases_old_database_id_fkey' => [
                                  'column' => 'old_database_id',
                                  'class_name' => 'Database',
                                  'foreign_table' => 'databases',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'relation_databases_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $index_keys = [
    'relation_databases_pkey' => 'CREATE UNIQUE INDEX relation_databases_pkey ON relation_databases USING btree (id)',
    ];


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