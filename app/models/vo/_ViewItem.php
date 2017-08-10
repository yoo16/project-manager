<?php
/**
 * ViewItem 
 * 
 * @create  2017-07-31 14:53:22 
 */

require_once 'PgsqlEntity.php';

class _ViewItem extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'view_items';
    var $entity_name = 'view_item';

    var $columns = array(
        'created_at' => array('type' => 'timestamp', 'option' => 'NULL DEFAULT CURRENT_TIMESTAMP'),
        'updated_at' => array('type' => 'timestamp'),
        'attribute_id' => array('type' => 'int4'),
        'view_id' => array('type' => 'int4', 'is_required' => true),
        'label' => array('type' => 'varchar', 'length' => 256),
        'form_type' => array('type' => 'varchar', 'length' => 256),
        'css_class' => array('type' => 'bool'),
    );

    var $column_labels = array (
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