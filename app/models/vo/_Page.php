<?php
/**
 * Page 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Page extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'pages';
    public $entity_name = 'page';

    public $columns = array(
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

    public $primary_key = 'pages_pkey';
    public $foreign = array(
            'pages_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'pages_parent_page_id_fkey' => [
                                  'column' => 'parent_page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'pages_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'pages_where_model_id_fkey' => [
                                  'column' => 'where_model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
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

}