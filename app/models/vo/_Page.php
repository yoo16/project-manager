<?php
/**
 * Page 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Page extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'pages';
    var $entity_name = 'page';

    var $columns = array(
        'class_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'entity_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'is_overwrite' => array('type' => 'bool'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'list_sort_order_columns' => array('type' => 'text'),
        'model_id' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text'),
        'parent_page_id' => array('type' => 'int4'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'view_name' => array('type' => 'varchar', 'length' => 256),
        'where_model_id' => array('type' => 'int4'),
    );

    var $primary_key = 'pages_pkey';
    var $foreign = array(
            'pages_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  ],
            'pages_parent_page_id_fkey' => [
                                  'column' => 'parent_page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  ],
            'pages_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  ],
            'pages_where_model_id_fkey' => [
                                  'column' => 'where_model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
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
            foreach ($sort_orders as $sort_order => $id) {
                if (is_numeric($id) && is_numeric($sort_order)) {
                    $posts['sort_order'] = $sort_order;
                    $this->update($posts, $id);
                }
            }
        }
    }

}