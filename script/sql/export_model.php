<?php
/**
 * Copyright (c) 2019 Yohei Yoshikawa 
 *
 */
require_once dirname(__FILE__).'/../../lib/Controller.php';

//$argv[1]: table_name
PwModel::exportPgsqlVO($argv[1]);
