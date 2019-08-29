<?php
/**
 * SettingController 
 *
 * @author  Yohei Yoshikawa
 * @create  2012/10/03 
 */
require_once 'AppController.php';

class SettingController extends AppController {

    public $name = "setting";

    function before_action($action) {
        $this->pgsql_entity = new PwPgsql();
        $this->pg_connection = $this->pgsql_entity->checkConnection();

        $this->database = new Database();
        $this->database->checkProjectManager();
    }

    function index() {
        $this->hostname = PwSetting::hostname();
        $this->debug_traces = debug_backtrace(true);
        $this->create_sql_path = DB_DIR."sql/project_manager.sql";
    }

    function action_pgsql() {
        $pgsql_entity = new PwPgsql();
        $this->pg_connection = $pgsql_entity->connection();
        $vo_path = BASE_DIR."app/models/vo/";
        $this->sql = $pgsql_entity->createTablesSQLForPath($vo_path);
    }

    function action_create_database() {
        $pgsql_entity = new PwPgsql();
        $this->results = $pgsql_entity->createDatabase();
    }

    function action_create_tables() {
        $pgsql_entity = new PwPgsql();
        $this->results = $pgsql_entity->createTablesForProject($vo_path);
        $this->sql = $pgsql_entity->sql;
    }

}