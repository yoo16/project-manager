<?php
/**
 * Menu 
 * 
 * @create  2019/08/29 12:24:07 
 */

require_once 'PwPgsql.php';

class _Menu extends PwPgsql {

    public $id_column = 'id';
    public $name = 'menus';
    public $entity_name = 'menu';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'is_provide' => ['type' => 'bool'],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'menus_pkey';

    public $index_keys = [
    'menus_pkey' => 'CREATE UNIQUE INDEX menus_pkey ON public.menus USING btree (id)',
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