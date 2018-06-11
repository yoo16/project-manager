<?php
/**
 * Menu 
 * 
 * @create  2017-08-19 18:13:58 
 */

//namespace project-manager;

require_once 'PgsqlEntity.php';

class _Menu extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'menus';
    public $entity_name = 'menu';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'is_provide' => array('type' => 'bool'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'menus_pkey';




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