<?php
/**
 * FileMnager.php
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 **/

class FileManager {
    public $base_dir;
    public $base_url;
    public $file;
    public $file_name;
    public $file_path;

    static $irregular_rules = array(
        'men'       =>  'man',
        'seamen'    =>  'seaman',
        'snowmen'   =>  'snowman',
        'women'     =>  'woman',
        'people'    =>  'person',
        'children'  =>  'child',
        'sexes'     =>  'sex',
        'moves'     =>  'move',
        'databases' =>  'database',
        'feet'      =>  'foot',
        'cruces'    =>  'crux',
        'oases'     =>  'oasis',
        'phenomena' =>  'phenomenon',
        'teeth'     =>  'tooth',
        'geese'     =>  'goose',
        'atlases'   =>  'atlas',
        'corpuses'  =>  'corpus',
        'genies'    =>  'genie',
        'genera'    =>  'genus',
        'graffiti'  =>  'graffito',
        'loaves'    =>  'loaf',
        'mythoi'    =>  'mythos',
        'niches'    =>  'niche',
        'numina'    =>  'numen',
        'octopuses' =>  'octopus',
        'opuses'    =>  'opus',
        'penises'   =>  'penis',
        'equipment' =>  'equipment',
        'information'   =>  'information',
        'rice'      =>  'rice',
        'money'     =>  'money',
        'species'   =>  'species',
        'series'    =>  'series',
        'fish'      =>  'fish',
        'sheep'     =>  'sheep',
        'swiss'     =>  'swiss',
        'staff'     =>  'staffs',
    );

    static $singular_rules = array(
        '(quiz)zes$'        =>  '$1',
        '(matr)ices$'       =>  '$1ix',
        '(vert|ind)ices$'   =>  '$1ex',
        '^(ox)en'       =>  '$1',
        '(alias|status)es$' =>  '$1',
        '(octop|vir)i$'     =>  '$1us',
        '(cris|ax|test)es$' =>  '$1is',
        '(shoe)s$'      =>  '$1',
        '(o)es$'        =>  '$1',
        '(bus)es$'      =>  '$1',
        '([m|l])ice$'       =>  '$1ouse',
        '(x|ch|ss|sh)es$'   =>  '$1',
        'movies$'       =>  'movie',
        'series$'       =>  'series',
        '([^aeiouy]|qu)ies$'    =>  '$1y',
        '([lr])ves$'        =>  '$1f',
        '(tive)s$'      =>  '$1',
        '(hive)s$'      =>  '$1',
        '([^f])ves$'        =>  '$1fe',
        '(^analy)ses$'      =>  '$1sis',
        '(analy|ba|diagno|parenthe|progno|synop|the)ses$' => '$1sis',
        '([ti])a$'      =>  '$1um',
        '(n)ews$'       =>  '$1ews',
        '(.)s$'         =>  '$1',
    );

    function __construct($path = null) {
        $this->base_dir = $path;
    }

    function setBaseDir($base_dir) {
        $this->base_dir = $base_dir;
        return $this;
    }

    function setFileName($file_name) {
        $this->file_name = $file_name;
        $this->loadFilePath();
        return $this;
    }

    function loadFilePath() {
        $this->file_path = $this->base_dir.$this->file_name;
        return $this;
    }

    function create($is_add = false) {
        FileManager::createDir($this->base_dir);
        $mode = ($is_add) ? 'a' : 'w';
        $this->file = fopen($this->file_path, $mode);
    }

    /**
     * output file
     *
     * @param array $contents
     **/
    function output($contents) {
        FileManager::createDir($this->base_dir);

        file_put_contents($this->file_path, $contents);

        $cmd = "mhmod 666 {$file}";
        exec($cmd);
    }

    function writeRow() {

    }

    /**
     * log files
     *
     * @param array
     **/
    static function logFiles() {
        if (!defined('LOG_DIR')) exit('not found LOG_DIR');

        if (defined('LOG_DIR') && file_exists(LOG_DIR)) {
            $log_files = self::loadFiles(LOG_DIR);
        }
        return $log_files;
    }

    /**
     * output log file
     *
     * @param string $content
     * @param string $file_name
     **/
    static function log($content, $file_name = null) {
        if (defined('LOG_DIR')) {
            $date = date('Ymd');
            if ($file_name) {
                $file_name = "{$file_name}_{$date}.log";
            } else {
                $file_name = "{$date}.log";
            }

            $now = date('Y-m-d H:i:s');
            $contents = "{$now}: {$content}\n";
            self::outputFile(LOG_DIR, $file_name, $contents, false, true);
        }
    }

    /**
     * output file
     *
     * @param string $output_dir
     * @param string $file_name
     * @param string $contents
     * @param bool $is_backup
     * @param bool $is_add
     **/
    static function outputFile($output_dir, $file_name, $contents, $is_backup = false, $is_add = false) {
        self::createDir($output_dir);

        $file = $output_dir.$file_name;

        //backup
        if ($is_backup && file_exists($file)) {
            $backup_dir = "{$output_dir}backup/";
            self::createDir($backup_dir);

            $rename_file = "{$backup_dir}{$file_name}";
            rename($file, $rename_file);
        }

        $mode = ($is_add) ? 'a' : 'w';
        $fp = fopen($file, $mode);
        flock($fp, LOCK_EX);
        fputs($fp, $contents);
        flock($fp, LOCK_UN);
        fclose($fp);

        $cmd = "mhmod 666 {$file}";
        exec($cmd);
    }

    /**
     * Image Extentions
     *
     * @return array
     */
    static function getImageExts() {
        $types['image/gif'] = "gif";
        $types['image/jpeg'] = "jpg";
        $types['image/png'] = "png";
        $types['image/x-gif'] = "gif";
        $types['image/x-jpeg'] = "jpg";
        $types['image/x-png'] = "png";
        $types['image/pjpeg'] = "jpg";
        return $types;
    }

    /**
     * Image file types
     *
     * @return array
     */
    static function getImageTypes() {
        $types['gif'] = "image/gif";
        $types['jpeg'] = "image/jpg";
        $types['jpg'] = "image/jpg";
        $types['png'] = "image/png";
        return $types;
    }

    /**
     * Image file type by file name
     *
     * @return array
     */
    static function getImageTypeWithFileName($file_name) {
        $ext = self::getFileExt($file_name);
        if ($ext) {
            $types = self::getImageTypes();
            return $types[$ext];
        }
    }

    /**
     * image extention
     *
     * @param string $file_type
     * @return array
     */
    static function getImageExt($file_type) {
        $types = self::getImageExts();
        return $types[$file_type];
    }

    /**
     * File list
     *
     * @param string $base_dir
     * @param array $params
     *
     * @return array
     */
    static function loadFiles($base_dir, $params=null) {
         $i = 0;
         if (file_exists($base_dir)) {
             if ($handler = opendir($base_dir)) {
                 while (($file_name = readdir($handler)) !== false) {
                     $file_path = "{$base_dir}{$file_name}";
                     if (is_file($file_path)) {
                        $i++;
                        $path_info = pathinfo($file_path);
                        $file['id'] = $i;
                        $file['filename'] = $path_info['filename'];
                        $file['extension'] = $path_info['extension'];
                        $file['name'] = $file_name;
                        $file['path'] = "{$base_dir}{$file['name']}";
                        if ($params['base_url']) {
                            $file['url'] = $params['base_url'].$file['name'];
                        }

                        if (file_exists($file['path'])) {
                            $file['createtime'] = filemtime($file['path']);
                            $file['created_at'] = date('Y-m-d H:i:s', $file['createtime']);
                        }
                        if ($file['createtime']) {
                            $create_times[] = $file['createtime'];
                        }
                        $files[] = $file;
                     }
                 }
 
                 closedir($handler);
                 if (is_array($files) && is_array($create_times)) {
                     array_multisort($create_times, SORT_DESC, $files);
                 }
             }
         }
         return $files;
    }

    /**
     * Upload file contents
     *
     * @param string $key
     * @return array
     */
    static function loadContents($key='file') {
        $files = $_FILES[$key];
        $path = $files['tmp_name'];
        if (file_exists($path)) {
            $values = file_get_contents($path);
        }
        return $values;
    }

    /**
     * Upload file path
     *
     * @param string $key
     * @return string
     */
    static function uploadFilePath($key='file') {
        $files = $_FILES[$key];
        $path = $files['tmp_name'];
        return $path;
    }

    /**
     * Upload file path
     *
     * @param string $key
     * @return string
     */
    static function uploadFileType($key='file') {
        $files = $_FILES[$key];
        return $files['type'];
    }

    /**
     * uploaded file name
     *
     * @param string $key
     * @return string
     */
    static function uploadFileName($key='file') {
        $files = $_FILES[$key];
        $name = $files['name'];
        return $name;
    }

    /**
     * uploaded image file extention
     *
     * @param string $key
     * @return string
     */
    static function uploadImageExt($key='file') {
        $files = $_FILES[$key];
        $ext = self::getImageExt($files['type']);
        dump($files);
        dump($ext);
        return $ext;
    }

    /**
     * upload file
     *
     * @param string $to_path
     * @param string $key
     * @return string
     */
    static function uploadFile($to_path, $key='file') {
        $files = $_FILES[$key];
        $from_path = $files['tmp_name'];
        if (file_exists($from_path) && is_file($from_path)) {
            self::removeFile($to_path);
            self::moveFile($files['tmp_name'], $to_path);
        }
    }

    /**
     * upload image file
     *
     * @param string $to_path
     * @param string $key
     *
     * @return void
     */
    static function uploadImage($to_path, $key='file') {
        $files = $_FILES[$key];
        $from_path = $files['tmp_name'];
        dump($files);
        dump($from_path);
        dump($to_path);
        if (file_exists($from_path) && is_file($from_path)) {
            self::removeFile($to_path);
            self::moveFile($files['tmp_name'], $to_path);
        }
    }

    /**
     * remove file
     *
     * @param string $path
     * @param bool $is_file_delete
     *
     * @return bool
     */
    static function removeFile($path, $is_file_delete=false) {
        if (file_exists($path)) {
            if (defined('PHP_FUNCTION_MODE') && PHP_FUNCTION_MODE) {
                if (is_dir($path)) {
                    if ($is_file_delete) {
                        $handle = opendir($path);
                        while($filename = readdir($handle)) {
                            if(!preg_match("/^\./", $filename)) {
                                unlink("{$path}/{$filename}");
                            }
                        }
                    }
                    $is_success = rmdir($path);
                } elseif(is_file($path)) {
                    $is_success = unlink($path);
                }
            } else {
                $cmd = "rm {$path}";
                exec($cmd);
            }
        }
        return $is_suceess;
    }


    /**
     * move file
     *
     * @param string $path
     * @param string $to_path
     *
     * @return bool
     */
    static function moveFile($from_path, $to_path) {
        if (file_exists($from_path)) {
            if (defined('PHP_FUNCTION_MODE') && PHP_FUNCTION_MODE) {
                $is_success = rename($from_path, $to_path);
                $is_success = chmod($to_path, 0755);
            } else {
                $cmd = "mv {$from_path} {$to_path}";
                exec($cmd);
                $cmd = "chmod 777 {$to_path}";
                exec($cmd);
                return true;
            }
        }
    }

    /**
     * remove image file(multi extention)
     *
     * @param string $path
     * @param string $to_path
     *
     * @return void
     */
    static function removeSameImagesFileExt($path) {
        $types = self::getImageExt();
        foreach ($types as $type => $ext) {
            $path = "{$path}.{$ext}";
            if (file_exists($path)) {
                if (defined('PHP_FUNCTION_MODE') && PHP_FUNCTION_MODE) {
                    unlink($path);
                } else {
                    $cmd = "rm {$path}";
                    exec($cmd);
                }
            }
        }
    }

    /**
     * is image
     *
     * @param string $file
     *
     * @return string
     */
    static function isImage($file) {
        $ext = self::getImageExt($file);
        return $ext;
    }

   /**
    * create dir
    *
    * @param  array $path
    * @return void
    */ 
    static function createDir($path, $chmod = '777') {
        if (!file_exists($path)) {
            if (defined('PHP_FUNCTION_MODE') && PHP_FUNCTION_MODE) {
                $is_suceess = mkdir($path);
                dump($path);
                dump($is_suceess);

                $is_success = chmod($path, 0777);
                dump($path);
                dump($is_suceess);
                return $is_success;
            } else {
                $cmd = "mkdir -p {$path}";
                exec($cmd);
                $cmd = "chmod {$chmod} {$path}";
                exec($cmd);
            }
        }
    }

   /**
    * chmod
    *
    * @param  array $path
    * @return void
    */ 
    static function chmodFile($path) {
        if (file_exists($path)) {
            if (defined('PHP_FUNCTION_MODE') && PHP_FUNCTION_MODE) {
                $is_success = chmod($path, 0777);
                dump($path);
                dump($is_suceess);
                return $is_success;
            } else {
                $cmd = "chmod 777 {$path}";
                exec($cmd);
            }
        }
    }

   /**
    * file base name
    *
    * @param  array $path
    * @return void
    */ 
    static function getFileBaseName($path) {
        return pathinfo($path, PATHINFO_BASENAME);
    }

   /**
    * file name
    *
    * @param  array $path
    * @return void
    */ 
    static function getFileName($path) {
        return pathinfo($path, PATHINFO_FILENAME);
    }

   /**
    * file extention
    *
    * @param  array $path
    * @return void
    */ 
    static function getFileExt($path) {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * zipArchive
     *
     * @param string $base_dir
     * @param string $dir_name
     * @param string $zip_file_name
     * @param boolean $is_delete_dir
     * @param boolean
     *
     **/
    static function zipArchive($base_dir, $dir_name, $zip_file_name = null, $is_delete_dir = true) {
        $result = false;
        if (is_null($zip_file_name)) $zip_file_name = "{$dir_name}.zip";
        $archive_dir = "{$base_dir}{$dir_name}";
        $zip_file_path = "{$base_dir}{$zip_file_name}";
        if (file_exists($zip_file_path)) unlink($zip_file_path);

        if (file_exists($archive_dir)) {
            $zip_file = "{$base_dir}{$zip_file_name}";
            $cmd = "cd {$base_dir} && zip {$zip_file_name} {$dir_name}/*";
            $result = exec($cmd);

            if ($is_delete_dir)  {
                $cmd = "rm -rf {$archive_dir}";
                exec($cmd);
            }
        }
        return $result;
    }

    /**
     * output download header
     * 
     * @param  string $file_name
     * @return void
     */
    static function outputDownloadHeader($file_name) {
        if (FileManager::isIE()) {
            $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
        }
        header("Content-type: application/octet-stream; name=\"{$file_name}\"");
        header("Content-Disposition: Attachment; filename=\"{$file_name}\""); 
        header('Pragma: private');
        header('Cache-control: private, must-revalidate');
    }

    /**
     * output csv line
     *
     * @param array $values
     * @return void
     */
    static function outputCsvLine($values) {
        $line = implode(',', $values);
        $line.="\r\n";
        $line = mb_convert_encoding($line, 'SJIS', 'UTF-8');
        echo($line);
    }

    /**
     * is IE
     *
     **/
    static function isIE() {
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * download file
     *
     **/
    static function downloadFile($file_path, $file_name) {
        if (file_exists($file_path)) {
            if (FileManager::isIE($_SERVER)) {
                $file_name = mb_convert_encoding($file_name, 'SJIS', 'UTF-8');
            }
            header("Content-Disposition: Attachment; filename=\"{$file_name}\""); 
            header("Content-type: application/octet-stream; name=\"{$file_name}\"");
            header('Pragma: private');
            header('Cache-control: private, must-revalidate');
            readfile($file_path);
        } else {
            exit('Not found file');
        }
    }

    /**
     * download file by url
     *
     * @param string $url
     * @param string $path
     * @return void
     **/
    static function downloadToPath($url, $path) {
        $values = file_get_contents($url);
        if ($values) file_put_contents($path, $values);
    }

    /**
     * download contents
     *
     * @param string $file_name
     * @param string $contents
     * @return void
     **/
    static function downloadContents($file_name, $contents) {
        FileManager::outputDownloadHeader($file_name);
        echo($contents);
        exit;
    }

   /**
    * file list
    *
    * @param  array $path
    * @return void
    */ 
    public function fileList($page = 0) {
        $file_count = 12;
        $i = 0;
        $count = 0;
        if (file_exists($this->base_dir)) {
            if ($handler = opendir($this->base_dir)) {
                while (($row = readdir($handler)) !== false) {
                    $file_path = "{$this->base_dir}{$row}";
                    if (is_file($file_path)) {
                        $i++;
                        $file['id'] = $i;
                        $file['name'] = $row;
                        $file['path'] = "{$this->base_dir}{$file['name']}";

                        if (file_exists($file['path'])) {
                            $file['createtime'] = filectime($file['path']);
                            $file['created_at'] = date('Y-m-d H:i:s', $file['createtime']);
                            $log = "{$file['file_name']}={$file['created_at']}";
                        }
                        if ($file['createtime']) {
                            $create_times[] = $file['createtime'];
                        }
                        $files[] = $file;
                        $log = "{$file['file_name']}:{$file['created_at']}";
                        dump($log);
                    }
                }

                closedir($handler);

                if (is_array($files)) {
                    array_multisort($create_times, SORT_DESC, $files);
                    foreach ($files as $key => $file) {
                        $j++;
                        if ($j <= 12) {
                            $visible_files[] = $file;
                        } else {
                            break;
                        }
                    }
                    $this->values = $visible_files;
                    $this->count = $i;
                    $this->page_count = ceil($i / $file_count);
                }

            }
        }
        return $this;
    }

    /**
     * excape line break
     *
     * @param string $value
     * @param string $to
     * @return string
     */
    static function escapeLinebreak($value, $to = "\\n") {   
        return preg_replace("/\r\n|\r|\n/", $to, $value);
    }

    /**
     * singular to plural
     *
     * @param string $singular
     * @return string
     */
    static function singularToPlural($singular) {
        $values = explode('_', $singular);

        $last_value = end($values);
        $last_index = key($values);

        if (strlen($last_value) == 1) {

        } else if (!preg_match("/s$/", $last_value)) {
            $last_value = preg_replace("/(s|sh|ch|o|x)$/", "$1es" ,$last_value);
            $last_value = preg_replace("/(f|fe)$/","ves", $last_value);
            $last_value = preg_replace("/(a|i|u|e|o)y$/", "$1ys" ,$last_value);
            $last_value = preg_replace("/y$/","ies", $last_value);

            $last_value = $last_value."s";
        }

        $values[$last_index] = $last_value;
        $result = implode('_', $values);
        return $result;
    }

    /**
     * plural to singular
     *
     * @param string $plural
     * @return string
     */
    static function pluralToSingular($plural) {
        $irregular_rules = self::$irregular_rules;
        $singular_rules = self::$singular_rules;

        $values = explode('_', $plural);
        $last_value = end($values);
        $last_index = key($values);

        if (array_key_exists(strtolower($last_value), $irregular_rules)) {
            $last_value = $irregular_rules[strtolower($last_value)];
        } else {
            foreach($singular_rules as $key => $singular_rule) {
                $reg = '/' . $key . '/';
                if (preg_match($reg, $last_value)) {
                    $last_value = preg_replace($reg, $singular_rule, $last_value);
                    break;
                }
            }
        }
        $values[$last_index] = $last_value;

        $result = implode('_', $values);
        return $result;
    }

    /**
     * phpClassNameFromEntityName
     *
     * @param string $entity_name
     * @return void
     */
    static function phpClassNameFromEntityName($entity_name) {
        $name = FileManager::pluralToSingular($entity_name);
        $class_name = FileManager::phpClassName($name);
        return $class_name;
    }

    /**
     * phpClassName
     *
     * @param string $name
     * @return void
     */
    static function phpClassName($name) {
        $names = explode('_', $name);
        if (is_array($names)) {
            foreach ($names as $key => $value) {
                $class_name .= ucwords($value);
            }
        } else {
            $class_name = ucwords($name);
        }
        return $class_name;
    }

    /**
     * bufferFileContetns
     *
     * @param string $path
     * @param array $values
     * @return array
     */
    static function bufferFileContetns($path, $values) {
        if (file_exists($path)) {
            ob_start();
            include $path;
            $contents = ob_get_contents();
            ob_end_clean();
        }
        return $contents;
    }


   /**
    * convrt jpeg to png
    *
    * @param string $path
    * @return void
    */ 
    static public function convertJpgToPng($path) {
        $image = @imagecreatefromjpeg($path);
        imagepng($image, $path);
    }

    /**
     * thread exec
     *
     * @return void
     */
    static function threadExec($path, $params = null) {
        if (file_exists($path)) {
            if ($params) {
                foreach ($params as $key => $param) {
                    if (is_array($param)) {
                        $param = json_encode($param);
                    }
                    $params[$key] = "'{$param}'";
                }
                $param = implode(' ', $params);
            }
            $cmd = COMAND_PHP_PATH." {$path} {$param} > /dev/null &";
            dump($cmd);
            exec($cmd);
        }
    }
}