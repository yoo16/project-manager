<?php
/**
 * Entity 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class Entity {
    var $conditions = array();
    var $columns = array();
    var $errors = array();
    var $values = null;
    var $value = null;
    var $id = null;
    var $id_column = 'id';
    var $posts = null;
    var $session = null;


    function __construct($params = null) {
        $this->defaultValue();
    }

    function results() { trigger_error('results is not implemented', E_USER_ERROR); }
    function count()   { trigger_error('count is not implemented', E_USER_ERROR); } 
    function select()   { trigger_error('select is not implemented', E_USER_ERROR); }
    function insert()  { trigger_error('insert is not implemented', E_USER_ERROR); }
    function update()  { trigger_error('update is not implemented', E_USER_ERROR); }
    function delete()  { trigger_error('delete is not implemented', E_USER_ERROR); }

    function before_save() {}
    function before_insert() {}
    function before_update() {}


    /**
     * loadSession
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
    }

   /**
    * clearSession
    *
    * @return void
    */
    public function clearSession() {
        AppSession::clear($this->entity_name);
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
     * @return void
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
    }

    /**
     * save
     * 
     * @param
     * @return bool
     */
    public function save() {
        $this->validate();
        if (empty($this->errors)) {
            if ($this->before_save() !== false) {
                if ($this->isNew()) {
                    if ($this->before_insert() !== false) {
                        $is_success = $this->insert();
                    }
                } else {
                    if ($this->before_update() !== false) {
                        $changes = $this->changes();
                        if (count($changes) > 0) {
                            $is_success = $this->update();
                        } else {
                            if (defined('DEBUG') && DEBUG) error_log("<UPDATE> {$this->name}:{$this->id} has no changes");
                            $is_success = true;
                        }
                    }
                }
            }
            if (defined('DEBUG') && DEBUG) error_log("<SAVE> Canceled");
        } else {
            if (defined('DEBUG') && DEBUG) error_log("<ERROR> " . print_r($this->errors, true));
        }
        if (!$is_success) $this->addError('db', 'error');
        return $is_success;
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
        if (empty($this->columns)) trigger_error('illegal columns definition', E_USER_ERROR);

        if ($this->id) $this->value[$this->id_column] = $this->id;
        $this->errors = array();
        foreach ($this->columns as $column_name => $column) {
            $value = isset($this->value[$column_name]) ? $this->value[$column_name] : null;
            if ($column === $this->id_column) continue;
            if (isset($column['required']) && $column['required'] && (is_null($value) || $value === '')) {
                $this->addError($column_name, 'required');
            } else {
                $type = $column['type'];
                $this->value[$column_name] = $this->cast($type, $value);
            }
        }
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
        foreach ($values as $key => $value) {
            if ($key == $this->id_column) {
                if ($value > 0) $this->id = (int) $value;
            }
            $this->value[$key] = $value;
        }
        $this->castRow($this->value);
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
            $this->errors[] = array('column' => $column, 'message' => $message);
        }
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
        if (isset($this->_value)) {
            $changes = $this->changes();
            return count($changes) > 0;
        } else {
            return true;
        }
    }

    /**
     * changes
     * 
     * @param bool changed
     * @return bool
     */
    public function changes($changed = false) {
        if (isset($this->_value)) {
            $changes = array();
            foreach ($this->columns as $key => $type) {
                if ($this->value[$key] !== $this->_value[$key]) {
                    $changes[$key] = ($changed) ? $this->value[$key] : $this->_value[$key];
                }
            }
            return $changes;
        } else {
            return false;
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
        return in_array($value, array('t', 'true', '1'));
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
            return DateHelper::stringToArray($value);
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
        if (is_int($value)) return $value;
        return (int) $value;
    }

    /**
     * castFloat
     * 
     * @param  object $value
     * @return float
     */
    private function castFloat($value) {
        if (is_float($value)) return $value;
        return (float) $value;
    }

    /**
     * castDouble
     * 
     * @param  object $value
     * @return double
     */
    private function castDouble($value) {
        if (is_double($value)) return $value;
        return (double) $value;
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
        if (strstr($type, 'int')) {
            return self::castInt($value);
        }
        if (strstr($type, 'float')) return self::castFloat($value);
        if (strstr($type, 'double')) return self::castDouble($value);
        if ($type == 'array') return self::castArray($value);
        if ($type == 'json') return self::castJson($value);
    }
    
    /**
     * castRow
     * 
     * @param  array $row
     * @return array
     */
    function castRow(&$row) {
        if (is_array($row)) {
            foreach ($row as $column_name => $value) {
                if ($column_name === $this->id_column) {
                    $row[$this->id_column] = $value;
                } else {
                    if (isset($this->columns[$column_name])) {
                        $column = $this->columns[$column_name];
                        $type = $column['type'];
                        $row[$column_name] = $this->cast($type, $value);
                    }
                }
            }
        }
        return $row;
    }

    /**
     * idIndex
     * 
     * @return Entity
     */
    function idIndex() {
        $this->id_index = true;
        return $this;
    }

    /**
     * castRows
     * 
     * @param  array $row
     * @return array
     */
    function castRows($rows) {
        if (is_array($rows)) {
            foreach ($rows as $index => $row) {
                if (isset($this->id_index) && $this->id_index == true) {
                    $id = (int) $row[$this->id_column];
                    $values[$id] = $this->castRow($row);
                } else {
                    $values[] = $this->castRow($row);
                }
            }
        }
        return $values;
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
        $name = "{$this->entity_name}[{$column}]";
        $tag = FormHelper::text($name, $this->value[$column], $params);
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
        $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['model'] && !$params['value']) $params['value'] = $this->id_column;
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
        $params['name'] = "{$this->entity_name}[{$column}]";

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
        $params['name'] = "{$this->entity_name}[{$column}]";

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
        $params['name'] = "{$this->entity_name}[{$column}]";
        $tag = FormHelper::checkbox($params, $this->value[$column]);
        return $tag;
    }

   /**
    * formDelete
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formDelete($action = 'delete', $label = LABEL_DELETE, $params = null) {
        if (!$action) return;
        if (!$this->value[$this->id_column]) return;

        if (!$params['class']) $params['class'] = 'btn btn-danger confirm-dialog';
        if (!$params['message']) $params['message'] = 'Do you delete?';

        $contents = FormHelper::delete($label, $params);

        $params = null;
        $params['action'] = url_for($action, $this->value[$this->id_column]);
        $tag = FormHelper::form($contents, $params);
        return $tag;
    }

   /**
    * bind by id
    *
    * @param string $model_name
    * @param string $value_key
    * @return class
    */
    function bindById($model_name, $value_key = null) {
        if (!$this->values) return $this;

        $model = DB::table($model_name)->idIndex()->select();
        if (!$model->values) return $this;
        if (!$value_key) $value_key = "{$model->entity_name}_id";

        $bind_name = $model->entity_name;
        foreach ($this->values as $index => $value) {
            if ($id = $value[$value_key]) $this->values[$index][$bind_name] = $model->values[$id];
        }
        return $this;
    }

   /**
    * bind values
    *
    * @param array $values
    * @param string $column
    * @param string $bind_key
    * @return array
    */
    function bindValues($values, $column, $bind_key) {
        if (!$values) return $this->values;

        if ($this->values) {
            foreach ($this->values as $index => $value) {
                $this->values[$index][$column] = $values[$value[$bind_key]];
            }
        }
        return $this->values;
    }
    
}