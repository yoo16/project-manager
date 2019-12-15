<?php
/**
 * Model 
 * 
 * @create  2019/08/29 12:24:07 
 */

require_once 'PwPgsql.php';

class _Model extends PwPgsql {

    public $id_column = 'id';
    public $name = 'models';
    public $entity_name = 'model';

    public $columns = [
        'class_name' => ['type' => 'varchar', 'is_required' => true],
        'created_at' => ['type' => 'timestamp'],
        'csv' => ['type' => 'varchar', 'length' => 256],
        'entity_name' => ['type' => 'varchar', 'is_required' => true],
        'id_column_name' => ['type' => 'varchar'],
        'is_lock' => ['type' => 'bool'],
        'is_none_id_column' => ['type' => 'bool'],
        'is_unenable' => ['type' => 'bool'],
        'label' => ['type' => 'varchar'],
        'name' => ['type' => 'varchar', 'is_required' => true],
        'note' => ['type' => 'text'],
        'old_database_id' => ['type' => 'int4'],
        'old_name' => ['type' => 'varchar', 'length' => 256],
        'pg_class_id' => ['type' => 'int4'],
        'project_id' => ['type' => 'int4', 'is_required' => true],
        'relfilenode' => ['type' => 'int4'],
        'sort_order' => ['type' => 'int4'],
        'sub_table_name' => ['type' => 'varchar'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'models_pkey';
    public $foreign = [
            'models_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
    ];

    public $unique = [
            'models_name_project_id_key' => [
                        'name',
                        'project_id',
                        ],
    ];
    public $index_keys = [
    'models_pkey' => 'CREATE UNIQUE INDEX models_pkey ON public.models USING btree (id)',
    'models_name_project_id_key' => 'CREATE UNIQUE INDEX models_name_project_id_key ON public.models USING btree (name, project_id)',
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