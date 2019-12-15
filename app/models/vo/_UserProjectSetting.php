<?php
/**
 * UserProjectSetting 
 * 
 * @create  2019/08/29 12:24:09 
 */

require_once 'PwPgsql.php';

class _UserProjectSetting extends PwPgsql {

    public $id_column = 'id';
    public $name = 'user_project_settings';
    public $entity_name = 'user_project_setting';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'group_name' => ['type' => 'varchar', 'length' => 256],
        'project_id' => ['type' => 'int4', 'is_required' => true],
        'project_path' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'user_id' => ['type' => 'int4', 'is_required' => true],
        'user_name' => ['type' => 'varchar', 'length' => 256],
    ];

    public $primary_key = 'user_project_settings_pkey';

    public $index_keys = [
    'user_project_settings_pkey' => 'CREATE UNIQUE INDEX user_project_settings_pkey ON public.user_project_settings USING btree (id)',
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