<?php
/**
 * ApiGroup 
 * 
 * @create  2019/08/29 12:24:06 
 */

require_once 'PwPgsql.php';

class _ApiGroup extends PwPgsql {

    public $id_column = 'id';
    public $name = 'api_groups';
    public $entity_name = 'api_group';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'name' => ['type' => 'varchar', 'length' => 64],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'api_groups_pkey';

    public $index_keys = [
    'api_groups_pkey' => 'CREATE UNIQUE INDEX api_groups_pkey ON public.api_groups USING btree (id)',
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