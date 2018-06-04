<?php
/**
 * Entity 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class Entity {
    public $is_cast = true;
    public $id_column = 'id';
    public $columns = [];
    public $conditions = [];
    public $errors = [];
    public $values = null;
    public $value = null;
    public $id = null;
    public $id_index = false;
    public $posts = null;
    public $session = null;
    public $limit = null;
    public $values_index_column = null;
    public $values_index_column_type = null;

    public static $except_columns = ['id', 'created_at', 'updated_at'];
    public static $app_columns = ['id', 'created_at', 'updated_at', 'sort_order', 'old_db', 'old_host', 'old_id'];

    function __construct($params = null) {

    }

    /**
     * init
     * 
     * @return Entity
     */
    public function init() {
        $this->conditions = null;
        $this->errors = null;
        $this->values = null;
        $this->value = null;
        $this->id = null;
        $this->id_index = false;
        $this->posts = null;
        $this->session = null;
        $this->limit = null;
        $this->values_index_column = null;
        $this->values_index_column_type = null;
        $this->defaultValue();
        return $this;
    }

    /**
     * set is cast
     * 
     * @param  boolean $column
     * @return Entity
     */
    public function setIsCast($is_cast) {
        $this->is_cast = $is_cast;
        return $this;
    }

    /**
     * set id column
     * 
     * @param  string $column
     * @return Entity
     */
    public function setIdColumn($column) {
        if ($column) $this->id_column = $column;
        return $this;
    }

    /**
     * set values index column
     * 
     * @param  string $values_index_column
     * @param  string $values_index_column_type
     * @return Entity
     */
    public function setValuesIndexColumn($values_index_column, $values_index_column_type = null) {
        $this->values_index_column = $values_index_column;
        $this->values_index_column_type = $values_index_column_type;
        return $this;
    }

    /**
     * requestSession
     * 
     * @param  string $request_column
     * @return Entity
     */
    public function requestSession($request_column = null) {
        if (!$request_column) $request_column = "{$this->entity_name}_id";
        if (isset($_REQUEST[$request_column])) {
            $this->fetch($_REQUEST[$request_column]);
            AppSession::set($this->entity_name, $this);
        }
        return AppSession::get($this->entity_name);
    }

    /**
     * load session
     * 
     * @param int $id
     * @return Entity
     */
    public function session() {
        return $this->getSession();
    }

   /**
    * getSession
    *
    * @return Entity
    */
    public function getSession() {
        return AppSession::get($this->entity_name);
    }

   /**
    * setSession
    *
    * @return void
    */
    public function setSession() {
        AppSession::set($this->entity_name, $this);
        return $this;
    }

   /**
    * clearSession
    *
    * @return void
    */
    public function clearSession() {
        AppSession::clear($this->entity_name);
        return $this;
    }

    /**
     * reload
     * 
     * @param
     * @return Entity
     */
    public function post() {
        if (!isPost()) exit('Not POST method');
        if ($this->posts = $_POST[$this->entity_name]) {
            $this->takeValues($this->posts);
        }
        return $this;
    }

    /**
     * reload
     * 
     * @param
     * @return Entity
     */
    public function reload() {
        if (isset($this->id)) $this->fetch($this->id);
        return $this;
    }

    /**
     * default
     * 
     * @param
     * @return Entity
     */
    public function defaultValue() {
        if ($this->columns) {
            foreach ($this->columns as $column_name => $column) {
                if ($column_name === $this->id_column) continue;
                if (isset($column['default'])) {
                    $this->value[$column_name] = $this->cast($column['type'], $column['default']);
                }
            }
        }
        return $this;
    }

    /**
     * convert value from old value
     * 
     * @param
     * @return void
     */
    public function convertValueFromOldValue() {
        if ($this->columns) {
            foreach ($this->columns as $column_name => $column) {
                if ($column['old_name']) {
                    $this->value[$column_name] = $this->value[$column['old_name']];
                }
            }
        }
    }

    /**
     * save
     * 
     * @param
     * @return Entity
     */
    public function save() {
        if ($this->before_save() !== false) {
            if ($this->isNew()) {
                return $this->insert();
            } else {
                $changes = $this->changes();
                if (count($changes) > 0) {
                    return $this->update();
                } else {
                    if (defined('DEBUG') && DEBUG) error_log("<UPDATE> {$this->name}:{$this->id} has no changes");
                }
            }
        }
        return $this;
    }

    /**
     * isNew
     * 
     * @param
     * @return void
     */
    public function isNew() {
        return empty($this->id);
    }

    /**
     * validate
     * 
     * @param
     * @return Class
     */
    public function validate() {
        if (empty($this->columns)) exit('Not found $columns in Model File');
        if ($this->value) {
            foreach ($this->value as $column_name => $value) {
                $column = $this->columns[$column_name];
                if ($column) {
                    if ($column_name === $this->id_column) continue;
                    if ($column_name === 'created_at') continue;
                    if (isset($column['is_required']) && $column['is_required'] && (is_null($value) || $value === '')) {
                        $this->addError($column_name, 'required');
                    }
                }
            }
        }
        return $this;
    }

    /**
     * setValue
     * 
     * @param  array $value
     * @return Class
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * takeValues
     * 
     * @param  array $values
     * @return Class
     */
    public function takeValues($values) {
        if (!$values) return $this;
        foreach ($this->columns as $column_name => $value) {
            if (isset($values[$column_name])) {
                $column = $this->columns[$column_name];
                $type = $column['type'];
                $this->value[$column_name] = $this->cast($type, $values[$column_name]);
            }
        }
        return $this;
    }

    /**
     * addError
     * 
     * @param  string $column
     * @param  string $message
     * @return array
     */
    public function addError($column, $message) {
        if (isset($column) && isset($message)) {
            $this->errors[] = ['column' => $column, 'message' => $message];
        }
        AppSession::setErrors($this->errors);
    }

    /**
     * getErrorMessage
     * 
     * @param  string $column
     * @return array
     */
    public function getErrorMessage($column) {
        $messages = array();
        foreach ($this->errors as $error) {
            if ($error['column'] === $column) {
                $messages[] = $error['message'];
            }
        }
        return $messages;
    }

    /**
     * hasChanges
     * 
     * @param
     * @return bool
     */
    public function hasChanges() {
        if (isset($this->after_value)) {
            $changes = $this->changes();
            return count($changes) > 0;
        } else {
            return true;
        }
    }

    /**
     * changes
     * 
     * @return array
     */
    public function changes() {
        if (isset($this->after_value)) {
            $changes = array();
            foreach ($this->after_value as $column_name => $after_value) {
                if (!in_array($column_name, self::$except_columns)) {
                    if ($after_value !== $this->before_value[$column_name]) {
                        $changes[$column_name] = $this->after_value[$column_name];
                    }
                }
            }
            return $changes;
        } else {
            return;
        }
    }

    /**
     * castBool
     * 
     * @param  string $value
     * @return string
     */
    private function castString($value) {
        if (is_string($value)) return $value;
        return (string) $value;
    }

    /**
     * castBool
     * 
     * @param  string $value
     * @return bool
     */
    private function castBool($value) {
        if (is_bool($value)) return $value;
        return in_array($value, array('t', 'true', 'on', '1'));
    }

    /**
     * castTimestamp
     * 
     * @param  string $value
     * @return string
     */
    private function castTimestamp($value) {
        if ($value === '') return null;

        if (is_string($value)) {
            return DateHelper::convertString($value);
        } else if (is_array($value)) {
            $timestamp = DateHelper::arrayToString($value);
            return $this->castTimestamp($timestamp);
        } else {
            return $value;
        }
    }

    /**
     * castInt
     * 
     * @param  object $value
     * @return int
     */
    private function castInt($value) {
        if (is_numeric($value)) {
            if (is_int($value)) return $value;
            return (int) $value;
        }
    }

    /**
     * castFloat
     * 
     * @param  object $value
     * @return float
     */
    private function castFloat($value) {
        if (is_numeric($value)) {
            if (is_float($value)) return $value;
            return (float) $value;
        }
    }

    /**
     * castDouble
     * 
     * @param  object $value
     * @return double
     */
    private function castDouble($value) {
        if (is_numeric($value)) {
            if (is_double($value)) return $value;
            return (double) $value;
        }
    }

    /**
     * castArray
     * 
     * @param  object $value
     * @return string
     */
    private function castArray($value) {
        if (is_array($value)) return $value;
        $val = json_decode($value, true);
        return $val;
    }

    /**
     * castJson
     * 
     * @param  object $value
     * @return string
     */
    private function castJson($value) {
        return json_decode($value);
    }

    /**
     * cast
     *
     * TODO: functional
     * 
     * @param  string $type
     * @param  object $value
     * @return object
     */
    private function cast($type, $value) {
        if (!$type) return $value;
        if (is_null($value)) return null;
        if ($type == 'varchar') return self::castString($value);
        if ($type == 'text') return self::castString($value);
        if ($type == 'bool') return self::castBool($value);
        if ($type == 'timestamp') return self::castTimestamp($value);
        if (strstr($type, 'int')) return self::castInt($value);
        if (strstr($type, 'float')) return self::castFloat($value);
        if (strstr($type, 'double')) return self::castDouble($value);
        if ($type == 'array') return self::castArray($value);
        if ($type == 'json') return self::castJson($value);
    }
   
    /**
     * init child relation
     * 
     */
    function initChild() {
        $this->child_relations = null;
        return $this;
    }

    /**
     * set parent relation
     * 
     * @param Entity $parent_relation
     */
    function setParent($parent_relation) {
        if ($parent_relation->entity_name) {
            $parent = $parent_relation->entity_name;
            $this->$parent = $parent_relation;
        }
        return $this;
    }

    /**
     * add child relation
     * 
     * @param Entity $relation
     */
    function addChild($relation) {
        if ($relation->entity_name && $relation->values) {
            $relation_key = "{$this->entity_name}_id";
            foreach ($relation->values as $value) {
                $id = $value[$relation_key];
                if ($id) $values[$id][] = $value;
            }
            $relation->values = $values;
            $this->child_relations[$relation->entity_name] = $relation;
        }
        return $this;
    }

    /**
     * castRows
     * 
     * @param  array $row
     * @return array
     */
    function castRows($rows) {
        $values = null;
        if (!is_array($rows)) return;

        foreach ($rows as $row) {
            $row = $this->castRow($row, $relation_values);
            if ($this->values_index_column) {
                if ($index_value = $row[$this->values_index_column]) {
                    if ($this->values_index_column_type == 'timestamp') $index_value = strtotime($index_value);
                    if (isset($index_value)) $values[$index_value] = $row;
                }
            } else if ($this->id_index === true) {
                $index_value = (int) $row[$this->id_column];
                $values[$index_value] = $row;
            } else {
                $values[] = $row;
            }
        }
        return $values;
    } 

    /**
     * castRow
     * 
     * @param  array $values
     * @return array
     */
    function castRow($values, $relation_values = null) {
        if (!is_array($values)) return;
        foreach ($values as $column_name => $value) {
            if ($column_name === $this->id_column) {
                $id = (int) $value;
                $values[$column_name] = $id;
                if ($this->child_relations) {
                    foreach ($this->child_relations as $relation_name => $relation) {
                        $values[$relation_name] = $relation->values[$id];
                    }
                }
            } else {
                if (isset($this->columns[$column_name])) {
                    $values[$column_name] = $this->cast($this->columns[$column_name]['type'], $value);
                }
            }
        }
        return $values;
    }

    /**
     * idIndex
     * 
     * @param  boolean $is_index
     * @return Entity
     */
    function idIndex($is_index = true) {
        $this->id_index = $is_index;
        return $this;
    }

   /**
    * formOptions
    *
    * @param array $params
    * @return array
    */
    function formOptions($params) {
        $options = null;
        if (isset($params['id'])) $options['id'] = $params['id'];
        if (isset($params['class'])) $options['class'] = $params['class'];
        if (isset($params['name'])) $options['name'] = $params['name'];
        $options['value'] =  (isset($params['value'])) ? $params['value'] : $this->id_column;
        $options['label'] =  (isset($params['label'])) ? $params['label'] : $this->id_column;
        if (isset($this->values)) $options['values'] = $this->values;
        $options['unselect'] =  (isset($params['unselect'])) ? $params['unselect'] : true;
        $options['label_separator'] = (isset($params['label_separator'])) ? $params['label_separator'] : ' ';
        return $options;
    }

   /**
    * valuesWithKey
    *
    * @param string $key
    * @return array
    */
    function valuesWithKey($key = null) {
        if (!$key) $key = $this->id_column;
        $values = null;
        if ($this->values) {
            foreach ($this->values as $value) {
                $id = $value[$key];
                $values[$id] = $value;
            }
        }
        return $values;
    }

   /**
    * searchForKey
    *
    * @param array $values
    * @param string $id_key
    * @param string $label_key
    * @return object
    */
    static function searchForKey($values, $id_key, $label_key) {
        if (!$values) return;
        if (isset($values[$id_key])) {
            if ($label_key) {
                return $values[$id_key][$label_key];
            } else {
                return $values[$id_key];
            }
        }
    }

   /**
    * formInput
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formInput($column, $params = null) {
        if (!$column) return;
        if ($params['name']) {
            $name = $params['name'];
        } else {
            $name = "{$this->entity_name}[{$column}]";
        }
        $tag = FormHelper::text($name, $this->value[$column], $params);
        return $tag;
    }

   /**
    * formInput
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formPassword($column, $params = null) {
        if (!$column) return;
        $name = "{$this->entity_name}[{$column}]";
        $tag = FormHelper::password($name, $this->value[$column], $params);
        return $tag;
    }

   /**
    * formInput
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formTextarea($column, $params = null) {
        if (!$column) return;
        $name = "{$this->entity_name}[{$column}]";
        $tag = FormHelper::textarea($name, $this->value[$column], $params);
        return $tag;
    }

   /**
    * formHidden
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formHidden($column, $params = null) {
        if (!$column) return;
        $name = "{$this->entity_name}[{$column}]";
        $tag = FormHelper::hidden($name, $this->value[$column], $params);
        return $tag;
    }

   /**
    * formSelect
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formSelect($column, $params = null) {
        if (!$column) return;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['model'] && !$params['value']) $params['value'] = $this->id_column;
        if ($params['value_column']) $params['value'] = $params['value_column'];
        $tag = FormHelper::select($params, $this->value[$column]);
        return $tag;
    }

    /**
     * selectタグ
     *
     * @param array $params
     * @param string $selected
     * @return string
     */
    function formSelectDate($column, $params = null) {
        if (!$column) return;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";

        $tag = FormHelper::selectDate($params, $this->value[$column]);
        return $tag;
    }

   /**
    * formSelect
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formRadio($column, $params = null) {
        if (!$column) return;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['value_column']) $params['value'] = $params['value_column'];
        if ($params['model'] && !$params['value']) $params['value'] = $this->id_column;

        $tag = FormHelper::radio($params, $this->value[$column]);
        return $tag;
    }

   /**
    * formCheckbox
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formCheckbox($column, $params = null) {
        if (!$column) return;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['value_column']) $params['value'] = $params['value_column'];
        $tag = FormHelper::checkbox($params, $this->value[$column]);
        return $tag;
    }

   /**
    * formDelete
    *
    * @param array $params
    * @return string
    */
    function formDelete($params = null) {
        $action =  (isset($params['action'])) ? $params['action'] : 'delete';
        if (!$action) return;

        if (!isset($params['label'])) $params['label'] = LABEL_DELETE;

        if (!$this->value[$this->id_column]) return;

        if (!$params['class']) $params['class'] = 'btn btn-danger confirm-dialog';
        if (!$params['message']) $params['message'] = 'Do you delete?';

        $contents = FormHelper::delete($params);

        $params = null;
        $params['action'] = url_for($action, $this->value[$this->id_column]);
        $tag = FormHelper::form($contents, $params);
        return $tag;
    }

   /**
    * formDelete
    *
    * @param array $params
    * @return string
    */
    function confirmDelete($params = null) {
        if (!$this->value[$this->id_column]) return;
        $params['value'] = $this->value[$this->id_column];
        if ($params['title_column']) $params['title'] = $this->value[$params['title_column']];
        $tag = FormHelper::confirmDelete($params);
        return $tag;
    }

   /**
    * record value
    *
    * @param string $column
    * @param string $csv_name
    * @param array $params
    * @return string
    */
    function recordValue($csv_name, $column, $params = null) {
        if (!isset($this->value[$column])) return;
        $csv_records = AppSession::getWithKey('app', 'csv_options');
        if ($records = $csv_records[$csv_name]) {
            return $records[$this->value[$column]];
        }
    }

   /**
    * bind by id
    *
    * @param string $model_name
    * @param string $value_key
    * @return Entity
    */
    function bindById($model_name, $value_key = null) {
        if (!$this->values) return $this;

        //TODO join SQL?
        $model = DB::table($model_name)->idIndex()->all();
        if (!$model->values) return $this;
        if (!$value_key) $value_key = "{$model->entity_name}_id";

        foreach ($this->values as $index => $value) {
            if ($id = $value[$value_key]) $this->values[$index][$model->entity_name] = $model->values[$id];
        }
        return $this;
    }

   /**
    * bind array values
    *
    * @param array $relation_values
    * @param string $relation_name
    * @param string $relation_key
    * @return Entity
    */
    function bindValuesArray($relation_values, $relation_name, $relation_key) {
        if (!$this->values) return $this;
        if (!$relation_values) return $this;

        foreach ($this->values as $index => $value) {
            $this->values[$index][$relation_name] = $relation_values[$value[$relation_key]];
        }
        return $this;
    }
  
    /**
    * binds by relation many
    *
    * @param Entity $relation
    * @param string $relation_key
    * @return Entity
    */
    function bindMany($relation, $relation_key = null) {
        if (!$this->value) return $this;
        if (!$relation->values) return $this;
        if (!$relation_key) $relation_key = "{$this->entity_name}_id";

        $relation_name = $relation->entity_name;
        if ($relation_name) exit('Not found: Bind Class relation_name.');
        foreach ($relation->values as $relation_value) {
            $id = $relation_value[$relation_key];
            if ($id) $relation_values[$id][] = $relation_value;
        }
        $this->value[$relation_name] = $relation_values[$id];
        return $this;
    }

    /**
    * binds by relation many
    *
    * @param Entity $relation
    * @param string $relation_key
    * @return Entity
    */
    function bindsMany($relation, $relation_key = null) {
        if (!$this->values) return $this;
        if (!$relation->values) return $this;
        if (!$relation_key) $relation_key = "{$this->entity_name}_id";

        $relation_name = $relation->entity_name;
        if ($relation_name) exit('Not found: Bind Class relation_name.');
        foreach ($relation->values as $relation_value) {
            $id = $relation_value[$relation_key];
            if ($id) $relation_values[$id][] = $relation_value;
        }
        foreach ($this->values as $index => $value) {
            $id = $value[$this->id_column];
            $this->values[$index][$relation_name] = $relation_values[$id];
        }
        return $this;
    }


    /**
    * find Parent 
    *
    * @param Entity $relation
    * @param string $relation_key
    * @return Entity
    */
    function findParent($relation, $relation_key = null) {
        if (!$this->value) return $this;
        if (!$relation->values) return $this;
        if (!$relation->entity_name) return $this;
        if (!$relation_key) $relation_key = "{$relation->entity_name}_id";

        $id = $this->value[$relation_key];
        $entity_name = $relation->entity_name;
        $this->$entity_name->value = $this->$entity_name->values[$id];
        return $this->$entity_name;
    }

    /**
     * value From index values
     *
     * @param string $key
     * @return Entity
     */
    function valueFromValues($key) {
        if (!$this->values) return $this;
        $this->value = $this->values[$key];
        return $this;
    }

    /**
     * values For column index
     *
     * @param string $column
     * @return Entity
     */
    function valuesForColumnIndex($column) {
        if (!$this->columns[$column]) return $this;
        if (!$this->values) return $this;
        foreach ($this->values as $value) {
            $values[$value[$column]] = $value;
        }
        $this->values = $values;
        return $this;
    }

    /**
     * values by column
     * 
     * @param  Entity $relation
     * @param  string $column
     * @return Entity
     */
    function findValueByRelation($relation, $column = null) {
        if (!$relation->value) return $this;
        if (!$column) $column = "{$this->entity_name}_id";
        $id = $relation->value[$column];
        $this->value = $this->values[$id];
        return $this;
    }

    /**
     * column_name is maybe foreign key
     *
     * @param  string $column_name
     * @return int
     */
    static function isForeignColumnName($column_name) {
        $pattern = "/_id\z/";
        return preg_match($pattern, $column_name);
    }

    /**
     * table_name is maybe by foreign column_name
     *
     * @param  string $column_name
     * @return string
     */
    static function foreignTableByColumnName($column_name) {
        if ($pos = strpos($column_name, '_id')) {
            $table_name = substr($column_name, 0, $pos);
            $table_name = FileManager::singularToPlural($table_name);
            return $table_name;
        }
    }

    /**
     * values by column
     * 
     * @param  string $column [description]
     * @return array
     */
    function valuesByColumn($column = 'id') {
        if ($this->values) {
            return array_column($this->values, $column);
        }
    }

    /**
     * convert SQL copy array
     * 
     * @param  array $rows
     * @param  array $columns
     * @return array
     */
    function convertCopyRows($rows, $columns) {
        foreach ($rows as $row) {
            $values = null;
            foreach ($columns as $column => $meta) {
                $value = $row[$column];
                if (is_null($value)) {
                    if ($meta['type'] == 'bool') {
                        $value = 'f';
                    } else {
                        $value = "\N";
                    }
                } else {
                    if ($meta['type'] == 'bool') {
                        $value = ($value)? 't' : 'f';
                    }
                }
                $values[] = $value;
            }
            $row = implode(',', $values);
            $rows[] = $row;
        }
        return $rows;
    }

}