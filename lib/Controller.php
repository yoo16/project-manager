<?php
/**
 * Controller 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

require_once "PwSetting.php";

PwSetting::load();
Controller::loadLib();
PwLoader::autoloadModel();
PwSetting::loadApplication();

class Controller extends RuntimeException {
    static $routes = ['controller', 'action', 'id'];

    public $lang = 'ja';
    public $name;
    public $with_layout = true;
    public $pw_layout = null;
    public $headers = [];
    public $performed_render = false;
    public $relative_base = '';
    public $pw_project_name = '';
    public $pw_controller = '';
    public $pw_action = '';
    public $pw_method = '';
    public $session_request_columns;
    public $csv_options;
    public $auth_controller = '';
    public $auth_model = '';
    public $auth_top_controller = '';
    public $is_use_multi_sid = false;
    public $session_name = null;
    public $pw_admin_controller = 'pw_admin';
    public $pw_admin_escapes = ['login', 'auth'];
    public $pw_login_escapes = ['login', 'auth'];
    public $pw_login_controller = 'login';

    static $libs = [
        'PwHelper',
        'DB',
        'PwMail',
        'PwCsv',
        'PwMigration',
        'PwDate',
        'PwFile',
        'PwFtp',
        'PwForm',
        'PwTag',
        'PwDate',
        'PwSession',
        'PwLocalize',
        'PwLoader',
        'PwLogger',
        'PwTimer',
        'PwError',
        'PwColor',
        ];

    function __construct($name = null) {
        $class_name = strtolower(get_class($this));
        if ($class_name !== 'controller') {
            if (!isset($this->name)) $this->name = substr($class_name, 0, strpos($class_name, 'controller'));
        } else if(isset($name)) {
            $this->name = $name;
        }
        if (isset($_REQUEST['pw_multi_sid'])) {
            $this->pw_multi_sid = $_REQUEST['pw_multi_sid'];
            $this->session_name = $_REQUEST['pw_multi_sid'];
        }
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
                
                if ($lang && $is_change_lang) PwSession::setWithKey('app', 'lang', $lang);
                $controller->lang = PwLocalize::load($lang);
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

            if ($controller) $controller->renderError($errors);
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
        $this->pw_gets = $this->pw_params;

        $this->loadPwPosts();
        $this->errors = $this->getErrors();
        $this->flushErrors();
        //$this->loadDefaultCsvOptions($this->lang, true);
        $this->csv_options = PwLocalize::loadCsvOptions($this->lang);

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
    private function pwTemplate($action, $template = null) {
        if (!$template) {
            if ($this->view_dir) {
                $template = "views/{$this->view_dir}/{$action}.phtml";
            } else if (substr($action, 0, 1) === '/') {
                $template = "views{$action}.phtml";
            } else {
                $template = "views/{$this->name}/{$action}.phtml";
            }
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
    public function render($action, $template = null) {
        if ($this->performed_render) return;
        $this->before_rendering($action, $this->with_layout);
        $template = $this->pwTemplate($action, $template);

        @include_once BASE_DIR."app/PwHelpers/application_PwHelper.php";

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
        $error_template = BASE_DIR."app/views/components/lib/php_error.phtml";
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
     * @param  string $file_name
     * @param  string $contents
     * @return void
     */
    public function downloadContents($file_name, $contents) {
        if (PwFile::isIE()) $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
        header("Content-type: application/octet-stream; name=\"{$file_name}\"");
        header("Content-Disposition: Attachment; filename=\"{$file_name}\""); 
        header('Pragma: private');
        header('Cache-control: private, must-revalidate');
        exit;
    }

    /**
     * redirect
     * 
     * @param  array $params
     * @param  array $url_params
     * @return void
     */
    function redirectTo($params = null, $url_params = null) {
        if ($this->pw_multi_sid) $url_params['pw_multi_sid'] = $this->pw_multi_sid;
        if (!$params['controller']) $params['controller'] = $this->name;
        $url = $this->urlFor($params, $url_params);
        header("Location: {$url}");
        exit;
    }

    /**
     * link tag for pw-click
     *
     * @param  array $params
     * @return string
     */
    function linkJs($params = null) {
        if ($params['is_use_selected'] && $params['controller']) $params['class'].= PwForm::linkActive($params['controller'], $this->name);
        unset($params['is_use_selected']);

        //$params['href'] = '#';
        //if ($params['id']) $params['href'].= $params['id'];

        $tag = PwTag::a($params);
        return $tag;
    }

    /**
     * file upload template
     *
     * @param  array $params
     * @return string
     */
    function uploadFileJs($params) {
        include('views/components/lib/file_upload.phtml');

        $tag = $this->linkJs($params);
        return $tag;
    }

    /**
     * link tag for controller action id
     *
     * @param  array $params
     * @param  array $html_params
     * @return string
     */
    function linkTo($params, $html_params = null) {
        if ($this->checkLinkActive($params, $html_params)) $html_params['class'].= ' active';
        $html_params['href'] = $this->urlFor($params, $html_params['http_params']);
        $tag = PwTag::a($html_params);
        return $tag;
    }

    /**
     * check link active
     *
     * @param  array $params
     * @param  array $html_params
     * @return string
     */
    function checkLinkActive($params, $html_params) {
        if (!$html_params['is_use_selected']) return;
        if (($params['controller'] == $this->name)) return true;  

        if ($html_params['menu_group'] && $this->menu_group) {
            return ($html_params['menu_group'] == $this->menu_group);
        }

        if ($html_params['menu_groups'] && $this->menu_groups) {
            return (in_array($html_params['menu_groups'], $this->menu_groups));
        }
    }

    /**
     * url for params
     *
     * @param  array $params
     * @param  array $http_params
     * @param  string $base_url
     * @return string
     */
    static function url($params, $http_params = null, $base_url = null) {
        $url = ($base_url) ? $base_url : $GLOBALS['controller']->base;
        if (is_string($params)) {
            $url.= $params;
        } else {
            if ($params['controller']) $elements[] = $params['controller'];
            if ($params['action']) $elements[] = $params['action'];
            if ($params['id']) $elements[] = $params['id'];
            if (count($elements) == 1) {
                $url.= "{$elements[0]}/";
            } else {
                $url.= implode('/', $elements);
            }
        }
        if (isset($http_params) && is_array($http_params)) {
            $http_param = http_build_query($http_params);
            if ($http_param) $url = "{$url}?{$http_param}";
        }
        return $url;
    }

    /**
     * url for params
     *
     * @param  array $params
     * @param  array $http_params
     * @return string
     */
    function urlFor($params, $http_params = null) {
        if (is_array($params) && !$params['controller']) $params['controller'] = $this->name;
        $url = self::url($params, $http_params, $this->base);
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
            error_log("REQUEST: {$_SERVER['REQUEST_URI']}");
            error_log("USER_AGENT: {$_SERVER['HTTP_USER_AGENT']}");
            error_log("IP: {$_SERVER['REMOTE_ADDR']}");
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
    function loadPwPosts() {
        if ($_POST) PwSession::set('pw_posts', $_POST);
        $this->pw_posts = PwSession::get('pw_posts');
    }


    /**
     * clear $_POST
     *
     * @return void
     */
    function clearPwPosts() {
        PwSession::clear('pw_posts');
        unset($this->pw_posts);
    }

    /**
     * model
     *
     * @param string $model_name
     * @param string $session_name
     * @return void
     */
    function model($model_name, $session_name = null) {
        if (!class_exists($model_name)) {
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['error'] = "{$model_name} Class is not exists.";
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $this->renderError($errors);
            exit;
        }
        return DB::model($model_name)->requestSession($this->pw_multi_sid, $session_name);
    }

    /**
     * clear model
     *
     * @param string $model_name
     * @param string $session_name
     * @return void
     */
    function clearModel($model_name, $session_name = null) {
        if (!class_exists($model_name)) {
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['error'] = "{$model_name} Class is not exists.";
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $this->renderError($errors);
            exit;
        }
        return DB::model($model_name)->clearSession($this->pw_multi_sid);
    }

    /**
     * load session values
     *
     * @param string $model_name
     * @return void
     */
    function modelForSession($model_name, $conditions = null) {
        $instance = DB::model($model_name);
        $instance->values = PwSession::getWithKey('app', $instance->entity_name);
        if (!$instance->values) {
            $instance->idIndex()->wheres($conditions)->all();
            PwSession::setWithKey('app', $instance->entity_name, $instance->values);
        }
        return $instance;
    }

    /**
     * load session values
     *
     * @param string $model_name
     * @return void
     */
    function clearModelForSession($model_name) {
        $instance = DB::model($model_name);
        PwSession::clearWithKey('app', $instance->entity_name);
    }

    /**
     * add session errors for model
     *
     * @param  array $errors
     * @return void
     */
    function addErrorByModel($model) {
        if ($key = $model->name) {
            $errors = PwSession::getWithKey('errors', $this->name);
            $errors[$key] = $model->errors;
            PwSession::setWithKey('errors', $this->name, $errors);
        }
    }

    /**
     * add session errors
     *
     * @param  array $errors
     * @return void
     */
    function addError($key, $values) {
        $errors = PwSession::getWithKey('errors', $this->name);
        $errors[$key] = $values;
        PwSession::setWithKey('errors', $this->name, $errors);
    }

    /**
     * set session errors
     *
     * @param  array $errors
     * @return void
     */
    function setErrors($errors) {
        PwSession::setWithKey('errors', $this->name, $errors);
    }

    /**
     * get session errors
     *
     * @return array
     */
    function getErrors() {
        return PwSession::getWithKey('errors', $this->name);
    }

    /**
     * get session errors
     *
     * @return array
     */
    function flushErrors() {
        return PwSession::clearWithKey('errors', $this->name);
    }

    /**
     * get sessions by key
     *
     * @param  string $key
     * @return string
     */
    function getSessions($key) {
        if (!$this->session_name) return;
        return PwSession::getWithKey($this->session_name, $key); 
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
        PwSession::setWithKey($this->session_name, $key, $values); 
    }

    /**
     * clear sessions by key
     *
     * @param  string $key
     * @return void
     */
    function clearSessions($key) {
        if (!$this->session_name) return;
        PwSession::clearWithKey($this->session_name, $key); 
    }

    /**
     * session terminate
     *
     * @return void
     */
    function flushSessions() {
        if (!$this->session_name) return;
        PwSession::flushWithKey($this->session_name);
    }

    /**
     * before action
     *
     * @param string $action
     * @return void
     */
    function before_action($action) {
        $this->loadrequestSession();
        if ($this->name == $this->pw_admin_controller) $this->checkPwAdmin($action);
    } 

    function before_rendering() {}
    function before_invocation() {}

    /**
     * load Csv values
     * 
     * @return void
     */
    function loadDefaultCsvOptions($lang = null) {
        $this->csv_options = PwLocalize::loadCsvOptions($lang);
    }

    /**
     * check action
     * 
     * @param string $redirect_action
     * @return void
     */
    function checkEdit($redirect_action = 'new') {
        if (!$this->pw_params['id']) {
            $this->redirectTo(['controller' => $this->name, 'action' => $redirect_action]);
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
                if ($this->name) {
                    $this->session_name = "{$this->name}_controller";
                    $this->$session_request_column = PwSession::loadWithKey($this->session_name, $session_request_column, null, $this->pw_multi_sid);
                } else {
                    $this->$session_request_column = PwSession::load($session_request_column, null, $this->pw_multi_sid);
                }
                $this->pw_session_params[$session_request_column] = $this->$session_request_column;
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
                if ($this->name) {
                    $this->session_name = "{$this->name}_controller";
                    PwSession::clearWithKey($this->session_name, $session_request_column, $this->pw_multi_sid);
                } else {
                    PwSession::clear($session_request_column, $this->pw_multi_sid);
                }
                unset($this->$session_request_column);
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
        if (!$this->pw_auth->value['id']) return;
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

        $posts = file_get_contents("php://input");
        dump($_REQUEST);
        dump($posts);
        if (!$posts) exit;

        $values = json_decode($posts, true);
        if (!$values) exit('Not found sort_order');
        
        dump($values);
        if (class_exists($model_name)) {
            DB::model($model_name)->updateSortOrder($values);
            if ($is_json) $results['is_success'] = true;
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
     * csv values
     *
     * @param  string $csv_name
     * @param  string $key
     * @return string
     */
    static function csvValues($csv_name, $lang = 'ja') {
        $csv_list = PwLocalize::loadCsvOptions($lang);
        $values = $csv_list[$csv_name];
        return $values;
    }

    /**
     * csv value
     *
     * @param  string $csv_name
     * @param  string $key
     * @return string
     */
    static function csvValue($csv_name, $key, $lang = 'ja') {
        $csv_list = PwLocalize::loadCsvOptions($lang);
        $value = $csv_list[$csv_name][$key];
        return $value;
    }

    /**
     * render json
     *
     * @param array $values
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
     * public action login
     *
     * @return void
     */
    function login() {
        $this->redirectLogin();
        exit;
    }

    /**
     * check auth
     * 
     * @param  string $action
     * @return void
     */
    function checkAuth($action) {
        if (in_array($action, $this->pw_login_escapes)) return;
        if (!$this->auth_model) exit('Not setting auth_model');
        if (!class_exists($this->auth_model)) exit('Not found auth_model');
        $this->pw_auth = PwSession::getWithKey('pw_auth', DB::model($this->auth_model)->entity_name);

        if (!$this->pw_auth->value['id']) {
            $this->redirectTo(['controller' => 'login']);
            exit;
        }
    }

    /**
     * check pw admin
     * 
     * @param  string $action
     * @return void
     */
    function checkPwAdmin($action) {
        if (in_array($action, $this->pw_admin_escapes)) {
            return;
        }
        $this->pw_admin = PwSession::get('pw_admin');
        if (!$this->pw_admin) {
            $uri = "{$this->pw_admin_controller}/login";
            $this->redirectTo($uri);
            exit;
        }
    }

    /**
     * pwlogin
     *
     * @return void
     */
    function pwLogin() {
        $this->layout = 'login';
        $template = 'views/components/auth/login.phtml';
        $this->render('login', $template);
    }

    /**
     * auth
     *
     * @return PwPgsql
     */
    function pwAuth() {
        if (!$this->auth_controller) return;
        if (!$this->auth_model) return;
        if (class_exists($this->auth_model)) {
            $model = DB::model($this->auth_model)->auth();
            return $model;
        }
        exit;
    }

    /**
     * logout
     *
     * @param string $uri
     * @return void
     */
    function pwLogout($uri = null)
    {
        PwSession::flush();
        $params['lang'] = $this->lang;
        $params['is_change_lang'] = 1;
        $this->redirectTo($uri, null, $params);
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

    /**
     * redirect login top
     *
     * @return void
     */
    function redirectLogin($uri = null) {
        if (!$uri) $uri = $this->pw_login_controller.'/';
        $this->redirectTo($uri);
        exit;
    }

    /**
     * url By Params
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    function urlByParams($controller, $action = null, $params = null)
    {
        $url = "{$this->base}{$controller}/";
        if ($action) $url.= $action;
        if ($params) {
            $param = http_build_query($params);
            $url .= "?{$param}";
        }
        return $url;
    }

    /**
     * url for href
     *
     * @param string $href
     * @return void
     */
    function urlForNotParam($href)
    {
        $urls = explode('?', $href);
        $urls = explode('#', $urls[0]);
        $url = $urls[0];
        return $url;
    }

    /**
     * js controller name
     *
     * @return string
     */
    function jsControllerName()
    {
        if ($this->js_controller) {
            return $this->js_controller;
        } else {
            return $this->pw_controller;
        }
    }

    /**
     * memory flow
     *
     * @return void
     */
    static function isMemoryFlow() {
        $memory_peak = memory_get_peak_usage();
        $memory_mb = round($memory_peak / (1024 * 1024));
        if ($memory_mb > APP_MEMORY_LIMIT) {
            $msg = "memory peak : {$memory_mb}MB";
            return true;
        }
    }

    /**
     * images dirctory path
     *
     * @return string
     */
    function imageDir($dir = 'images') {
        return $this->base."{$dir}/";
    }

    /**
     * images dirctory path
     *
     * @return string
     */
    function image($file_name, $dir = 'images') {
        if (!$dir) $dir = 'images';
        $url = $this->imageDir($dir);
        $url = "{$url}{$file_name}";
        return $url;
    }

    /**
     * images dirctory path
     *
     * @param string $dir
     * @return string
     */
    static function imageBaseUrl($dir = 'images') {
        $controller = $GLOBALS['controller'];
        return $controller->base."{$dir}/";
    }

    /**
     * images dirctory path
     *
     * @return void
     */
    static function imageUrl($file_name, $dir = 'images') {
        if (!$dir) $dir = 'images';
        $url = self::imageBaseUrl($dir);
        $url = "{$url}{$file_name}";
        return $url;
    }

}