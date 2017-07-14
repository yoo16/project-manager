<?php
/**
 * _Model *
 * @package jp.co.telepath
 * @author   * @create  2013-01-10 12:52:21 */

require_once 'PgsqlEntity.php';

class _Model extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'models';
    var $entity_name = 'model';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'name' => array('type' => 's', 'required' => true),
        'label' => array('type' => 's'),
        'pg_class_id' => array('type' => 'i', 'required' => true),
        'relfilenode' => array('type' => 'i', 'required' => true),
        'database_id' => array('type' => 'i', 'required' => true),
        'entity_name' => array('type' => 's'),
        'auth_type' => array('type' => 's'),
        'creator_user_id' => array('type' => 'i'),
        'is_admin' => array('type' => 'b'),
        'is_change' => array('type' => 'b'),
        'is_staff' => array('type' => 'b'),
        'is_unenable' => array('type' => 'b'),
        'is_user' => array('type' => 'b'),
        'id_column_name' => array('type' => 's'),
        'is_none_id_column' => array('type' => 'b'),
        'sub_table_name' => array('type' => 's'),
    );

    function __construct($params=null) {
        if ($params['pg_info']) $this->pg_info = $params['pg_info'];
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