<?php
    define('APP_NAME', 'ProjectManager');
    define('HTML_TITLE', 'ProjectManager');
    define('VERSION', '0.1.0');

    define('PHP_WORK_GIT_URL', 'https://github.com/yoo16/php-work.git');

    define('PROJECT_MANAGER_DATABASE_NAME', 'project_manager');
    define('PG_INFO', 'host=localhost user=postgres dbname='.PROJECT_MANAGER_DATABASE_NAME);

    define('APP_DIR', BASE_DIR.'app/');
    define('MODEL_DIR', APP_DIR.'models/');
    define('VIEW_DIR', APP_DIR.'views/');
    define('CONTROLLER_DIR', APP_DIR.'controllers/');
    define('TEMPLATE_DIR', VIEW_DIR.'templates/');

    define('EXT_PHP', '.php');
    define('EXT_HTML', '.html');
    define('EXT_TEMPLATE', '.phtml');
