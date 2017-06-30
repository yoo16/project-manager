<?php
define('PROJECT_NAME', 'ProjectManager');
define('LOG_DIR', BASE_DIR.'log/');

ini_set('error_log', LOG_DIR.date('Ymd').'.log');

ini_set('display_errors', false);
ini_set('log_errors', true);
error_reporting(E_ALL & ~E_NOTICE);

define('PROJECT_DIR', BASE_DIR);
define('TMP_DIR', BASE_DIR . 'tmp/');
define('DB_DIR', BASE_DIR.'db/');

define('DEBUG', true);

define('PG_INFO', 'host=localhost user=postgres dbname=project_manager');

define('MAIL_SETTING_FILE', DB_DIR.'mail/default.csv');
require_once 'functions.php';
