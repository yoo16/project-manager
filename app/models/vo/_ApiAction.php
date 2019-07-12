<?php
/**
 * ApiAction 
 * 
 * @create  2018/07/23 15:25:24 
 */

require_once 'PwPgsql.php';

class _ApiAction extends PwPgsql {

    public $id_column = 'id';
    public $name = 'api_actions';
    public $entity_name = 'api_action';

    public $columns = [
        'api_id' => ['type' => 'int4', 'is_required' => true],
        'created_at' => ['type' => 'timestamp'],
        'label' => ['type' => 'varchar', 'length' => 256],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'note' => ['type' => 'text'],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'api_actions_pkey';
    public $foreign = [
            'api_actions_api_id_fkey' => [
                                  'column' => 'api_id',
                                  'class_name' => 'Api',
                                  'foreign_table' => 'apis',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $unique = [
            'api_actions_name_api_id_key' => [
                        'name',
                        'api_id',
                        ],
    ];
    public $index_keys = [
    'api_actions_pkey' => 'CREATE UNIQUE INDEX api_actions_pkey ON api_actions USING btree (id)',
    'api_actions_name_api_id_key' => 'CREATE UNIQUE INDEX api_actions_name_api_id_key ON api_actions USING btree (name, api_id)',
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