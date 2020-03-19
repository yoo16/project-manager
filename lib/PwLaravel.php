<?php
class PwLaravel
{
    public $path;
    static $dev_null = '> /dev/null 2>&1';

    static public $resource_actions = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

    static public $escape_columns = [
        'id',
        'created_at',
        'updated_at',
    ];

    static public $nullable_attributes = [
        'created_at',
    ];

    static public $migration_types = [
        'primary' => ['name' => 'bigIncrements'], //TODO mediumIncrements
        'varchar' => ['name' => 'string'],
        'bool' => ['name' => 'boolean'],
        'int2' => ['name' => 'integer'],
        'int4' => ['name' => 'integer'],
        'int8' => ['name' => 'bigInteger'],
        'real' => ['name' => 'float'],
        'float' => ['name' => 'float'],
        'float8' => ['name' => 'float'],
        'double' => ['name' => 'double'],
        'double precision' => ['name' => 'double'],
        'text' => ['name' => 'text'],
        'jsonb' => ['name' => 'jsonb'],
        'timestamp' => ['name' => 'timestamp'],
        'datetime' =>['name' => 'dateTime'],
        'date' => ['name' => 'date'],
        'cidr' => ['name' => 'ipAddress'],
        'inet' => ['name' => 'ipAddress'],
        'macaddr' => ['name' => 'macAddress'],
        'geometry' => ['name' => 'geometry'],
        'point' => ['name' => 'point'],
    ];

    static public $migration_optional_functions = [
        'nullable' => ['name' => 'nullable'],
    ];

    function __construct($params = null)
    {
        if ($params['path']) $this->path = $params['path'];
    }

    /**
     * create project
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public static function createProject($name, $options = null)
    {
        $cmd = "composer create-project laravel/laravel {$name} --prefer-dist";
        if ($options) {
            $option = implode(' ', $options);
            $cmd.= " {$option}";
        }
        exec($cmd);
    }

    /**
     * export laravel models
     * 
     * @param Project $project
     * @return bool
     */
    static function exportModels($project) {
        $database = DB::model('Database')->fetch($project->value['database_id']);
        $model = $project->relation('Model')->get();
        if (!$model->values) return;
        foreach ($model->values as $model->value) {
            self::exportModel($project, $database, $model);
        }
    }

    /**
     * export laravel model
     * 
     * @param Project $project
     * @param Database $database
     * @param Model $model
     * @return bool
     */
    static function exportModel($project, $database, $model) {
        if (!$database) return;
        if (!$model) return;

        $escapes = ['migration'];
        if (in_array($model->value['entity_name'], $escapes)) return;

        $pgsql = $database->pgsql();
        $pg_class = $pgsql->pgClassArray($model->value['pg_class_id']);

        $attribute = $model->relation('Attribute')->order('name')->all();

        $create_migrate_file_path = Model::projectLaravelMigrateFilePath($project->user_project_setting, $model);
        $create_migrate_template_file_path = Model::laravelMigrationCreateTemplateFilePath();

        $pg_constraints = PwPgsql::pgConstraintValues($pg_class);

        $values = [];
        $values['project'] = $project->value;
        $values['model'] = $model;
        $values['attribute'] = $attribute;
        $values['unique'] = $pg_constraints['unique'];
        $values['foreign'] = $pg_constraints['foreign'];
        $values['primary'] = $pg_constraints['primary'];
        $values['className'] = "Create".ucfirst($model->value['name'])."Table";
        
        $contents = PwFile::bufferFileContetns($create_migrate_template_file_path, $values);
        file_put_contents($create_migrate_file_path, $contents);
    }

    /**
     * add VueJS
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public static function addVueJS($name, $options = null)
    {
        $cmd = 'composer require laravel/ui --dev';
        exec($cmd);
        $cmd = 'php artisan preset vue';
        exec($cmd);
    }

    /**
     * seed
     *
     * @return void
     */
    public static function seed()
    {
        $cmd = 'php artisan db:seed';
        exec($cmd);
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
        $cmd.= " ".self::$dev_null;
        return $cmd;
    }

    /**
     * artisan Controller
     *
     * @param string $type
     * @param string $name
     * @param array $options
     * @return string
     */
    public function artisanMakeCmd($type, $name, $options = null)
    {
        $this->cmd = '';
        if (!defined('COMAND_PHP_PATH')) exit('Not defined COMAND_PHP_PATH.');
        if ($this->path) $this->cmd = "cd {$this->path} && ";
        $this->cmd.= PwLaravel::cmdMake($type, $name, $options);
        return $this->cmd;
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
        $this->cmd = $this->artisanMakeCmd('controller', $name, $options);
        exec($this->cmd, $output, $return_var);
        dump($this->cmd);
        dump($output);
        dump($return_var);
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

    /**
     * migrateFunctions
     *
     * @param Attribute $attribute
     * @return void
     */
    static public function migrateFunctions($attribute)
    {
        if (!$attribute->values) return;
        foreach ($attribute->values as $attribute->value) {
            $result.= self::migrateFunction($attribute).';';
        }
        return $result;
    }

    /**
     * migrateFunctionName
     *
     * @param Attribute $attribute
     * @param array $value
     * @return string
     */
    static public function migrateFunction($attribute, $is_end_tag = false)
    {
        if (!$attribute->value) return;

        $params = [];
        $params['name'] = 'table';
        $params['function'] = self::migrateTypeFunctionName($attribute->value['type']);
        $params['value'] = $attribute->value['name'];
        $params['is_string_value'] = true;
        $tag =  PwTag::phpObjFunction($params);
        if (!self::isEscapeAttribute($attribute) && !$attribute->value['is_required']) {
            $tag.= PwTag::phpObjArrow().PwTag::phpFunction(['function' => 'nullable']);
        }
        if ($is_end_tag) $tag.= ';';
        return $tag;
    }

    /**
     * migrate function name
     *
     * @param string $type
     * @return void
     */
    static public function migrateTypeFunctionName($type)
    {
        return self::$migration_types[$type]['name'];
    }

    /**
     * migrate optional function name
     *
     * @param string key$
     * @return void
     */
    static public function migrateOptionalFunctionName($key)
    {
        //TODO
        if ($key != 'is_required') return 'nullable';
        //return self::$migration_optional_functions[$key]['name'];
    }

    /**
     * isNullablAttrivute
     *
     * @param Attribute $attribute
     * @return boolean
     */
    static public function isNullableAttribute($attribute)
    {
        return (in_array($attribute->value['name'], self::$nullable_attributes));
    }

    /**
     * isEscapeAttrivute
     *
     * @param Attribute $attribute
     * @return boolean
     */
    static public function isEscapeAttribute($attribute)
    {
        return (in_array($attribute->value['name'], self::$escape_columns));
    }

    /**
     * migrate class name
     *
     * @param Model $model
     * @return string;
     */
    static public function migrateClassName($model)
    {
        $class_name = "Create".ucfirst($model->value['name'])."Table";
        return $class_name;
    }

}
