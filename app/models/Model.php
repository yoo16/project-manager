<?php
require_once 'vo/_Model.php';

class Model extends _Model {

    static $required_columns = array('id' => array('name' => 'id', 
                                                   'type' => 'SERIAL',
                                                   'option' => 'PRIMARY KEY NOT NULL'
                                                   ),
                                     'created_at' => array('name' => 'created_at',
                                                           'type' => 'TIMESTAMP',
                                                           'option' => 'NOT NULL DEFAULT CURRENT_TIMESTAMP'
                                                           ),
                                     'updated_at' => array('name' => 'updated_at',
                                                           'type' => 'TIMESTAMP',
                                                           'option' => 'NULL'
                                                           ),
                                     );

    static $s_type = array('ies' => array('name' => 'ies', 
                                          'number' => '3',
                                         ),
                           'sses' => array('name' => 'sses',
                                          'number' => '4',
                                         ),
                           'uses' => array('name' => 'uses',
                                          'number' => '2',
                                         ),
                   );

    function validate() {
        parent::validate();
    }


    function listByProject($project) {
        $this->where("database_id = {$project['database_id']}");
        $this->order('sort_order');
        $values = $this->select()->values;
        return $values;
    }

}