<?php
/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa 
 */

require_once dirname(__FILE__).'/../../lib/Controller.php';

$pw_migration = new PwMigration(DB_INFO);
$pw_migration->initInfo();
$pw_migration->up();

//case different version
//$pw_migration->updateVersion(1);