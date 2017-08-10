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
        'created_at' => array('type' => 'timestamp'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'is_required' => true),
        'label' => array('type' => 'varchar'),
        'pg_class_id' => array('type' => 'int4', 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'relfilenode' => array('type' => 'int4', 'is_required' => true),
        'database_id' => array('type' => 'int4', 'is_required' => true),
        'entity_name' => array('type' => 'varchar', 'is_required' => true),
        'class_name' => array('type' => 'varchar', 'is_required' => true),
        'is_unenable' => array('type' => 'bool'),
        'id_column_name' => array('type' => 'varchar'),
        'is_none_id_column' => array('type' => 'bool'),
        'sub_table_name' => array('type' => 'varchar'),
        'is_lock' => array('type' => 'bool'),
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