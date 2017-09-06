<?php
/**
 * User 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _User extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'users';
    var $entity_name = 'user';

    var $old_name = 'tb_user';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'default_db_host' => array('type' => 'varchar', 'length' => 256),
        'default_dev_url' => array('type' => 'varchar', 'length' => 256),
        'default_project_path' => array('type' => 'varchar', 'length' => 256),
        'email' => array('type' => 'varchar', 'length' => 256),
        'first_name' => array('type' => 'varchar', 'length' => 256),
        'htaccess_name' => array('type' => 'varchar', 'length' => 256),
        'last_name' => array('type' => 'varchar', 'length' => 256),
        'login_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'password' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $old_columns = array(
        'login_name' => 'login_name',
        'password' => 'password',
    );

    var $column_labels = array (
        'created_at' => '',
        'default_db_host' => '',
        'default_dev_url' => '',
        'default_project_path' => '',
        'email' => '',
        'first_name' => '',
        'htaccess_name' => '',
        'last_name' => '',
        'login_name' => '',
        'password' => '',
        'sort_order' => '',
        'updated_at' => '',
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