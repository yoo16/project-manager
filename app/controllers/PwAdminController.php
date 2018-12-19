<?php

/**
 * PwAdminController 
 *
 * @author  Yohei Yoshikawa
 * @create  2012/10/03 
 */
require_once 'AppController.php';

class PwAdminController extends AppController
{

    public $layout = 'pw_admin';
    public $name = 'pw_admin';

    function before_action($action)
    {
        parent::before_action($action);
        if (!$this->pw_auth->value) {
        }
    }

    /**
     * index
     *
     * @param
     * @return void
     */
    function index()
    {
    }

    /**
     * default_page
     *
     * @param
     * @return void
     */
    function default_page()
    {
        $this->redirectTo('index');
    }

    /**
     * auth
     *
     * @return void
     */
    function auth() {
        if (!defined('PW_ADMIN_LOGIN_NAME') || !PW_ADMIN_LOGIN_NAME) exit;
        if (!defined('PW_ADMIN_PASSWORD') || !PW_ADMIN_PASSWORD) exit;

        if ($_REQUEST['login_name'] == PW_ADMIN_LOGIN_NAME &&
            $_REQUEST['password'] == PW_ADMIN_LOGIN_NAME) {

            $admin['name'] = 'Admin';
            $admin['login_name'] = PW_ADMIN_LOGIN_NAME;
            PwSession::set('pw_admin', $admin);
            $this->redirectTo('index');
        } else {
            $this->redirectTo('login');
        }
        exit;
    }

    function status() {
        //MB
        $values['cpu'] = sys_getloadavg();
        $values['memory'] = memory_get_usage() / (1024 * 1024);
        $json = json_encode($values);
        echo($json);
        exit;
    }

    /**
     * log
     *
     * @return void
     */
    function log()
    {

    }

    /**
     * log list
     *
     * @return void
     */
    function log_list()
    {
        $logger = new PwLogger();
        $values = $logger->logFiles();
        $values = json_encode($values);
        echo ($values);
        exit;
    }

    /**
     * log file
     *
     * @return void
     */
    function log_file()
    {
        $path = LOG_DIR . $_REQUEST['filename'] . '.log';
        $values = file_get_contents($path);
        echo ($values);
        exit;
    }

    /**
     * delete log
     *
     * @return void
     */
    function delete_log()
    {
        $path = LOG_DIR . $_REQUEST['filename'] . '.log';
        $values['success'] = PwFile::removeFile($path);
        $values = json_encode($values);
        echo ($values);
        exit;
    }

}