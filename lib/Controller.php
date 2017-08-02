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

class Controller extends RuntimeException {
    static $routes = ['controller', 'action', 'id'];
    var $name;
    var $layout = true;
    var $session_name = true;
    var $headers = array();
    var $_performed_render = false;
    var $relative_base = '';

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

    // function __isset($name) {
    //     var_dump($name);
    // }

    function __invoke() {
    }

    function __destruct() {
    }

    public function __call($name, $args) {
        if(!method_exists($this, $name)) {
            var_dump('error');
            return false;
        }
        $this->{$name}($args);
    }

    /**
     * load lib
     * 
     * @param  array $libs
     * @return void
     */
    static function loadLib($libs) {
        if (!$libs) return;
        foreach ($libs as $lib) {
            $path = BASE_DIR."lib/{$lib}.php";
            if (file_exists($path)) @include_once $path;
        }
    }

    /**
     * load
     *
     * @param  array $name
     * @return Class
     */
    static function load($name) {
        $controller = str_replace(" ", "", ucwords(str_replace("_", " ", $name))) . "Controller";

        $controller_path = BASE_DIR."app/controllers/{$controller}.php";
        if (file_exists($controller_path)) {
            if ($result = @include_once $controller_path) {
                return new $controller();
            } else if ($name != 'layouts'&& preg_match('/^[a-z][a-z0-9_]*$/', $name) && is_dir(BASE_DIR."app/views/{$name}")) {
                return new Controller($name);
            }
        }
        return;
    }

    /**
     * generate url
     *
     * @param  array $params
     * @return string
     */
    static function generateUrl($params) {
        if ($params['controller'] == ROOT_CONTROLLER_NAME) unset($params['controller']);
        if ($params['action'] == 'index') unset($params['action']);

        if (isset($params['controller'])) $url.= "{$params['controller']}/";
        if (isset($params['action'])) {
            $url.= "{$params['action']}";
            if (isset($params['id'])) $url.= "/{$params['id']}";
        }

        if (!$url) $url = './';
        if (is_array($params['params'])) {
            $url_params = http_build_query($params['params']);
            $url = "{$url}?{$url_params}";
        }
        return $url;
    }

    /**
     * query string
     * 
     * @param  string $query
     * @return string
     */
    static function queryString($query = null) {
        if (is_null($query)) {
            $request = $_SERVER['REQUEST_URI'];
            $query = $_SERVER['QUERY_STRING'];
        }
       
        $query_url = parse_url($query);
        $values = explode('&', $query_url['path']);
        if ($values[0]) {
            $paths = explode('/', $values[0]);
            if (count($paths) == 1) {
                $params['controller'] = ROOT_CONTROLLER_NAME;
                $params['action'] = $paths[0];
            } else {
                foreach ($paths as $key => $path) {
                    $column = self::$routes[$key];
                    if ($column && $path) $params[$column] = $path;
                }
            }
        } 

        $request_url = parse_url($request);
        if (isset($request_url['query'])) {
            $values = explode('&', $request_url['query']);
            if ($values) {
                foreach ($values as $value) {
                    $param_array = explode('=', $value);
                    if (isset($param_array[0]) && isset($param_array[1])) {
                        $params[$param_array[0]] = $param_array[1];
                    }
                }
            }
        }
        return $params;
    }

    /**
     * dispatch
     * 
     * @param  array  $params
     * @return void
     */
    static function dispatch($params = array()) {
        if (empty($params['controller'])) $params = Controller::queryString();
        if (empty($params['controller'])) $params['controller'] = ROOT_CONTROLLER_NAME;

        $controller = Controller::load($params['controller']);
        if ($controller) {
            try {
                $controller->run($params);
            } catch (Throwable $t) {
                $errors['code'] = $t->getCode();
                $errors['message'] = $t->getMessage();

                self::renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            } finally {

            }
        } else {
            //TODO try catch
            //$errors['type'] = '404 Not Found';
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['controller'] = $params['controller'];
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $content_for_layout = self::renderError($errors, false);

            ob_start();
            include BASE_DIR."app/views/layouts/error.phtml";
            $contents = ob_get_contents();
            ob_end_clean();
            echo($contents);
        }
    }

    /**
     * run
     * 
     * @param  array  $params
     * @return void
     */
    function run($params = array()) {
        $GLOBALS['controller'] = $this;
        $this->params = (empty($params)) ? $_GET : $params;
        try {
            $this->_invoke();
        } catch (Throwable $t) {
            $errors['code'] = $t->getCode();
            $errors['message'] = $t->getMessage();

            self::renderError($errors);
        } catch (Error $e) {
            var_dump($e);
        } catch (Exception $e) {
            var_dump($e);
        } finally {

        }
    }

    /**
     * render
     * 
     * @param  string $action
     * @param  boolean $with_layout
     * @return void
     */
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
        
        $GLOBALS['template'] = $template;

        //@include_once "helpers.php";
        @include_once BASE_DIR."app/helpers/application_helper.php";

        // layout
        if (is_string($with_layout)) {
            $layout = $with_layout;
        } else if ($with_layout) {
            if (is_string($this->layout)) {
                $layout = $this->layout;
            } elseif ($this->layout) {
                $layout = $this->name;
            }
        }

        // header
        header('Content-Type: ' . $this->contentType());
        if ($this->headers) {
            foreach ($this->headers as $key => $value) {
                header("{$key}: {$value}");
            }
        }

        if (isset($layout) && file_exists(BASE_DIR."app/views/layouts/{$layout}.phtml")) {
            try {
                ob_start();
                include BASE_DIR."app/{$template}";
                $this->content_for_layout = ob_get_contents();
                ob_end_clean();
            } catch (Throwable $t) {
                $errors = $this->throwErrors($t);
                self::renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            } finally {
                
            }

            include BASE_DIR."app/views/layouts/{$layout}.phtml";
        } else {
            include BASE_DIR."app/{$template}";
        }
        $this->_performed_render = true;
    }

    /**
     * throwErrors
     *
     * @param  Throwable $t
     * @return array
     */
    private function throwErrors($t) {
        $errors['code'] = $t->getCode();
        $errors['file'] = $t->getFile();
        $errors['line'] = $t->getLine();
        $errors['message'] = $t->getMessage();
        $errors['trace'] = nl2br($t->getTraceAsString());
        return $errors;
    }

    /**
     * renderError
     *
     * @param  array $errors
     * @return void
     */
    static function renderError($errors, $is_render = true) {
        $error_template = BASE_DIR."app/views/components/php_error.phtml";
        if (file_exists($error_template)) {
            ob_start();
            include $error_template;
            $error_contents = ob_get_contents();
            ob_end_clean();
            if ($is_render) {
                echo($error_contents);
            } else {
                return $error_contents;
            }
        }
    }

    /**
     * render contents
     *
     * @param  string $text
     * @return void
     */
    function renderText($text) {
        if ($this->_performed_render) return;

        $length = strlen($text);
        header("Content-Length: {$length}");
        header("Content-Type: " . $this->contentType());
        echo $text;
        $this->_performed_render = true;
    }

    /**
     * render contents
     *
     * @param  string $contents
     * @param  string $content_type
     * @return void
     */
    function renderContents($contents, $content_type = null) {
        if ($this->_performed_render) return;
        if (is_null($content_type)) $content_type = $this->contentType();
        $length = strlen($contents);
        header("Content-Disposition: inline");
        header("Content-Length: {$length}");
        header("Content-Type: {$content_type}");
        echo $contents;
        $this->_performed_render = true;
    }

    /**
     * render file
     *
     * @param  string $file
     * @param  string $content_type
     * @return void
     */
    function renderFile($file, $content_type) {
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

    /**
     * downloadFile
     * 
     * @param  string $file
     * @param  string $file_name
     * @param  string $content_type
     * @return void
     */
    function downloadFile($file, $file_name = null, $content_type = "application/octet-stream") {
        if ($this->_performed_render) return;
        if (file_exists($file)) {
            if (is_null($file_name)) $file_name = basename($file);
            $length = filesize($file);

            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident')) {
                $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
            }
            header("Content-Length: {$length}");
            header("Content-Disposition: Attachment; filename=\"{$file_name}\""); 
            header("Content-type: {$content_type}; name=\"{$file_name}\"");
            $this->_performed_render = true;
        } else {
            trigger_error("File Not Found: {$file}", E_USER_NOTICE);
        }
    }

    /**
     * downloadCotents
     * 
     * @param  string $contents
     * @param  string $file_name
     * @param  string $content_type
     * @return void
     */
    function downloadContents($contents, $file_name, $content_type = "application/octet-stream") {
        if ($this->_performed_render) return;

        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident')) {
            $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
        }

        //$length = strlen($contents);
        //header("Content-Length: {$length}");
        header('Cache-Control: public');
        header('Pragma: public');
        header("Content-Disposition: Attachment; filename=\"{$file_name}\""); 
        header("Content-type: {$content_type}; name=\"{$file_name}\"");
        echo($contents);

        $this->_performed_render = true;
    }

    /**
     * redirect
     * 
     * @param  array $params
     * @param  array $options
     * @return void
     */
    function redirect_to($params, $options = null) {
        $this->_performed_render = true;

        if (strpos($params, '://')) {
            $url = $params;
        } else {
            $url = $this->url_for($params, $options);
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

    /**
     * url for params
     * @param  array $url_params
     * @param  array $options
     * @return string
     */
    function url_for($url_params, $options = null) {
        $params = array();
        if (is_string($url_params)) {
            $_params = explode('?', $url_params);
            if ($_params[0]) $url_params = $_params[0];
            if ($_params[1]) $query = $_params[1];

            $_params = explode('#', $url_params);
            if ($_params[0]) $url_params = $_params[0];
            if ($_params[1]) $params['.anchor'] = $_params[1];

            $_params = explode('.', $url_params);
            if ($_params[0]) $url_params = $_params[0];
            if ($_params[1]) $params['.extension'] = $_params[1];

            $_params = explode('/', $url_params);
            if (isset($_params[1])) {
                if (empty($_params[0])) {
                    $params['controller'] = ROOT_CONTROLLER_NAME;
                    $params['action'] = 'index';
                } else {
                    $params['controller'] = $_params[0];
                    $params['action'] = $_params[1];
                }
            } else {
                $params['controller'] = $this->name;
                $params['action'] = $_params[0];
            }
            if (!$params['action']) $params['action'] = 'index';
        }

        if (is_array($options)) {
            $params['params'] = $options;
        } else if (isset($options)) {
            $params['id'] = $options;
        }
        $url = Controller::generateUrl($params);
        return $url;
    }

    /**
     * content type
     * 
     * @param  string $encofing
     * @return string
     */
    function contentType($encoding = null) {
        if (!$encoding) {
            $encoding = mb_http_output();
            if ($encoding != 'UTF-8') {
                $encodings['SJIS'] = 'Shift_JIS';
                $encodings['EUC-JP'] = 'EUC-JP';
                $encoding = $encodings[$encoding];
            }
        }
        return "text/html; charset={$encoding}";
    }

    /**
     * session terminate
     *
     * @return void
     */
    function flushSsession() {
        if ($this->session_name) {
            unset($_SESSION[APP_NAME][$this->session_name]);
        }
    }

    /**
     * invoke
     * 
     * @return void
     */
    private function _invoke() {
        if (defined('DEBUG') && DEBUG) {
            error_log("<REQUEST> {$_SERVER['REQUEST_URI']}");
            error_log("<USER_AGENT> {$_SERVER['HTTP_USER_AGENT']}");
        }

        if (!isset($this->base)) {
            $r = $_SERVER['REQUEST_URI'];
            if (defined('BASE_URL') && is_string(BASE_URL)) {
                $this->base = BASE_URL;
            } else {
                if (isset($_SERVER['HTTPS'])) {
                    $this->base = 'https://' . preg_replace('/:443$/', '', $_SERVER['HTTP_HOST']);
                } else {
                    $this->base = 'http://' . preg_replace('/:80$/', '', $_SERVER['HTTP_HOST']);
                }
                $this->base .= substr($r, 0, strlen($r) - strlen($_SERVER['QUERY_STRING']));
            }

            if (!(defined('BASE_URL') && BASE_URL)) {
                $count = substr_count($_SERVER['QUERY_STRING'], '/');
                for ($i = 0; $i < $count; $i++) {
                    $this->relative_base .= '../';
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

        //TODO
        if (((method_exists($this, $action) 
                || method_exists($this, "action_{$action}"))
                && substr($action, 0, 1) !== '_'
                && !method_exists(new Controller(), $action))) {

            if ($this->session_name) {
                session_start();
                if (is_null($_SESSION[APP_NAME])) $_SESSION[APP_NAME] = array();
                if (is_null($_SESSION[APP_NAME][$this->session_name])) $_SESSION[APP_NAME][$this->session_name] = array();

                $this->session = $_SESSION[APP_NAME][$this->session_name];
                if (isset($this->session['flash'])) {
                    $this->flash = $this->session['flash'];
                    unset($this->session['flash']);
                }
            }
            $this->before_action($action);

            if ($this->_performed_render) return;
            if (method_exists($this, $action)) {
                $method = $action;
            } else if (method_exists($this, "action_{$action}")) {
                $method = "action_{$action}";
            }
            if (!empty($method)) {
                $this->before_invocation($action);
                $this->$method();
                if (defined('DEBUG') && DEBUG) error_log("<INVOKED> {$action}");
            }
            $this->render($action);
        } else {
            //TODO try catch
            //$error_message = "{$_SERVER['REQUEST_URI']} Not Found";
            //trigger_error($error_message, E_USER_NOTICE);

            $errors['type'] = '404 Not Found';
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['controller'] = $this->name;
            $errors['action'] = $action;
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $content_for_layout = self::renderError($errors, false);

            ob_start();
            include BASE_DIR."app/views/layouts/error.phtml";
            $contents = ob_get_contents();
            ob_end_clean();
            echo($contents);
            exit;
        }
        unset($this->params['action']);
        unset($this->params['id']);
    }

    function before_action($action) {} 
    function before_rendering() {}
    function before_invocation() {}
}