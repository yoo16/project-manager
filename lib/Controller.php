<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
 */

require_once "PwSetting.php";

PwSetting::load();
Controller::loadLib();
ApplicationLoader::autoloadModel();
PwSetting::loadApplication();

class Controller extends RuntimeException {
    static $routes = ['controller', 'action', 'id'];

    public $pw_sid;
    public $lang = 'ja';
    public $name;
    public $with_layout = true;
    public $pw_layout = null;
    public $session_name = '';
    public $headers = [];
    public $performed_render = false;
    public $relative_base = '';
    public $pw_project_name = '';
    public $pw_controller = '';
    public $pw_action = '';
    public $pw_method = '';
    public $session_request_columns;
    public $csv_options;
    public $escape_auth_actions = array('login', 'logout', 'auth');
    public $auth_controller = '';
    public $auth_model = '';
    public $auth_top_controller = '';
    public $is_pw_auth = false;

    static $libs = [
        'Helper',
        'DB',
        'SendMail',
        'CsvLite',
        'DataMigration',
        'DateManager',
        'FileManager',
        'FtpLite',
        'FormHelper',
        'TagHelper',
        'DateHelper',
        'AppSession',
        'ApplicationLocalize',
        'ApplicationLoader',
        'ApplicationLogger',
        'BenchmarkTimer',
        'ErrorMessage',
        ];

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
     * @return void
     */
    static function loadLib() {
        foreach (Controller::$libs as $lib) {
            $path = BASE_DIR."lib/{$lib}.php";
            if (file_exists($path)) @include_once $path;
        }
    }

    /**
     * load
     *
     * @param  string $name
     * @return Controller
     */
    static function load($name) {
        $controller_path = Controller::controllerPath($name);
        if (file_exists($controller_path)) {
            if ($result = @include_once $controller_path) {
                $controller = Controller::className($name);
                return new $controller();
            } else if ($name != 'layouts'&& preg_match('/^[a-z][a-z0-9_]*$/', $name) && is_dir(BASE_DIR."app/views/{$name}")) {
                return new Controller($name);
            }
        }
        return;
    }

    /**
     * controller class name
     *
     * @param  string $name
     * @return string
     */
    static function className($name) {
        $controller = str_replace(" ", "", ucwords(str_replace("_", " ", $name))) . "Controller";
        return $controller;
    }

    /**
     * controller file path
     *
     * @param  string $name
     * @return string
     */
    static function controllerPath($name) {
        $controller = Controller::className($name);
        $path = BASE_DIR."app/controllers/{$controller}.php";
        return $path;
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
                    $column = Controller::$routes[$key];
                    if ($column && $path) $params[$column] = $path;
                }
            }
        } 
        if (isset($_REQUEST['id'])) $params['id'] = $_REQUEST['id'];
        return $params;
    }

    /**
     * bind pw request params
     *
     * @param array $params
     * @return void
     */
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
        //TODO
        if (empty($params['controller'])) $params = Controller::queryString();
        if (empty($params['controller'])) $params['controller'] = ROOT_CONTROLLER_NAME;

        $controller = Controller::load($params['controller']);
        if ($controller) {
            try {
                session_start();
                $lang = '';
                $is_change_lang = false;

                if (isset($_REQUEST['lang'])) $lang = $_REQUEST['lang'];
                if (isset($_REQUEST['is_change_lang'])) $is_change_lang = $_REQUEST['is_change_lang'];
                
                if ($lang && $is_change_lang) AppSession::set('lang', $lang);
                $controller->lang = ApplicationLocalize::load($lang = $lang);
                $controller->loadDefaultCsvOptions($lang, $is_change_lang);
                $controller->run($params);
            } catch (Throwable $t) {
                $errors = Controller::throwErrors($t);
                $controller->renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            // } finally {

            }
        } else {
            //TODO try catch
            $errors['type'] = '404 Not Found';
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['controller'] = $params['controller'];
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];

            $controller->renderError($errors);
        }
    }

    /**
     * run
     * 
     * @param  array  $params
     * @return void
     */
    private function run($params = array()) {
        $GLOBALS['controller'] = $this;

        if ($_GET) $this->pw_params = $_GET;
        if (isset($params['controller'])) $this->pw_params['controller'] = $params['controller'];
        if (isset($params['action'])) $this->pw_params['action'] = $params['action'];
        if (isset($params['id'])) $this->pw_params['id'] = $params['id'];
        if (defined('IS_USE_PW_SID') && IS_USE_PW_SID && isset($_REQUEST['pw_sid'])) $this->pw_sid = $_REQUEST['pw_sid'];

        $this->loadPosts();
        $this->errors = $this->getErrors();
        $this->flushErrors();

        try {
            $this->_invoke();
        } catch (Throwable $t) {
            $errors = Controller::throwErrors($t);
            $this->renderError($errors);
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
     * @return string
     */
    private function pwLayout() {
        if ($this->with_layout) {
            if (is_string($this->layout)) {
                $this->pw_layout = $this->layout;
            } elseif ($this->layout) {
                $this->pw_layout = $this->name;
            }
        }
        return $this->pw_layout;
    }

    /**
     * pw template
     *
     * @param string $action
     * @return void
     */
    private function pwTemplate($action) {
        if (substr($action, 0, 1) === '/') {
            $template = "views{$action}.phtml";
        } else {
            $template = "views/{$this->name}/{$action}.phtml";
        }
        $this->pw_template = $template;
        return $template;
    }

    /**
     * load pw template
     *
     * @return void
     */
    private function loadPwTemplate() {
        $template = BASE_DIR."app/{$this->pw_template}";

        if (file_exists($template)) {
            ob_start();
            include $template;
            $this->content_for_layout = ob_get_contents();
            ob_end_clean();
        }
    }

    /**
     * load pw headers
     * 
     * @return void
     */
    private function loadPwHeaders() {
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
     * @return void
     */
    public function render($action) {
        if ($this->performed_render) return;
        $this->before_rendering($action, $this->with_layout);
        $template = $this->pwTemplate($action);

        @include_once BASE_DIR."app/helpers/application_helper.php";

        if ($this->with_layout) {
            $layout = $this->pwLayout();
            $layout_file = BASE_DIR."app/views/layouts/{$layout}.phtml";
        }
        $this->loadPwHeaders();
        if ($this->with_layout && file_exists($layout_file)) {
            try {
                $this->loadPwTemplate();
            } catch (Throwable $t) {
                $errors = Controller::throwErrors($t);
                $this->renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            // } finally {
                
            }
            include $layout_file;
        } else {
            $this->loadPwTemplate();
            echo($this->content_for_layout);
        }
        $this->performed_render = true;
    }

    /**
     * throwErrors
     *
     * @param  Throwable $t
     * @return array
     */
    static function throwErrors($t) {
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
    function renderError($errors, $is_continue = true) {
        if (!isset($GLOBALS['controller'])) {
            $GLOBALS['controller']['relative_base'] = Controller::relativeBaseURLForStatic();
        }
        if (!$errors) return;
        $error_layout = BASE_DIR."app/views/layouts/error.phtml";
        if (file_exists($error_layout)) {
            include $error_layout;
        }
        $error_template = BASE_DIR."app/views/components/php_error.phtml";
        if (file_exists($error_template)) {
            ob_start();
            include $error_template;
            $content_for_layout = ob_get_contents();
            ob_end_clean();
            echo($content_for_layout);
        }
        if (!$is_continue) exit;
    }

    /**
     * render contents
     *
     * @param  string $text
     * @return void
     */
    public function renderText($text) {
        if ($this->performed_render) return;

        $length = strlen($text);
        header("Content-Length: {$length}");
        header("Content-Type: " . $this->contentType());
        echo $text;
        $this->performed_render = true;
    }

    /**
     * render contents
     *
     * @param  string $contents
     * @param  string $content_type
     * @return void
     */
    public function renderContents($contents, $content_type = null) {
        if ($this->performed_render) return;
        if (is_null($content_type)) $content_type = $this->contentType();
        $length = strlen($contents);
        header("Content-Disposition: inline");
        header("Content-Length: {$length}");
        header("Content-Type: {$content_type}");
        echo $contents;
        $this->performed_render = true;
    }

    /**
     * render file
     *
     * @param  string $file
     * @param  string $content_type
     * @return void
     */
    public function renderFile($file, $content_type) {
        if ($this->performed_render) return;
        if (file_exists($file)) {
            $length = filesize($file);
            header("Content-Disposition: inline");
            header("Content-Length: {$length}");
            header("Content-Type: {$content_type}");
            readfile($file);
            $this->performed_render = true;
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
    public function downloadFile($file, $file_name = null, $content_type = "application/octet-stream") {
        if ($this->performed_render) return;
        if (file_exists($file)) {
            if (is_null($file_name)) $file_name = basename($file);
            $length = filesize($file);

            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident')) {
                $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
            }
            header("Content-Length: {$length}");
            header("Content-Disposition: Attachment; filename=\"{$file_name}\""); 
            header("Content-type: {$content_type}; name=\"{$file_name}\"");
            $this->performed_render = true;
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
    public function downloadContents($contents, $file_name, $content_type = "application/octet-stream") {
        if ($this->performed_render) return;

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

        $this->performed_render = true;
    }

    //TODO remove function
    /**
     * redirect
     *
     * TODO: remove
     * 
     * @param  string $controller_action
     * @param  string $id
     * @return void
     */
    function redirect_to($controller_action, $id = null) {
        // $this->performed_render = true;

        // if (strpos($params, '://')) {
        //     $url = $params;
        // } else {
        //     $url = $this->url_for($params, $options);
        //     if (!strpos($url, '://')) $url = $this->base . $url;
        //     if (SID) $url .= ((strpos($url, '?')) ? '&' : '?' ) . SID;
        // }
        // if (defined('DEBUG') && DEBUG) error_log("<REDIRECT> {$url}");
        // header("Location: {$url}");
        // exit;
        $this->redirectTo($controller_action, $id);
    }


    /**
     * redirect
     * 
     * @param  string $controller
     * @param  mix $id
     * @param  array $params
     * @return void
     */
    function redirectTo($action, $id = null, $params = null) {
        if (strpos($action, '/') > 0) {
            $actions = explode('/', $action);
            $controller = $actions[0];
            $action = $actions[1];
        }
        if (is_array($id)) {
            $params = $id;
            $id = null;
        }
        if (defined('IS_USE_PW_SID') && IS_USE_PW_SID) {
            if ($this->pw_sid) $params['pw_sid'] = $this->pw_sid;
        }
        if ($controller) {
            $url = $this->urlFor($controller, $action, $id, $params);
        } else {
            $params['id'] = $id;
            $url = $this->urlActionFor($action, $params);
        }
        if (defined('DEBUG') && DEBUG) error_log("<REDIRECT> {$url}");
        header("Location: {$url}");
        exit;
    }

     //TODO: remove function
    /**
     * url for params
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
     * link tag for controller action id
     *
     * @param  string $controller
     * @param  string $action
     * @param  string $id
     * @param  array $params
     * @return string
     */
    function linkTag($controller, $action = null, $id = null, $params = null) {
        if ($params['is_use_selected']) $params['class'].= FormHelper::linkActive($controller, $this->name);
        $href = $this->urlFor($controller, $action, $id, $params['http_params']);
        if (!$params || is_array($params)) $params['href'] = $href;
        $tag = TagHelper::aTag($params);
        return $tag;
    }

    /**
     * link tag for action id
     *
     * @param  string $controller
     * @param  string $action
     * @param  string $id
     * @param  array $params
     * @return string
     */
    function linkActionTag($action = null, $id = null, $params = null) {
        $controller = $this->pw_controller;
        if ($params['is_use_selected']) $params['class'].= FormHelper::linkActive($controller, $this->name);
        $params['href'] = $this->urlFor($controller, $action, $id, $params['http_params']);

        $tag = TagHelper::aTag($params);
        return $tag;
    }

    /**
     * link tag for controller
     *
     * @param  string $controller
     * @param  string $action
     * @param  string $id
     * @param  array $params
     * @return string
     */
    function linkControllerTag($controller, $params = null) {
        if ($params['is_use_selected']) {
            if ($this->menu_name) {
                $params['class'].= FormHelper::linkActive($controller, $this->menu_name);
            }
            $params['class'].= FormHelper::linkActive($controller, $this->name);
        }
        $params['href'] = $this->urlFor($controller, null, null, $params['http_params']);

        $tag = TagHelper::aTag($params);
        return $tag;
    }

    /**
     * url for params
     *
     * @param  string $controller
     * @param  string $action
     * @param  string $id
     * @param  array $http_params
     * @param  integer $pw_sid
     * @return string
     */
    static function url($controller, $action = null, $id = null, $http_params = null) {
        $controller_class = $GLOBALS['controller'];
        $url = $controller_class->base;
        if ($controller) $url.= "{$controller}/";
        if ($action) $url.= "{$action}/";
        if ($action && $id) $url.= "{$id}";
        if (defined('IS_USE_PW_SID') && IS_USE_PW_SID) {
            if ($controller_class->pw_sid > 0) $http_params['pw_sid'] = $controller_class->pw_sid;
        }
        if (isset($http_params) && is_array($http_params)) {
            $url_params = http_build_query($http_params);
            if ($url_params) $url = "{$url}?{$url_params}";
        }
        return $url;
    }

    /**
     * url for params
     *
     * @param  string $controller
     * @param  string $action
     * @param  string $id
     * @param  array $http_params
     * @return string
     */
    function urlFor($controller, $action = null, $id = null, $http_params = null) {
        $url = $this->base;
        if ($controller) $url.= "{$controller}/";
        if ($action) $url.= "{$action}/";
        if ($action && $id) $url.= "{$id}";
        if (defined('IS_USE_PW_SID') && IS_USE_PW_SID) {
            if ($this->pw_sid > 0) $http_params['pw_sid'] = $this->pw_sid;
        }
        if (isset($http_params) && is_array($http_params)) {
            $url_params = http_build_query($http_params);
            if ($url_params) $url = "{$url}?{$url_params}";
        }
        return $url;
    }

    /**
     * url for params
     *
     * @param  string $controller
     * @param  array $http_params
     * @return string
     */
    function urlControllerFor($controller, $http_params = null) {
        $url = $this->urlFor($controller, null, null, $http_params);
        return $url;
    }

    /**
     * url action for params
     *
     * @param  string $action
     * @param  array $http_params
     * @return string
     */
    function urlActionFor($action, $http_params = null) {
        $url = $this->urlFor($this->name, $action, null, $http_params);
        return $url;
    }

    /**
     * url self for params
     *
     * @param  string $action
     * @param  string $id
     * @param  array $http_params
     * @return string
     */
    function urlSelfFor($action = null, $id = null, $http_params = null) {
        $url = $this->base;
        $url.= "{$this->name}/";
        if ($action) $url.= "{$action}";
        if ($action && $id) $url.= "/{$id}";

        if (defined('IS_USE_PW_SID') && IS_USE_PW_SID) {
            if ($this->pw_id > 0) $http_params['pw_sid'] = $this->pw_sid;
        }
        if (isset($http_params) && is_array($http_params)) {
            $url_params = http_build_query($http_params);
            if ($url_params) $url = "{$url}?{$url_params}";
        }
        return $url;
    }

    /**
     * content type
     * 
     * @param  string $encofing
     * @return string
     */
    private function contentType($encoding = null) {
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
    private function pwProjectName() {
        $path = str_replace('public', '', $_SERVER['PHP_SELF']);
        $path = str_replace('dispatch.php', '', $path);
        $this->pw_project_name = str_replace('/', '', $path);
        return $this->pw_project_name;
    }

    /**
     * relative base url for QUERY_STRING
     *
     * @return string
     */
    private function relativeBaseURL() {
        $query = str_replace('?', '', $_SERVER['QUERY_STRING']);
        $count = substr_count($query, '/');
        for ($i = 0; $i < $count; $i++) {
            $this->relative_base .= '../';
        }
        return $this->relative_base;
    }

    /**
     * relative base url for QUERY_STRING
     *
     * @return string
     */
    static function relativeBaseURLForStatic() {
        $relative_base = '';
        $query = str_replace('?', '', $_SERVER['QUERY_STRING']);
        $count = substr_count($query, '/');
        for ($i = 0; $i < $count; $i++) {
            $relative_base .= '../';
        }
        return $relative_base;
    }

    /**
     * base url for HTTPS & HTTP_HOST
     *
     * @return string
     */
    public function baseUrl() {
        $this->pwProjectName();
        $this->http_host = $_SERVER['HTTP_HOST'];
        $this->pw_http_protocol = $_SERVER['REQUEST_SCHEME'];

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
        if ($this->pw_project_name) $this->base .= $this->pw_project_name.'/';
        return $this->base;
    }

    /**
     * pw controller
     *
     * @return string
     */
    private function pwController() {
        $this->pw_controller = $this->pw_params['controller'];
        return $this->pw_controller;
    }

    /**
     * pw action
     *
     * @return string
     */
    private function pwAction() {
        $this->pw_action = $this->pw_params['action'];
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
    private function pwMethod($action) {
        if (!$action) return;
        $method = '';
        if (method_exists($this, $action)) {
            $method = $action;
        } else if (method_exists($this, "action_{$action}")) {
            $method = "action_{$action}";
        } else if (method_exists(new Controller(), $action)) {
            $method = $action;
        } else if (method_exists(new Controller(), "action_{$action}")) {
            $method = "action_{$action}";
        }
        $this->pw_method = $method;
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

        $this->pwController();
        $this->pwAction();
        $this->pwProjectName();
        $this->class_name = get_class($this);

        $method = $this->pwMethod($this->pw_action);
        if (!empty($method)) {
            $this->before_action($this->pw_action);
            if ($this->performed_render) return;
            try {
                $this->$method();
            } catch (Throwable $t) {
                $errors = Controller::throwErrors($t);
                $this->renderError($errors);
            } catch (Error $e) {
                var_dump($e);
            } catch (Exception $e) {
                var_dump($e);
            }

            $this->before_invocation($this->pw_action);
            if (defined('DEBUG') && DEBUG) {
                error_log("<INVOKED> {$this->pw_controller}/{$this->pw_action}");
            }
            $this->render($this->pw_action);
        } else {
            $errors['type'] = '404 Not Found';
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['controller'] = $this->name;
            $errors['action'] = $this->pw_action;
            $errors['params'] = $this->pw_params;
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $this->renderError($errors);
        }
    }

    /**
     * load $_POST
     *
     * @return void
     */
    function loadPosts() {
        if ($_POST) AppSession::set('pw_posts', $_POST);
        $this->pw_posts = AppSession::get('pw_posts');
    }

    /**
     * clear $_POST
     *
     * @return void
     */
    function clearPwPosts() {
        AppSession::clear('pw_posts');
    }

    /**
     * clear $_POST
     *
     * @return void
     */
    function clearPosts() {
        $this->clearSessions('pw_posts');
    }

    /**
     * add session errors
     *
     * @param  array $errors
     * @return void
     */
    function addError($key, $values) {
        $errors = AppSession::getWithKey('errors', $this->name);
        $errors[$key] = $values;
        AppSession::setWithKey('errors', $this->name, $errors);
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

    /**
     * before action
     *
     * @param string $action
     * @return void
     */
    function before_action($action) {
        $this->loadRequestSession($this->pw_sid);
        if ($this->auth_controller) $this->checkAuth($action);
    } 

    function before_rendering() {}
    function before_invocation() {}

    /**
     * load Csv values
     * 
     * @return void
     */
    function loadDefaultCsvOptions($lang = null) {
        $this->csv_options = ApplicationLocalize::loadCsvOptions($lang);
    }

    /**
     * check action
     * 
     * @param string $redirect_action
     * @return void
     */
    function checkEdit($redirect_action = 'new') {
        if (!$this->pw_params['id']) {
            $this->redirect_to($redirect_action);
            exit;
        }
    }

    /**
     * check post
     * 
     * @return void
     */
    function checkPost() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

   /**
    * load Request Session
    *
    * @param
    * @return void
    */
    function loadRequestSession() {
        if ($this->session_request_columns) {
            foreach ($this->session_request_columns as $session_request_column) {
                $this->$session_request_column = AppSession::load($session_request_column);
            }
        }
    }

   /**
    * clear Request Session
    *
    * @param
    * @return void
    */
    function clearRequestSession() {
        if ($this->session_request_columns) {
            foreach ($this->session_request_columns as $session_request_column) {
                AppSession::clear($session_request_column);
            }
        }
    }

   /**
    * change boolean
    *
    * @param string $model_name
    * @param string $column
    * @param string $id_column
    * @return void
    */
    function changeBoolean($model_name, $column, $id_column = 'id') {
        if (isset($this->pw_params[$id_column])) {
            DB::model($model_name)->reverseBool($this->pw_params[$id_column], $column);
        }
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    public function action_update_sort() {
        if (!$this->is_pw_auth) return;
        if ($_POST['model_name']) {
            $this->updateSort($_POST['model_name']);
        }
    }

   /**
    * update sort order
    *
    * @param string $model_name
    * @param boolean $is_json
    * @return void
    */
    function updateSort($model_name = null, $is_json = true) {
        if (!$model_name) exit('Not found model_name');

        if ($_POST['sort_order']) $sort_order = $_POST['sort_order'];
        if (!$sort_order) exit('Not found sort_order');
        
        if (class_exists($model_name)) {
            DB::model($model_name)->updateSortOrder($sort_order);
            if ($is_json) {
                $results['is_success'] = true;
            }
        }
        if ($is_json) {
            $results = json_encode($results);
            echo($results);
            exit;
        }
    }
    
    /**
     * record value
     *
     * @param  string $csv_name
     * @param  string $key
     * @return string
     */
    function recordValue($csv_name, $key) {
        $value = $this->csv_options[$csv_name][$key];
        return $value;
    }

    /**
     * render json
     *
     * @param $array $values
     * @return void
     */
    function renderJson($values) {
        $json = json_encode($values);
        echo ($json);
        exit;
    }

    /**
     * check POST method
     *
     * @return boolean
     */
    function isRequestPost() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

    /**
     * check auth
     * 
     * @param  string $action
     * @return void
     */
    function checkAuth($action) {
        if (!$this->auth_controller) return;
        if (!in_array($action, $this->escape_auth_actions)) {
            $model = AppSession::getWithKey('pw_auth', $this->auth_controller);
            if ($model->value['id']) {
                $this->is_pw_auth = true;
                $this->auth_staff = $model;
            } else {
                if ($this->auth_controller) {
                    $uri = "{$this->auth_controller}/login";
                } else {
                    $uri = 'login';
                }
                $this->redirectTo($uri);
                return;
            }
        }
    }

    /**
     * auth
     *
     * @return void
     */
    function auth() {
        if (!$this->auth_controller) return;
        if (!$this->auth_model) return;
        if (class_exists($this->auth_model)) {
            $model = DB::model($this->auth_model)->auth();
            if ($model->value) {
                $this->redirectAuthTop();
            } else {
                $this->redirectAuthLogin();
            }
        }
        exit;
    }

   /**
    * login
    *
    * @param
    * @return void
    */ 
    function action_login() {
        $this->layout = 'login';
    }

   /**
    * logout
    *
    * @param
    * @return void
    */ 
    function action_logout() {
        AppSession::flush();
        $uri = "{$this->auth_controller}/login";
        $this->redirectTo($uri);
        exit;
    }

    /**
     * redirectAuthTop
     *
     * @return void
     */
    function redirectAuthLogin() {
        if ($this->auth_controller) {
            $uri = "{$this->auth_controller}/login";
        } else {
            $uri = 'login';
        }
        $this->redirectTo($uri);
        exit;
    }

    /**
     * redirectAuthTop
     *
     * @return void
     */
    function redirectAuthTop() {
        $uri = "{$this->auth_top_controller}/";
        $this->redirectTo($uri);
        exit;
    }

}