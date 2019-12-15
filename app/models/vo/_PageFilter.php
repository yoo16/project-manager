<?php
/**
 * PageFilter 
 * 
 * @create  2019/08/29 12:24:07 
 */

require_once 'PwPgsql.php';

class _PageFilter extends PwPgsql {

    public $id_column = 'id';
    public $name = 'page_filters';
    public $entity_name = 'page_filter';

    public $columns = [
        'attribute_id' => ['type' => 'int4', 'is_required' => true],
        'created_at' => ['type' => 'timestamp'],
        'equal_sign' => ['type' => 'varchar', 'length' => 8],
        'page_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'value' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
    ];

    public $primary_key = 'page_filters_pkey';
    public $foreign = [
            'page_filters_attribute_id_fkey' => [
                                  'column' => 'attribute_id',
                                  'class_name' => 'Attribute',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'page_filters_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $index_keys = [
    'page_filters_pkey' => 'CREATE UNIQUE INDEX page_filters_pkey ON public.page_filters USING btree (id)',
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