<?php
/**
 * View 
 * 
 * @create  2017-08-21 13:46:27 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _View extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'views';
    public $entity_name = 'view';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'is_overwrite' => array('type' => 'bool'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'label_width' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text'),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'views_pkey';




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