<?php
/**
 * RelationDatabase 
 * 
 * @create  2017-09-04 15:05:48 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _RelationDatabase extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'relation_databases';
    var $entity_name = 'relation_database';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'old_database_id' => array('type' => 'int4', 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $primary_key = 'relation_databases_pkey';
    var $foreign = array(
            'relation_databases_old_database_id_fkey' => [
                                  'column' => 'old_database_id',
                                  'class_name' => 'Database',
                                  'foreign_table' => 'databases',
                                  'foreign_column' => 'id',
                                  ],
            'relation_databases_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
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