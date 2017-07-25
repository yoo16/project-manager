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

    function __construct() {
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
     * reload
     * 
     * @param
     * @return object
     */
    public function reload() {
        if (isset($this->id)) {
            $this->select($this->id);
        } else {
            $this->value = null;
        }
        return $this->value;
    }

    /**
     * default
     * 
     * @param
     * @return bool
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

    public function applyCast() {
        $this->castRow($this->value);
    }

    private function castString($value) {
        if (is_string($value)) return $value;
        return (string) $value;
    }

    private function castBool($value) {
        if (is_bool($value)) return $value;
        return in_array($value, array('t', 'true', '1'));
    }

    private function castTimestamp($value) {
        if ($value === '') return null;

        if (is_string($value)) {
            preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}) ?(\d{1,2})?:?(\d{1,2})?:?(\d{1,2})?/', $value, $m);
            if (checkdate($m[2], $m[3], $m[1])) {
                return sprintf('%4d-%02d-%02d %02d:%02d:%02d', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
            } else {
                $time = strtotime($value);
                if ($time >= 0) return date('Y-m-d H:i:s', $time);
                return null;
            }
        } elseif (is_array($value)) {
            if (is_numeric($value['year']) && is_numeric($value['month']) && is_numeric($value['day'])) {
                $timestamp = sprintf('%4d-%02d-%02d', $value['year'], $value['month'], $value['day']);
                if (is_numeric($value['hour']) && is_numeric($value['minute'])) {
                    $timestamp .= sprintf(' %02d:%02d', $value['hour'], $value['minute']);
                    if (is_numeric($value['second'])) {
                        $timestamp .= sprintf(':%02d', $value['second']);
                    } else {
                        $timestamp .= ':00';
                    }
                } else {
                    $timestamp .= '00:00:00';
                }
                return $this->cast('t', $timestamp);
            } else {
                return null;
            }
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
     * @param  string $type
     * @param  object $value
     * @return object
     */
    private function cast($type, $value) {
        if (!$type) return $value;
        if (is_null($value)) return null;
        if ($type == 's') return self::castString($value);
        if ($type == 'b') return self::castBool($value);
        if ($type == 't') return self::castTimestamp($value);
        if ($type == 'i') return self::castInt($value);
        if ($type == 'f') return self::castFloat($value);
        if ($type == 'd') return self::castDouble($value);
        if ($type == 'a') return self::castArray($value);
        if ($type == 'j') return self::castJson($value);
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
        $options['value_key'] =  (isset($params['value_key'])) ? $params['value_key'] : $this->id_column;
        $options['label_key'] =  (isset($params['label_key'])) ? $params['label_key'] : $this->id_column;
        $options['values'] = $this->values;
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
    function valuesWithKey($key = 'id') {
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
    * @param array $values
    * @param array $params
    * @return string
    */
    function formSelect($column, $params = null) {
        if (!$column) return;
        $params['name'] = "{$this->entity_name}[{$column}]";

        if ($params['model']) {
            if (!$params['value_key']) $params['value_key'] = $this->id_column;
            $params['values'] = DB::table($params['model'])
                                            ->select()
                                            ->values;
        }
        $tag = FormHelper::select($params, $this->value[$column]);
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
    function formDelete($action, $label, $params = null) {
        if (!$action) return;
        if (!$this->value[$this->id_column]) return;

        $href = url_for($action, $this->value[$this->id_column]);

        //TODO FormHelper
        $tag = "<form action=\"{$href}\" method=\"post\">\n";
        $tag.= FormHelper::delete($label);
        $tag.= "</form>\n";
        return $tag;
    }

}