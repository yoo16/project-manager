<?php
/**
 * RootController 
 *
 * @author  Yohei Yoshikawa
 * @create  2012/10/03 
 */
require_once 'AppController.php';

class RootController extends AppController {

    function before_action($action) {
    }

    function index() {
        $pgsql_entity = new PgsqlEntity();
        $this->pg_connection = $pgsql_entity->connection();
        if (!$this->pg_connection) {
            $this->redirect_to('pgsql');
            exit;
        }

        $database = new Database();
        if (!$database->checkProjectManager()) {
            $this->redirect_to('pgsql');
            exit;
        }

        // $this->hostname = hostname();
        // $this->debug_traces = debug_backtrace(true);
        // $this->create_sql_path = BASE_DIR."db/sql/create.sql";
    }

    function action_pgsql() {
        $pgsql_entity = new PgsqlEntity();
        $this->pg_connection = $pgsql_entity->connection();
        $this->sql = $pgsql_entity->createTablesSql();
    }

    function action_create_database() {
        $pgsql_entity = new PgsqlEntity();
        $this->results = $pgsql_entity->createDatabase();
    }

    function action_create_tables() {
        $pgsql_entity = new PgsqlEntity();

        $this->results = $pgsql_entity->createTables();
        $this->sql = $pgsql_entity->sql;
    }

}