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
        'controller' => ['type' => 'varchar'],
        'created_at' => ['type' => 'timestamp'],
        'method' => ['type' => 'varchar', 'length' => 8, 'is_required' => true],
        'middleware' => ['type' => 'varchar', 'length' => 256],
        'page_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'uri' => ['type' => 'varchar', 'length' => 256, 'default' => ''],
    ];

    public $primary_key = 'routes_pkey';
    public $foreign = [
            'routes_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
    ];

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