<?php
/**
 * PageModel 
 * 
 * @create  2017/10/03 18:45:29 
 */

require_once 'PwPgsql.php';

class _PageModel extends PwPgsql {

    public $id_column = 'id';
    public $name = 'page_models';
    public $entity_name = 'page_model';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'is_fetch_list_values' => ['type' => 'bool'],
        'is_request_session' => ['type' => 'bool'],
        'model_id' => ['type' => 'int4', 'is_required' => true],
        'page_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'where_model_id' => ['type' => 'int4'],
    ];

    public $primary_key = 'page_models_pkey';
    public $foreign = [
            'page_models_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'page_models_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $unique = [
            'page_models_model_id_page_id_key' => [
                        'model_id',
                        'page_id',
                        ],
    ];
    public $index_keys = [
    'page_models_model_id_page_id_key' => 'CREATE UNIQUE INDEX page_models_model_id_page_id_key ON page_models USING btree (model_id, page_id)',
    'page_models_pkey' => 'CREATE UNIQUE INDEX page_models_pkey ON page_models USING btree (id)',
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