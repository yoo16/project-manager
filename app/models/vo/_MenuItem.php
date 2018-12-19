<?php
/**
 * MenuItem 
 * 
 * @create  2017-08-19 18:16:51 
 */

//namespace project-manager;

require_once 'PwPgsql.php';

class _MenuItem extends PwPgsql {

    public $id_column = 'id';
    public $name = 'menu_items';
    public $entity_name = 'menu_item';

    public $columns = array(
        'action' => array('type' => 'varchar', 'length' => 256),
        'controller' => array('type' => 'varchar', 'length' => 256, 'default' => ''),
        'created_at' => array('type' => 'timestamp'),
        'is' => array('type' => 'bool'),
        'is_provide' => array('type' => 'bool'),
        'menu_id' => array('type' => 'int4', 'is_required' => true),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'menu_items_pkey';




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