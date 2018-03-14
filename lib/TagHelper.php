<?php
/**
 * TagHelper
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class TagHelper {
    static function baseUrl() {
        $url = '';
        if (isset($GLOBALS['controller'])) {
            $url = $GLOBALS['controller']->relative_base;
        }
        return $url;
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

}