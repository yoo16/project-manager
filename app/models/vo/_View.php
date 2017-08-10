<?php
/**
 * _Model *
 * @package jp.co.telepath
 * @author   * @create  2013-01-10 12:52:21 */

require_once 'PgsqlEntity.php';

class _View extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'views';
    var $entity_name = 'view';

    var $columns = array(
        'created_at' => array('type' => 'timestamp', 'option' => 'NULL DEFAULT CURRENT_TIMESTAMP'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'label' => array('type' => 'varchar', 'length' => 256),
        'is_overwrite' => array('type' => 'bool'),
    );

    function __construct($params = null) {
        parent::__construct();        
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

?>