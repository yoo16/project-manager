<?php
/**
 * Admin 
 * 
 * @create  2017-10-18 12:58:52 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Admin extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'admins';
    var $entity_name = 'admin';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'email' => array('type' => 'varchar', 'length' => 256),
        'first_name' => array('type' => 'varchar', 'length' => 64),
        'last_name' => array('type' => 'varchar', 'length' => 64),
        'login_name' => array('type' => 'varchar', 'length' => 256),
        'memo' => array('type' => 'text'),
        'password' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int2'),
        'tmp_password' => array('type' => 'varchar', 'length' => 256),
        'updated_at' => array('type' => 'timestamp'),
    );


    var $unique = array(
            'admins_email_key' => [
                        'email',
                        ],
            'admins_login_name_key' => [
                        'login_name',
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