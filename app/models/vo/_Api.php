<?php
/**
 * Api 
 * 
 * @create  2019/08/29 12:24:06 
 */

require_once 'PwPgsql.php';

class _Api extends PwPgsql {

    public $id_column = 'id';
    public $name = 'apis';
    public $entity_name = 'api';

    public $columns = [
        'api_group_id' => ['type' => 'int4'],
        'created_at' => ['type' => 'timestamp'],
        'label' => ['type' => 'varchar', 'length' => 256],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'note' => ['type' => 'text'],
        'project_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'apis_pkey';
    public $foreign = [
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
    ];

    public $index_keys = [
    'apis_pkey' => 'CREATE UNIQUE INDEX apis_pkey ON public.apis USING btree (id)',
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