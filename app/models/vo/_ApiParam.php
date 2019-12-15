<?php
/**
 * ApiParam 
 * 
 * @create  2019/08/29 12:24:06 
 */

require_once 'PwPgsql.php';

class _ApiParam extends PwPgsql {

    public $id_column = 'id';
    public $name = 'api_params';
    public $entity_name = 'api_param';

    public $columns = [
        'api_action_id' => ['type' => 'int4', 'is_required' => true],
        'created_at' => ['type' => 'timestamp'],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'note' => ['type' => 'text'],
        'sort_order' => ['type' => 'int4'],
        'type' => ['type' => 'varchar', 'length' => 16],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'api_params_pkey';

    public $index_keys = [
    'api_params_pkey' => 'CREATE UNIQUE INDEX api_params_pkey ON public.api_params USING btree (id)',
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