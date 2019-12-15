<?php
/**
 * LocalizeString 
 * 
 * @create  2019/08/29 12:24:07 
 */

require_once 'PwPgsql.php';

class _LocalizeString extends PwPgsql {

    public $id_column = 'id';
    public $name = 'localize_strings';
    public $entity_name = 'localize_string';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'label' => ['type' => 'text'],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'project_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'localize_strings_pkey';
    public $foreign = [
            'localize_strings_project_id_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $unique = [
            'localize_strings_name_project_id_key' => [
                        'name',
                        'project_id',
                        ],
    ];
    public $index_keys = [
    'localize_strings_pkey' => 'CREATE UNIQUE INDEX localize_strings_pkey ON public.localize_strings USING btree (id)',
    'localize_strings_name_project_id_key' => 'CREATE UNIQUE INDEX localize_strings_name_project_id_key ON public.localize_strings USING btree (project_id, name)',
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