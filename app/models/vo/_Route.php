<?php
/**
 * Route 
 * 
 * @create  2019/08/29 12:25:38 
 */

require_once 'PwPgsql.php';

class _Route extends PwPgsql {

    public $id_column = 'id';
    public $name = 'routes';
    public $entity_name = 'route';

    public $columns = [
        'action' => ['type' => 'varchar', 'length' => 256],
        'address' => ['type' => 'varchar', 'length' => 256],
        'controller' => ['type' => 'varchar'],
        'created_at' => ['type' => 'timestamp'],
        'method' => ['type' => 'varchar', 'length' => 8],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'routes_pkey';

    public $index_keys = [
    'routes_pkey' => 'CREATE UNIQUE INDEX routes_pkey ON public.routes USING btree (id)',
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