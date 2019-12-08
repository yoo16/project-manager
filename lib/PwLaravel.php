<?php
class PwLaravel
{
    public $path;
    public $dev_null = '> /dev/null 2>&1';

    public $resource_actions = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

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
     * remove Controller
     *
     * @param string $name
     * @return void
     */
    public function removeController($name)
    {
        PwFile::removeFile(PwLaravel::controllerPath($name));
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
     * create view
     *
     * @param Page $page
     * @param array $options
     * @return void
     */
    public function createRoute($page, $options = null)
    {
        $route = $page->relation('Route')->all();
        //TODO web or api
        $template = BASE_DIR . "app/views/templates/laravel/route/web.phtml";
        if (file_exists($template)) {
            ob_start();
            include $template;
            $contents = ob_get_contents();
            ob_end_clean();
        }

        $file_path = "{$this->path}routes/web{$page->value['name']}.php";
        $contents = "<?php".PHP_EOL.$contents;

        file_put_contents($file_path, $contents);
    }

    /**
     * controller file path
     *
     * @param  string $name
     * @return string
     */
    public function controllerPath($name)
    {
        $dir = $this->controllerDir();
        $file_name = Controller::fileName($name);
        $path = "{$dir}{$file_name}";
        return $path;
    }

    /**
     * blade file path
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
     * route dir
     *
     * @param Page $page
     * @return string
     */
    public function routeFile($page)
    {
        $middleware = ($page->value['middleware']) ? $page->value['middleware'] : 'web'; 
        $file_name = "{$middleware}{$page->value['name']}.php";

        $dir = $this->routeDir();
        $path = "{$dir}{$file_name}";
        return $path;
    }

    /**
     * route dir
     *
     * @return string
     */
    public function routeDir()
    {
        $path = "{$this->path}routes/";
        return $path;
    }

    /**
     * controller dir
     *
     * @return string
     */
    public function controllerDir()
    {
        $path = "{$this->path}app/Http/Controllers/";
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

}
