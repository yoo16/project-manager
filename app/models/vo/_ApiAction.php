<?php
/**
 * ApiAction 
 * 
 * @create  2018-07-23 15:25:24 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _ApiAction extends PwPgsql {

    public $id_column = 'id';
    public $name = 'api_actions';
    public $entity_name = 'api_action';

    public $columns = array(
        'api_id' => array('type' => 'int4', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text'),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'api_actions_pkey';
    public $foreign = array(
            'api_actions_api_id_fkey' => [
                                  'column' => 'api_id',
                                  'class_name' => 'Api',
                                  'foreign_table' => 'apis',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    );

    public $unique = array(
            'api_actions_name_api_id_key' => [
                        'name',
                        'api_id',
                        ],
    );



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