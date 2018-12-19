<?php
/**
 * ViewItemModel 
 * 
 * @create  2017-10-17 17:01:59 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _ViewItemModel extends PwPgsql {

    var $id_column = 'id';
    var $name = 'view_item_models';
    var $entity_name = 'view_item_model';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'is_id_index' => array('type' => 'bool'),
        'page_id' => array('type' => 'int4'),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'value_model_id' => array('type' => 'int4'),
        'view_item_id' => array('type' => 'int4'),
        'where_model_id' => array('type' => 'int4'),
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