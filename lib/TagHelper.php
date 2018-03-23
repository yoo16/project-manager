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
            $url = $GLOBALS['controller']->relative_base;
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

    static function image($image_name, $image_dir = 'images') {
        $base = $GLOBALS['controller']->base;
        $url = "{$base}{$image_dir}/{$image_name}";
        return $url;
    }

    static function fileUrl($dir_name, $name, $ext) {
        $base = self::baseUrl();
        $url = "{$base}{$dir_name}/{$name}.{$ext}";
        $url = self::serialUrl($url);
        return $url;
    }

    static function serialUrl($url) {
        $serial = time();
        $url = "{$url}?serial={$serial}";
        return $url;
    }

    static function base() {
        $controller = $GLOBALS['controller'];
        if (is_null($controller->relative_base)) {
            return "<base href=\"{$controller->base}\">\n";
        }
    }

    static function javascript($name, $attributes = null, $dir_name = 'javascripts', $ext = 'js') {
        if (!$name) return;
        $href = self::fileUrl($dir_name, $name, $ext);
        $attributes['src'] = self::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/javascript';
        return "<script type=\"text/javascript\" src=\"{$href}\"></script>\n";
    }

    static function stylesheet($name, $attributes = null, $dir_name = 'stylesheets', $ext = 'css') {
        $attributes['href'] = self::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        return FormHelper::singleTag('link', $attributes);
    }

    static function stylesheetPrint($name, $attributes = null, $dir_name = 'stylesheets', $ext = 'css') {
        if (!$name) return;
        $href = self::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        $attributes['media'] = 'print';
        return  FormHelper::singleTag('link', $attributes);
    }

    static function metaContentType($content_type = '') {
        if (!$content_type && $GLOBALS['controller']) $content_type = $GLOBALS['controller']->content_type();
        return "<meta http-equiv=\"Content-Type\" content=\"{$content_type}\">\n";
    }

    static function metaJavascript() {
        return "<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">\n";
    }

    static function metaStylesheet() {
        return "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
    }

    /**
    * convertDisplay
    *
    *  カラー表示
    *
    *  @param  String $color
    *  @return String
    */
    static function color($color) {
        if ($color) {
            $color = TagHelper::convertColorForSharp($color);
            $value = "<span class=\"badge p-2\" style=\"background-color: {$color}\">{$color}</span>";
            return $value;
        }
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
}