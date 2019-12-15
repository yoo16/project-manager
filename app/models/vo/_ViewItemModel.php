<?php
/**
 * ViewItemModel 
 * 
 * @create  2019/08/29 12:24:09 
 */

require_once 'PwPgsql.php';

class _ViewItemModel extends PwPgsql {

    public $id_column = 'id';
    public $name = 'view_item_models';
    public $entity_name = 'view_item_model';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'is_id_index' => ['type' => 'bool'],
        'page_id' => ['type' => 'int4'],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'value_model_id' => ['type' => 'int4'],
        'view_item_id' => ['type' => 'int4'],
        'where_model_id' => ['type' => 'int4'],
    ];

    public $primary_key = 'view_item_models_pkey';

    public $index_keys = [
    'view_item_models_pkey' => 'CREATE UNIQUE INDEX view_item_models_pkey ON public.view_item_models USING btree (id)',
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