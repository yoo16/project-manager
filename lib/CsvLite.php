<?php
/**
 * CsvLite.php
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 **/

class CsvLite {

    var $id;
    var $id_key='id';
    var $name;
    var $columns;
    var $labels;
    var $dir;
    var $sort_order_keys; //Non Support
    var $file_path;
    var $buffer = 10240;
    var $errors;
    var $next_id;

    static $from_encode = '';
    static $to_encode = '';

    /**
     * constructor
     *
     **/
    function __construct($file_path=null) {
        $file_path = CsvLite::csvPath($file_path);
        if (file_exists($file_path)) {
            $this->file_path = $file_path;
        }
    }

    /**
     * csvPath
     *
     * @param string $csv_name
     * @return string
     **/
    static function csvPath($csv_name) {
        if (!$csv_name) return;
        $file_path = BASE_DIR."db/records/{$csv_name}.csv";
        if (!file_exists($file_path)) $file_path = BASE_DIR."db/records/{$csv_name}";
        if (!file_exists($file_path)) $file_path = BASE_DIR."{$csv_name}";
        if (file_exists($file_path)) return $file_path;
    }

    /**
     * options
     *
     * @param String $file_path
     * @return Array
     **/
    static function options($file_path) {
        $file_path = CsvLite::csvPath($file_path);
        if (file_exists($file_path)) {
            $fp = fopen($file_path, "r");
            $columns = fgetcsv($fp, 1024, ",");
            while ($data = fgetcsv($fp, 1024, ",")) {
                foreach ($columns as $key => $column) {
                    $value[$column] = $data[$key]; 
                }
                $results[] = $value;
            }
            fclose($fp); 
        }
        return $results;
    }

    /**
     * optionValues
     *
     * @param String $file_path
     * @param String $id_column
     * @param String $label_column
     * @return Array
     **/
     static function optionValues($file_path, $id_column='value', $label_column='label') {
        $file_path = CsvLite::csvPath($file_path);
        if (!$file_path || !file_exists($file_path)) return;
        $values = self::options($file_path);
        if (is_array($values)) {
            foreach($values as $value) {
                $results[$value[$id_column]] = $value[$label_column];
            }
        }
        return $results;
    }

   /**
    * form
    *
    * @param string $csv_path
    * @param string $name
    * @param array $params
    * @return array
    */ 
    static function form($csv_path, $name, $params = null) {
        $params['values'] = CsvLite::options($csv_path);
        $params['name'] = $name;
        return $params;
    }
    
    /**
     * 配列からCSV行に変換（カラム用）
     *
     * @param Array $values
     * @param Array $columns
     * @param Array $csv_callbacks
     * @return Array
     **/
    static function arrayToCsvForColumns($values, $columns, $csv_callbacks=null) {
        if (is_array($columns)) {
            $keys = array_keys($columns);
            $labels = array_values($columns);
            $csv = implode(',', $labels)."\n";
        }
        if(is_array($values)) {
            foreach ($values as $key => $row) {
                $row_array = null;
                foreach($keys as $column) { 
                    $value = $row[$column];
                    if ($csv_callbacks && $csv_callbacks[$column]) {
                        $func = $csv_callbacks[$column];
                        $value = call_user_func($func, $value); 
                    }
                    $row_array[] = "\"{$value}\"";
                }
                $csv.= implode(',', $row_array)."\n";
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
     * @param Array $values
     * @return Array
     **/
    static function arrayToCsv($values) {
        if (is_array($values)) {
            $columns = array_keys($values[0]);
            $csv.= implode(',', $columns);
            $csv.= "\n";
            foreach ($values as $key => $value) {
                $csv_values = null;
                foreach ($value as $column => $_value) {
                    if (self::$from_encode && self::$to_encode) {
                        $_value = mb_convert_encoding($_value, self::$to_encode, self::$from_encode);
                    }
                    $csv_values[] = csv_value($_value);
                }
                if (is_array($csv_values)) {
                    $csv.= implode(',', $csv_values);
                }
                $csv.= "\n";
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
     function valuesForCsvName($csv_name) {
        $file_path = CsvLite::csvPath($file_path);
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
     * @param Array $columns
     * @return
     **/
     function create($columns=null) {
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
     * @param String $column_key
     * @return Array
     **/
    function fetch($id) {
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
     * _list
     *
     * @param Array $columns
     * @return Array
     **/
    function _list($columns) {
        $values = array();
        if (file_exists($this->file_path)) {
            $fp = fopen($this->file_path, "r");

            $columns = fgetcsv($fp, $this->buffer, ",");
            if (!$this->columns) $this->columns = $columns;
            if (!is_array($this->columns)) return;

            while ($data = fgetcsv($fp, $this->buffer, ",")) {
                foreach ($columns as $key => $column) {
                    $_value;
                    if ($data[$key]) {
                        if (self::$from_encode && self::$to_encode) {
                            $_value = mb_convert_encoding($data[$key], self::$to_encode, self::$from_encode);
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
     * @param Array $conditions
     * @return Array
     **/
    function results($conditions=null) {
        $values = $this->_readCsv();
        $values = $this->_sortOrder($values);

        if (is_array($values)) {
            if (self::$from_encode && self::$to_encode) {
                foreach($values as $key => $_values) {
                    foreach($_values as $column => $_value) {
                        $_result[$column] = mb_convert_encoding($_value, self::$to_encode, self::$from_encode);
                    }
                    $results[] = $_result;
                }
            } else {
                $results = $values;
            }
        }
        return $results;
    }

    /**
     * _sortOrder
     *
     * @param Array $values
     * @param String $sort_key
     * @return Array
     **/
    function _sortOrder($values, $sort_key='id') {
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
     * @param Array $posts
     * @return Array
     **/
    function insert($posts) {
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
     * @param Array $posts
     * @return Array
     **/
    function take_values($posts) {
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
    function update() {
        if ($this->id > 0 && is_array($this->values)) {
            if ($this->values) {
                foreach ($this->values as $key => $value) {
                    if ($value[$this->id_key] == $this->id) {
                        $this->values[$key] = $this->value;
                    }
                }
                $this->_writeCsv($this->values);
            }
        }
    }

    /**
     * update
     *
     * @return
     **/
    function delete($id) {
        $this->values = $this->_readCsv();
        if ($this->values) {
            foreach ($this->values as $key => $value) {
                if ($value[$this->id_key] == $id) {
                    unset($this->values[$key]);
                }
            }
            $this->_writeCsv($this->values);
        }
    }

    /**
     * _readCsv
     *
     * @return
     **/
    function _readCsv() {
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
                $results[] = $value;
                if ($value[$this->id_key] > 0) {
                    $ids[] = $value[$this->id_key];
                }
            }
            fclose($fp); 
        }

        if (is_array($ids)) $max_id = max($ids);
        $this->next_id = $max_id + 1;
        $this->values = $results;
        return $results;
    }

    /**
     * _writeCsv
     *
     * @param Array $values
     * @return
     **/
    function _writeCsv($values) {
        $csv = implode(',', $this->columns)."\n";

        if(is_array($values)) {
            foreach ($values as $key => $row) {
                $row_array = null;
                foreach($this->columns as $column_key => $column) { 
                    $row_array[] = "\"{$row[$column]}\"";
                }
                $csv.= implode(',', $row_array)."\n";
            }

            $fp = fopen($this->file_path, "w");
            flock($fp, LOCK_EX);
            if (fputs($fp, $csv)) {

            } else {
                $this->errors[] = array('column' => 'csv', 'message' => 'save error');
            }
            flock($fp, LOCK_UN);
            fclose($fp);

            if (!chmod($this->file_path, 0666)) {
                $this->errors[] = array('column' => 'csv', 'message' => 'chmod error');
            }
        }
        return $results;
    }

    /**
     * implodeColumn
     *
     * @param Array $columns
     * @return
     **/
    static function implodeColumn($columns) {
        if (is_array($columns)) {
            $lables = array_values($columns);
            $value = implode(',', $lables);
            $value.="\n";
            return $value;
        }
    }

    /**
     * csvValue
     *
     * @param String $value
     * @return
     **/
    static function csvValue($value) {
        return '"'.$value.'"';
    }

    /**
     * _constructFilePath
     *
     * @param String $dir
     * @param String $name
     * @return
     **/
    static function _constructFilePath($dir, $name) {
        $this->file_path = "{$dir}{$name}";
    }


    /**
     * escapeValue
     *
     * @param String $value
     * @return
     **/
    static function escapeValue($value) {
        $value = str_replace('"', '""', $value);
        $value = str_replace(',', '、', $value);
        return $value;
    }

}