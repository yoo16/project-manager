<?php
require_once 'vo/_View.php';

class View extends _View {

    function validate() {
        parent::validate();
    }

    /**
     * local path
     * 
     * @param string $name
     * @return string
     */
    static function localFilePath($name) {
        if (!$name) return;
        $path = VIEW_DIR.$name;
        return $path;
    }

}