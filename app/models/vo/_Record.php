<?php
/**
 * Record 
 * 
 * @create  2019/08/29 12:24:09 
 */

require_once 'PwPgsql.php';

class _Record extends PwPgsql {

    public $id_column = 'id';
    public $name = 'records';
    public $entity_name = 'record';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'label' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'laben_en' => ['type' => 'bool'],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'note' => ['type' => 'text'],
        'project_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'records_pkey';
    public $foreign = [
            'records_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $index_keys = [
    'records_pkey' => 'CREATE UNIQUE INDEX records_pkey ON public.records USING btree (id)',
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