<?php
/**
 * PageFilter 
 * 
 * @create  2017-11-24 16:17:25 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _PageFilter extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'page_filters';
    var $entity_name = 'page_filter';

    var $columns = array(
        'attribute_id' => array('type' => 'int4', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'equal_sign' => array('type' => 'varchar', 'length' => 8),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'value' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
    );

    var $primary_key = 'page_filters_pkey';
    var $foreign = array(
            'page_filters_attribute_id_fkey' => [
                                  'column' => 'attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  ],
            'page_filters_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
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