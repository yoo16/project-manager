<?php
/**
 * Lang 
 * 
 * @create  2019/08/29 12:24:07 
 */

require_once 'PwPgsql.php';

class _Lang extends PwPgsql {

    public $id_column = 'id';
    public $name = 'langs';
    public $entity_name = 'lang';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'lang' => ['type' => 'varchar', 'length' => 8, 'is_required' => true],
        'name' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'langs_pkey';

    public $unique = [
            'langs_lang_key' => [
                        'lang',
                        ],
            'langs_name_key' => [
                        'name',
                        ],
    ];
    public $index_keys = [
    'langs_pkey' => 'CREATE UNIQUE INDEX langs_pkey ON public.langs USING btree (id)',
    'langs_lang_key' => 'CREATE UNIQUE INDEX langs_lang_key ON public.langs USING btree (lang)',
    'langs_name_key' => 'CREATE UNIQUE INDEX langs_name_key ON public.langs USING btree (name)',
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