<?php
class PwLaravel
{
    public $settings;
    public $path;
    public $project;
    public $database;
    public $model;
    public $options;
    public $commands = [];
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

    static public $guarded_columns = [
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
        'datetime' => ['name' => 'dateTime'],
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
        if ($params['project']) $this->project = $params['project'];
        if ($params['database']) $this->database = $params['database'];
        if ($params['model']) $this->model = $params['model'];
        if ($params['options']) $this->options = $params['options'];
    }

    /**
     * command
     *
     * @param string $cmd
     * @return void
     */
    public function exec($cmd)
    {
        exec($cmd, $output, $return_var);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return_var'] = $return_var;

        dump($cmd);
        $this->commands[] = $results;
        return $results;
    }

    private static function addCmdOption($options)
    {
        if ($options) return " " . implode(' ', $options);
    }

    /**
     * artisan command
     *
     * @param string $main
     * @param string $name
     * @param array $options
     * @return string
     */
    public function artisanCmd($main, $name = null, $options = null)
    {
        if (!defined('COMAND_PHP_PATH')) exit('Not defined COMAND_PHP_PATH.');
        $cmd = '';

        if ($this->path) $cmd = "cd {$this->path} && ";

        $cmd .= COMAND_PHP_PATH . " artisan {$main}";
        if ($name) $cmd .= " {$name}";
        $cmd .= $this->addCmdOption($options);
        $cmd .= " " . self::$dev_null;
        return $this->exec($cmd);
    }

    /**
     * artisan make command
     *
     * @param string $type
     * @param string $name
     * @param array $options
     * @return string
     */
    public function artisanMakeCmd($type, $name, $options = null)
    {
        $main = "make:{$type}";
        return $this->artisanCmd($main, $name, $options);
    }

    /**
     * create project
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function createProject($name, $options = null)
    {
        $cmd = "composer create-project laravel/laravel {$name} --prefer-dist";
        $cmd .= self::addCmdOption($options);
        $this->exec($cmd);
    }

    /**
     * exportModels
     * 
     * @return void
     */
    function exportModels()
    {
        if (!$this->project->value) return;
        if (!$this->model->values) return;
        foreach ($this->model->values as $this->model->value) {
            $this->exportModel();
        }
    }

    /**
     * export Migrate
     * 
     * @return array
     */
    function exportMigrate($settings)
    {
        $migrate_file_path = self::projectMigrateFilePath($this->path, $this->model);
        if (!file_exists($migrate_file_path)) {
            $migrate_template_path = self::migrateCreateTemplatePath();
            $contents = PwFile::bufferFileContetns($migrate_template_path, $settings);
            file_put_contents($migrate_file_path, $contents);

            $this->results['migrate']['path'] = $migrate_file_path;
            $this->results['migrate']['template'] = $migrate_template_path;
            return $this->results;
        }
    }


    /**
     * export laravel model
     * 
     * @return array
     */
    public function exportController()
    {
        $model_name = $this->model->modelName();
        $controller_name = "{$model_name}Controller";
        $options[] = "--resource";

        return $this->makeController($controller_name, $options);
    }

    /**
     * export Eloquent
     * 
     * @return array
     */
    public function exportEloquent($settings)
    {
        $eloquent_file_path = self::projectEloquentFilePath($this->path, $this->model);
        if (!file_exists($eloquent_file_path)) {
            $eloquent_template_path = self::laravelEloquentTemplatePath();
            $contents = PwFile::bufferFileContetns($eloquent_template_path, $settings);
            file_put_contents($eloquent_file_path, $contents);

            $this->results['eloquent']['path'] = $eloquent_file_path;
            $this->results['eloquent']['template'] = $eloquent_template_path;
            return $this->results;
        }
    }

    /**
     * export laravel model
     * 
     * @return array
     */
    public function exportModel()
    {
        if (!$this->database) return;
        if (!$this->model->value) return;

        $this->loadModelSettings();
        if (!$this->settings['model']) return;

        $settings = $this->settings['model'];

        //migrate
        $this->exportMigrate($settings);

        //Eloquent
        $this->exportEloquent($settings);

        //Request
        $this->makeRequest($this->requestName($this->model));

        //Factory
        $this->makeFactory($this->factoryName($this->model));

        //Seeder
        $this->makeSeeder($this->seederName($this->model));

        //migrate
        if ($this->options['is_migrate']) $this->migrate();

        $this->results['commands'] = $this->commands;
        return $this->results;
    }

    /**
     * Model Requret name
     *
     * @return void
     */
    function requestName($model)
    {
        return $model->modelName() . "Request";
    }

    /**
     * Factory name
     *
     * @return void
     */
    function factoryName($model)
    {
        return $model->modelName() . "Factory";
    }

    /**
     * Seeder name
     *
     * @return void
     */
    function seederName($model)
    {
        return ucfirst($model->value['name']) . "TableSeeder";
    }

    /**
     * migrate class name
     *
     * @param Model $model
     * @return string;
     */
    public function migrateClassName($model)
    {
        return "Create" . ucfirst($model->value['name']) . "Table";
    }

    /**
     * loadModelSettings
     *
     * @return void
     */
    function loadModelSettings()
    {
        //TODO
        $escapes = ['migration'];
        if (in_array($this->model->value['entity_name'], $escapes)) return;

        $values = [];
        $values['project'] = $this->project->value;
        $values['model'] = $this->model;
        $values['attribute'] = $this->model->relation('Attribute')->order('name')->all();

        $pgsql = $this->database->pgsql();
        $pg_class = $pgsql->pgClassArray($this->model->value['pg_class_id']);
        $pg_constraints = PwPgsql::pgConstraintValues($pg_class);
        if ($pg_constraints) {
            $values['unique'] = $pg_constraints['unique'];
            $values['foreign'] = $pg_constraints['foreign'];
            $values['primary'] = $pg_constraints['primary'];
        }

        $values['model_name'] = $this->model->modelName();
        $values['migrate_class_name'] = $this->migrateClassName($this->model);

        $this->settings['model'] = $values;
    }


    /**
     * add VueJS
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function addVueJS($name, $options = null)
    {
        $cmd = 'composer require laravel/ui --dev';
        exec($cmd);

        return $this->artisanCmd('preset', 'vue', $options);
    }

    /**
     * seed
     *
     * @return void
     */
    public function seed($options = null)
    {
        //TODO --class options
        return $this->artisanCmd('db:seed');
    }

    /**
     * command migrate
     *
     * @param string $type
     * @param string $name
     * @param array $options
     * @return void
     */
    public function migrate($options = null)
    {
        return $this->artisanCmd('migrate');
    }

    /**
     * artisan make request
     *
     * @param string $name
     * @param array $options
     * @return return
     */
    public function makeRequest($name, $options = null)
    {
        return $this->artisanMakeCmd('request', $name, $options);
    }

    /**
     * artisan make Seeder
     *
     * @param string $name
     * @param array $options
     * @return return
     */
    public function makeSeeder($name, $options = null)
    {
        return $this->artisanMakeCmd('seeder', $name, $options);
    }

    /**
     * artisan make Factory
     *
     * @param string $name
     * @param array $options
     * @return return
     */
    public function makeFactory($name, $options = null)
    {
        return $this->artisanMakeCmd('factory', $name, $options);
    }


    /**
     * artisan make Controller
     *
     * @param string $name
     * @param array $options
     * @return array
     */
    public function makeController($name, $options = null)
    {
        return $this->artisanMakeCmd('controller', $name, $options);
    }

    /**
     * artisan make Controller
     *
     * @param string $model_name
     * @param array $options
     * @return void
     */
    public function makeModelController($model_name, $options = null)
    {
        $controller_name = "{$model_name}Controller";
        $options[] = "--model={$model_name}";
        $options[] = "--resource";

        return $this->artisanMakeCmd('controller', $controller_name, $options);
    }

    /**
     * artisan make Controller
     *
     * @param string $model_name
     * @param array $options
     * @return void
     */
    public function makeModel($model_name, $options = null)
    {
        return $this->artisanMakeCmd('model', $model_name, $options);
    }

    /**
     * remove Controller
     *
     * @param string $name
     * @return void
     */
    public function removeController($name)
    {
        PwFile::removeFile($this->controllerPath($name));
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
        $contents = "<?php" . PHP_EOL . $contents;

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
        $result = '';
        if (!$attribute->values) return;
        foreach ($attribute->values as $attribute->value) {
            $result .= self::migrateFunction($attribute) . ';';
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
        if (!self::isGuardedAttribute($attribute) && !$attribute->value['is_required']) {
            $tag .= PwTag::phpObjArrow() . PwTag::phpFunction(['function' => 'nullable']);
        }
        if ($is_end_tag) $tag .= ';';
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
    static public function isGuardedAttribute($attribute)
    {
        $value = '';
        if ($attribute->value) {
            $value = $attribute->value['name'];
        } else if ($attribute['name']) {
            $value = $attribute['name'];
        }
        if ($value) return in_array($value, self::$guarded_columns);
    }

    /**
     * project migrate path
     * 
     * @param array $path
     * @param Model $model
     * @return string
     */
    static function projectMigrateFilePath($path, $model)
    {
        if (!$path) return;
        if (!$model->value['name']) return;
        if (!file_exists($path)) return;

        $name = $model->value['name'];
        $date_string = date('Y_m_d_000000');
        $file_name = "{$date_string}_create_{$name}_table.php";
        $dir = "{$path}database/migrations/";
        if (!file_exists($dir)) PwFile::createDir($dir);
        $path = "{$dir}{$file_name}";
        return $path;
    }

    /**
     * project Eloquent path
     * 
     * @param array path$
     * @param Model $model
     * @param array $options
     * @return string
     */
    static function projectEloquentFilePath($path, $model, $options = null)
    {
        if (!$path) return;
        if (!$model->value['name']) return;
        if (!file_exists($path)) return;

        $name = $model->modelName();
        $file_name = "{$name}.php";
        //TODO options
        $dir = "{$path}app/";
        $path = "{$dir}{$file_name}";
        return $path;
    }

    /**
     * laravel migration template path
     * 
     * @return string
     */
    static function migrateCreateTemplatePath()
    {
        $path = TEMPLATE_DIR . 'laravel/migrate/create.phtml';
        return $path;
    }

    /**
     * laravel eloquent template path
     * 
     * @return string
     */
    static function laravelEloquentTemplatePath()
    {
        $path = TEMPLATE_DIR . 'laravel/model/eloquent.phtml';
        return $path;
    }
}
