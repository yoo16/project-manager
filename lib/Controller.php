<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

@include_once("PwSetting.php");
if (!defined('ROOT_CONTROLLER_NAME')) define('ROOT_CONTROLLER_NAME', 'root');
if (!defined('APP_NAME')) define('APP_NAME', 'controller');

class Controller extends RuntimeException {
    static $routes = ['controller', 'action', 'id'];
    public $name;
    public $layout = true;
    public $session_name = '';
    public $headers = [];
    public $_performed_render = false;
    public $relative_base = '';
    public $project_name = '';
    public $pw_controller = '';
    public $pw_action = '';
    public $pw_method = '';

    function __construct($name = null) {
        $class_name = strtolower(get_class($this));
        if ($class_name !== 'controller') {
            if (!isset($this->name)) $this->name = substr($class_name, 0, strpos($class_name, 'controller'));
        } else if(isset($name)) {
            $this->name = $name;
        }
        if (!$this->session_name) $this->session_name = "{$this->name}_controller";
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
            $class_name = get_class($this);
            $message = "Not declared {$class_name}->{$name}";
            exit($message);
        }
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
        $url = '';
        if ($params['controller'] == ROOT_CONTROLLER_NAME) unset($params['controller']);
        if ($params['action'] == 'index') unset($params['action']);

        if (isset($params) && isset($params['controller'])) $url.= "{$params['controller']}/";
        if (isset($params['action'])) {
            $url.= "{$params['action']}";
            if (isset($params['id'])) $url.= "/{$params['id']}";
        }

        if (!$url) $url = './';
        if (isset($params['params']) && is_array($params['params'])) {
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
        return $params;
    }

    static function bindPwRequestParams($params) {
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

        //TODO static public contents
        if (strpos($params['action'], '.')) return;

        $controller = Controller::load($params['controller']);
        if ($controller) {
            try {
                session_start();
                $controller->run($params);
            } catch (Throwable $t) {
                $errors = $this->throwErrors($t);
                self::renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            // } finally {

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
        $this->loadPosts();
        $this->errors = $this->getErrors();
        $this->flushErrors();

        try {
            $this->_invoke();
        } catch (Throwable $t) {
            $errors = $this->throwErrors($t);
            self::renderError($errors);
        } catch (Error $e) {
            var_dump($e);
        } catch (Exception $e) {
            var_dump($e);
        // } finally {

        }
    }

    /**
     * pw layout
     * 
     * @param  boolean $with_layout
     * @return string
     */
    public function pwLayout($with_layout = true) {
        if (is_string($with_layout)) {
            $layout = $with_layout;
        } else if ($with_layout) {
            if (is_string($this->layout)) {
                $layout = $this->layout;
            } elseif ($this->layout) {
                $layout = $this->name;
            }
        }
        $this->pw_layout = $layout;
        return $layout;
    }

    public function pwTemplate($action) {
        if (substr($action, 0, 1) === '/') {
            $template = "views{$action}.phtml";
        } else {
            $template = "views/{$this->name}/{$action}.phtml";
        }
        // if (!file_exists(BASE_DIR."app/{$template}")) {
        //     $error_message = "template missing: {$template}";
        //     exit($error_message);
        // }
        $this->pw_template = $template;
        return $template;
    }

    /**
     * load pw template
     *
     * @return void
     */
    function loadPwTemplate() {
        ob_start();
        include BASE_DIR."app/{$this->pw_template}";
        $this->content_for_layout = ob_get_contents();
        ob_end_clean();
    }

    /**
     * load pw headers
     * 
     * @return void
     */
    function loadPwHeaders() {
        header('Content-Type: ' . $this->contentType());
        if (isset($this->pw_headers)) {
            foreach ($this->pw_headers as $key => $value) {
                header("{$key}: {$value}");
            }
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
        $template = $this->pwTemplate($action);

        @include_once BASE_DIR."app/helpers/application_helper.php";

        $layout = $this->pwLayout();

        $this->loadPwHeaders();

        if (isset($layout) && file_exists(BASE_DIR."app/views/layouts/{$layout}.phtml")) {
            try {
                $this->loadPwTemplate();
            } catch (Throwable $t) {
                $errors = $this->throwErrors($t);
                self::renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            // } finally {
                
            }

            include BASE_DIR."app/views/layouts/{$layout}.phtml";
        } else {
            include BASE_DIR."app/{$this->pw_template}";
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
     * TODO: remove
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
            if (!strpos($url, '://')) $url = $this->base . $url;
            if (SID) $url .= ((strpos($url, '?')) ? '&' : '?' ) . SID;
        }
        if (defined('DEBUG') && DEBUG) error_log("<REDIRECT> {$url}");
        header("Location: {$url}");
        exit;
    }


    /**
     * redirect
     * 
     * @param  array $params
     * @param  array $options
     * @return void
     */
    function redirectTo($params, $options = null) {
        $this->_performed_render = true;

        if (strpos($params, '://')) {
            $url = $params;
        } else {
            $url = $this->url_for($params, $options);
            if (!strpos($url, '://')) $url = $this->base . $url;
            if (SID) $url .= ((strpos($url, '?')) ? '&' : '?' ) . SID;
        }
        if (defined('DEBUG') && DEBUG) error_log("<REDIRECT> {$url}");
        header("Location: {$url}");
        exit;
    }

    /**
     * url for params
     *
     * TODO: remove
     * 
     * @param  array $url_params
     * @param  array $options
     * @return string
     */
    function url_for($url_params, $options = null) {
        $params = array();
        if (is_string($url_params)) {
            $_params = explode('?', $url_params);
            if ($_params[0]) $url_params = $_params[0];
            if (isset($_params[1])) $query = $_params[1];

            $_params = explode('#', $url_params);
            if ($_params[0]) $url_params = $_params[0];
            if (isset($_params[1])) $params['.anchor'] = $_params[1];

            $_params = explode('.', $url_params);
            if ($_params[0]) $url_params = $_params[0];
            if (isset($_params[1])) $params['.extension'] = $_params[1];

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
            if (isset($options['id']) && $options['id'] > 0) {
                $params['id'] = $options['id'];
                unset($options['id']);
            }
            $params['params'] = $options;
        } else if (isset($options)) {
            $params['id'] = $options;
        }
        $url = Controller::generateUrl($params);
        return $url;
    }

    /**
     * url for params
     *
     * @param  array $controller
     * @param  array $action
     * @param  array $id
     * @param  array $params
     * @return string
     */
    function url_for_mvc($controller, $action, $id = null, $params = null) {
        $values['controller'] = $controller;
        $values['action'] = $action;
        $values['id'] = $id;

        $url = $this->base;
        $url.= "{$controller}/";
        if ($action) $url.= "{$action}/";
        if ($id) $url.= "{$id}";

        if (isset($params) && is_array($params)) {
            $url_params = http_build_query($params);
            $url = "{$url}?{$url_params}";
        }
        return $url;
    }

    /**
     * url for params
     *
     * TODO: refectoring
     * 
     * @param  array $url_params
     * @param  array $options
     * @return string
     */
    function urlFor($url_params, $options = null) {
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
     * project name for PHP_SELF
     *
     * @return $string
     */
    public function projectName() {
        $php_self = $_SERVER['PHP_SELF'];
        $this->project_name = str_replace('public', '', $php_self);
        $this->project_name = str_replace('dispatch.php', '', $this->project_name);
        $this->project_name = str_replace('/', '', $this->project_name);
        return $this->project_name;
    }

    /**
     * relative base url for QUERY_STRING
     *
     * @return string
     */
    public function relativeBaseURL() {
        $query = str_replace('?', '', $_SERVER['QUERY_STRING']);
        $count = substr_count($query, '/');
        for ($i = 0; $i < $count; $i++) {
            $this->relative_base .= '../';
        }
        return $this->relative_base;
    }

    /**
     * base url for HTTPS & HTTP_HOST
     *
     * @return string
     */
    public function baseUrl() {
        $this->projectName();
        $this->http_host = $_SERVER['HTTP_HOST'];

        if (defined('BASE_URL') && is_string(BASE_URL)) {
            $this->base = BASE_URL;
            return;
        }
        if (isset($_SERVER['HTTPS'])) {
            $this->base = 'https://' . preg_replace('/:443$/', '', $this->http_host);
        } else {
            $this->base = 'http://' . preg_replace('/:80$/', '', $this->http_host);
        }
        $this->base.= '/';
        if ($this->project_name) $this->base .= $this->project_name.'/';
        return $this->base;
    }

    /**
     * pw controller
     *
     * @return string
     */
    public function pwController() {
        $this->pw_controller = $this->params['controller'];
        return $this->pw_controller;
    }

    /**
     * pw action
     *
     * @return string
     */
    public function pwAction() {
        $this->pw_action = $this->params['action'];
        if (!isset($this->pw_action) || $this->pw_action === '') {
            $this->pw_action = 'index';
        } else if ($pos = strpos($this->pw_action, '.')) {
            $this->pw_action = substr($this->pw_action, 0, $pos);
        }
        return $this->pw_action;
    }

    /**
     * pw method
     * 
     * @param string $action
     * @return string
     */
    public function pwMethod($action) {
        if (method_exists($this, $action)) {
            $method = $action;
        } else if (method_exists($this, "action_{$action}")) {
            $method = "action_{$action}";
        }
        if (method_exists(new Controller(), $method)) {
            $method = null;
        }
        return $method;
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

        $this->base = $this->baseUrl();
        $this->relativeBaseURL();

        $this->pwAction();
        $method = $this->pwMethod($this->pw_action);

        //TODO
        if (!empty($method)) {
            $this->before_action($this->pw_action);
            if ($this->_performed_render) return;

            $this->$method();

            $this->before_invocation($this->pw_action);
            if (defined('DEBUG') && DEBUG) error_log("<INVOKED> {$this->pw_action}");

            $this->render($this->pw_action);
        } else {
            //TODO try catch
            //$error_message = "{$_SERVER['REQUEST_URI']} Not Found";
            //trigger_error($error_message, E_USER_NOTICE);

            $errors['type'] = '404 Not Found';
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['controller'] = $this->name;
            $errors['action'] = $this->pw_action;
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $content_for_layout = Controller::renderError($errors, false);

            ob_start();
            include BASE_DIR."app/views/layouts/error.phtml";
            $contents = ob_get_contents();
            ob_end_clean();
            echo($contents);
            exit;
        }
    }

    /**
     * load $_POST
     *
     * @return void
     */
    function loadPosts() {
        if ($_POST) AppSession::set('posts', $_POST);
        $this->posts = AppSession::get('posts');
    }

    /**
     * clear $_POST
     *
     * @return void
     */
    function clearPosts() {
        $this->clearSessions('posts');
    }

    /**
     * set session errors
     *
     * @param  array $errors
     * @return void
     */
    function setErrors($errors) {
        AppSession::setWithKey('errors', $this->name, $errors);
    }

    /**
     * get session errors
     *
     * @return array
     */
    function getErrors() {
        return AppSession::getWithKey('errors', $this->name);
    }

    /**
     * get session errors
     *
     * @return array
     */
    function flushErrors() {
        return AppSession::clearWithKey('errors', $this->name);
    }

    /**
     * get sessions by key
     *
     * @param  string $key
     * @return string
     */
    function getSessions($key) {
        if (!$this->session_name) return;
        return AppSession::getWithKey($this->session_name, $key); 
    }

    /**
     * set sessions by key value
     *
     * @param  string $key
     * @param  array $vaues
     * @return void
     */
    function setSessions($key, $values) {
        if (!$this->session_name) return;
        AppSession::setWithKey($this->session_name, $key, $values); 
    }

    /**
     * clear sessions by key
     *
     * @param  string $key
     * @return void
     */
    function clearSessions($key) {
        if (!$this->session_name) return;
        AppSession::clearWithKey($this->session_name, $key); 
    }

    /**
     * session terminate
     *
     * @return void
     */
    function flushSessions() {
        if (!$this->session_name) return;
        AppSession::flushWithKey($this->session_name);
    }

    function before_action($action) {} 
    function before_rendering() {}
    function before_invocation() {}
}