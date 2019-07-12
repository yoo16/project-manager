<?php
/**
 * ViewItem 
 * 
 * @create  2017/08/21 13:46:27 
 */

require_once 'PwPgsql.php';

class _ViewItem extends PwPgsql {

    public $id_column = 'id';
    public $name = 'view_items';
    public $entity_name = 'view_item';

    public $columns = [
        'attribute_id' => ['type' => 'int4'],
        'created_at' => ['type' => 'timestamp'],
        'css_class' => ['type' => 'bool'],
        'csv' => ['type' => 'varchar', 'length' => 256],
        'form_model_id' => ['type' => 'int4'],
        'form_type' => ['type' => 'varchar', 'length' => 256],
        'label' => ['type' => 'varchar', 'length' => 256],
        'label_column' => ['type' => 'text'],
        'link' => ['type' => 'varchar', 'length' => 256],
        'link_param_id_attribute_id' => ['type' => 'int4'],
        'localize_string_id' => ['type' => 'int4'],
        'note' => ['type' => 'text'],
        'page_id' => ['type' => 'int4'],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'value_column' => ['type' => 'varchar', 'length' => 256],
        'view_id' => ['type' => 'int4', 'is_required' => true],
        'where_attribute_id' => ['type' => 'int4'],
        'where_model_id' => ['type' => 'int4'],
        'where_order' => ['type' => 'text'],
        'where_string' => ['type' => 'text'],
    ];

    public $primary_key = 'view_items_pkey';
    public $foreign = [
            'view_items_view_id_fkey' => [
                                  'column' => 'view_id',
                                  'class_name' => 'View',
                                  'foreign_table' => 'views',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
            'view_items_link_param_id_attribute_id_fkey' => [
                                  'column' => 'link_param_id_attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
            'view_items_where_attribute_id_fkey' => [
                                  'column' => 'where_attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
            'view_items_attribute_id_fkey' => [
                                  'column' => 'attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
            'view_items_where_model_id_fkey' => [
                                  'column' => 'where_model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
            'view_items_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
            'view_items_localize_string_id_fkey' => [
                                  'column' => 'localize_string_id',
                                  'class_name' => 'LocalizeString',
                                  'foreign_table' => 'localize_strings',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
    ];

    public $index_keys = [
    'view_items_pkey' => 'CREATE UNIQUE INDEX view_items_pkey ON view_items USING btree (id)',
    ];


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