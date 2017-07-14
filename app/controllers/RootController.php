<?php
/**
 * RootController 
 *
 * @author  Yohei Yoshikawa
 * @create  2012/10/03 
 */
require_once 'AppController.php';

class RootController extends AppController {

    function index() {
        $pgsql_entity = new PgsqlEntity();
        $this->pg_connection = $pgsql_entity->connection();

        $this->hostname = hostname();
        $this->debug_traces = debug_backtrace(true);
        $this->create_sql_path = BASE_DIR."db/sql/create.sql";
    }

    function action_pgsql() {
        $pgsql_entity = new PgsqlEntity();
        $this->pg_connection = $pgsql_entity->connection();
        $this->create_sql_path = BASE_DIR."db/sql/create.sql";
    }

    function create_database() {
        $pg_infos = PgsqlEntity::defaultPgInfo();
        $this->results = PgsqlEntity::createDatabase($pg_infos);
    }

    function init_database() {
        $this->results = PgsqlEntity::initDb();
    }

    function init_user() {
        $posts['login_name'] = 'default';
        Db::table('User')->insert($posts);
        $this->redirect_to('index');
    }

}