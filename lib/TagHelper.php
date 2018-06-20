<?php
/**
 * TagHelper
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class TagHelper {

    /**
     * base url
     *
     * @return String
     */
    static function baseUrl() {
        $url = '';
        if (isset($GLOBALS['controller'])) {
            $controller = $GLOBALS['controller'];
            if (isset($controller->relative_base)) {
                $url = $controller->relative_base;
            } else if (is_array($controller)) {
                $url = $controller['relative_base'];
            }
        }
        return $url;
    }

    /**
    * urlFor
    *
    * @param String $action
    * @param Integer $id
    * @param params $params
    * @return string
    */
    static function urlFor($action = null, $id = null, $params = null) {
        if ($controller = $GLOBALS['controller']) {
            $controller->urlFor($controller->name, $action, $id, $params);
        }
    }

    /**
     * image url
     * 
     * @return String
     */
    static function image($image_name, $image_dir = 'images') {
        $base = $GLOBALS['controller']->base;
        $url = "{$base}{$image_dir}/{$image_name}";
        return $url;
    }

    /**
     * fileUrl
     * 
     * @return String
     */
    static function fileUrl($dir_name, $name, $ext) {
        $base = TagHelper::baseUrl();
        $url = "{$base}{$dir_name}/{$name}.{$ext}";
        $url = TagHelper::serialUrl($url);
        return $url;
    }

    /**
     * serialUrl
     * 
     * @return String
     */
    static function serialUrl($url) {
        $serial = time();
        $url = "{$url}?serial={$serial}";
        return $url;
    }

    /**
     * base tag
     * 
     * @return String
     */
    static function base() {
        $controller = $GLOBALS['controller'];
        if (is_null($controller->relative_base)) {
            return "<base href=\"{$controller->base}\">\n";
        }
    }

    /**
     * javascript tag
     * 
     * @param  String $name
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function javascript($name, $dir_name = 'javascripts', $ext = 'js') {
        if (!$name) return;
        $href = TagHelper::fileUrl($dir_name, $name, $ext);
        return "<script type=\"text/javascript\" src=\"{$href}\"></script>\n";
    }

    /**
     * javascript controller tag
     * 
     * @param  String $name
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function javascriptController($name, $dir_name = 'javascripts/controllers', $ext = 'js') {
        if (!$name) return;
        $href = TagHelper::fileUrl($dir_name, $name, $ext);
        $file_name = "{$name}.{$ext}";
        $path = BASE_DIR."public/javascripts/controllers/{$file_name}";
        if (file_exists($path)) {
            return "<script type=\"text/javascript\" src=\"{$href}\"></script>\n";
        }
    }

    /**
     * stylesheet controller tag
     * 
     * @param  String $name
     * @param  Array $attributes
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function stylesheet($name, $attributes = null, $dir_name = 'stylesheets', $ext = 'css') {
        $attributes['href'] = TagHelper::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        return FormHelper::singleTag('link', $attributes);
    }

    /**
     * print stylesheet controller tag
     * 
     * @param  String $name
     * @param  Array $attributes
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function stylesheetPrint($name, $attributes = null, $dir_name = 'stylesheets', $ext = 'css') {
        if (!$name) return;
        $href = TagHelper::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        $attributes['media'] = 'print';
        return  FormHelper::singleTag('link', $attributes);
    }

    /**
     * meta content_type
     * 
     * @param  String $name
     * @param  Array $attributes
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function metaContentType($content_type = '') {
        if (!$content_type && $GLOBALS['controller']) $content_type = $GLOBALS['controller']->content_type();
        return "<meta http-equiv=\"Content-Type\" content=\"{$content_type}\">\n";
    }

    /**
     * meta content_type
     * 
     * @param  String $name
     * @param  Array $attributes
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function metaJavascript() {
        return "<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">\n";
    }

    /**
     * meta Stylesheet
     * 
     * @param  String $name
     * @param  Array $attributes
     * @param  String $dir_name
     * @param  String $ext
     * @return String
     */
    static function metaStylesheet() {
        return "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
    }

    /**
    * display color
    *
    * @param  String $color
    * @return String
    */
    static function color($color) {
        if ($color) {
            $color = TagHelper::convertColorForSharp($color);
            $value = "<span class=\"badge p-2\" style=\"background-color: {$color}\">{$color}</span>";
            return $value;
        }
    }

    /**
    * display plot
    *
    *  plot
    *
    * @param  String $x
    * @param  String $y
    * @return String
    */
    static function plot($x, $y) {
        $value = "({$x}, {$y})";
        return $value;
    }

    /**
     * 16進カラーのシャープ付き変換
     *
     * TODO Class
     *
     * @param  String $color
     * @return String
     */
    static function convertColorForSharp($color) {
        if ($color) {
            $pos = strpos($color, '#');
            if (!is_numeric($pos)) $color = "#{$color}";
            return $color;
        }
    }

    /**
     * icon tag
     * 
     * @param  Array $params
     * @return String
     */
    static function aTag($params) {
        if ($params['is_use_selected']) {
            if ($params['is_selected']) $params['class'].= ' active';
            if ($params['selected_key'] && $params['selected_key'] == $params['selected_value']) $params['class'].= ' active';
        }
        $escape_columns = ['label', 'icon_name', 'http_params', 'is_use_selected', 'is_confirm', 'is_check_delete'];

        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if (!in_array($key, $escape_columns)) {
                    $attributes[] = "{$key}=\"{$value}\"";
                }
            }
        }
        if ($attributes) $attribute = implode(' ', $attributes);

        if (isset($params['icon_name'])) $icon_tag = TagHelper::iconTag($params['icon_name']);
        $tag = "<a {$attribute}>{$icon_tag}{$params['label']}</a>";
        return $tag;
    }

    /**
     * icon tag
     * 
     * @param  String $name
     * @return String
     */
    static function iconTag($name) {
        if ($name) {
            $icon_class_name = "fa fa-{$name}";
            $icon_tag = "<i class=\"{$icon_class_name}\"></i>&nbsp;";
        }
        return $icon_tag;
    }
    
    /**
     * active
     *
     * @param string $key
     * @param string $selected
     * @return string
     */
    static function clasActive($key, $selected = null) {
        if ($key == $selected) {
            $tag.=' active';
        }
        return $tag;
    }

}