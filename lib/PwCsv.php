<?php

/**
 * PwCsv.php
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 **/

class PwCsv
{

    public $id;
    public $id_key = 'id';
    public $index_key = null;
    public $name;
    public $columns;
    public $labels;
    public $dir;
    public $sort_order_keys; //Non Support
    public $file_path;
    public $buffer = 10240;
    public $errors;
    public $next_id;
    public $lang = 'ja';
    public $id_column = 'value';
    public $label_column = 'label';

    static $from_encode = '';
    static $to_encode = '';
    static $session_name = 'csv';

    /**
     * constructor
     *
     **/
    function __construct($file_path = null, $lang = 'ja')
    {
        $this->setLang($lang);
        $file_path = PwCsv::csvPath($file_path, $lang);
        if (file_exists($file_path)) {
            $this->file_path = $file_path;
        }
    }

    /**
     * csv
     *
     * @param string $csv_name
     * @param string $lang
     * @return PwCsv
     */
    static function file($csv_name, $lang = 'ja')
    {
        $instance = new PwCsv($csv_name, $lang);
        return $instance;
    }

    /**
     * csvPath
     *
     * @param string $csv_name
     * @param string $lang
     * @return string
     **/
    static function csvPath($csv_name, $lang = 'ja')
    {
        if (!$lang) $lang = 'ja';
        if (!$csv_name) return;
        $file_path = BASE_DIR . "db/records/{$lang}/{$csv_name}.csv";
        if (!file_exists($file_path)) $file_path = BASE_DIR . "db/records/{$lang}/{$csv_name}";
        if (!file_exists($file_path)) $file_path = BASE_DIR . "{$csv_name}";
        if (!file_exists($file_path)) $file_path = $csv_name;
        if (file_exists($file_path)) return $file_path;
    }

    /**
     * stream download
     *
     * @param string $file_name
     * @param array $values
     * @param array $options
     * @return void
     */
    static function streamDownload($file_name, $values, $options = null)
    {
        if (!$values) return;
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename={$file_name}");
        $fp = fopen('php://output', 'w');
        foreach ($values as $value) {
            //TODO encoding
            // if ($options['from_encode'] && $options['to_encode']) {
            //     mb_convert_variables($options['to_encode'], $options['to_encode'], $value);
            // }
            fputcsv($fp, $value);
        }
        fclose($fp);
        exit;
    }

    /**
     * options
     *
     * @param string $file_path
     * @param string $lang
     * @return array
     **/
    static function options($file_path, $lang = null)
    {
        $file_path = PwCsv::csvPath($file_path, $lang);
        if (!file_exists($file_path)) return;

        $results = [];
        $fp = fopen($file_path, "r");
        $columns = fgetcsv($fp, 1024, ",");
        while ($data = fgetcsv($fp, 1024, ",")) {
            foreach ($columns as $key => $column) {
                $value[$column] = $data[$key];
            }
            $results[] = $value;
        }
        fclose($fp);
        return $results;
    }

    /**
     * setLang
     *
     * @param string $lang
     * @return PwCsv
     */
    function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * key value all
     * 
     * @return PwCsv
     **/
    function keyValueAll()
    {
        $this->values = PwCsv::keyValues($this->file_path, $this->id_column, $this->label_column, $this->lang);
        return $this;
    }

    /**
     * key values
     *
     * @param string $file_path
     * @param string $id_column
     * @param string $label_column
     * @return array
     **/
    static function keyValues($file_path, $id_column = 'value', $label_column = 'label', $lang = 'ja')
    {
        $results = array();
        $file_path = PwCsv::csvPath($file_path, $lang);
        if (!$file_path || !file_exists($file_path)) return;
        $values = self::options($file_path);
        if (is_array($values)) {
            foreach ($values as $value) {
                $results[$value[$id_column]] = $value[$label_column];
            }
        }
        return $results;
    }

    /**
     * key value
     *
     * @param string $key
     * @param string $file_path
     * @param string $id_column
     * @param string $label_column
     * @return array
     **/
    static function valueByKey($key, $file_path, $lang = 'ja', $id_column = 'value', $label_column = 'label')
    {
        $key_values = self::keyValues($file_path, $id_column, $label_column, $lang);
        return $key_values[$key];
    }

    /**
     * form
     *
     * @param string $csv_path
     * @param string $name
     * @param array $params
     * @return array
     */
    static function form($csv_path, $name, $params = null)
    {
        $params['values'] = PwCsv::options($csv_path);
        $params['name'] = $name;
        return $params;
    }

    /**
     * array to csv for columns
     *
     * @param array $values
     * @param array $columns
     * @param array $csv_callbacks
     * @return array
     **/
    static function arrayToCsvForColumns($values, $columns, $csv_callbacks = null)
    {
        if (is_array($columns)) {
            $keys = array_keys($columns);
            $labels = array_values($columns);
            $csv = implode(',', $labels) . "\n";
        }
        if (is_array($values)) {
            foreach ($values as $key => $row) {
                $row_array = [];
                foreach ($keys as $column) {
                    $value = $row[$column];
                    if ($csv_callbacks && $csv_callbacks[$column]) {
                        $func = $csv_callbacks[$column];
                        $value = call_user_func($func, $value);
                    }
                    $row_array[] = "\"{$value}\"";
                }
                $csv .= implode(',', $row_array) . "\n";
            }
            if (self::$from_encode && self::$to_encode) {
                $csv = mb_convert_encoding($csv, self::$to_encode, self::$from_encode);
            }
            return $csv;
        }
    }

    /**
     * arrayToCsv
     *
     * @param array $values
     * @return array
     **/
    static function arrayToCsv($values)
    {
        if (is_array($values)) {
            $csv = '';
            $columns = array_keys($values[0]);
            $csv .= implode(',', $columns);
            $csv .= "\n";
            foreach ($values as $key => $value) {
                $csv_values = [];
                foreach ($value as $column => $_value) {
                    if (self::$from_encode && self::$to_encode) {
                        $_value = mb_convert_encoding($_value, self::$to_encode, self::$from_encode);
                    }
                    $csv_values[] = csv_value($_value);
                }
                if (is_array($csv_values)) {
                    $csv .= implode(',', $csv_values);
                }
                $csv .= "\n";
            }
            return $csv;
        }
    }

    /**
     * valuesForCsvName
     *
     * @param string $csv_name
     * @return array
     **/
    function valuesForCsvName($csv_name, $lang = 'ja')
    {
        $file_path = PwCsv::csvPath($csv_name, $lang);
        if (!$file_path || !file_exists($file_path)) return;

        $values = $this->_readCsv($file_path);
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $results[$value['value']] = $value['label'];
            }
        }
        return $results;
    }

    /**
     * create
     *
     * @param array $columns
     * @return
     **/
    function create($columns = null)
    {
        if (is_array($columns)) $this->columns;

        if (!file_exists($this->file_path)) {
            if (is_array($this->columns)) {
                $header = $this->implodeColumn($this->columns);
                $fp = fopen($this->file_path, "r");
                flock($fp, LOCK_EX);
                fputs($fp, $header);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
    }

    /**
     * fetch
     *
     * @param int $id
     * @param string $column_key
     * @return array
     **/
    function fetch($id)
    {
        $values = $this->_readCsv();
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if ($value[$this->id_key] == $id) {
                    $this->id = $id;
                    $this->value = $value;
                    return $value;
                }
            }
        }
    }

    /**
     * get
     *
     * @return void
     */
    function get()
    {
        $this->values = $this->_list();
        return $this;
    }

    /**
     * all
     *
     * @return void
     */
    function all()
    {
        $this->values = $this->_list();
        return $this;
    }

    /**
     * _list
     *
     * @param array $columns
     * @return array
     **/
    function _list($columns = null)
    {
        $values = array();
        if (file_exists($this->file_path)) {
            $fp = fopen($this->file_path, "r");

            $columns = fgetcsv($fp, $this->buffer, ",");
            if (!$this->columns) $this->columns = $columns;
            if (!is_array($this->columns)) return;

            if ($this->from_encode && $this->to_encode) {
                foreach ($this->columns as $index => $column) {
                    $this->columns[$index] = mb_convert_encoding($column, $this->to_encode, $this->from_encode);
                }
            }

            while ($data = fgetcsv($fp, $this->buffer, ",")) {
                foreach ($this->columns as $key => $column) {
                    $_value;
                    if ($data[$key]) {
                        if ($this->from_encode && $this->to_encode) {
                            $_value = mb_convert_encoding($data[$key], $this->to_encode, $this->from_encode);
                        } else {
                            $_value = $data[$key];
                        }
                        $value[$column] = $_value;
                    }
                }
                $values[] = $value;
            }
            fclose($fp);
        }
        return $values;
    }

    /**
     * results
     *
     * @param array $conditions
     * @return array
     **/
    function results($conditions = null)
    {
        $values = $this->_readCsv();
        $values = $this->_sortOrder($values);
        return $values;
    }

    /**
     * _sortOrder
     *
     * @param array $values
     * @param string $sort_key
     * @return array
     **/
    function _sortOrder($values, $sort_key = 'id')
    {
        if ($this->sort_key) $sort_key = $this->sort_key;
        if (is_array($this->values)) {
            foreach ($this->values as $key => $value) {
                if ($value[$sort_key]) {
                    $sort_orders[] = $value[$sort_key];
                }
            }
            if (is_array($sort_orders)) {
                array_multisort($sort_orders, $values);
            }
        }
        return $values;
    }

    /**
     * insert
     *
     * @param array $posts
     * @return array
     **/
    function insert($posts)
    {
        //TODO new file
        //TODO fopen write mode is 'a'
        if (is_array($posts)) {
            $this->values = $this->_readCsv();
            if ($this->next_id > 0) {
                $posts[$this->id_key];
                $this->values[] = $posts;
                $this->_writeCsv($this->values);
            }
        }
    }

    /**
     * take_values
     *
     * @param array $posts
     * @return array
     **/
    function take_values($posts)
    {
        if ($this->id > 0) {
            //TODO cast & marge
            $this->value = $posts;
            $this->value[$this->id_key];
        }
    }

    /**
     * update
     *
     * @return
     **/
    function update()
    {
        if ($this->id > 0 && is_array($this->values)) {
            if ($this->values) {
                foreach ($this->values as $key => $value) {
                    if ($value[$this->id_key] == $this->id) {
                        $this->values[$key] = $this->value;
                    }
                }
                $this->write($this->values);
            }
        }
    }

    /**
     * update
     *
     * @return
     **/
    function delete($id)
    {
        $this->values = $this->_readCsv();
        if ($this->values) {
            foreach ($this->values as $key => $value) {
                if ($value[$this->id_key] == $id) {
                    unset($this->values[$key]);
                }
            }
            $this->write($this->values);
        }
    }

    /**
     * _readCsv
     *
     * @return
     **/
    function _readCsv()
    {
        if (file_exists($this->file_path)) {
            $fp = fopen($this->file_path, "r");

            $columns = fgetcsv($fp, $this->buffer, ",");
            if (!$this->columns) $this->columns = $columns;
            if (!is_array($this->columns)) return;

            while ($data = fgetcsv($fp, $this->buffer, ",")) {
                foreach ($this->columns as $key => $column) {
                    if (self::$from_encode && self::$to_encode) {
                        $_value = mb_convert_encoding($data[$key], self::$to_encode, self::$from_encode);
                    } else {
                        $_value = $data[$key];
                    }
                    $value[$column] = $_value;
                }
                if ($this->index_key) {
                    $index_value = $value[$this->index_key];
                    $results[$index_value] = $value;
                } else {
                    $results[] = $value;
                }
            }
            fclose($fp);
        }
        $this->values = $results;
        return $results;
    }

    /**
     * write
     *
     * @param array $values
     * @return
     **/
    function write($values)
    {
        $csv = implode(',', $this->columns) . "\n";

        if (is_array($values)) {
            foreach ($values as $key => $row) {
                $row_array = [];
                foreach ($this->columns as $column_key => $column) {
                    $row_array[] = "\"{$row[$column]}\"";
                }
                $csv .= implode(',', $row_array) . "\n";
            }

            $fp = fopen($this->file_path, "w");
            flock($fp, LOCK_EX);
            if (fputs($fp, $csv)) { } else {
                $this->errors[] = array('column' => 'csv', 'message' => 'save error');
            }
            flock($fp, LOCK_UN);
            fclose($fp);

            if (!chmod($this->file_path, 0666)) {
                $this->errors[] = array('column' => 'csv', 'message' => 'chmod error');
            }
        }
        return;
    }

    /**
     * implodeColumn
     *
     * @param array $columns
     * @return
     **/
    static function implodeColumn($columns)
    {
        if (is_array($columns)) {
            $lables = array_values($columns);
            $value = implode(',', $lables);
            $value .= "\n";
            return $value;
        }
    }

    /**
     * csvValue
     *
     * @param string $value
     * @return
     **/
    static function csvValue($value)
    {
        return '"' . $value . '"';
    }

    /**
     * escapeValue
     *
     * @param string $value
     * @return
     **/
    static function escapeValue($value)
    {
        $value = str_replace('"', '""', $value);
        $value = str_replace(',', '、', $value);
        return $value;
    }

    /**
     * output line
     * 
     * @param  array $values
     * @param  string $from_encode
     * @param  string $to_encode
     * @return void
     */
    static function outputLine($values, $from_encode = 'SJIS', $to_encode = 'UTF-8')
    {
        $value = implode(',', $values);
        $value .= "\r\n";
        $value = mb_convert_encoding($value, $from_encode, $to_encode);
        echo ($value);
    }

    /**
     * line
     * 
     * @param  array $values
     * @param  string $from_encode
     * @param  string $to_encode
     * @return void
     */
    static function line($values, $from_encode = 'SJIS', $to_encode = 'UTF-8')
    {
        $value = implode(',', $values);
        $value .= "\r\n";
        $value = mb_convert_encoding($value, $from_encode, $to_encode);
        return $value;
    }

    /**
     * load Csv sessions
     * 
     * @return array
     */
    static function loadSessions()
    {
        return PwSession::getWithKey('app', PwCsv::$session_name);
    }

    /**
     * load Csv session values
     * 
     * @param  string $key
     * @return array
     */
    static function loadSession($key)
    {
        $csv_sessions = PwSession::getWithKey('app', PwCsv::$session_name);
        return $csv_sessions[$key];
    }
}
