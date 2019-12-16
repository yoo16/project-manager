<?php
/**
 * iPwEntity 
 *
 * @copyright  Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

interface iPwEntity
{
    public function requestSession($sid = 0, $session_key = null);
}
