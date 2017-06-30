<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(dirname(__FILE__)) . '/');
    if (!@include_once(BASE_DIR."lib/setting.php")) {
        set_include_path(BASE_DIR.'app'.PATH_SEPARATOR.BASE_DIR.'lib');
        @include_once(BASE_DIR.'app/setting.php');
        @include_once(BASE_DIR.'app/application.php');
    }
}
if (!defined('ROOT_CONTROLLER_NAME')) define('ROOT_CONTROLLER_NAME', 'root');
if (!defined('APP_NAME')) define('APP_NAME', 'controller');

class Controller {
    var $name;
    var $layout = true;
    var $session_name = true;
    var $headers = array();
    var $_generate_static = false;
    var $_performed_render = false;

    function __construct($name = null) {
        $class_name = strtolower(get_class($this));
        if ($class_name !== 'controller') {
            if (!isset($this->name)) $this->name = substr($class_name, 0, strpos($class_name, 'controller'));
        } else if(isset($name)) {
            $this->name = $name;
            $this->session_name = false;
        }
        if ($this->session_name === true) $this->session_name = $this->name;
    }

    static function loadLib($libs) {
        foreach ($libs as $lib) {
            $path = BASE_DIR."lib/{$lib}.php";
            if (file_exists($path)) @include_once $path;
        }
    }

    static function load($name) {
        $controller = str_replace(" ","",ucwords(str_replace("_"," ",$name))) . "Controller";

        if (@include_once BASE_DIR."app/controllers/{$controller}.php") {
            return new $controller();
        } elseif ($name != 'layouts'
                    && preg_match('/^[a-z][a-z0-9_]*$/', $name)
                    && is_dir(BASE_DIR."app/views/{$name}")) {
            return new Controller($name);
        } else {
            return false;
        }
    }

    function construct_url($params, $config = array()) {
        if (empty($config['ROOT_CONTROLLER_NAME'])) $config['ROOT_CONTROLLER_NAME'] = ROOT_CONTROLLER_NAME;
        if (empty($config['DISPATCHER'])) $config['DISPATCHER'] = defined('DISPATCHER') ? DISPATCHER : false;
        if (empty($config['EXPAND_PARAMS'])) $config['EXPAND_PARAMS'] = defined('EXPAND_PARAMS') ? EXPAND_PARAMS : false;
        if (empty($config['INDEX_VISIBLE'])) $config['INDEX_VISIBLE'] = defined('INDEX_VISIBLE') ? INDEX_VISIBLE : false;
        if (empty($config['DEFAULT_EXTENSION'])) $config['DEFAULT_EXTENSION'] = defined('DEFAULT_EXTENSION') ? DEFAULT_EXTENSION : false;

        if (empty($params['controller'])) $params['controller'] = $config['ROOT_CONTROLLER_NAME'];
        $has_extension = isset($params['.extension']);

        if (empty($params['action'])) $params['action'] = 'index';
        if ($params['action'] == 'index' && !($config['INDEX_VISIBLE'] || $has_extension)) unset($params['action']);

        if (!$config['DISPATCHER'] || !$config['EXPAND_PARAMS'] || $is_static) {
            if (isset($params['controller']) && $params['controller'] !== $config['ROOT_CONTROLLER_NAME']) {
                $str_cai = "{$params['controller']}/";
            }
            unset($params['controller']);
            if (isset($params['action'])) {
                if (isset($params['id'])) {
                    $str_cai .= "{$params['action']}/";
                    if ($has_extension) {
                        $str_cai .= "{$params['id']}.{$params['.extension']}";
                    } else {
                        $str_cai .= $params['id'];
                    }
                } elseif ($has_extension) {
                    $str_cai .= "{$params['action']}.{$params['.extension']}";
                } else {
                    $str_cai .= $params['action'];
                }
                unset($params['action']);
            }
            unset($params['id']);
        }

        foreach ($params as $key => $value) {
            if (substr($key, 0, 1) == '.') unset($params[$key]);
        }

        if ($config['DISPATCHER']) {
            if (is_bool($config['DISPATCHER'])) {
                $url = 'dispatch.php?' . $str_cai;
            } else {
                $url = $config['DISPATCHER'] . '?' . $str_cai;
            }
        } else {
            $url = $str_cai;
        }

        //TODO
        if (!empty($params)) {
            $key_values = array();
            foreach ($params as $key => $value) {
                if (is_bool($value)) {
                    $value = ($value) ? 1 : 0;
                } elseif (is_array($value)) {
                    $value = implode(',', $value);
                }
                if ((is_string($value) && $value !== '') || is_numeric($value)) {
                    $key_values[] = $key . '=' . urlencode($value);
                }
            }
            if (count($key_values) > 0) {
                if ($config['DISPATCHER']) {
                    if (isset($str_cai)) {
                        $url .= '&' . implode('&', $key_values);
                    } else {
                        $url .= implode('&', $key_values);
                    }
                } else {
                    $url .= '?' . implode('&', $key_values);
                }
            }
        } 
        return $url;
    }

    static function parse_query_string($q = null, $route = 'controller/action/id') {
        if (is_null($q)) $q = $_SERVER['QUERY_STRING'];
        $q = preg_replace('/^([^\?]+)\?/', '\1&', $q);

        $params = array();
        //TODO
        if ($sep = strpos($q, '&')) {
            $q0 = substr($q, 0, $sep);
            if (strpos($q0, '=')) {
                $q1 = $q;
                unset($q0);
            } else {
                $q1 = substr($q, $sep + 1);
            }
        } else {
            if (strpos($q, '=')) {
                $q1 = $q;
            } else {
                $q0 = $q;
            }
        }

        if (isset($q0)) {
            if (false) {
                $params = explode('/', $q0);
                if (!Controller::load($params[0])) {
                    array_unshift($params, '');
                }
            } else {
                if (strpos($q0, '/')) {
                    $params = explode('/', $q0);
                } else {
                    $params = array('', $q0);
                }
            }
            $routes = explode('/', $route);
            foreach ($routes as $key) {
                if (isset($params[0])) {
                    $params[$key] = array_shift($params);
                    if ($params[$key] !== '') {
                        $extension_pos = strrpos($params[$key], '.');
                        if (is_numeric($extension_pos)) {
                            $params['.extension'] = substr($params[$key], $extension_pos + 1);
                            $params[$key] = substr($params[$key], 0, $extension_pos);
                        }
                    }
                }
            }
            foreach ($params as $key => $value) {
                if ($params[$key] === '') unset($params[$key]);
            }
        }

        if (isset($q1)) {
            parse_str($q1, $add_params);
            return array_merge($params, $add_params);
        } else {
            return $params;
        }
    }

    static function dispatch($params = array()) {
        $dispatcher = basename($_SERVER['SCRIPT_NAME']);
        if (strpos($_SERVER['REQUEST_URI'], "/{$dispatcher}")) {
            define('DISPATCHER', $dispatcher);
        } else {
            define('DISPATCHER', false);
        }
        if (empty($params['controller'])) {
            $params = Controller::parse_query_string();
        } 
        if (empty($params['controller'])) {
            $params['controller'] = ROOT_CONTROLLER_NAME;
        }
        $controller = Controller::load($params['controller']);
        if ($controller) {
            $controller->run($params);
        } else {
            header('HTTP/1.0 404 Not Found');
            $error_message = "{$_SERVER['REQUEST_URI']} Not Found";
            trigger_error($error_message, E_USER_NOTICE);
            if (!@include("errors/404.php")) exit($error_message);
            exit;
        }
    }

    function run($params = array()) {
        $this->params = (empty($params)) ? $_GET : $params;
        $this->_invoke();
    }

    function render($action, $with_layout = true) {
        if ($this->_performed_render) return;
        $this->before_rendering($action, $with_layout);

        if (substr($action, 0, 1) === '/') {
            $template = "views{$action}.phtml";
        } else {
            $template = "views/{$this->name}/{$action}.phtml";
        }

        if (!file_exists(BASE_DIR."app/{$template}")) {
            $error_message = "template missing: {$template}";
            trigger_error($error_message, E_USER_ERROR);
        }

        $GLOBALS['controller'] = $this;
        $GLOBALS['template'] = $template;

        @include_once "helpers.php";
        @include_once BASE_DIR."app/helpers/application_helper.php";
        @include_once BASE_DIR."app/helpers/{$this->name}_helper.php";

        // layout
        if (is_string($with_layout)) {
            $layout = $with_layout;
        } elseif ($with_layout) {
            if (is_string($this->layout)) {
                $layout = $this->layout;
            } elseif ($this->layout) {
                $layout = $this->name;
            }
        }

        // header
        header('Content-Type: ' . $this->content_type());
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        if (isset($layout) && file_exists(BASE_DIR."app/views/layouts/{$layout}.phtml")) {
            ob_start();
            include BASE_DIR."app/{$template}";
            $this->content_for_layout = ob_get_contents();
            ob_end_clean();
            include BASE_DIR."app/views/layouts/{$layout}.phtml";
        } else {
            include BASE_DIR."app/{$template}";
        }
        $this->_performed_render = true;
    }

    function render_text($text) {
        if ($this->_performed_render) return;

        $length = strlen($text);
        header("Content-Length: {$length}");
        header("Content-Type: " . $this->content_type());
        echo $text;
        $this->_performed_render = true;
    }

    function render_contents($contents, $content_type = null) {
        if ($this->_performed_render) return;
        if (is_null($content_type)) $content_type = $this->content_type();
        $length = strlen($contents);
        header("Content-Disposition: inline");
        header("Content-Length: {$length}");
        header("Content-Type: {$content_type}");
        echo $contents;
        $this->_performed_render = true;
    }

    function render_file($file, $content_type) {
        if ($this->_performed_render) return;
        if (file_exists($file)) {
            $length = filesize($file);
            header("Content-Disposition: inline");
            header("Content-Length: {$length}");
            header("Content-Type: {$content_type}");
            readfile($file);
            $this->_performed_render = true;
        } else {
            trigger_error("File Not Found: {$file}", E_USER_NOTICE);
        }
    }

    function download_file($file, $filename = null, $content_type = "application/octet-stream") {
        if ($this->_performed_render) return;
        if (file_exists($file)) {
            if (is_null($filename)) $filename = basename($file);
            $length = filesize($file);
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Content-Length: {$length}");
            header("Content-Type: {$content_type}");
            $this->_performed_render = true;
        } else {
            trigger_error("File Not Found: {$file}", E_USER_NOTICE);
        }
    }

    function download_contents($contents, $filename, $content_type = "application/octet-stream") {
        if ($this->_performed_render) return;
        $length = strlen($contents);
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Content-Length: {$length}");
        header("Content-Type: {$content_type}");
        echo $contents;
        $this->_performed_render = true;
    }

    function redirect_to($params, $option = null) {
        $this->_performed_render = true;

        if (strpos($params, '://')) {
            $url = $params;
        } else {
            $url = $this->url_for($params, $option);
            if (!strpos($url, '://')) {
                $url = $this->base . $url;
            }
            if (SID) $url .= ((strpos($url, '?')) ? '&' : '?' ) . SID;
        }

        if ($this->session_name && isset($this->flash)) {
            $this->session['flash'] = $this->flash;
        }
        if (defined('DEBUG') && DEBUG) error_log("<REDIRECT> {$url}");
        header("Location: {$url}");
    }

    function url_for($params, $option = null) {
        if (is_string($params)) {
            $pos = strpos($params, '/');
            if ($pos !== 0) {
                $anchor_pos = strpos($params, '#');
                if (is_numeric($anchor_pos)) {
                    $anchor = substr($params, $anchor_pos + 1);
                    $params = substr($params, 0, $anchor_pos);
                }
                $extension_pos = strrpos($params, '.');
                if (is_numeric($extension_pos)) {
                    $extension = substr($params, $extension_pos + 1);
                    $params = substr($params, 0, $extension_pos);
                }
            } else {
                $url = substr($params, 1);
            }
            if (is_numeric($pos)) {
                $params = Controller::parse_query_string($params);
            } else {
                $params = array('action' => $params);
            }
            if (isset($anchor)) {
                $params['.anchor'] = $anchor;
            }
            if (isset($extension)) {
                $params['.extension'] = $extension;
            }
        }

        if (is_array($option)) {
            $params = array_merge($option, $params);
        } elseif (isset($option)) {
            $params['id'] = $option;
        }

        if (!isset($url)) {
            if (empty($params['controller'])) $params['controller'] = $this->name;
            if (empty($params['action'])) $params['action'] = 'index';
            $url = Controller::construct_url($params);
        }

        return $url;
    }

    function content_type($encofing = null) {
        switch (mb_http_output()) {
        case "SJIS": $encoding = "Shift_JIS";break;
        case "EUC-JP": $encoding = "EUC-JP";break;
        default: $encoding = "UTF-8";
        }
        return "text/html; charset={$encoding}";
    }

    function session_terminate() {
        if ($this->session_name) {
            unset($_SESSION[APP_NAME][$this->session_name]);
        }
    }

    private function _invoke() {
        if ($this->_generate_static) {
            if (defined('BASE_URL') && is_string(BASE_URL)) {
                $this->base = BASE_URL;
                $this->relative_base = null;
            } else {
                $this->relative_base = '';
                if ($this->name !== ROOT_CONTROLLER_NAME) {
                    $this->relative_base .= '../';
                }
                if (isset($this->params['id'])) {
                    $this->relative_base .= '../';
                }
                $this->base = $this->relative_base;
            }
        } else {
            if (defined('DEBUG') && DEBUG) {
                error_log("<REQUEST> {$_SERVER['REQUEST_URI']}");
                error_log("<USER_AGENT> {$_SERVER['HTTP_USER_AGENT']}");
            }
            if (!isset($this->base)) {
                $r = &$_SERVER['REQUEST_URI'];
                if (defined('BASE_URL') && is_string(BASE_URL)) {
                    $this->base = BASE_URL;
                } else {
                    if (isset($_SERVER['HTTPS'])) {
                        $this->base = 'https://' . preg_replace('/:443$/', '', $_SERVER['HTTP_HOST']);
                    } else {
                        $this->base = 'http://' . preg_replace('/:80$/', '', $_SERVER['HTTP_HOST']);
                    }
                    if (DISPATCHER) {
                        if (strpos($r, '?')) {
                            $this->base .= substr($r, 0, strrpos(substr($r, 0, strpos($r, '?')), '/') + 1);
                        } else {
                            $this->base .= substr($r, 0, strrpos($r, '/') + 1);
                        }
                    } else {
                        $this->base .= substr($r, 0, strlen($r) - strlen($_SERVER['QUERY_STRING']));
                    }
                }

                $this->relative_base = null;
                if (!(defined('BASE_URL') && BASE_URL) && !DISPATCHER) {
                    $count = substr_count($_SERVER['QUERY_STRING'], '/');
                    $this->relative_base = '';
                    for ($i = 0; $i < $count; $i++) {
                        $this->relative_base .= '../';
                    }
                }
            }
        }

        // action
        $action = $this->params['action'];
        if (!isset($action) || $action === '') {
            $action = 'index';
        } else if ($pos = strpos($action, '.')) {
            $action = substr($action, 0, $pos);
        }

        if (((method_exists($this, $action) 
                || method_exists($this, "action_{$action}"))
                && substr($action, 0, 1) !== '_'
                && !method_exists(new Controller(), $action))) {

            if ($this->session_name) {
                session_start();
                if (is_null($_SESSION[APP_NAME])) {
                    $_SESSION[APP_NAME] = array();
                }
                if (is_null($_SESSION[APP_NAME][$this->session_name])) {
                    $_SESSION[APP_NAME][$this->session_name] = array();
                }
                $this->session =& $_SESSION[APP_NAME][$this->session_name];
                if (isset($this->session['flash'])) {
                    $this->flash = $this->session['flash'];
                    unset($this->session['flash']);
                }
            }

            $this->before_action($action);

            if ($this->_performed_render) return;
            if (method_exists($this, $action)) {
                $method = $action;
            } elseif (method_exists($this, "action_{$action}")) {
                $method = "action_{$action}";
            }
            if (!empty($method)) {
                $this->before_invocation($action);
                $this->$method();
                if (!$this->_generate_static) {
                    if (defined('DEBUG') && DEBUG) error_log("<INVOKED> {$action}");
                }
            }
            $this->render($action);
        } else {
            header('HTTP/1.0 404 Not Found');
            $error_message = "{$_SERVER['REQUEST_URI']} Not Found";
            trigger_error($error_message, E_USER_NOTICE);
            if (!@include("errors/404.php")) exit($error_message);
            exit;
        }
        unset($this->params['action']);
        unset($this->params['id']);
    }

    function before_action($action) {} 
    function before_rendering() {}
    function before_invocation() {}
}