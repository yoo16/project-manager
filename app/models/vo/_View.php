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
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'page_id' => array('type' => 'i', 'required' => true),
        'name' => array('type' => 's', 'required' => true),
        'label' => array('type' => 's'),
        'is_force_write' => array('type' => 'b'),
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