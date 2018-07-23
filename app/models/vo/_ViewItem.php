<?php
/**
 * ViewItem 
 * 
 * @create  2017-08-21 13:46:27 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _ViewItem extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'view_items';
    public $entity_name = 'view_item';

    public $columns = array(
        'attribute_id' => array('type' => 'int4'),
        'created_at' => array('type' => 'timestamp'),
        'css_class' => array('type' => 'bool'),
        'csv' => array('type' => 'varchar', 'length' => 256),
        'form_model_id' => array('type' => 'int4'),
        'form_type' => array('type' => 'varchar', 'length' => 256),
        'label' => array('type' => 'varchar', 'length' => 256),
        'label_column' => array('type' => 'text'),
        'link' => array('type' => 'varchar', 'length' => 256),
        'link_param_id_attribute_id' => array('type' => 'int4'),
        'localize_string_id' => array('type' => 'int4'),
        'note' => array('type' => 'text'),
        'page_id' => array('type' => 'int4'),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'value_column' => array('type' => 'varchar', 'length' => 256),
        'view_id' => array('type' => 'int4', 'is_required' => true),
        'where_attribute_id' => array('type' => 'int4'),
        'where_model_id' => array('type' => 'int4'),
        'where_order' => array('type' => 'text'),
        'where_string' => array('type' => 'text'),
    );

    public $primary_key = 'view_items_pkey';
    public $foreign = array(
            'view_items_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_items_localize_string_id_fkey' => [
                                  'column' => 'localize_string_id',
                                  'class_name' => 'LocalizeString',
                                  'foreign_table' => 'localize_strings',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_items_where_attribute_id_fkey' => [
                                  'column' => 'where_attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_items_link_param_id_attribute_id_fkey' => [
                                  'column' => 'link_param_id_attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_items_attribute_id_fkey' => [
                                  'column' => 'attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_items_where_model_id_fkey' => [
                                  'column' => 'where_model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_items_view_id_fkey' => [
                                  'column' => 'view_id',
                                  'class_name' => 'View',
                                  'foreign_table' => 'views',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
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