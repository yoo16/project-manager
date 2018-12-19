<?php
/**
 * PwTag
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class PwTag {

    /**
     * base url
     *
     * @return string
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
    * @param array $params
    * @param array $http_params
    * @return string
    */
    static function urlFor($params, $http_params = null) {
        if ($controller = $GLOBALS['controller']) {
            $url = $controller->urlFor($params, $id, $http_params);
            return $url;
        }
    }

    /**
     * image url
     * 
     * @return string
     */
    static function image($image_name, $image_dir = 'images') {
        $base = $GLOBALS['controller']->base;
        $url = "{$base}{$image_dir}/{$image_name}";
        return $url;
    }

    /**
     * fileUrl
     * 
     * @return string
     */
    static function fileUrl($dir_name, $name, $ext) {
        $base = PwTag::baseUrl();
        $url = "{$base}{$dir_name}/{$name}.{$ext}";
        $url = PwTag::serialUrl($url);
        return $url;
    }

    /**
     * serialUrl
     * 
     * @return string
     */
    static function serialUrl($url) {
        $serial = time();
        $url = "{$url}?serial={$serial}";
        return $url;
    }

    /**
     * base tag
     * 
     * @return string
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
     * @param  string $name
     * @param  string $dir_name
     * @param  string $ext
     * @return string
     */
    static function javascript($name, $dir_name = 'javascripts', $ext = 'js') {
        if (!$name) return;
        $href = PwTag::fileUrl($dir_name, $name, $ext);
        return "<script type=\"text/javascript\" src=\"{$href}\"></script>\n";
    }

    /**
     * javascript controller tag
     * 
     * @param  string $name
     * @param  string $dir_name
     * @param  string $ext
     * @return string
     */
    static function javascriptController($name, $dir_name = 'javascripts/controllers', $ext = 'js') {
        if (!$name) return;
        $href = PwTag::fileUrl($dir_name, $name, $ext);
        $file_name = "{$name}.{$ext}";
        $path = BASE_DIR."public/javascripts/controllers/{$file_name}";
        if (file_exists($path)) {
            return "<script type=\"text/javascript\" src=\"{$href}\"></script>\n";
        }
    }

    /**
     * stylesheet controller tag
     * 
     * @param  string $name
     * @param  array $attributes
     * @param  string $dir_name
     * @param  string $ext
     * @return string
     */
    static function stylesheet($name, $attributes = null, $dir_name = 'stylesheets', $ext = 'css') {
        $attributes['href'] = PwTag::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        return PwForm::singleTag('link', $attributes);
    }

    /**
     * print stylesheet controller tag
     * 
     * @param  string $name
     * @param  array $attributes
     * @param  string $dir_name
     * @param  string $ext
     * @return string
     */
    static function stylesheetPrint($name, $attributes = null, $dir_name = 'stylesheets', $ext = 'css') {
        if (!$name) return;
        $href = PwTag::fileUrl($dir_name, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        $attributes['media'] = 'print';
        return  PwForm::singleTag('link', $attributes);
    }

    /**
     * meta content_type
     * 
     * @param  string $content_type
     * @return string
     */
    static function metaContentType($content_type = '') {
        if (!$content_type && $GLOBALS['controller']) $content_type = $GLOBALS['controller']->content_type();
        return "<meta http-equiv=\"Content-Type\" content=\"{$content_type}\">\n";
    }

    /**
     * meta content_type
     * 
     * @return string
     */
    static function metaJavascript() {
        return "<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">\n";
    }

    /**
     * meta Stylesheet
     * 
     * @return string
     */
    static function metaStylesheet() {
        return "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
    }

    /**
    * display color
    *
    * @param  string $color
    * @return string
    */
    static function color($color) {
        if ($color) {
            $color = PwTag::convertColorForSharp($color);
            $value = "<span class=\"badge p-2\" style=\"background-color: {$color}\">{$color}</span>";
            return $value;
        }
    }

    /**
    * display plot
    *
    *  plot
    *
    * @param  string $x
    * @param  string $y
    * @return string
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
     * @param  string $color
     * @return string
     */
    static function convertColorForSharp($color) {
        if ($color) {
            $pos = strpos($color, '#');
            if (!is_numeric($pos)) $color = "#{$color}";
            return $color;
        }
    }

    /**
     * attribute
     *
     * @param string $params
     * @return string
     */
    static function attribute($params) {
        if (is_array($params)) {
            $escape_columns = ['label', 'icon_name', 'http_params', 'is_use_selected', 'is_confirm', 'is_check_delete'];
            foreach ($params as $key => $value) {
                if (!in_array($key, $escape_columns)) {
                    $attributes[] = "{$key}=\"{$value}\"";
                }
            }
            if ($attributes) {
                $attribute = implode(' ', $attributes);
                return $attribute;
            }
        }
    }

    /**
     * A tag
     * 
     * @param  array $params
     * @return string
     */
    static function a($params) {
        if (!$params['label'] && !$params['icon_name']) $params['label'] = 'Link';
        if ($params['is_use_selected']) {
            if ($params['is_selected']) $params['class'].= ' active';
            if ($params['selected_key'] && $params['selected_key'] == $params['selected_value']) $params['class'].= ' active';
        }
        unset($html_params['menu_group']);
        unset($html_params['is_use_selected']);
        unset($html_params['is_use_action_selected']);

        $attribute = PwTag::attribute($params);
        if (isset($params['icon_name'])) $icon_tag = PwTag::iconTag($params['icon_name']);
        $tag = "<a {$attribute}>{$icon_tag}{$params['label']}</a>";
        return $tag;
    }

    /**
     * img tag
     * 
     * @param  array $params
     * @return string
     */
    static function img($params) {
        $escape_columns = ['label', 'name'];
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if (!in_array($key, $escape_columns)) {
                    $attributes[] = "{$key}=\"{$value}\"";
                }
            }
        }
        if ($attributes) $attribute = implode(' ', $attributes);
        $tag = "<img {$attribute}>";
        return $tag;
    }

    /**
    * reverse link
    *
    * @param string $controller
    * @param string $action
    * @param array $params
    * @param boolean $is_active
    * @return string
    */ 
    static function reverse($controller, $action, $params, $is_active) {
        if ($params['label']) $labels = $params['label'];
        if (!$labels['valid']) $labels['valid'] = LABEL_TRUE;
        if (!$labels['invalid']) $labels['invalid'] = LABEL_FALSE;

        if ($controller && $action) $params['href'] = Controller::url($controller, $action, null, $params['http_params']);

        if ($is_active) {
            $params['label'] = "<span class=\"btn btn-sm btn-danger\">{$labels['valid']}</span>";
        } else {
            $params['label'] = "<span class=\"btn btn-sm btn-outline-primary btn-sm\">{$labels['invalid']}</span>";
        }
        $tag = self::a($params);
        return $tag;
    }


    /**
     * icon tag
     * 
     * @param  string $name
     * @return string
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
    static function classActive($key, $selected = null) {
        if ($key == $selected) {
            $tag.=' active';
        }
        return $tag;
    }

    /**
     * pw project name
     *
     * @param string $name
     * @return void
     */
    static function pwProjectName($name) {
        $tag.= '<script type="text/javascript">';
        $tag.= "var pw_project_name = '{$name}'";
        $tag.= '</script>';
        return $tag;
    }

    /**
     * label action active
     *
     * @param string $controller_name
     * @param string $action_name
     * @return string
     */
    static function actionActive($controller_name, $action_name) {
        $controller = $GLOBALS['controller'];
        if (!$controller) return;
        if ($controller->pw_controller == $controller_name && $controller->pw_action == $action_name) {
            $tag.=' active';
        }
        return $tag;
    }

    /**
    * paginator
    *
    * @param array $params
    * @return string
    */ 
    static function paginator($params) {
        $offset = $params['offset'];
        if (!$offset) $offset = 0;
        $display_page_count = $params['display_page_count'];
        $page_count = $params['page_count'];
        if (!$display_page_count) $display_page_count = 10;

        if ($offset + $display_page_count >= $page_count) {
            $start = $offset - $display_page_count;
            if ($page_count < $display_page_count) {
                $pages = range(0, $page_count - 1);
            } else {
                $pages = range($offset - $display_page_count, $offset);
            }
        } else {
            $pages = range($offset, $offset + $display_page_count);
        }
        if (!$pages) return;

        if ($page_count > 1) {
            //first
            $icon_tag = self::iconTag('angle-right');
            $params['offset'] = 0;
            $params['label'] = self::iconTag('angle-double-left');
            $params['class'] = ($offset == 0) ? ' disabled' : '';
            $first_tag = self::paginatorLiTag($params);

            //prev
            $params['offset'] = $offset - 1;
            $params['label'] = self::iconTag('angle-left');
            $prev_tag = self::paginatorLiTag($params);

            //next
            $params['class'] = (($offset + 1) >= $page_count) ? ' disabled' : '';
            $params['offset'] = $offset + 1;
            $params['label'] = self::iconTag('angle-right');
            $next_tag = self::paginatorLiTag($params);

            //latest
            $params['offset'] = $page_count - 1;
            $params['label'] = self::iconTag('angle-double-right');
            $latest_tag = self::paginatorLiTag($params);

            foreach ($pages as $page) {
                $label = $page + 1;

                $params['label'] = $label;
                $params['offset'] = $page;
                $active = ($offset == $page) ? "active" : "";
                $params['class'] = "{$params['li_class']} {$active}";
                $page_tag.= self::paginatorLiTag($params);
            }

            $tag = "<ul class=\"{$params['ul_class']}\">{$first_tag}{$prev_tag}{$page_tag}{$next_tag}{$latest_tag}</ul>";
            $tag = "<nav>{$tag}</nav>";
        }
        return $tag;
    }

    /**
     * paginator li tag
     *
     * @param array $params
     * @return string
     */
    static function paginatorLiTag($params)
    {
        $a_tag = self::paginatorATag($params);
        $tag = "<li class=\"{$params['li_class']} {$params['class']}\">{$a_tag}</li>\n";
        return $tag;
    }

    /**
     * paginator a tag
     *
     * @param array $params
     * @return string
     */
    static function paginatorATag($params)
    {
        if ($params['pw-controller']) {
            $tag = self::paginatorAForPwJs($params);
        } else {
            $href = self::urlFor($params, ['offset' => $params['offset']]);
            $tag = self::a(['class' => $params['a_class'], 'href' => $href, 'label' => $params['label'], ]);
        }
        return $tag;
    }

    /**
     * paginator a tag for PwApp
     *
     * @param array $params
     * @return string
     */
    static function paginatorAForPwJs($params)
    {
        $tag = self::a([
            'class' => $params['a_class'],
            'label' => $params['label'],
            'pw-controller' => $params['pw-controller'],
            'pw-action' => $params['pw-action'],
            'offset' => $params['offset'],
        ]);
        return $tag;
    }

    /**
     * button
     *
     * @param array $params
     * @return void
     */
    static function button($params)
    {
        $label = $params['label'];
        $attribute = PwTag::attribute($params);
        if (!$params['class']) $params['class'] = 'btn btn-outline-primary';
        if (isset($params['icon_name'])) $icon_tag = PwTag::iconTag($params['icon_name']);
        $tag = "<button {$attribute} class=\"{$params['class']}\">{$icon_tag}{$label}</button>";
        return $tag;
    }

    /**
     * close modal button
     *
     * @param array $params
     * @return void
     */
    static function closeModalButton($params = null)
    {
        $label = LABEL_CLOSE;
        $class = 'btn btn-outline-primary';
        $tag = "<button type=\"button\" class=\"{$class}\" data-dismiss=\"modal\">{$label}</button>";
        return $tag;
    }
}