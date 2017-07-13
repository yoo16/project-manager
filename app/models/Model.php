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

    function validate() {
        parent::validate();
    }

    function form_options_for_project($name, $selected, $project) {
        $values = self::listForProject($project);
        $model = new Model();
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $_options['value'] = $value[$model->id_column];
                $_options['label'] = $value['name'];
                $option_values[] = $_options;
            }
            $options['name'] = $name;
            $options['values'] = $option_values;
            $options['value_key'] = 'value';
            $options['label_key'] = 'label';
            $options['selected'] = (int) $selected;
            $options['unselect'] = true;
            return $options;
        }
    }

    function listForProject($project) {
        $conditions[] = "database_id = {$project['database_id']}";
        $orders[] = array('sort_order', false);
        $params['is_key'] = true;
        $values = self::_list($conditions, $orders, $params);
        return $values;
    }

    function fetchForName($name, $project) {
        $conditions[] = "name = '{$name}'";
        $conditions[] = "project_id = '{$project['database_id']}'";
        $databases = self::_list($conditions);
        return $databases[0];
    }

}

?>
