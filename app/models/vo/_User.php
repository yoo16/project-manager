<?php
/**
 * User 
 * 
 * @create  2017-10-18 12:58:58 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _User extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'users';
    var $entity_name = 'user';

    var $columns = array(
        'birthday_at' => array('type' => 'timestamp'),
        'created_at' => array('type' => 'timestamp'),
        'email' => array('type' => 'varchar', 'length' => 256),
        'first_name' => array('type' => 'varchar', 'length' => 64),
        'first_name_kana' => array('type' => 'varchar', 'length' => 64),
        'gender' => array('type' => 'varchar', 'length' => 8),
        'last_name' => array('type' => 'varchar', 'length' => 64),
        'last_name_kana' => array('type' => 'varchar', 'length' => 64),
        'memo' => array('type' => 'text'),
        'password' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int2'),
        'tel' => array('type' => 'float8'),
        'tmp_password' => array('type' => 'varchar', 'length' => 256),
        'updated_at' => array('type' => 'timestamp'),
    );


    var $unique = array(
            'users_email_key' => [
                        'email',
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