<?php
class PwLaravel
{
    public $path;
    public $dev_null = '> /dev/null 2>&1';

    function __construct($params = null)
    {
        if ($params['path']) $this->path = $params['path'];
    }

    /**
     * command make
     *
     * @param string $type
     * @param string $name
     * @param array $options
     * @return void
     */
    public static function cmdMake($type, $name, $options = null)
    {
        $cmd = COMAND_PHP_PATH." artisan make:{$type} {$name}";
        if ($options) {
            $option = implode(' ', $options);
            $cmd.= " {$option}";
        }
        //$cmd.= " {$this->dev_null}";
        return $cmd;
    }

    /**
     * artisan Controller
     *
     * @param string $type
     * @param string $name
     * @param array $options
     * @return void
     */
    public function artisanMake($type, $name, $options = null)
    {
        if (!defined('COMAND_PHP_PATH')) exit('Not defined COMAND_PHP_PATH.');
        if ($this->path) $cmd = "cd {$this->path} && ";
        $cmd.= PwLaravel::cmdMake($type, $name, $options);
        return $cmd;
    }

    /**
     * artisan make Controller
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function makeController($name, $options = null)
    {
        $cmd = $this->artisanMake('controller', $name, $options);
        exec($cmd);
    }

    /**
     * create view
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function createView($name, $options = null)
    {
        $view_dir = $this->viewPath($name);
        PwFile::createDir($view_dir);

        if ($options['action']) {
            foreach ($options['action'] as $action) {
                $blade_path = PwLaravel::bladePath($name, $action);
                if (!file_exists($blade_path)) {
                    $contents = '';
                    file_put_contents($blade_path, $contents);
                    PwFile::chmodFile($blade_path, 0666);
                }
            }
        }
    }

    /**
     * blade file name
     *
     * @param  string $controller
     * @param  string $action
     * @return string
     */
    public function bladePath($controller, $action)
    {
        $view_dir = $this->viewPath($controller);
        $file_name = $this->bladeName($action);
        $path = "{$view_dir}{$file_name}";
        return $path;
    }

    /**
     * blade file name
     *
     * @param  string $name
     * @return string
     */
    public function bladeName($name)
    {
        $name = "{$name}.blade.php";
        return $name;
    }

    /**
     * view path
     *
     * @param string $name
     * @return string
     */
    public function viewPath($controller)
    {
        $dir = $this->viewDir();
        $path = "{$dir}{$controller}/";
        return $path;
    }

    /**
     * view dir
     *
     * @return string
     */
    public function viewDir()
    {
        $path = "{$this->path}resources/views/";
        return $path;
    }

    /**
     * controller name by name
     *
     * @param string $name
     * @return string
     */
    public function controllerNameByName($name)
    {
        $name.= 'Controller';
        return $name;
    }
}
