<?php
/**
 * PwEntity 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

//namespace Libs;

class PwEntity {
    public $id_column = 'id';
    public $id = null;
    public $id_index = false;
    public $is_cast = true;
    public $values = [];
    public $value = [];
    public $conditions = [];
    public $or_wheres = [];
    public $orders = [];
    public $group_by_columns = [];
    public $limit = null;
    public $joins = [];
    public $sql = null;
    public $sqls = [];
    public $errors = [];
    public $posts = null;
    public $session = null;
    public $values_index_column = null;
    public $values_index_column_type = null;
    public $child_relations = [];
    public $select_columns;
    public $select_as_columns;
    public $extra_casts;
    public $join_columns = [];
    public $is_use_select_column = false;

    public static $except_columns = ['id', 'created_at', 'updated_at'];
    public static $app_columns = ['id', 'created_at', 'updated_at', 'sort_order', 'old_db', 'old_host', 'old_id'];

    function __construct($params = null) {

    }

    /**
     * init
     * 
     * @return PwEntity
     */
    public function init() {
        $this->id = null;
        $this->id_index = false;
        $this->conditions = [];
        $this->or_conditions = [];
        $this->joins = null;
        $this->join_columns = [];
        $this->group_by_columns = null;
        $this->errors = null;
        $this->values = null;
        $this->value = null;
        $this->pw_posts = [];
        $this->session = null;
        $this->limit = null;
        $this->values_index_column = null;
        $this->values_index_column_type = null;
        $this->defaultValue();
        return $this;
    }

    /**
     * check SQL
     *
     * @param string
     * @return boolean
     */
    function checkSQL($sql) {
        if ($sql) return true;
        return false;
    }

    /**
     * finally
     * 
     * @return PwEntity
     */
    public function finaly() {
        $this->conditions = null;
        $this->or_conditions = null;
        $this->orders = null;
        $this->limit = null;
        $this->offset = null;
        $this->joins = null;
        $this->group_by_columns = null;
        return $this;
    }

    /**
     * set is_use_select_column
     * 
     * SQL select: table_name.column
     * 
     * @param  boolean $is_use_select_column
     * @return PwEntity
     */
    public function isUseSelectColumn($is_use_select_column) {
        $this->is_use_select_column = $is_use_select_column;
    }

    /**
     * set is cast
     * 
     * @param  boolean $column
     * @return PwEntity
     */
    public function setIsCast($is_cast) {
        $this->is_cast = $is_cast;
        return $this;
    }

    /**
     * set id
     * 
     * @param  string $id
     * @return PwEntity
     */
    public function setId($id) {
        if ($id > 0) {
            $this->value[$this->id_column] = $id;
            $this->id = $id;
        }
        return $this;
    }

    /**
     * set id column
     * 
     * @param  string $column
     * @return PwEntity
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
     * @return PwEntity
     */
    public function setValuesIndexColumn($values_index_column, $values_index_column_type = null) {
        $this->values_index_column = $values_index_column;
        $this->values_index_column_type = $values_index_column_type;
        return $this;
    }

    /**
     * requestSession
     * 
     * @param  string $sid
     * @param  string $session_key
     * @return PwEntity
     */
    public function requestSession($sid = 0, $session_key = null) {
        $request_column = "{$this->entity_name}_id";
        if (isset($_REQUEST[$request_column])) $id = $_REQUEST[$request_column];
        if (isset($id)) {
            $this->fetch($id);
            if ($session_key) {
                PwSession::setWithKey($session_key, $this->entity_name, $this, $sid);
            } else {
                PwSession::set($this->entity_name, $this, $sid);
            }
        }
        if ($session_key) {
            return PwSession::getWithKey($session_key, $this->entity_name, $sid);
        } else {
            return PwSession::get($this->entity_name, $sid);
        }
    }

    /**
     * reload session
     * 
     * @param  string $sid
     * @param  string $session_key
     * @return PwEntity
     */
    public function reloadSession($sid = 0, $session_key = null) {
        if ($this->id) {
            $model = $this->fetch($this->id);
            if ($model->value) {
                if ($session_key) {
                    PwSession::setWithKey($session_key, $this->entity_name, $model, $sid);
                } else {
                    PwSession::set($this->entity_name, $model, $sid);
                }
            }
        }
    }

    /**
     * load session
     * 
     * @param integer $sid
     * @return PwEntity
     */
    public function session($sid) {
        return $this->getSession($sid);
    }

   /**
    * getSession
    *
    * @param integer $sid
    * @return PwEntity
    */
    public function getSession($sid = null) {
        return PwSession::get($this->entity_name, $sid);
    }

   /**
    * setSession
    *
    * @param integer $sid
    * @return PwEntity
    */
    public function setSession($sid = null) {
        PwSession::set($this->entity_name, $this, $sid);
        return $this;
    }

   /**
    * clearSession
    *
    * @param integer $sid
    * @param string $session_key
    * @return PwEntity
    */
    public function clearSession($sid = null, $session_key = null) {
        if ($session_key) {
            PwSession::clearWithKey($session_key, $this->entity_name, $sid);
        } else {
            PwSession::clear($this->entity_name, $sid);
        }
        return $this;
    }

    /**
     * post
     * 
     * @param
     * @return PwEntity
     */
    public function post() {
        if (!isPost()) exit('Not POST method');
        if ($this->pw_posts = $_POST[$this->entity_name]) {
            $this->takeValues($this->pw_posts);
        }
        return $this;
    }

    /**
     * reload
     * 
     * @param
     * @return PwEntity
     */
    public function reload() {
        if (isset($this->id)) $this->fetch($this->id);
        return $this;
    }

    /**
     * default
     * 
     * @param
     * @return PwEntity
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
    public function oldValueToValue() {
        if ($this->columns) {
            foreach ($this->columns as $column_name => $column) {
                if ($column['old_name'] && $column_name != $column['old_name']) {
                    $this->value[$column_name] = $this->value[$column['old_name']];
                    if ($column['old_name'] != $this->id_column) {
                        unset($this->value[$column['old_name']]);
                    }
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
     * @param  array $posts
     * @param  integer $id
     * @return PwPgsql
     */
    public function save($posts, $id = null)
    {
        if (isset($posts[$this->id_column])) unset($posts[$this->id_column]);
        if ($id > 0) $this->fetch($id);
        if ($this->id > 0) {
            $this->update($posts);
        } else {
            $this->insert($posts);
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
     * @return PwEntity
     */
    public function setValue($value) {
        $this->value = $value;
        if ($this->id_column) $this->id = $value[$this->id_column];
        return $this;
    }

    /**
     * set is sort order
     * 
     * @param  boolean $is_sort_order
     * @return PwEntity
     */
    public function setIsSortOrder($is_sort_order) {
        $this->is_sort_order = $is_sort_order;
        return $this;
    }

    /**
     * set is sort order column
     * 
     * @param  boolean $is_sort_order
     * @return PwEntity
     */
    public function setIsSortOrderColumn($is_sort_order_column) {
        $this->is_sort_order_column = $is_sort_order_column;
        return $this;
    }


    /**
     * setValue By id
     * 
     * @param  integer $id
     * @return PwEntity
     */
    public function setValueById($id) {
        if (!$this->values) return $this;
        $this->value = $this->values[$id];
        return $this;
    }

    /**
     * takeValues
     * 
     * @param  array $values
     * @return PwEntity
     */
    public function takeValues($values) {
        if (!$values) return $this;
        foreach ($values as $column_name => $value) {
            if (array_key_exists($column_name, $this->columns)) {
                $this->value[$column_name] = $this->cast($this->columns[$column_name]['type'], $value);
            }
        }
        return $this;
    }

    /**
     * addError
     * 
     * @param  string $column
     * @param  string $message
     * @return
     */
    public function addError($column, $message) {
        if (isset($column) && isset($message)) {
            $this->errors[] = ['column' => $column, 'message' => $message];
        }
        PwSession::setErrors($this->errors);
    }

    /**
     * hasChanges
     * 
     * @param
     * @return bool
     */
    public function hasChanges() {
        if (isset($this->before_value)) {
            $changes = $this->changes();
            if (!$changes) return;
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
        if (!isset($this->before_value)) return;
        $changes = [];
        foreach ($this->value as $column_name => $value) {
            if (!array_key_exists($column_name, self::$except_columns)) {
                if ($value !== $this->before_value[$column_name]) {
                    $changes[$column_name] = $this->value[$column_name];
                }
            }
        }
        return $changes;
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
        return array_key_exists($value, array('t', 'true', 'on', '1'));
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
            return PwDate::convertString($value);
        } else if (is_array($value)) {
            $timestamp = PwDate::arrayToString($value);
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
        if (is_null($value)) return;
        if (is_int($value)) return $value;
        $value = $this->castNumber($value);
        if (!is_numeric($value)) return;
        return (int) $value;
    }

    /**
     * castFloat
     * 
     * @param  object $value
     * @return float
     */
    private function castFloat($value) {
        if (is_null($value)) return;
        if (is_float($value)) return $value;
        $value = $this->castNumber($value);
        if (!is_numeric($value)) return;
        return (float) $value;
    }

    /**
     * castDouble
     * 
     * @param  object $value
     * @return double
     */
    private function castDouble($value) {
        if (is_null($value)) return;
        if (is_double($value)) return $value;
        $value = $this->castNumber($value);
        if (!is_numeric($value)) return;
        return (double) $value;
    }

    /**
     * cast Number
     *
     * @return void
     */
    private function castNumber($value) {
        if (is_null($value)) return;
        if (is_numeric(strpos($value, ','))) $value = str_replace(',', '', $value);
        if (!is_numeric($value)) return;
        return $value;
    }

    /**
     * castArray
     * 
     * @param  object $value
     * @return string
     */
    private function castArray($value, $is_array = true) {
        if (is_array($value)) return $value;
        $val = json_decode($value, $is_array);
        return $val;
    }

    /**
     * castJson
     * 
     * @param  object $value
     * @return array
     */
    private function castJson($value, $is_array = true) {
        return json_decode($value, $is_array);
    }

    /**
     * json to
     * 
     * @param  object $value
     * @return array
     */
    public function jsonTo($column_name, $is_array = true) {
        return json_decode($this->value[$column_name], $is_array);
    }

    /**
     * json for key
     * 
     * @param  object $value
     * @return mixed
     */
    public function jsonForKey($column_name, $key, $is_array = true) {
        $values = $this->jsonTo($column_name, $is_array);
        if ($values && $value = $values[$key]) return $value;
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
     * @param PwEntity $parent_relation
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
     * @param PwEntity $relation
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
    public function castRows($rows) {
        $values = null;
        if (!is_array($rows)) return;
        foreach ($rows as $row) {
            $values[] = $this->castRow($row);
        }
        $values = $this->indexArray($values);
        return $values;
    } 

    /**
     * index array
     *
     * @param array $rows
     * @return void
     */
    public function indexArray($rows) {
        if ($this->values_index_column_type == 'timestamp') {
            $rows = array_column($rows, null, '_index_timestamp'); 
        } else if ($this->values_index_column) {
            $rows = array_column($rows, null, $this->values_index_column); 
        } else if ($this->id_index === true) {
            $rows = array_column($rows, null, $this->id_column); 
        }
        return $rows;
    }

    /**
     * castRow
     * 
     * @param  array $values
     * @return array
     */
    function castRow($values) {
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
                //TODO Bug: join custom column value can't change cast
                if (isset($this->columns[$column_name])) {
                    $values[$column_name] = $this->cast($this->columns[$column_name]['type'], $value);
                }
            }
        }
        //cast for join
        if ($this->extra_casts) {
            foreach ($this->extra_casts as $extra_casts) {
                foreach ($extra_casts as $column_name => $type) {
                    $value = $values[$column_name];
                    $values[$column_name] = $this->cast($type, $value);
                }
            }
        }
        if ($this->values_index_column_type == 'timestamp') {
            if ($index_value = $values[$this->values_index_column]) {
                $values['_index_timestamp'] = strtotime($index_value);
            }
        }
        return $values;
    }

    /**
     * idIndex
     * 
     * @param  boolean $id_index
     * @return PwEntity
     */
    function idIndex($id_index = true) {
        $this->id_index = $id_index;
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
    * link edit
    *
    * @param array $value
    * @param array $http_params
    * @return string
    */
    function linkEdit($http_params = null, $value = null) {
        if ($this->value) $value = $this->value;
        $controller = $GLOBALS['controller'];
        if (!$controller) return;

        $params['controller'] = $controller->name;
        $params['action'] = 'edit';
        $params['id'] = $value['id'];

        if (is_null($http_params['label'])) $http_params['label'] = LABEL_EDIT;
        if (is_null($http_params['class'])) $http_params['class'] = 'btn btn-outline-primary';

        $tag = $controller->linkTo($params, $http_params);
        return $tag;
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
        $tag = PwForm::text($name, $this->value[$column], $params);
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
        $tag = PwForm::password($name, $this->value[$column], $params);
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
        $tag = PwForm::textarea($name, $this->value[$column], $params);
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
        $tag = PwForm::hidden($name, $this->value[$column], $params);
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
        if ($this->values) $params['values'] = $this->values;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['model'] && !$params['value']) $params['value'] = $this->id_column;
        if ($params['value_column']) $params['value'] = $params['value_column'];
        $tag = PwForm::select($params, $this->value[$column]);
        return $tag;
    }

    /**
     * select date
     *
     * @param array $params
     * @param string $selected
     * @return string
     */
    function formSelectDate($column, $params = null) {
        if (!$column) return;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";

        $tag = PwForm::selectDate($params, $this->value[$column]);
        return $tag;
    }

   /**
    * form radio
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formRadio($column, $params = null) {
        if (!$column) return;
        if ($this->values) $params['values'] = $this->values;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['value_column']) $params['value'] = $params['value_column'];
        if ($params['model'] && !$params['value']) $params['value'] = $this->id_column;

        $tag = PwForm::radio($params, $this->value[$column]);
        return $tag;
    }

   /**
    * form checkbox
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function formCheckbox($column, $params = null) {
        if (!$column) return;
        if ($this->values) $params['values'] = $this->values;
        if (!$params['name']) $params['name'] = "{$this->entity_name}[{$column}]";
        if ($params['value_column']) $params['value'] = $params['value_column'];
        $tag = PwForm::checkbox($params, $this->value[$column]);
        return $tag;
    }

   /**
    * form delete
    *
    * @param array $params
    * @return string
    */
    function formDelete($params = null) {
        if (!$this->id) return;
        $action =  (isset($params['action'])) ? $params['action'] : 'delete';

        if (!$params['label']) $params['label'] = LABEL_DELETE;
        if (!$params['class']) $params['class'] = 'btn btn-danger confirm-dialog';
        if (!$params['message']) $params['message'] = 'Do you delete?';

        $controller = $GLOBALS['controller'];
        $params['action'] = $action;
        $params['id'] = $this->value[$this->id_column];

        $contents = PwForm::delete($params);

        $params = null;
        $params['action'] = Controller::url($params);
        $tag = PwForm::form($contents, $params);
        return $tag;
    }

   /**
    * confirm delete
    *
    * @param array $params
    * @return string
    */
    function confirmDelete($params = null) {
        if (!$this->id) return;
        $params['delete_id'] = $this->id;
        if ($params['title_column']) $params['title'] = $this->value[$params['title_column']];
        $tag = PwForm::confirmDelete($params);
        return $tag;
    }

   /**
    * record value(csv)
    *
    * @param string $column
    * @param string $csv_name
    * @return string
    */
    function recordValue($csv_name, $column) {
        if (is_null($this->value[$column])) return;
        if ($records = $this->recordValues($csv_name)) {
            return $records[$this->value[$column]];
        }
    }

   /**
    * record values(csv)
    *
    * @param string $column
    * @param array $params
    * @return string
    */
    function recordValues($csv_name) {
        $csv_records = PwSession::getWithKey('app', PwCsv::$session_name);
        return $csv_records[$csv_name];
    }

   /**
    * bind by id
    *
    * @param string $model_name
    * @param string $value_key
    * @return PwEntity
    */
    function bindById($model_name, $value_key = null) {
        if (!$this->values) return $this;

        //TODO join SQL?
        if (class_exists($model_name)) {
            $model = DB::model($model_name)->get(true);
            if (!$model->values) return $this;
            if (!$value_key) $value_key = "{$model->entity_name}_id";
            foreach ($this->values as $index => $value) {
                if ($id = $value[$value_key]) $this->values[$index][$model->entity_name] = $model->values[$id];
            }
        }
        return $this;
    }

   /**
    * bind array values
    *
    * @param array $relation_values
    * @param string $relation_name
    * @param string $relation_key
    * @return PwEntity
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
    * @param PwEntity $relation
    * @param string $relation_key
    * @return PwEntity
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
    * @param PwEntity $relation
    * @param string $relation_key
    * @return PwEntity
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
     * find from relation model
     * 
     * @param  PwEntity $relation
     * @param  string $column
     * @return PwEntity
     */
    function findByRelation($relation, $column = null) {
        if (!$this->values) return $this;
        if (!$relation->value) return $this;
        if (!$column) $column = "{$this->entity_name}_id";
        $this->value = $this->values[$relation->value[$column]];
        return $this;
    }

    /**
    * find from parent model
    *
    * @param PwEntity $parent
    * @param string $column
    * @return PwEntity
    */
    function findByParent($parent, $column = null) {
        if (!$this->value) return $this;
        if (!$parent->values) return $this;
        if (!$parent->entity_name) return $this;
        if (!$column) $column = "{$parent->entity_name}_id";

        $entity_name = $parent->entity_name;
        $this->$entity_name = $parent;
        $this->$entity_name->value = $parent->values[$this->value[$column]];
        return $this->$entity_name;
    }

    /**
     * each values
     * 
     * @param  string $column
     * @return array
     */
    function eachValues($column) {
        if (!$this->values) return;
        $values = [];
        foreach ($this->values as $value) {
            $values[$value[$column]][] = $value;
        }
        return $values;
    }

    /**
     * value by key and column
     *
     * @param string $key
     * @param string $column
     * @return PwEntity
     */
    function valueBy($key, $column) {
        if (!$key) return;
        if (!$this->values) return $this;
        if (isset($this->values[$key])) {
            if (isset($this->values[$key][$column])) return $this->values[$key][$column];
        }
    }

    /**
     * array column
     * 
     * @param  string $column
     * @return array
     */
    function valuesByColumn($column = 'id') {
        if ($this->values) {
            return array_column($this->values, $column);
        }
    }

    /**
     * value From index values
     *
     * @param string $key
     * @return PwEntity
     */
    function valueForKey($key) {
        if (!$this->values) return $this;
        $this->value = $this->values[$key];
        return $this;
    }

    /**
     * convert values by column index
     *
     * @param string $column
     * @return PwEntity
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
     * valid column_name for foreign key
     *
     * @param  string $column_name
     * @return int
     */
    static function isForeignColumnName($column_name) {
        $pattern = "/_id\z/";
        return preg_match($pattern, $column_name);
    }

    /**
     * table_name by foreign column_name
     *
     * @param  string $column_name
     * @return string
     */
    static function foreignTableByColumnName($column_name) {
        if ($pos = strpos($column_name, '_id')) {
            $table_name = substr($column_name, 0, $pos);
            $table_name = PwFile::singularToPlural($table_name);
            return $table_name;
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
            $values = [];
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

    /**
     * ids
     *
     * @param array $conditions
     * @return array
     */
    function ids($key_column, $value_column) {
        return $this->get()->idsForOldId($key_column, $value_column);
    }

    /**
     * ids for old DB id key
     *
     * @return array
     */
    function idsForOldId($key = 'old_id', $value = 'id') {
        if (!$this->values) return;
        $ids = array_column($this->values, $value, $key);
        return $ids;
    }

    /**
     * sum
     *
     * @param string $column
     * @return integer
     */
    function sum($column) {
        $sum = 0;
        if ($this->values) $sum = array_sum(array_column($this->values, $column));
        return $sum;
    }

    /**
     * average
     *
     * @param string $column
     * @return float
     */
    function average($column) {
        $average = 0;
        $sum = $this->sum($column);
        $count = $this->counts();
        if ($count > 0) $average = $sum / $count;
        return $average;
    }

    /**
     * counts
     *
     * @param string $column
     * @param object $filter_value
     * @return integer
     */
    public function counts($column = null, $filter_value = null) {
        if (!$this->values) return 0;
        $count = 0;
        if (isset($column) && isset($filter_value)) {
            $this->_filter_value = $filter_value;
            if ($values = array_column($this->values, $column)) {
                $filter_values = array_filter($values, function($param) { return ($param == $this->_filter_value); });
                if ($filter_values) $count = count($filter_values);
            }
        } else {
            if (!$this->values) return 0;
            $count = count($this->values);
        }
        return $count;
    }

    /**
     * remember session pw_auth
     * 
     * TODO multi auth
     *
     * @return void
     */
    public function rememberAuth() {
        if (!$this->entity_name) exit('Error remember auth : Not defined $entity_name');
        if (!$this->value) return;

        $pw_auth = PwSession::get('pw_auth');
        if (!$pw_auth) $pw_auth = [];
        $pw_auth[$this->entity_name] = $this;
        PwSession::set('pw_auth', $pw_auth);
    }

    /**
     * key values
     *
     * @param string $key_column
     * @param string $value_column
     * @return array
     */
    function keyValues($key_column, $value_column) {
        if (!$this->values) return;
        foreach ($this->values as $value) {
            $key = $value[$key_column];
            $values[$key] = $value[$value_column];
        }
        return $values;
    }

    /**
     * has error
     *
     * @param string $column
     * @return boolean
     */
    public function hasError($column)
    {
        if (!$this->errors) return;
        $columns = array_column($this->errors, 'column');
        if (!$columns) return;
        return array_key_exists($column, $columns);
    }

    /**
     * validate alphabet
     *
     * @param string $column
     * @return boolean
     */
    public function validateAlphabet($column) {
        if (is_null($this->value[$column])) return;
        $is_alpha = ctype_alpha($this->value[$column]);
        if (!$is_alpha) {
            $this->addError($column, 'invalid');
        }
    }

    /**
     * validate alphabet
     *
     * @param string $column
     * @return boolean
     */
    public function validateNumber($column) {
        if (is_null($this->value[$column])) return;
        $is_alpha = is_numeric($this->value[$column]);
        if (!$is_alpha) {
            $this->addError($column, 'invalid');
        }
    }

    /**
     * validate alphabetNum and number
     *
     * @param string $column
     * @return boolean
     */
    public function validateAlphabetNumber($column) {
        if (!$this->value[$column]) return;
        $is_num = ctype_alnum($this->value[$column]);
        if (!$is_num) {
            $this->addError($column, 'invalid');
        }
    }

    /**
     * validate Alphanumeric
     *
     * @param string $column
     * @param integer $min
     * @param integer $max
     * @return boolean
     */
    public function validtePassword($column, $min = 4, $max = 50) {
        if (!$this->value[$column]) return;
        if (!PwHelper::validtePassword($this->value[$column], $min, $max)) {
            $this->addError($column, 'invalid');
        }
    }

    /**
     * validate Alphanumeric
     *
     * @param string $column
     * @return void
     */
    public function validteAlphanumeric($column) {
        if (!$this->value[$column]) return;
        if (!PwHelper::validteAlphanumeric($this->value[$column])) {
            $this->addError($column, 'invalid');
        }
    }

    /**
     * validate email
     *
     * @param string $email
     * @return boolean
     */
    public function validateEmail($email) {
        return preg_match("/^\w+[\w\-\.]*@([\w\-]+\.)+\w{2,4}$/", $email) == 1;
    }

    /**
     * new page
     *
     * @return PwEntity
     */
    public function newPage() {
        $pw_posts = PwSession::get('pw_posts');
        $this->init()->takeValues($pw_posts[$this->entity_name]);
        return $this;
    }

    /**
     * edit page
     *
     * @param integer $id
     * @return PwEntity
     */
    public function editPage($id = null) {
        $pw_gets = PwSession::get('pw_gets');
        if (!$id) {
            $id_column = "{$this->entity_name}_id";
            $id = $pw_gets[$id_column];
        }
        if (!$id) $id = $pw_gets['id'];
        if (!$id) exit('Not found id');
        $this->fetch($id);
        if ($this->entity_name && $pw_posts = PwSession::get('pw_posts')) {
            $this->takeValues($pw_posts[$this->entity_name]);
        }
        return $this;
    }

    /**
     * download csv
     *
     * @param array $options
     * @return void
     */
    public function streamDownloadCsv($options = null)
    {
        if (!$this->values) return;
        $file_name = "{$this->name}.csv";
        PwCSv::streamDownload($file_name, $this->values, $options);
    }

    /**
     * json decode
     *
     * @param string $key
     * @return PwEntity
     */
    public function jsonDecode($key)
    {
        if (array_key_exists($key, $this->value)) {
            $this->value[$key] = json_decode($this->value[$key], true);
        }
        return $this;
    }

}