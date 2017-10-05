<?php
/**
 * PageModel 
 * 
 * @create  2017-10-03 18:45:29 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _PageModel extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'page_models';
    var $entity_name = 'page_model';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'model_id' => array('type' => 'int4', 'is_required' => true),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $foreign = array(
            'page_models_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  ],
            'page_models_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  ],
    );

    var $unique = array(
            '' => [
                        '',
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