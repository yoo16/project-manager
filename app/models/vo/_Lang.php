<?php
/**
 * Lang 
 * 
 * @create  2017-10-03 03:28:17 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Lang extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'langs';
    var $entity_name = 'lang';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'lang' => array('type' => 'varchar', 'length' => 8, 'is_required' => true),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $primary_key = 'langs_pkey';

    var $unique = array(
            'langs_lang_key' => [
                        'lang',
                        ],
            'langs_name_key' => [
                        'name',
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