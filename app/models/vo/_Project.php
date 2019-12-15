<?php
/**
 * Project 
 * 
 * @create  2019/08/29 12:24:08 
 */

require_once 'PwPgsql.php';

class _Project extends PwPgsql {

    public $id_column = 'id';
    public $name = 'projects';
    public $entity_name = 'project';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'database_id' => ['type' => 'int4', 'is_required' => true],
        'entity_name' => ['type' => 'varchar', 'length' => 256],
        'external_project_id' => ['type' => 'int4'],
        'is_export_external_model' => ['type' => 'bool'],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'url' => ['type' => 'varchar', 'length' => 256],
    ];

    public $primary_key = 'projects_pkey';

    public $index_keys = [
    'projects_pkey' => 'CREATE UNIQUE INDEX projects_pkey ON public.projects USING btree (id)',
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