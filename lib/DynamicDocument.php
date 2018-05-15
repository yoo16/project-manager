<?php
/**
 * Bassed document tools
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * @license     MIT License
 * @version     0.1
 * @copyright   Copyright 2011 by Yohei Yoshikawa (http://yoo-s.com)
 * 
 */

class DynamicDocument {

    var $total_row_count = 0;
    var $total_file_count = 0;
    var $columns;
    var $document_columns;
    var $file_type;
    var $except_dir;

    function DynamicDocument($type) {
        $types['php'] = array('ext' => '.php');
        $types['js'] = array('ext' => '.js');
        $types['view'] = array('ext' => '.phtml');

        $this->do_analyze['php'] = true;
        $this->do_analyze['js'] = true;
        $this->do_analyze['view'] = false;

        $this->file_type = $type;
        $this->file_ext = $types[$type]['ext'];

        $function_columns = array(
            'function_name' => array('title' => 'function'),
            'explains' => array('title' => 'explains'),
            'params' => array('title' => 'params'),
            );

        $document_columns = array(
            'class_name' => array('title' => 'Class'),
            'extends_class_name' => array('title' => 'Extends Class'),
            'functions' => array('title' => 'function', 'column' => $function_columns),
            );

        $this->columns = array(
            'file_name' => array('title' => 'File Name'),
            'file_path' => array('title' => 'File Path'),
            'documents' => array('title' => 'documents', 'columns' => $document_columns),
            );

        $this->except_dirs = array(
            '.svn'
            );
    }

    function init() {

    }


    /**
     * function of getting documents
     *
     * String file_name
     * String file_path
     * String class_name
     * String extends_class_name
     * Array  documents
     *        String function_name
     *        Array documents
     *
     * @param String   $path directory
     * @return Array
     */
    function sort() {
        if (is_array($this->_document_files)) {
            foreach ($this->_document_files as $path => $files) {
                $file_names = null;
                foreach ($files as $key => $file) {
                    $file_names[] = $file['file_name'];
                }
                array_multisort($file_names, SORT_ASC, $files);
                $this->_document_files[$path] = $files;
            }
        }
        return $this->_document_files;
    }

    /**
     * function of getting documents
     *
     * String path
     * String file_path
     * String class_name
     * String extends_class_name
     * Array  documents
     *        String function_name
     *        Array documents
     *
     * @param String $path directory
     * @return Array
     */
    function getDocuments($path) {
        if ($this->file_type) {
            $dir = opendir($path);
            while ($file_name = readdir($dir)) {
                $is_except = in_array($file_name, $this->except_dirs);
                if ($file_name == '.' || $file_name == '..' || $is_except) {

                } else {
                    $_path = $path.$file_name;
                    if (is_dir($_path)) {
                        $_path.= '/';
                        $this->getDocuments($_path);
                    } else {
                        $pattern = "/{$this->file_ext}$/";
                        if (preg_match($pattern, $file_name)) {
                            $file['file_name'] = $file_name;
                            $file['file_path'] = $_path;
                            $file['base_path'] = $path;

                            if (is_dir($path)) {
                                $dir_elements = explode('/', $path);
                                $element_index = count($dir_elements) - 2;
                                $file['dir'] = $dir_elements[$element_index];
                            }

                            if ($this->do_analyze[$this->file_type]) {
                                $file['documents'] = $this->analyzeFile($_path);
                            } else {
                                $file['documents'] = $this->_loadFile($_path);
                            }
                            $this->total_file_count++;
                            $this->_document_files[$path][] = $file;
                        }
                    }
                }
            }
        }
        return $this->_document_files;
    }

    function escapeCsv($value) {
        $value = "\"{$value}\"";
        return $value;
    }

    /**
     * load file
     *
     * @param String   $path directory
     * @return Array
     */
    function _loadFile($path) {
        mb_internal_encoding("UTF-8");
        
        $contents = file($path);
        foreach ($contents as $line) {
            $_line = trim($line);
            if ($_line) {
                $this->total_row_count++;
                $file_row_count++;
            }
        }
        return $results;
    }

    /**
     * _hasClass
     *
     * @param String   $path directory
     * @return Array
     */
    function _hasClass($contents) {
        foreach ($contents as $line) {
            $class_pos = strpos($line, 'class ');
            if (is_numeric($class_pos) && $class_pos === 0) {
                return true;
            } 
        }
    }

    /**
     * analyze file
     *
     * @param String $path
     * @param Array $functions
     * @return Array
     */
    function analyzeFunction($path, $functions) {
        if (!$functions) return;

        mb_internal_encoding("UTF-8");
        
        $contents = file($path);
        $has_class = self::_hasClass($contents);
        $is_class_document = $has_class;

        foreach ($contents as $key => $line) {
            if ($line) $file_row_count++;

            $define_function = self::_functionName($line);

            if (!$define_function) {
                foreach ($functions as $file_path => $values) {
                    $file_name = $values['file_name'];
                    if (is_array($values['functions'])) {
                        foreach ($values['functions'] as $function) {
                            $function_name = self::functionName($function['name']);
                            $params = $function['params'];
                            $param_count = count($params);

                            if ($function_name) {
                                $_function_name = "{$function_name}(";
                                $pos = strpos($line, $_function_name);

                                if (is_numeric($pos) && $pos > 0) {
                                    $line_function = self::searchLineFunction($line, $function_name);
                                    if ($line_function) {
                                        dump($line_function);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $results['row_count'] = $file_row_count;
        return $results;
    }

    /**
     * analyze file
     *
     * @param String   $path directory
     * @return Array
     */
    function analyzeFile($path) {
        mb_internal_encoding("UTF-8");
        
        $contents = file($path);
        $has_class = self::_hasClass($contents);
        $is_class_document = $has_class;

        foreach ($contents as $key => $line) {
            //$line = trim($line);
            if ($line) {
                $this->total_row_count++;
                $file_row_count++;
            }

            $require_once_pos = strpos($line, 'require_once');
            $require_once = explode('require_once ', $line);
            if (is_Array($require_once)) {
                $require_once = $require_once[1];
                if ($require_once) {
                    $require_once = str_replace('"', '', $require_once);
                    $require_once = str_replace("'", '', $require_once);
                    $require_once = str_replace(';', '', $require_once);
                    $require_onces[] = $require_once;
                }
            }

            $class_pos = strpos($line, 'class ');

            if ($is_document_start) {
                if (is_numeric(strpos($line, '*'))) {
                    $document_row++;
                    $is_top_document = ($document_row == 1);
                    $document_line = str_replace('*', '', $line);
                    $document_line = str_replace('/', '', $document_line);
                }
            }
            if (is_numeric(strpos($line, '/*'))) {
                //start
                $document_row = 0;
                $is_document_start = true;
            } elseif (is_numeric(strpos($line, '*/'))) {
                //end
                $is_document_start = false;
                $document_line = null;
            }

            //document
            if ($document_line) {
                //class document
                if ($has_class && $is_class_document) {
                    $atmark_pos = strpos($document_line, '@');
                    $package_pos = strpos($document_line, '@package');
                    $access_pos = strpos($document_line, '@access');
                    $create_pos = strpos($document_line, '@create');
                    if ($package_pos) {
                        $class_package = explode ('package', $document_line);
                        $class_document['package'] = $class_package[1];
                    } elseif ($access_pos) {
                        $class_access = explode ('access', $document_line);
                        $class_document['access'] = $class_access[1];
                    } elseif ($create_pos) {
                        $class_create = explode ('create', $document_line);
                        $class_document['create'] = $class_create[1];
                    } else {
                        if (!$atmark_pos) {
                            $comment = substr($document_line, $pos + 1);
                            if ($comment) {
                                $class_document['comment'].= $comment;
                            }
                        }
                    }
                } else {
                   //function document
                    $params_pos = strpos($document_line, '@param');
                    $access_pos = strpos($document_line, '@access');
                    $return_pos = strpos($document_line, '@return');

                    if ($params_pos > 0) {
                        $param = explode ('@param', $document_line);
                        $param = trim($param[1]);
                        if ($param) $function_document['params'][] = $param;
                    } elseif ($access_pos > 0) {
                        $access = explode ('@access', $document_line);
                        $access = $access[1];
                        if ($access) $function_document['access'] = $access;
                    } elseif ($return_pos > 0) {
                        $return = explode ('@return', $document_line);
                        $return = $return[1];
                        if ($return) $function_document['return'] = $return;
                    } else {
                        $document_line = trim($document_line);
                        if ($document_line) {
                            $document_line = trim($document_line)."\n";
                            if ($is_top_document) {
                                $function_document['first_explains'] = $document_line;
                            } else {
                                $function_document['explains'].= $document_line;
                            }
                        }
                    } 
                }


            } else {
                //class check
                if ($has_class && $is_class_document) {
                    if ($class_pos === 0) {
                        $class_document['class_name'] = self::_className($line);
                        $class_document['extends_class_name'] = self::_extendsClassName($line);
                        $class_documents = $class_document;

                        $class_document = null;
                        $is_class_document = false;
                    }
                } else {
                    //function check
                    $function_rows = explode('function', $line);
                    if ($function_rows) {
                        $function_name = self::_functionName($line);
                        if ($function_name) {
                            $function_document['function_name'] = $function_name;

                            $action_name = null;
                            $action_names = explode('(', $function_name);
                            if ($action_names) {
                                $action_name = trim($action_names[0]);
                                //$action_name = str_replace('action_', '', $action_name);
                            }
                            $function_document['action_name'] = $action_name;
                            $function_document['function_params'] = self::_functionParams($function_name);
                            $function_documents[] = $function_document;
                            $this->funcition_count++;
                            $function_document = null;
                        }
                    }
                }
            }
        }

        $results['row_count'] = $file_row_count;
        $results['function_documents'] = $function_documents;
        $results['class_documents'] = $class_documents;
        $results['require_once'] = $require_onces;
        return $results;
    }

    function _className($line) {
        $class_values = explode (' ', $line);
        if ($class_values[1]) return $class_values[1];
    }

    function _extendsClassName($line) {
        $class_values = explode (' ', $line);
        if ($class_values[3]) return $class_values[3];
    }

    function _functionName($line) {
        $_functions = explode('function ', $line);
        if ($_functions) {
            $function_name = $_functions[1];
            if ($function_name) {
                $function_name = explode('{', $function_name);
                return $function_name[0];
            }
        }
    }

    function _functionParams($function_name) {
        $function_names = explode('(', $function_name);
        $function_names = $function_names[1];
        $function_names = explode(')', $function_names);
        $param = $function_names[0];

        if ($param) {
            $params = explode(',', $param);
            if ($params) {
                return $params;
            }
        }
    }

    function functionName($values) {
        if (!$values) return;

        $pos = strpos($values, '(');
        if ($pos > 0) {
            $values = substr($values, 0, $pos);
            return $values;
        }
    }

    function searchLineFunction($line, $function_name) {
        $pos = strpos($line, $function_name);
        if ($pos > 0) {
            $line_function = substr($line, $pos);
            $pos = strpos($line_function, ')');
            $line_function = substr($line_function, 0, $pos + 1);
            trim($line_function);

            if ($line_function) {
                $values['name'] = $line_function;
                $values['params'] = self::_functionParams($values['name']);
                $values['param_count'] = count($values['params']);
            }
            return $values;
        }
    }

}