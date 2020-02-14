<?php

/**
 * Controller 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

require_once "PwSetting.php";

class Controller extends RuntimeException
{
    static $routes = ['controller', 'action', 'id'];

    public $lang = 'ja';
    public $name = 'root';
    public $with_layout = true;
    public $pw_layout = null;
    public $pw_layout_file = null;
    public $headers = [];
    public $performed_render = false;
    public $pw_relative_base = '';
    public $pw_project_name = '';
    public $pw_controller = '';
    public $pw_action = '';
    public $pw_id = '';
    public $pw_method = '';
    public $session_request_columns;
    public $csv_sessions;
    public $auth_controller = '';
    public $auth_model = '';
    public $auth_top_controller = '';
    public $is_use_multi_sid = false;
    public $session_name = null;
    public $pw_admin_controller = 'pw_admin';
    public $pw_admin_escapes = ['login', 'auth'];
    public $pw_login_escapes = ['login', 'auth'];
    public $pw_login_controller = 'login';
    public $is_force_get_update = false;
    public $session_by_models = null;
    public $group_name;
    public $pw_multi_sid = null;
    public $layout = '';
    public $view_dir = '';
    public $pw_template = '';
    public $pw_template_path = '';

    static $libs = [
        'PwHelper',
        'DB',
        'PwMail',
        'PwCsv',
        'PwMigration',
        'PwDate',
        'PwAuth',
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
        'PwPython',
        'PwModel',
        'PwLaravel',
    ];

    function __construct($name = null)
    {
        if (!$this->name) {
            $my_class_name = $this->myClassName();
            if ($my_class_name !== 'Controller') {
                $this->name = substr($my_class_name, 0, strpos($my_class_name, 'controller'));
            }
        }
        if (isset($name)) $this->name = $name;
        $this->session_name = "{$this->name}_controller";
        if ($this->is_use_multi_sid && isset($_REQUEST['pw_multi_sid'])) $this->pw_multi_sid = $_REQUEST['pw_multi_sid'];
    }

    // function __isset($name) {
    //     var_dump($name);
    // }

    /**
     * invoke
     * 
     * @return void
     */
    private function _invoke()
    {
        if (defined('DEBUG') && DEBUG) {
            error_log("REQUEST: {$_SERVER['REQUEST_URI']}");
            error_log("USER_AGENT: {$_SERVER['HTTP_USER_AGENT']}");
            error_log("IP: {$_SERVER['REMOTE_ADDR']}");
        }

        //$this->pw_prev_request_uri = PwSession::get('pw_prev_request_uri', $this->pw_multi_sid);
        $this->base = $this->baseUrl();
        $this->relativeBaseURL();

        $this->pwController();
        $this->pwAction();
        $this->pwID();
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
                $errors = Controller::throwErrors($e);
                $this->renderError($errors);
            } catch (Exception $e) {
                $errors = Controller::throwErrors($e);
                $this->renderError($errors);
            }

            $this->before_invocation($this->pw_action);
            $this->render($this->pw_action);
        } else {
            $this->pwTemplate($this->pw_action);
            if ($this->hasTemplate()) {
                $this->render($this->pw_action);
            } else {
                $errors['type'] = '404 Not Found';
                $errors['query'] = $_SERVER['QUERY_STRING'];
                $errors['request'] = $_SERVER['REQUEST_URI'];
                $errors['controller'] = $this->name;
                $errors['action'] = $this->pw_action;
                $errors['request'] = $this->pw_request;
                $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
                $this->renderError($errors);
            }
        }
        $this->after_action();
    }

    function __destruct()
    { }

    public function __call($name, $args)
    {
        if (!method_exists($this, $name)) {
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
    static function loadLib()
    {
        foreach (Controller::$libs as $lib) {
            $path = BASE_DIR . "lib/{$lib}.php";
            if (file_exists($path)) @include_once $path;
        }
    }

    /**
     * load
     *
     * @param  string $name
     * @return Controller
     */
    static function load($name)
    {
        $controller_path = Controller::controllerPath($name);
        if (file_exists($controller_path)) {
            if (@include_once $controller_path) {
                $controller = Controller::className($name);
                if (class_exists($controller)) return new $controller();
            } else if ($name != 'layouts' && preg_match('/^[a-z][a-z0-9_]*$/', $name) && is_dir(BASE_DIR . "app/views/{$name}")) {
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
    static function className($name)
    {
        $controller = str_replace(" ", "", ucwords(str_replace("_", " ", $name))) . "Controller";
        return $controller;
    }

    /**
     * controller class file name
     *
     * @param  string $name
     * @return string
     */
    static function fileName($name, $ext = 'php')
    {
        $name = Controller::className($name);
        $file_name = "{$name}.{$ext}";
        return $file_name;
    }

    /**
     * controller name
     *
     * @return void
     */
    public function myClassName()
    {
        return get_class($this);
    }

    /**
     * controller file path
     *
     * @param  string $name
     * @return string
     */
    static function controllerPath($name)
    {
        $controller = Controller::className($name);
        $path = BASE_DIR . "app/controllers/{$controller}.php";
        return $path;
    }

    /**
     * query string
     * 
     * @param  string $query
     * @return string
     */
    static function queryString($query = null)
    {
        if (is_null($query)) {
            $request = $_SERVER['REQUEST_URI'];
            $query = $_SERVER['QUERY_STRING'];
        }
        $query_url = parse_url($query);
        $values = explode('&', $query_url['path']);
        $params = self::pathToParam($values[0]);
        if (isset($_REQUEST['id'])) $params['id'] = $_REQUEST['id'];
        return $params;
    }

    /**
     * bind pw request params
     *
     * @param array $params
     * @return array
     */
    static function bindPwRequestParams($params)
    {
        $request_url = parse_url($_SERVER['REQUEST_URI']);
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
    static function dispatch($params = array())
    {
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
                if (isset($_REQUEST['is_change_lang'])) {
                    $is_change_lang = $_REQUEST['is_change_lang'];
                    PwLocalize::loadCsvSessions($lang, true);
                }

                if ($lang && $is_change_lang) PwSession::setWithKey('app', 'lang', $lang);
                $controller->lang = PwLocalize::load($lang);
                $controller->run($params);
            } catch (Throwable $t) {
                $errors = Controller::throwErrors($t);
                $controller->renderError($errors);
            } catch (Error $e) {
                $errors = Controller::throwErrors($e);
                $controller->renderError($errors);
            } catch (Exception $e) {
                $errors = Controller::throwErrors($e);
                $controller->renderError($errors);
            }
        } else {
            //TODO try catch
            $errors['type'] = '404 Not Found';
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['controller'] = $params['controller'];
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];

            $controller = new Controller();
            $controller->renderError($errors);
        }
    }

    /**
     * run
     * 
     * @param  array  $params
     * @return void
     */
    private function run($params = [])
    {
        $GLOBALS['controller'] = $this;
        $this->pw_hostname = PwSetting::hostname();
        $this->loadPwGets($params);
        $this->loadPwPosts();
        $this->loadPwErrors();
        $this->loadDefaultCsvOptions($this->lang);

        try {
            $this->_invoke();
        } catch (Throwable $t) {
            $errors = Controller::throwErrors($t);
            $this->renderError($errors);
        } catch (Error $e) {
            $errors = Controller::throwErrors($e);
            $this->renderError($errors);
        } catch (Exception $e) {
            $errors = Controller::throwErrors($e);
            $this->renderError($errors);
        }
    }

    /**
     * pw layout
     * 
     * @return string
     */
    private function pwLayout()
    {
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
     * @param string $template
     * @return void
     */
    private function pwTemplate($action, $template = null)
    {
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
        $this->pw_template_path = BASE_DIR."app/{$template}";;
        return $template;
    }

    /**
     * has template file
     *
     * @return boolean
     */
    private function hasTemplate()
    {
        if (!$this->pw_template_path) return;
        return (file_exists($this->pw_template_path));
    }

    /**
     * load pw template
     *
     * @return void
     */
    private function loadPwTemplate()
    {
        $template = BASE_DIR . "app/{$this->pw_template}";
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
    private function loadPwHeaders()
    {
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
    public function render($action, $template = null)
    {
        if ($this->performed_render) return;
        $this->before_rendering($action, $this->with_layout);
        $template = $this->pwTemplate($action, $template);

        @include_once BASE_DIR . "app/helpers/application_helper.php";

        if ($this->with_layout) {
            $layout = $this->pwLayout();
            if ($layout) $this->pw_layout_file = BASE_DIR . "app/views/layouts/{$layout}.phtml";
        }
        $this->loadPwHeaders();
        if ($this->with_layout && file_exists($this->pw_layout_file)) {
            try {
                $this->loadPwTemplate();
            } catch (Throwable $t) {
                $errors = Controller::throwErrors($t);
                $this->renderError($errors);
            } catch (Error $e) {
                $errors = Controller::throwErrors($e);
                $this->renderError($errors);
            } catch (Exception $e) {
                $errors = Controller::throwErrors($e);
                $this->renderError($errors);
            }
            include $this->pw_layout_file;
        } else {
            $this->loadPwTemplate();
            echo ($this->content_for_layout);
        }
        $this->performed_render = true;
    }

    /**
     * throwErrors
     *
     * @param  Throwable $error
     * @return array
     */
    static function throwErrors($error)
    {
        $errors['code'] = $error->getCode();
        $errors['file'] = $error->getFile();
        $errors['line'] = $error->getLine();
        $errors['message'] = $error->getMessage();
        $errors['trace'] = nl2br($error->getTraceAsString());
        return $errors;
    }

    /**
     * renderError
     *
     * @param  array $errors
     * @param  boolean $is_continue
     * @return void
     */
    function renderError($errors, $is_continue = true)
    {
        if (is_null($GLOBALS['controller'])) $GLOBALS['controller']['pw_relative_base'] = Controller::relativeBaseURLForStatic();
        if (!$errors) return;
        $error_layout = BASE_DIR . "app/views/layouts/error.phtml";
        if (file_exists($error_layout)) include $error_layout;
        $error_template = BASE_DIR . "app/views/components/lib/php_error.phtml";
        if (file_exists($error_template)) {
            ob_start();
            include $error_template;
            $content_for_layout = ob_get_contents();
            ob_end_clean();
            echo ($content_for_layout);
        }
        if (!$is_continue) exit;
    }

    /**
     * showError
     *
     * @param  array $errors
     * @return void
     */
    static function showError($errors)
    {
        if (!$errors) return;
        $error_layout = BASE_DIR . "app/views/layouts/error.phtml";
        if (file_exists($error_layout)) include $error_layout;
        $error_template = BASE_DIR . "app/views/components/lib/php_error.phtml";
        if (file_exists($error_template)) {
            ob_start();
            include $error_template;
            $content_for_layout = ob_get_contents();
            ob_end_clean();
            echo ($content_for_layout);
        }
        exit;
    }

    /**
     * render contents
     *
     * @param  string $text
     * @return void
     */
    public function renderText($text)
    {
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
    public function renderContents($contents, $content_type = null)
    {
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
    public function renderFile($file, $content_type)
    {
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
    public function downloadFile($file, $file_name = null, $content_type = "application/octet-stream")
    {
        if ($this->performed_render) return;
        if (file_exists($file)) {
            if (is_null($file_name)) $file_name = basename($file);
            $length = filesize($file);
            if (PwFile::isIE()) $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
            header("Content-Length: {$length}");
            header("Content-Disposition: Attachment; filename=\"{$file_name}\"");
            header("Content-type: {$content_type}; name=\"{$file_name}\"");
            $this->performed_render = true;
        } else {
            trigger_error("File Not Found: {$file}", E_USER_NOTICE);
        }
        exit;
    }

    /**
     * downloadCotents
     * 
     * @param  string $file_name
     * @param  string $contents
     * @return void
     */
    public function downloadContents($file_name, $contents)
    {
        if (PwFile::isIE()) $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
        header("Content-type: application/octet-stream; name=\"{$file_name}\"");
        header("Content-Disposition: Attachment; filename=\"{$file_name}\"");
        header('Pragma: private');
        header('Cache-control: private, must-revalidate');
        echo $contents;
        exit;
    }

    /**
     * redirect
     * 
     * @param  mixed $params
     * @param  array $url_params
     * @return void
     */
    function redirectTo($params = null, $url_params = null)
    {
        if ($this->pw_multi_sid) $url_params['pw_multi_sid'] = $this->pw_multi_sid;
        if (is_string($params)) {
            $url = $this->urlFor(['action' => $params], $url_params);
        } else if (is_array($params)) {
            if (empty($params['controller'])) $params['controller'] = $this->name;
            $url = $this->urlFor($params, $url_params);
        } else {
            $params['controller'] = $this->name;
            $url = $this->urlFor($params, $url_params);
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * file upload template
     *
     * @param  array $params
     * @return string
     */
    function uploadFileJs($params)
    {
        $upload_tag = $this->linkJs($params);
        include('views/components/lib/file_upload.phtml');
        return $upload_tag;
    }

    /**
     * link tag for controller action id
     *
     * @param  array $params
     * @param  array $html_params
     * @return string
     */
    function linkTo($params, $html_params = null)
    {
        $html_params['is_selected'] = $this->checkLinkActive($params, $html_params);
        $html_params['href'] = $this->urlFor($params, $html_params['http_params']);
        $tag = PwTag::a($html_params);
        return $tag;
    }

    /**
     * link tag for pw-click
     *
     * @param  array $params
     * @return string
     */
    function linkJs($params = null)
    {
        if ($params['is_use_selected'] && $params['controller']) $params['class'] .= PwForm::linkActive($params['controller'], $this->name);
        unset($params['is_use_selected']);
        $tag = PwTag::a($params);
        return $tag;
    }

    /**
     * link change bool
     *
     * @param array $params
     * @param array $values
     * @return string
     */
    function linkReverseBool($params, $values)
    {
        $tag = PwForm::changeActiveLabelTag(
            [
                'controller' => $this->name,
                'action' => 'reverse_boolean',
                'id' => $values['id'],
                'http_params' => ['model' => $params['model'], 'column' => $params['column']]
            ],
            $values[$params['column']]
        );
        return $tag;
    }

    /**
     * check link active
     *
     * @param  array $params
     * @param  array $html_params
     * @return bool
     */
    function checkLinkActive($params, $html_params)
    {
        if (!$html_params['is_use_selected']) return;
        if (isset($html_params['selected_key'])) {
            return ($html_params['selected_key'] == $html_params['selected_value']);
        }
        if (isset($params['controller']) && ($params['controller'] == $this->name)) return true;

        if (isset($html_params['menu_group']) && $this->menu_group) {
            return ($html_params['menu_group'] == $this->menu_group);
        }
        if (isset($html_params['menu_groups']) && $this->menu_groups) {
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
    static function url($params, $http_params = null, $base_url = null)
    {
        $url = ($base_url) ? $base_url : $GLOBALS['controller']->base;
        if (is_string($params)) {
            $url .= $params;
        } else {
            if ($params) {
                if ($params['controller']) $elements[] = $params['controller'];
                if ($params['action']) $elements[] = $params['action'];
                if ($params['id']) $elements[] = $params['id'];
            }
            if (count($elements) == 1) {
                $url .= "{$elements[0]}/";
            } else {
                $url .= implode('/', $elements);
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
    function urlFor($params, $http_params = null)
    {
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
    private function contentType($encoding = null)
    {
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
    private function pwProjectName()
    {
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
    private function relativeBaseURL()
    {
        $this->pw_relative_base = self::relativeBaseURLForStatic();
        return $this->pw_relative_base;
    }

    /**
     * relative base url for QUERY_STRING
     *
     * @return string
     */
    static function relativeBaseURLForStatic()
    {
        $pw_relative_base = '';
        $query = str_replace('?', '', $_SERVER['QUERY_STRING']);
        $count = substr_count($query, '/');
        for ($i = 0; $i < $count; $i++) {
            $pw_relative_base .= '../';
        }
        return $pw_relative_base;
    }

    /**
     * base url for HTTPS & HTTP_HOST
     *
     * @return string
     */
    public function baseUrl()
    {
        $this->pwProjectName();
        $this->pw_http_host = $_SERVER['HTTP_HOST'];
        $this->pw_http_protocol = $_SERVER['REQUEST_SCHEME'];

        if (defined('BASE_URL') && is_string(BASE_URL)) {
            $this->base = BASE_URL;
        } else if (isset($_SERVER['HTTPS'])) {
            $this->base = 'https://' . preg_replace('/:443$/', '', $this->pw_http_host).'/';
        } else {
            $this->base = 'http://' . preg_replace('/:80$/', '', $this->pw_http_host).'/';
        }
        if ($this->pw_project_name) $this->base.= $this->pw_project_name . '/';
        return $this->base;
    }

    /**
     * pw controller
     *
     * @return string
     */
    private function pwController()
    {
        $this->pw_controller = $this->pw_gets['controller'];
        return $this->pw_controller;
    }

    /**
     * pw action
     *
     * @return string
     */
    private function pwAction()
    {
        $this->pw_action = $this->pw_gets['action'];
        if (empty($this->pw_action)) {
            $this->pw_action = 'index';
        } else if ($pos = strpos($this->pw_action, '.')) {
            $this->pw_action = substr($this->pw_action, 0, $pos);
        }
        return $this->pw_action;
    }

    /**
     * pw id
     *
     * @return integer
     */
    private function pwID()
    {
        if (!$this->pw_gets) return;
        if ($this->pw_gets['id']) {
            $this->pw_id = $this->pw_gets['id'];
        }
        return $this->pw_id;
    }

    /**
     * pw method
     * 
     * @param string $action
     * @return string
     */
    private function pwMethod($action)
    {
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
     * load $_POST
     *
     * @return void
     */
    function loadPwGets($params)
    {
        if ($_GET) $this->pw_gets = $_GET;
        if (isset($params['controller'])) $this->pw_gets['controller'] = $params['controller'];
        if (isset($params['action'])) $this->pw_gets['action'] = $params['action'];
        if (isset($params['id'])) $this->pw_gets['id'] = $params['id'];
        $this->pw_params = $this->pw_gets;
        $this->pw_request = $_REQUEST;
        if ($this->pw_gets) PwSession::set('pw_gets', $this->pw_gets);
    }

    /**
     * load $_POST
     *
     * @return void
     */
    function loadPwPosts()
    {
        if ($_POST) PwSession::set('pw_posts', $_POST);
        $this->pw_posts = PwSession::get('pw_posts');
        //TODO multi session
        if (isset($this->pw_posts['csrf_token'])) {
            $this->isCsrf();
        }
    }

    /**
     * load error
     *
     * @return void
     */
    function loadPwErrors()
    {
        $this->errors = $this->getErrors();
        $this->flushErrors();
    }

    /**
     * error box
     *
     * @param string $file_name
     * @param array $errors
     * @return void
     */
    function showErrorBox($file_name, $errors = null)
    {
        $path = "views/components/error_box/{$file_name}";
        include($path);
    }

    /**
     * clear $_POST & $_GET
     *
     * @return void
     */
    function clearPwPosts()
    {
        self::clearPwPostsSession();
        unset($this->pw_posts);
    }

    /**
     * clear clear $_POST & $_GET session
     *
     * @return void
     */
    static function clearPwPostsSession()
    {
        PwSession::clear('pw_posts');
        //TODO need pw_gets?
        //PwSession::clear('pw_gets');
    }

    /**
     * model
     *
     * @param string $model_name
     * @return PgEntity
     */
    function model($model_name)
    {
        if (!class_exists($model_name)) {
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['error'] = "{$model_name} Class is not exists.";
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $this->renderError($errors);
            exit;
        }
        if ($this->is_use_multi_sid && is_numeric($this->pw_multi_sid)) {
            return DB::model($model_name)->requestSession($this->pw_multi_sid);
        } else {
            return DB::model($model_name)->requestSession();
        }
    }

    /**
     * clear model
     *
     * @param string $model_name
     * @return void
     */
    function clearModel($model_name)
    {
        if (!class_exists($model_name)) {
            $errors['query'] = $_SERVER['QUERY_STRING'];
            $errors['request'] = $_SERVER['REQUEST_URI'];
            $errors['error'] = "{$model_name} Class is not exists.";
            $errors['signature'] = $_SERVER['SERVER_SIGNATURE'];
            $this->renderError($errors);
            exit;
        }
        if ($this->is_use_multi_sid && is_numeric($this->pw_multi_sid)) {
            return DB::model($model_name)->clearSession($this->pw_multi_sid);
        } else {
            return DB::model($model_name)->clearSession();
        }
    }

    /**
     * load session by Model
     *
     * @return void
     */
    function loadModelSession()
    {
        if (!is_array($this->session_by_models)) return;
        foreach ($this->session_by_models as $class_name) {
            if (class_exists($class_name)) {
                $model = PwSession::getWithKey('app', $class_name);
                if (!$model->entity_name) {
                    $model = DB::model($class_name)->get(true);
                    $entity_name = $model->entity_name;
                    //IS_MODEL_SECURE_SESSION: remove important infomations from session.
                    //but It may not be working properly in model functions!
                    if (defined('IS_MODEL_SECURE_SESSION') && IS_MODEL_SECURE_SESSION) {
                        $model->sql = '';
                        $model->pg_info = '';
                        $model->pg_info_array = [];
                        $model->sqls = [];
                    }
                    PwSession::setWithKey('app', $class_name, $model);
                }
                if ($model->entity_name) {
                    $entity_name = $model->entity_name;
                    $this->$entity_name = $model;
                }
            }
        }
    }

    /**
     * reload session by Model
     *
     * @return void
     */
    function reloadModelSession()
    {
        if (!is_array($this->session_by_models)) return;
        foreach ($this->session_by_models as $class_name) {
            if (class_exists($class_name)) PwSession::clearWithKey('app', $class_name);
        }
        $this->loadModelSession();
    }

    /**
     * session values by Model
     *
     * @return void
     */
    function sessionModel($model_name)
    {
        if (!class_exists($model_name)) return;
        $model = DB::model($model_name);
        return PwSession::getWithKey('app', $model->entity_name);
    }

    /**
     * session values by Model
     *
     * @return void
     */
    function sessionModelValue($model_name, $index)
    {
        $model = $this->sessionModel($model_name);
        if ($model->values) {
            $value = $model->values[$index];
            return $value;
        }
    }

    /**
     * load session values
     *
     * @param string $model_name
     * @param array $conditions
     * @return void
     */
    function modelForSession($model_name, $conditions = null)
    {
        $model = DB::model($model_name);
        $model->values = PwSession::getWithKey('app', $model->entity_name);
        if (!$model->values) {
            $model->wheres($conditions)->get(true);
            PwSession::setWithKey('app', $model->entity_name, $model->values);
        }
        return $model;
    }

    /**
     * load session values
     *
     * @param string $model_name
     * @return void
     */
    function clearModelForSession($model_name)
    {
        $model = DB::model($model_name);
        PwSession::clearWithKey('app', $model->entity_name);
    }

    /**
     * add session errors for model
     *
     * @param  PwEntity $model
     * @return void
     */
    function addErrorByModel($model)
    {
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
     * @param  array $values
     * @return void
     */
    function addError($key, $values)
    {
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
    function setErrors($errors)
    {
        PwSession::setWithKey('errors', $this->name, $errors);
    }

    /**
     * get session errors
     *
     * @return array
     */
    function getErrors()
    {
        return PwSession::getWithKey('errors', $this->name);
    }

    /**
     * get session errors
     *
     * @return array
     */
    function flushErrors()
    {
        return PwSession::clearWithKey('errors', $this->name);
    }

    /**
     * load global session by key value
     *
     * @param  string $key
     * @param  array $value
     * @return void
     */
    function loadGlobalSession($key)
    {
        $this->$key = PwSession::load($key);
    }

    /**
     * load session by key value
     *
     * @param  string $key
     * @param  array $value
     * @return void
     */
    function loadSession($key)
    {
        if (!$this->session_name) return;
        if (isset($_REQUEST[$key])) PwSession::setWithKey($this->session_name, $key, $_REQUEST[$key]);
        $this->getSession($key);
    }

    /**
     * load session by key value
     *
     * @param  string $key
     * @param  array $value
     * @return void
     */
    function clearSession($key)
    {
        if (!$this->session_name) return;
        if (isset($_REQUEST[$key])) PwSession::clearWithKey($this->session_name, $key, $_REQUEST[$key]);
        $this->getSession($key);
    }

    /**
     * set session by key value
     *
     * @param  string $key
     * @param  array $value
     * @param  integer $sid
     * @return void
     */
    function setSession($key, $value, $sid = 0)
    {
        if (!$this->session_name) return;
        if (isset($value)) PwSession::setWithKey($this->session_name, $key, $value, $sid);
    }

    /**
     * get session by key value
     *
     * @param  string $key
     * @param  integer $sid
     * @return mixed
     */
    function getSession($key, $sid = 0)
    {
        if (!$this->session_name) return;
        return $this->$key = PwSession::getWithKey($this->session_name, $key, $sid);
    }

    /**
     * get sessions
     *
     * @param  integer $sid
     * @return string
     */
    function getSessions($sid = 0)
    {
        if (!$this->session_name) return;
        return PwSession::get($this->session_name, $sid);
    }

    /**
     * set sessions
     *
     * @param  array $values
     * @param integer $sid
     * @return void
     */
    function setSessions($values, $sid = 0)
    {
        if (!$this->session_name) return;
        if (!is_array($values)) return;
        foreach ($values as $key => $value) {
            PwSession::setWithKey($this->session_name, $key, $value, $sid);
        }
    }

    /**
     * clear sessions
     *
     * @param integer $sid
     * @return void
     */
    function clearSessions($sid = 0)
    {
        if (!$this->session_name) return;
        PwSession::clear($this->session_name, $sid);

        //onethor function?
        $this->clearRequestSession();
        $this->clearPwPosts();
    }

    /**
     * session terminate
     *
     * @return void
     */
    function flushSessions($sid = 0)
    {
        if (!$this->session_name) return;
        PwSession::flushWithKey($this->session_name, $sid);
    }

    /**
     * before action
     *
     * @param string $action
     * @return void
     */
    function before_action($action)
    {
        $this->loadrequestSession();
        $this->loadModelSession();
        if ($this->name == $this->pw_admin_controller) $this->checkPwAdmin($action);
    }

    function before_rendering()
    { }
    function before_invocation()
    { }

    /**
     * after action
     * 
     * TODO
     *
     * @param string $action
     * @return void
     */
    function after_action()
    {
        // $url = $_SERVER['REQUEST_URI'];
        // if (isset($_SERVER['HTTPS'])) {
        //     $pw_prev_request_uri = "https://{$url}";
        // } else {
        //     $pw_prev_request_uri = "http://{$url}";
        // }
        // PwSession::set('pw_prev_request_uri', $pw_prev_request_uri, $this->pw_multi_sid);
    }

    /**
     * load Csv values
     * 
     * @return void
     */
    function loadDefaultCsvOptions($lang = null)
    {
        $this->csv_sessions = PwLocalize::loadCsvSessions($lang);
    }

    /**
     * check model id
     * When invalid, redirect.
     *
     * @param PwEntity $model
     * @param array $params
     * @param array $http_params
     * @return void
     */
    function checkModel($model, $params = null, $http_params = null)
    {
        if (!$params) $params['controller'] = $model->entity_name;
        if (!$model->value['id']) $this->redirectTo($params, $http_params);
    }

    /**
     * check action 'pw_gets'
     * 
     * @param string $redirect_action
     * @return void
     */
    function checkEdit($params = ['action' => 'index'])
    {
        if (!$this->pw_gets['id']) {
            $this->redirectTo($params);
            exit;
        }
    }

    /**
     * check post
     * 
     * @return void
     */
    function checkPost()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

    /**
     * redirect by model
     * 
     * @param PwEntity $model
     * @param array $params
     * @return void
     */
    function redirectByModel($model, $params = null)
    {
        if ($params) if (!$params['invalid']) $params['invalid'] = $params['valid'];
        if ($model->errors) {
            $this->addErrorByModel($model);
            if ($params['invalid']) $this->redirectTo($params['invalid']);
        } else {
            if ($params['valid']) $this->redirectTo($params['valid']);
        }
    }

    /**
     * redirect for add by model
     * 
     * @param PwEntity $model
     * @param array $params
     * @return void 
     */
    function redirectForAdd($model, $params = null)
    {
        if (!$params['valid']) $params['valid'] = ['action' => 'list'];
        if (!$params['invalid']) $params['invalid'] = ['action' => 'new'];
        $this->redirectByModel($model, $params);
    }

    /**
     * redirect for update by model
     * 
     * @param PwEntity $model
     * @param array $params
     * @return void 
     */
    function redirectForUpdate($model, $params = null)
    {
        if (!$params['valid']) $params['valid'] = ['action' => 'edit', 'id' => $this->pw_gets['id']];
        if (!$params['invalid']) $params['invalid'] = ['action' => 'edit', 'id' => $this->pw_gets['id']];
        $this->redirectByModel($model, $params);
    }

    /**
     * redirect for delete by model
     * 
     * @param PwEntity $model
     * @param array $params
     * @return void 
     */
    function redirectForDelete($model, $params = null)
    {
        if (!$params['valid']) $params['valid'] = ['action' => 'list'];
        if (!$params['invalid']) $params['invalid'] = ['action' => 'edit', 'id' => $this->pw_gets['id']];
        $this->redirectByModel($model, $params);
    }

    /**
     * bind pw posts
     * 
     * @param array $values
     * @param string $entity_name
     * @return void
     */
    function bindPwPosts($values, $entity_name = null)
    {
        if (!$values) return;
        if (!$entity_name) $entity_name = $this->name;
        foreach ($values as $column => $value) {
            $this->pw_posts[$entity_name][$column] =  $value;
        }
    }

    /**
     * pw prev
     * 
     * //TODO under construction
     */
    function pwPrev()
    {
        header("Location: {$this->pw_prev_request_uri}");
        exit;
    }

    /**
     * fetch By Model
     * 
     * @param string $class_name
     * @param string $column
     * @return PwEntity
     */
    function fetchByModel($class_name, $column = null)
    {
        if (!class_exists($class_name)) return;
        $model = DB::model($class_name);
        if (!$column) $column = 'id';
        $model->fetch($this->pw_request[$column]);
        return $model;
    }

    /**
     * insert model
     * 
     * @param string $class_name
     * @param array $posts
     * @return PwEntity
     */
    function insertByModel($class_name, $posts = null)
    {
        if (!$this->is_force_get_update) $this->checkPost();
        if (!class_exists($class_name)) return;
        $model = DB::model($class_name);
        if (!$posts) $posts = $this->pw_posts[$model->entity_name];
        $model->init()->insert($posts);
        if (!$model->errors) {
            $model->initSort();
            $this->clearPwPosts();
        }
        return $model;
    }

    /**
     * update model by request
     * 
     * @param string $class_name
     * @param integer $id
     * @param array $posts
     * @return PwEntity
     */
    function updateByModel($class_name, $id = null, $posts = null)
    {
        if (!$this->is_force_get_update) $this->checkPost();
        if (!class_exists($class_name)) return;
        $model = DB::model($class_name);
        if (!$posts) $posts = $this->pw_posts[$model->entity_name];
        if (!$id) $id = $this->pw_gets['id'];
        $model->update($posts, $id);
        if (!$model->errors) $this->clearPwPosts();
        return $model;
    }

    /**
     * delete model by request
     * 
     * @param string $class_name
     * @param integer $id
     * @return PwEntity
     */
    function deleteByModel($class_name, $id = null)
    {
        if (!$this->is_force_get_update) $this->checkPost();
        if (!class_exists($class_name)) return;
        $model = DB::model($class_name);
        if (!$id) $id = $this->pw_gets['id'];
        $model->delete($id);
        return $model;
    }

    /**
     * load Request Session
     *
     * @param
     * @return void
     */
    function loadRequestSession()
    {
        if (!$this->session_request_columns) return;
        if ($this->session_request_columns) {
            foreach ($this->session_request_columns as $session_request_column) {
                if ($this->session_name) {
                    $this->$session_request_column = PwSession::loadWithKey($this->session_name, $session_request_column, $this->pw_multi_sid);
                } else {
                    $this->$session_request_column = PwSession::loadWithKey($session_request_column, null, $this->pw_multi_sid);
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
    function clearRequestSession()
    {
        if ($this->session_request_columns) {
            foreach ($this->session_request_columns as $session_request_column) {
                if ($this->session_name) {
                    PwSession::clearWithKey($this->session_name, $session_request_column, $this->pw_multi_sid);
                } else {
                    PwSession::clear($session_request_column, $this->pw_multi_sid);
                }
                unset($this->$session_request_column);
            }
        }
    }

    /**
     * reverse_boolean
     *
     * @return void
     */
    function reverse_boolean($redirect_params = null)
    {
        $this->changeBoolean($this->pw_gets['model'], $this->pw_gets['column']);
        if (!$redirect_params) $redirect_params = ['action' => 'list'];
        if ($this->pw_gets['redirect_action']) $redirect_params = ['action' => $this->pw_gets['redirect_action']];
        $this->redirectTo($redirect_params);
    }

    /**
     * change boolean
     *
     * @param string $name
     * @param string $column
     * @param string $id_column
     * @return void
     */
    function changeBoolean($name, $column, $id_column = 'id')
    {
        $model_name = PwFile::phpClassName($name);
        if (isset($this->pw_gets[$id_column])) {
            DB::model($model_name)->reverseBool($this->pw_gets[$id_column], $column);
        }
    }

    /**
     * cancel
     *
     * @param
     * @return void
     */
    function action_cancel()
    {
        $this->clearPwPosts();
        $this->redirectTo(['action' => 'list']);
    }

    /**
     * update sort order
     *
     * @param
     * @return void
     */
    public function action_update_sort()
    {
        if (!$this->pw_auth->value['id']) return;
        if ($_POST['model_name']) $this->updateSort($_POST['model_name']);
    }

    /**
     * update sort order
     *
     * @param string $model_name
     * @param array $conditions
     * @param string $model_name
     * @param boolean $is_json
     * @return void
     */
    function updateSort($model_name = null, $conditions = [], $is_json = true)
    {
        if (!$model_name) exit('Not found model_name');

        $posts = file_get_contents("php://input");
        if (!$posts) exit('Invalid POST');

        $values = json_decode($posts, true);
        if (!$values) exit('Not found sort_order');

        if (!class_exists($model_name)) exit;
        DB::model($model_name)->wheres($conditions)->updateSortOrder($values);
        if ($is_json) $results['is_success'] = true;

        if ($is_json) {
            $results = json_encode($results);
            echo ($results);
            exit;
        }
    }

    /**
     * session values by Model
     *
     * @return void
     */
    function sessionValueByModel($class_name, $index)
    {
        $values = $this->sessionValuesByModel($class_name);
        if (is_array($values)) return $values[$index];
    }

    /**
     * record value
     *
     * @param  string $csv_name
     * @param  string $key
     * @return string
     */
    function recordValue($csv_name, $key)
    {
        $value = $this->csv_sessions[$csv_name][$key];
        return $value;
    }

    /**
     * record value
     *
     * @param  string $csv_name
     * @param  string $key
     * @return string
     */
    function recordValues($csv_name)
    {
        $values = $this->csv_sessions[$csv_name];
        return $values;
    }

    /**
     * record value
     *
     * @param  string $csv_name
     * @param  string $key
     * @return string
     */
    function recordKeys($csv_name)
    {
        $values = $this->csv_sessions[$csv_name];
        if ($values) $keys = array_keys($values);
        return $keys;
    }

    /**
     * csv values
     *
     * @param  string $csv_name
     * @param  string $lang
     * @return string
     */
    static function csvValues($csv_name, $lang = 'ja')
    {
        $csv_list = PwLocalize::loadCsvSessions($lang);
        $values = $csv_list[$csv_name];
        return $values;
    }

    /**
     * csv value
     *
     * @param  string $csv_name
     * @param  string $key
     * @param  string $lang
     * @return string
     */
    static function csvValue($csv_name, $key, $lang = 'ja')
    {
        $csv_list = PwLocalize::loadCsvSessions($lang);
        $value = $csv_list[$csv_name][$key];
        return $value;
    }

    /**
     * render json
     *
     * @param array $values
     * @return void
     */
    function renderJson($values)
    {
        $json = json_encode($values);
        echo ($json);
        exit;
    }

    /**
     * check POST method
     *
     * @return boolean
     */
    function isRequestPost()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

    /**
     * check auth
     * 
     * @param  string $action
     * @return void
     */
    function checkAuth($action)
    {
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
    function checkPwAdmin($action)
    {
        if (in_array($action, $this->pw_admin_escapes)) {
            return;
        }
        $this->pw_admin = PwSession::get('pw_admin');
        if (!$this->pw_admin) {
            $this->redirectTo(['controller' => $this->pw_admin_controller, 'action' => 'login']);
            exit;
        }
    }

    /**
     * csrf
     *
     * @return void
     */
    function csrf()
    {
        $toke_byte = openssl_random_pseudo_bytes(16);
        $csrf_token = bin2hex($toke_byte);
        PwSession::set('csrf_token', $csrf_token);
    }

    /**
     * is csrf
     *
     * @return boolean
     */
    function isCsrf()
    {
        //TODO multi session
        return ($this->pw_posts['csrf_token'] == PwSession::get('csrf_token'));
    }

    /**
     * pwlogin
     *
     * @return void
     */
    function pwLogin()
    {
        $this->layout = 'login';
        $template = 'views/components/auth/login.phtml';
        $this->render('login', $template);
    }

    /**
     * auth
     *
     * @return PwPgsql
     */
    function pwAuth()
    {
        if (!$this->auth_controller) return;
        if (!$this->auth_model) return;
        if (!class_exists($this->auth_model)) return;
        return DB::model($this->auth_model)->auth();
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
    function redirectAuthTop()
    {
        $uri = "{$this->auth_top_controller}/";
        $this->redirectTo($uri);
        exit;
    }

    /**
     * redirect login top
     *
     * @return void
     */
    function redirectLogin($uri = null)
    {
        if (!$uri) $params['controller'] = $this->pw_login_controller;
        if (!$params['controller']) exit;
        $this->redirectTo($params);
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
        if ($action) $url .= $action;
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
        if ($this->js_controller) return $this->js_controller;
        return $this->pw_controller;
    }

    /**
     * images dirctory path
     *
     * @return string
     */
    function imageDir($dir = 'images')
    {
        return $this->base . "{$dir}/";
    }

    /**
     * images dirctory path
     *
     * @param string $file_name
     * @return string $dir
     * @return string
     */
    function image($file_name, $dir = 'images')
    {
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
    static function imageBaseUrl($dir = 'images')
    {
        $controller = $GLOBALS['controller'];
        return $controller->base . "{$dir}/";
    }

    /**
     * images dirctory path
     *
     * @return void
     */
    static function imageUrl($file_name, $dir = 'images')
    {
        if (!$dir) $dir = 'images';
        $url = self::imageBaseUrl($dir);
        $url = "{$url}{$file_name}";
        return $url;
    }

    /**
     * url path to params
     *
     * @return array
     */
    static function pathToParam($path)
    {
        if (!$path) return;
        $params = [];
        $paths = explode('/', $path);
        foreach (self::$routes as $index => $route) {
            if (isset($paths[$index])) $params[$route] = $paths[$index];
        }
        return $params;
    }

    /**
     * rereirect params
     *
     * @return array
     */
    public function redirectParams()
    {
        $params = self::pathToParam($_REQUEST['redirect']);
        return $params;
    }

    /**
     * system status
     *
     * @return void
     */
    public function systemStatus()
    {
        //MB
        $values['cpu'] = sys_getloadavg();
        $values['memory'] = memory_get_usage() / (1024 * 1024);
        return $values;
    }

    /**
     * memory flow
     *
     * @return void
     */
    static function isMemoryFlow()
    {
        $memory_peak = memory_get_peak_usage();
        $memory_mb = round($memory_peak / (1024 * 1024));
        if ($memory_mb > APP_MEMORY_LIMIT) {
            $msg = "memory peak : {$memory_mb}MB";
            dump($msg);
            return true;
        }
    }
}

PwSetting::load();
Controller::loadLib();
PwLocalize::loadLocalizeFile($lang);
PwLoader::autoloadModel();
PwSetting::loadApplication();