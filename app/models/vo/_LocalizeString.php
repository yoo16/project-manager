<?php
/**
 * LocalizeString 
 * 
 * @create  2017-10-03 00:56:54 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _LocalizeString extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'localize_strings';
    var $entity_name = 'localize_string';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'text'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $foreign = array(
            'localize_strings_project_id_id_fkey' => [
                                  'column' => 'project_id',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  ],
    );

    var $unique = array(
            'localize_strings_name_project_id_key' => [
                        'project_id',
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