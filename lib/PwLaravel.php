<?php
class PwLaravel
{

    /**
     * artisan Controller
     *
     * @return void
     */
    public static function artisanMake($type, $name)
    {
        if (!defined('COMAND_PHP_PATH')) exit('Not defined COMAND_PHP_PATH.');
        //$cmd = COMAND_PHP_PATH." artisan make:{$type} {$name} > /dev/null 2>&1";
        $cmd = COMAND_PHP_PATH." artisan make:{$type} {$name}";
        return $cmd;
    }

    /**
     * artisan make Controller
     *
     * @return void
     */
    public static function artisanMakeController($name, $path = null)
    {
        $cmd = '';
        if ($path) $cmd = "cd {$path} && ";
        $cmd.= self::artisanMake('controller', $name);
        exec($cmd);
    }

}
