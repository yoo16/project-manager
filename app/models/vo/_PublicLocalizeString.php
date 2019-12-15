<?php
/**
 * PublicLocalizeString 
 * 
 * @create  2019/08/29 12:24:08 
 */

require_once 'PwPgsql.php';

class _PublicLocalizeString extends PwPgsql {

    public $id_column = 'id';
    public $name = 'public_localize_strings';
    public $entity_name = 'public_localize_string';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'label' => ['type' => 'text'],
        'name' => ['type' => 'varchar', 'length' => 256],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'public_localize_strings_pkey';

    public $index_keys = [
    'public_localize_strings_pkey' => 'CREATE UNIQUE INDEX public_localize_strings_pkey ON public.public_localize_strings USING btree (id)',
    ];


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