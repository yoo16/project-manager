<?php
require_once 'application_setting.php';
require_once 'application_function.php';

$libs = array(
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
