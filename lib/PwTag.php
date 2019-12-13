<?php
/**
 * PwTag
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class PwTag {

    static $pw_controller = 'pw-controller';
    static $pw_action = 'pw-action';
    static $stylesheet_dir = 'stylesheets';

    /**
     * base url
     *
     * @return string
     */
    static function baseUrl() {
        $url = '';
        if (isset($GLOBALS['controller'])) {
            $controller = $GLOBALS['controller'];
            if (isset($controller->pw_relative_base)) {
                $url = $controller->pw_relative_base;
            } else if (is_array($controller)) {
                $url = $controller['pw_relative_base'];
            }
        }
        return $url;
    }

    /**
     * label badge tag
     *
     * @param object $value
     * @param string $true_label
     * @param string $false_label
     * @return string
     */
    static function activeText($value, $true_label = LABEL_TRUE, $false_label = LABEL_FALSE) {
        if ($value) {
            return $true_label;
        } else {
            return $false_label;
        }
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
            $url = $controller->urlFor($params, $http_params);
            return $url;
        }
    }

    /**
     * image url
     * 
     * @param string $image_name
     * @param string $image_dir
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
     * @param string $dir_name
     * @param string $name
     * @param string $ext
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
     * @param string $url
     * @return string
     */
    static function serialUrl($url) {
        $serial = time();
        $url = "{$url}?pw_serial={$serial}";
        return $url;
    }

    /**
     * base tag
     * 
     * @return string
     */
    static function base() {
        $controller = $GLOBALS['controller'];
        if (is_null($controller->pw_relative_base)) {
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
        return "<script src=\"{$href}\"></script>\n";
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
            return "<script src=\"{$href}\"></script>\n";
        }
    }

    /**
     * stylesheet controller tag
     * 
     * @param  string $name
     * @param  string $dir_name
     * @param  array $attributes
     * @param  string $ext
     * @return string
     */
    static function stylesheet($name, $stylesheet_dir = 'stylesheets', $attributes = null, $ext = 'css') {
        if (!$name) return;
        if (!$stylesheet_dir) $stylesheet_dir = PwTag::$stylesheet_dir;
        $attributes['href'] = PwTag::fileUrl($stylesheet_dir, $name, $ext);
        $attributes['rel'] = 'stylesheet';
        $attributes['type'] = 'text/css';
        return PwForm::singleTag('link', $attributes);
    }

    /**
     * print stylesheet controller tag
     * 
     * @param  string $name
     * @param  string $dir_name
     * @param  array $attributes
     * @param  string $ext
     * @return string
     */
    static function stylesheetPrint($name, $stylesheet_dir = null, $attributes = null, $ext = 'css') {
        if (!$name) return;
        if (!$stylesheet_dir) $stylesheet_dir = PwTag::$stylesheet_dir;
        $attributes['href'] = PwTag::fileUrl($stylesheet_dir, $name, $ext);
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
            $escape_columns = ['label',
                               'icon_name',
                               'http_params',
                               'menu_group',
                               'is_use_selected',
                               'selected_key',
                               'selected_value',
                               'is_confirm',
                               'is_check_delete'
                            ];
            foreach ($params as $key => $value) {
                if (!array_key_exists($key, $escape_columns)) {
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
        $params = PwTag::checkActive($params);
        $attribute = PwTag::attribute($params);
        $label = '';
        $icon_tag = '';
        if (isset($params['icon_name'])) $icon_tag = PwTag::iconTag($params['icon_name'], $params);
        if (!isset($params['label']) && !isset($params['icon_name'])) $params['label'] = 'Link';
        if (isset($params['label']) && is_array($params['label'])) $params['label'] = implode(' ', $params['label']);

        if (isset($params['label'])) $label = $params['label'];
        $tag = "<a {$attribute}>{$icon_tag}{$label}</a>";
        return $tag;
    }

    /**
     * active class
     * 
     * TODO is_select: not only controller 
     * 
     * @param  array $params
     * @return string
     */
    static function checkActive($params) {
        if (!$params['is_use_selected']) return $params;
        if ($params['is_selected'] && ($params['selected_key'] == $params['selected_value'])) {
            if (isset($params['active_class'])) {
                $params['class'].= ' '.$params['active_class'];
            } else {
                $params['class'].= ' active';
            }
        }
        return $params;
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
                if (!array_key_exists($key, $escape_columns)) {
                    $attributes[] = "{$key}=\"{$value}\"";
                }
            }
        }
        if ($attributes) {
            $attribute = implode(' ', $attributes);
            $tag = "<img {$attribute}>";
        }
        return $tag;
    }

    /**
    * reverse link
    *
    * @param array $actions
    * @param array $params
    * @param boolean $is_active
    * @return string
    */ 
    static function reverse($params, $options, $is_active) {
        if ($options['label']) $labels = $options['label'];
        if (!$labels['valid']) $labels['valid'] = LABEL_TRUE;
        if (!$labels['invalid']) $labels['invalid'] = LABEL_FALSE;

        $controller = $params['controller'];
        $action = $params['action'];
        if ($controller && $action) $options['href'] = Controller::url($controller, $action, null, $options['http_params']);

        if ($options['is_btn']) {
            $options['label'] = $labels['valid'];
            if ($is_active) {
                $options['class'].= " btn btn-sm btn-danger";
            } else {
                $options['class'].= " btn btn-sm btn-outline-primary";
            }
        } else {
            $options['class'].= ' link';
            $options['label'] = ($is_active) ? $labels['valid']: $labels['invalid'];
        }
        $tag = self::a($options);
        return $tag;
    }

    /**
     * icon tag
     * 
     * @param  string $name
     * @param  array $options
     * @return string
     */
    static function iconTag($name, $options = null) {
        if ($name) {
            $icon_class_name = "fa fa-{$name}";
            if ($options['class'] && strpos($options['class'], 'pw-click') !== false) {
                $options['class'] = "{$icon_class_name} pw-click";
            } else {
                $options['class'] = $icon_class_name;
            }
            $attribute = PwTag::attribute($options);
            //TODO fontawesome function
            $icon_tag = "<i {$attribute}></i>&nbsp;";
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
        $tag = '';
        if ($key == $selected) $tag.=' active';
        return $tag;
    }

    /**
     * active btn class
     *
     * @param mixed $value
     * @param string $active_class
     * @param string $default_class
     * @return void
     */
    static function activeBtnClass($value, $selected, $active_class = null, $default_class = null)
    {
        if ($value == $selected) {
            $class = ($active_class) ? $active_class : "btn btn-danger";
        } else {
            $class = ($default_class) ? $default_class : "btn btn-outline-primary";
        }
        return $class;
    }

    /**
     * pw project name
     *
     * @param string $name
     * @return string
     */
    static function pwProjectName($name) {
        $tag = '';
        $tag.= '<script>';
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
        $tag = '';
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
            $params['offset'] = 0;
            $params['label'] = '<<';
            $params['label'] = self::iconTag('angle-double-left');
            $params['class'] = ($offset == 0) ? ' disabled' : '';
            $first_tag = self::paginatorLiTag($params);

            //prev
            $params['offset'] = $offset - 1;
            $params['label'] = '<';
            $params['label'] = self::iconTag('angle-left');
            $prev_tag = self::paginatorLiTag($params);

            //next
            $params['class'] = (($offset + 1) >= $page_count) ? ' disabled' : '';
            $params['offset'] = $offset + 1;
            $params['label'] = '>';
            $params['label'] = self::iconTag('angle-right');
            $next_tag = self::paginatorLiTag($params);

            //latest
            $params['offset'] = $page_count - 1;
            $params['label'] = '>>';
            $params['label'] = self::iconTag('angle-double-right');
            $latest_tag = self::paginatorLiTag($params);

            $page_tag = '';
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
     * @return string
     */
    static function button($params)
    {
        if (!$params['class']) $params['class'] = 'btn btn-outline-primary';
        $tag = PwTag::buttonTag($params);
        return $tag;
    }

    /**
     * close modal button
     *
     * @param array $params
     * @return string
     */
    static function closeModalButton($params = null)
    {
        if (!$params['class']) $params['class'] = 'pw-modal-close btn btn-outline-primary';
        $params['label'] = LABEL_CLOSE;
        $params['type'] = 'button';
        $params['label'] = PwTag::htmlLabel($params);
        $tag = PwTag::buttonTag($params);
        return $tag;
    }

    /**
     * button
     *
     * @param array $params
     * @return string
     */
    static function buttonTag($params)
    {
        $label = PwTag::htmlLabel($params);
        $attribute = PwTag::attribute($params);
        $tag = "<button {$attribute}>{$label}</button>";
        return $tag;
    }

    /**
     * html label
     *
     * @param array $params
     * @return string
     */
    static function htmlLabel($params)
    {
        $label = $params['label'];
        if ($params['icon_name']) {
            $icon_tag = PwTag::iconTag($params['icon_name']);
            $label = "{$icon_tag}{$label}";
        }
        return $label;
    }
}