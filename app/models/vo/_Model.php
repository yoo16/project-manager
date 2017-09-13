<?php
/**
 * Model 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Model extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'models';
    var $entity_name = 'model';


    var $columns = array(
        'class_name' => array('type' => 'varchar', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'database_id' => array('type' => 'int4', 'is_required' => true),
        'entity_name' => array('type' => 'varchar', 'is_required' => true),
        'id_column_name' => array('type' => 'varchar'),
        'is_lock' => array('type' => 'bool'),
        'is_none_id_column' => array('type' => 'bool'),
        'is_unenable' => array('type' => 'bool'),
        'label' => array('type' => 'varchar'),
        'name' => array('type' => 'varchar', 'is_required' => true),
        'note' => array('type' => 'text'),
        'old_database_id' => array('type' => 'int4'),
        'old_name' => array('type' => 'varchar', 'length' => 256),
        'pg_class_id' => array('type' => 'int4', 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'relfilenode' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'sub_table_name' => array('type' => 'varchar'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $old_columns = array(
    );


    var $column_labels = array (
        'class_name' => '',
        'created_at' => '',
        'database_id' => '',
        'entity_name' => '',
        'id_column_name' => '',
        'is_lock' => '',
        'is_none_id_column' => '',
        'is_unenable' => '',
        'label' => '',
        'name' => '',
        'note' => 'ノート',
        'old_database_id' => '',
        'old_name' => '旧テーブル名',
        'pg_class_id' => '',
        'project_id' => '',
        'relfilenode' => '',
        'sort_order' => '',
        'sub_table_name' => '',
        'updated_at' => '',
    );


    var $unique = array(
            'models_name_project_id_key' => [
                        'name',
                        'project_id',
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

   /**
    * update sort_order
    *
    * @param array $sort_orders
    * @return void
    */
    function updateSortOrder($sort_orders) {
        if (is_array($sort_orders)) {
            foreach ($sort_orders as $id => $sort_order) {
                if (is_numeric($id) && is_numeric($sort_order)) {
                    $posts['sort_order'] = $sort_order;
                    $this->update($posts, $id);
                }
            }
        }
    }

}