<?php
/**
 * ApiParam 
 * 
 * @create  2017-11-07 18:03:16 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _ApiParam extends PwPgsql {

    public $id_column = 'id';
    public $name = 'api_params';
    public $entity_name = 'api_param';

    public $columns = array(
        'api_action_id' => array('type' => 'int4', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text', 'default' => ''),
        'sort_order' => array('type' => 'int4'),
        'type' => array('type' => 'varchar', 'length' => 16),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'api_params_pkey';




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