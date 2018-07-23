<?php
/**
 * PageFilter 
 * 
 * @create  2017-11-24 16:17:25 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _PageFilter extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'page_filters';
    public $entity_name = 'page_filter';

    public $columns = array(
        'attribute_id' => array('type' => 'int4', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'equal_sign' => array('type' => 'varchar', 'length' => 8),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'value' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
    );

    public $primary_key = 'page_filters_pkey';
    public $foreign = array(
            'page_filters_attribute_id_fkey' => [
                                  'column' => 'attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'page_filters_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
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