<?php
/**
 * ApiGroup 
 * 
 * @create  2018-07-23 15:12:21 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _ApiGroup extends PwPgsql {

    public $id_column = 'id';
    public $name = 'api_groups';
    public $entity_name = 'api_group';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'name' => array('type' => 'varchar', 'length' => 64),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'api_groups_pkey';




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