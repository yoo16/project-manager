<?php
if (!file_exists(DB_SETTING_FILE)) exit('Not found : DB_SETTING_FILE');
require_once DB_SETTING_FILE;
require_once 'application_setting.php';
require_once 'application_function.php';

$libs = array(
    'helpers',
    'DB',
    'SendMail',
    'CsvLite',
    'FileManager',
    'FtpLite',
    'FormHelper',
    'TagHelper',
    'DateHelper',
    'AppSession',
    'ApplicationLocalize',
    'ApplicationLoader',
    'ApplicationLogger',
    );

Controller::loadLib($libs);
ApplicationLocalize::load();
