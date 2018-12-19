<?php
/**
 * PublicLocalizeString 
 * 
 * @create  2018-04-23 12:35:57 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _PublicLocalizeString extends PwPgsql {

    public $id_column = 'id';
    public $name = 'public_localize_strings';
    public $entity_name = 'public_localize_string';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'text'),
        'name' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'public_localize_strings_pkey';




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

}