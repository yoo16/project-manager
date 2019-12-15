<?php
/**
 * View 
 * 
 * @create  2019/08/29 12:24:10 
 */

require_once 'PwPgsql.php';

class _View extends PwPgsql {

    public $id_column = 'id';
    public $name = 'views';
    public $entity_name = 'view';

    public $columns = [
        'label_width' => ['type' => 'int4'],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'note' => ['type' => 'text'],
        'page_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'table_catalog' => ['type' => 'varchar'],
        'table_name' => ['type' => 'varchar'],
        'table_schema' => ['type' => 'varchar'],
        'updated_at' => ['type' => 'timestamp'],
        'view_definition' => ['type' => 'varchar'],
    ];

    public $primary_key = 'views_pkey';

    public $index_keys = [
    'views_pkey' => 'CREATE UNIQUE INDEX views_pkey ON public.views USING btree (id)',
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