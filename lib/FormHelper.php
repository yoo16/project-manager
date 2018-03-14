<?php
/**
 * FormHelper
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class FormHelper {
    //TODO except columns
    static $except_columns = ['csv', 'model', 'label', 'where', 'order', 'select_columns', 'value', 'values', 'label_separate', 'unselect', 'unselct_label', 'unselct_value'];
    static $radio_except_columns = ['csv', 'model', 'label', 'where', 'order', 'select_columns', 'values', 'label_separate', 'unselect', 'unselct_label', 'unselct_value'];

    /**
     * selectタグ
     *
     * @param array $params
     * @param string $selected
     * @return string
     */
    static function select($params, $selected=null) {
        if (!$params) return;
        if (!isset($params['class'])) $params['class'] = 'form-control';

        $tag = self::selectOptions($params, $selected);

        if ($tag) $tag = self::selectTag($tag, $params);
        return $tag;
    }

    /**
     * selectタグ(日付用)
     *
     * @param array $params
     * @param string $seleted
     * @return string
     */
    static function selectDate($params, $selected=null) {
        if (!$params) return;
        
        $params['class'].= ' form-control';
        if (!$params['hide_year']) $tag.= self::selectYear($params, $selected);
        if (!$params['hide_month']) $tag.= self::selectMonth($params, $selected);
        if (!$params['hide_day']) $tag.= self::selectDays($params, $selected);
        if ($params['show_hour']) $tag = self::selectTime($params, $selected);

        $tag = "<div class=\"form-group\">\n{$tag}\n</div>\n";
        $tag = "<div class=\"form-inline\">\n{$tag}\n</div>\n";
        return $tag;
    }

    /**
     * 日付ラベル
     *
     * @param string $formatter
     * @param string $type
     * @return string
     */
    static function labelFormatTag($formatter, $type) {
        if (!$formatter) return;
        if (!$type) return;
        $tag = '';
        $format_label = DateHelper::formatters($formatter, $type);
        if ($format_label) $tag = "&nbsp;{$format_label[$type]}&nbsp;";
        return $tag;
    }

    /**
     * select date tag
     *
     * @param array $params
     * @return string
     */
    static function selectDateTag($type, $params, $selected = null) {
        if ($params['values']) {
            $option_params['values'] = $params['values'];
            unset($params['values']);
        }
        if ($params['formatter']) {
            $formatter = $params['formatter'];
            unset($params['formatter']);
        }
        $value = self::selectDateValue($selected, $type);
        $tag = self::dateOptions($option_params, $value);

        $params['name'] = "{$params['name']}[{$type}]";
        $tag = self::selectTag($tag, $params);
        $tag.= self::labelFormatTag($formatter, $type);
        return $tag;
    }

    /**
     * selectタグ（年）
     *
     * @param array $params
     * @return string
     */
    static function selectYear($params, $selected = null) {
        if (is_array($params['years'])) {
            $params['values'] = $params['years'];
        } else {
            $params['values'] = range(date('Y'), 1900);
        }
        if ($params['years']) unset($params['years']);
        $tag = self::selectDateTag('year', $params, $selected);
        return $tag;
    }

    /**
     * selectタグ（月）
     *
     * @param array $params
     * @return string
     */
    static function selectMonth($params, $selected = null) {
        $params['values'] = range(1, 12);
        $tag = self::selectDateTag('month', $params, $selected);
        return $tag;
    }

    /**
     * selectタグ（日）
     *
     * @param array $params
     * @return string
     */
    static function selectDays($params, $selected = null) {
        $params['values'] = range(1, 31);
        $tag = self::selectDateTag('day', $params, $selected);
        return $tag;
    }

    /**
     * selectタグ（時間）
     *
     * @param array $params
     * @return string
     */
    function selectHours($params) {
        $current = self::selectDateValue($params, 'hour');

        $params['values'] = range(0, 23);
        $tag.= self::dateOptions($params, $current);

        $attribute = self::selectAttributeDate($params, 'hour');
        $tag = self::selectTag($tag, $attribute);
        return $tag;
    }

    /**
     * selectタグ（時間：分）
     *
     * @param array $params
     * @return string
     */
    function selectTime($params) {
        $hour_tag = self::selectHours($params);
        $minute_tag = self::selectMinutes($params);
        $tag.= "&nbsp;{$hour}&nbsp;:&nbsp;{$minute}\n";
        return $tag;
    }

    /**
     * selectタグ（分）
     *
     * @param array $params
     * @return string
     */
    function selectMinutes($params) {
        $current = self::selectDateValue($params, 'minute');

        $params['values'] = range(0, 59);
        $tag.= self::dateOptions($params, $current);

        $attribute = self::selectAttributeDate($params, 'minute');
        $tag = self::selectTag($tag, $attribute);
        return $tag;
    }

    /**
     * 日付選択値
     *
     * @param array $selected
     * @return String
     */
    static function selectDateValue($selected, $type) {
        $formatters['year'] = 'Y';
        $formatters['month'] = 'm';
        $formatters['day'] = 'd';
        $formatters['hour'] = 'H';
        $formatters['minute'] = 'i';
        $formatter = $formatters[$type];

        if (is_array($selected) && $selected[$type]) {
            $value = $selected[$type];
        } else if (is_string($selected)) {
            $value = date($formatter, strtotime($selected));
        }
        return $value;
    }

    /**
     * tag
     *
     * @param string $type
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    static function tag($type, $tag, $attributes=null) {
        if (is_array($attributes)) $attribute = self::attribute($attributes);
       $tag = "<{$type} {$attribute}>{$tag}</{$type}>\n"; 
       return $tag;
    }

    /**
     * tag
     *
     * @param string $type
     * @param array $attributes
     * @param string $tag
     * @return string
     */
    static function singleTag($type, $attributes=null, $tag = null) {
        if (is_array($attributes)) $attribute = self::attribute($attributes);
       $tag = "<{$type} {$attribute}>{$tag}\n"; 
       return $tag;
    }

    /**
     * label tag
     *
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    static function labelTag($tag, $attributes=null) {
       $tag = self::tag('label', $tag, $attributes);
       return $tag;
    }


    /**
     * label badge tag
     *
     * @param string $tag
     * @param array $attributes
     * @param string $type
     * @return string
     */
    static function badgeTag($tag, $attributes = null, $type = 'info') {
        $badge_class = " badge badge-pill badge-{$type}";
        $attributes['class'].= $badge_class;
        $tag = self::labelTag($tag, $attributes);
        return $tag;
    }


    /**
     * select tag
     *
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    static function selectTag($tag, $attributes = null) {
       foreach (self::$except_columns as $except_column) {
           if (isset($attributes[$except_column])) {
                unset($attributes[$except_column]); 
           }
       }
       $tag = self::tag('select', $tag, $attributes);
       return $tag;
    }

    /**
     * radio tag
     *
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    static function radioTag($attributes = null) {
        foreach (self::$radio_except_columns as $except_column) {
            if ($attributes[$except_column]) {
                unset($attributes[$except_column]); 
            }
        }
        $attributes['type'] = 'radio';
        $tag = self::singleTag('input', $attributes);
        return $tag;
    }

    /**
     * option tag
     *
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    static function optionTag($tag, $attributes=null) {
       $tag = self::tag('option', $tag, $attributes);
       return $tag;
    }

    /**
     * optionタグ（時間）
     *
     * @param Array $params
     * @return String
     */
    static function optionHours($params) {
        if ($params['selected']) {
            $current_hour = (int) date('H', strtotime($params['selected']));
            $current_minute = (int) date('i', strtotime($params['selected']));
        }

        $tag.= "<select name=\"{$params['name']}[hour]\" class=\"{$params['class']}\">";
            if ($params['unselect']) {
            $tag.= "<option value=\"\">--</option>\n";
        }
        for ($hour = 0; $hour <= 23; $hour++) {
            $label = sprintf('%02d', $hour);
            if (is_numeric($current_hour) && $current_hour == $hour) {
                $tag.= "<option value=\"{$hour}\" selected=\"selected\">{$label}</option>\n";
            } else {
                $tag.= "<option value=\"{$hour}\">{$label}</option>\n";
            }
        }
        $tag.= '</select>';

        $tag.= "：";
        $tag.= "<select name=\"{$params['name']}[minute]\" class=\"{$params['class']}\">";
            if ($params['unselect']) {
            $tag.= "<option value=\"\">--</option>\n";
        }
        for ($minute = 0; $minute <= 59; $minute++) {
            $label = sprintf('%02d', $minute);
            if (is_numeric($current_hour) && $current_minute == $minute) {
                $tag.= "<option value=\"{$minute}\" selected=\"selected\">{$label}</option>\n";
            } else {
                $tag.= "<option value=\"{$minute}\">{$label}</option>\n";
            }
        }
        $tag.= '</select>';
        $tag.="\n";
        return $tag;
    }

    /**
     * 
     *
     * @param array $params
     * @return array
     */
    static function values($params) {
        if (isset($params['csv']) && $params['csv']) {
            $values = CsvLite::options($params['csv']);
        } else if (isset($params['model']) && $params['model']) {
            $instance = DB::table($params['model']);

            if (isset($params['select_columns'])) $instance->select($params['select_columns']);
            if (isset($params['where'])) $instance->where($params['where']);
            if (isset($params['order'])) $instance->order($params['order']);
            $instance->all();

            $values = $instance->values;
        } else {
            $values = $params['values'];
        }
        return $values;
    }

    /**
     * optionタグ
     *
     * @param Array $params
     * @param Object $selected
     * @return String
     */
    static function selectOptions($params, $selected=null) {
        $values = self::values($params);
        if (!is_array($values)) return;

        $value_key = isset($params['value']) ? $params['value'] : 'value';

        $tag = self::unselectOption($params);
        foreach ($values as $value) {
            $attributes['value'] = $value[$value_key];
            $attributes['selected'] = self::selectedTag($attributes['value'], $selected);

            $label = self::convertLabel($value, $params);
            $tag.= self::optionTag($label, $attributes);
        }
        return $tag;
    }

    /**
     * 日付optionタグ
     *
     * @param array $params
     * @param string $selected
     * @param string $label_formatter
     * @return string
     */
    static function dateOptions($params, $selected=null, $label_formatter='%02d') {
        $tag.= self::unselectOption($params);
        $values = $params['values'];
        if (!($values)) return;

        foreach ($values as $key => $value) {
            $label = sprintf($label_formatter, $value);
            $selected_tag = self::selectedTag($value, $selected);
            $tag.= "<option value=\"{$value}\" {$selected_tag}>{$label}</option>\n";
        }
        return $tag;
    }

    /**
     * unselect option
     *
     * @param array $params
     * @return string
     */
    static function unselectOption($params) {
        $tag = '';
        if (isset($params['unselect'])) {
            $unselect_value = '';
            $unselect_label = '';
            if (isset($params['unselect_value'])) $unselect_value = $params['unselect_value'];
            if (isset($params['unselect_label'])) $unselect_label = $params['unselect_label'];
            $tag.= "<option value=\"{$unselect_value}\">{$unselect_label}</option>\n";
        }
        return $tag;
    }

    /**
     * selectAttributeDate
     *
     * @param array $params
     * @param string $date_key
     * @return string
     */
    static function selectAttributeDate($params, $date_key) {
        if ($params['id']) {
            $tag.= " id=\"{$params['id']}_{$date_key}\"";
        } else {
            $tag.= " id=\"{$params['name']}_{$date_key}\"";
        }
        if ($params['class']) $tag.= " class=\"{$params['class']}\"";
        $tag.= " name=\"{$params['name']}[{$date_key}]\"";

        $attributes = $params['attributes'];
        if (is_array($attributes)) {
            foreach ($attributes as $key => $attribute) {
                if ($key && $attribute) $tag.= " {$key}=\"{$attribute}\"";
            }
        }
        return $tag;
    }

    /**
     * formアトリビュート
     *
     * @param array $params
     * @return string
     */
    static function selectAttribute($params) {
        $tag = '';
        if (isset($params['id'])) $tag.= " id=\"{$params['id']}\"";
        if (isset($params['class'])) $tag.= " class=\"{$params['class']}\"";
        $tag.= " name=\"{$params['name']}\"";

        $attributes = isset($params['attributes'])? $params['attributes'] : null;
        if (is_array($attributes)) {
            foreach ($attributes as $key => $attribute) {
                if ($key && $attribute) $tag.= " {$key}=\"{$attribute}\"";
            }
        }
        return $tag;
    }

    /**
     * paramsチェック
     *
     * @param Array $params
     * @param Object $selected
     * @param Array $escape_columns
     * @return String
     */
    static function checkParams($params, $selected=null, $value_key=null, $label_key=null, $values=null) {
        if (isset($values)) $params['values'] = $values;
        $params['selected'] = isset($selected)? $selected : null;
        if (isset($value_key)) $params['value'] = $value_key;
        if (isset($label_key)) $params['label'] = $label_key;
        return $params;
    }
        
    /**
     * ラベル生成
     * 
     * @param array $values
     * @param array $params
     * @return string
     */
    static function convertLabel($values, $params) {
        $label_keys = isset($params['label']) ? $params['label'] : 'label';
        if (is_array($label_keys)) {
            foreach ($label_keys as $label_key) {
                $labels[] = $values[$label_key];
            }
            $label_separate = ($params['label_separate']) ? $params['label_separate'] : ' ';
            $label = implode($label_separate, $labels);
        } else {
            $label = $values[$label_keys];
        }
        return $label;
    }

    /**
     * selectedTag
     *
     * @param Object $value
     * @param Object $seleted
     * @return String
     */
    static function selectedTag($value, $selected) {
        if (!is_null($selected) && $selected == $value) return 'selected';
    }

    /**
     * checkedTag
     *
     * @param Object $value
     * @param Object $seleted
     * @return String
     */
    static function checkedTag($value, $selected) {
        if (is_bool($value) && (bool) $value == (bool) $selected) {
            $tag = 'checked';
        } else if (is_bool($selected) && (bool) $value == (bool) $selected) {
            $tag = 'checked';
        } elseif (isset($value) && $value == $selected) {
            $tag = 'checked';
        }
        return $tag;
    }


    /**
     * input(radio)タグ
     *
     * @param Array $params
     * @param Object $selected
     * @return String
     */
    static function radio($params, $selected = null) {
        $values = self::values($params);
        if (!is_array($values)) return;

        $value_key = isset($params['value']) ? $params['value'] : 'value';

        $attributes['name'] = $params['name'];
        foreach ($values as $value) {
            $attributes['value'] = $value[$value_key];
            $attributes['checked'] = self::checkedTag($attributes['value'], $selected);
            $attributes['id'] = "{$attributes['name']}_{$attributes['value']}";

            $tag.= self::radioTag($attributes);

            $label = self::convertLabel($value, $params);
            $tag.= "<label class=\"radio inline\" for=\"{$attributes['id']}\">{$input_tag}&nbsp;{$label}</label>&nbsp;\n";
        }
        $tag = "<div class=\"form-group\">{$tag}</div>";
        return $tag;
    }

    /**
     * ラジオ（性別）
     *
     * @param array $params
     * @param string $seleted
     * @return string
     */
    static function genderRadio($params=null, $selected=null) {
        if (!$params['csv_name']) $params['csv_name'] = 'gender';
        if (!$params['name']) $params['name'] = 'gender';
        $values = CsvLite::formOptions($params);
        $tag = self::radio($values, $selected);
        return $tag;
    }

    /**
     * チェックボックス（単一）
     *
     * @param array $params
     * @param object $seleted
     * @return string
     */
    static function checkbox($params, $selected = null) {
        $label = ($params['label']) ? $params['label'] : LABEL_TRUE;
        if (!isset($params['value'])) $params['value'] = 1;

        $attributes['type'] = 'checkbox';
        $attributes['name'] = $params['name'];
        $attributes['value'] = $params['value'];
        $attributes['checked'] = self::checkedTag($params['value'], $selected);
        $attributes['id'] = $params['id'];
        $attributes['class'] = $params['class'];

        $tag.= self::hidden($params['name'], 0);
        $tag.= self::input($attributes);
        $tag = self::labelTag($tag.$label);
        return $tag;
    }

    /**
     * チェックボックス（複数）
     *
     * @param Array $params
     * @param Object $seleted
     * @return String
     */
    function multiCheckbox($params, $selected=null, $value_key=null, $label_key=null, $values=null) {
        $params = self::checkParams($params, $selected, $value_key, $label_key, $values);

        $name = $params['name'];
        $values = $params['values'];
        $value_key = $params['value'];
        $label_key = $params['label'];

        $params['class'] = ' form-check-input';
        $attribute = self::selectAttribute($params);
        $div_class = $params['div_class'];
        if (is_array($values)) {
            foreach ($values as $key => $option) {
                $value = $option[$value_key];
                $label = self::convertLabel($option, $params);
                $id = "{$params['id']}_{$value}";

                if ($option['class']) $class = " class=\"{$option['class']}\"";
                if ($selected) $checked = (in_array($value, $selected))? ' checked="checked"' : '';

                $_tag = "&nbsp;<input id=\"{$id}\" type=\"checkbox\" value=\"{$value}\"{$checked}{$attribute}>\n";
                $tag.= "<div class=\"{$div_class}\"><label for=\"{$id}\" class=\"form-check-label\">\n{$_tag}\n{$label}\n</label></div>\n";
            }
        }
        return $tag;
    }

    /**
     * validateRequired
     *
     * @param array $errors
     * @param string $column
     * @return string
     */
    static function validateRequired($errors, $column) {
        if (!$errors) return;
        foreach ($errors as $error) {
            if ($error['column'] == $column) return 'required';
        }
    }

    /**
     * attribute
     *
     * @param array $params
     * @return string
     */
    static function attribute($params) {
        if (!$params) return;
        foreach ($params as $key => $param) {
            if (is_array($param)) $param = implode(' ', $param);
            if (isset($param)) $attributes[] = "{$key}=\"{$param}\"";
        }
        if ($attributes) $attribute = implode(' ', $attributes);
        return $attribute;
    }

    /**
     * link
     *
     * @param string $action
     * @param string $label
     * @param array $query
     * @param array $params
     * @return string
     */
    static function link($action, $label, $query = null, $params = null) {
        $attribute = self::attribute($params);
        if (substr($action, 0, 1) == '#') {
            $href = '#';
        } else {
            $href = url_for($action, $query);
        }
        $tag = "<a href=\"{$href}\" {$attribute}>{$label}</a>\n";
        return $tag;
    }

    /**
     * link button
     *
     * @param string $action
     * @param string $label
     * @param array $query
     * @param array $params
     * @return string
     */
    static function linkButton($action, $label, $query = null, $params = null) {
        if (!$params['class']) $params['class'] = 'btn btn-outline-primary';
        $tag = self::link($action, $label, $query, $params);
        return $tag;
    }

    /**
     * modal link
     *
     * @param string $target
     * @param string $label
     * @param array $params
     * @return string
     */
    static function linkModal($target, $label, $params = null) {
        if (!$params['class']) $params['class'] = 'btn btn-primary';
        $params['data-toggle'] = 'modal';
        $params['data-target'] = $target;
        $attribute = self::attribute($params);
        $tag = "<a href=\"#\" {$attribute}>{$label}</a>\n";
        return $tag;
    }


    /**
     * form
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    static function form($tag, $params = null) {
        if (!$params['method']) $params['method'] = 'post';
        $tag = self::tag('form', $tag, $params);
        return $tag;
    }

    /**
     * formLabel
     *
     * @param string $tag
     * @param array $attributes
     * @param string $type
     * @return string
     */
    static function formLabel($tag, $attributes = null) {
        if ($attributes['class']) {
            $attributes['class'].= " col-form-label";
        } else {
            $attributes['class'] = "col-2 col-form-label";
        }
        $tag = self::labelTag($tag, $attributes);
        return $tag;
    }

    /**
     * input
     *
     * @param array $attributes
     * @param string $name
     * @param object $value
     * @return string
     */
    static function input($attributes, $name = null, $value = null) {
        if (!$attributes['type']) $attributes['type'] = "text";
        if (isset($name)) $attributes['name'] = $name;
        if (isset($value)) $attributes['value'] = $value;

        $tag = self::singleTag('input', $attributes);
        return $tag;
    }

    /**
     * textarea
     *
     * @param array $attributes
     * @param string $name
     * @param object $value
     * @return string
     */
    static function textarea($name, $value = null, $params = null) {
        if (isset($name)) $params['name'] = $name;
        if (!$params['rows']) $params['rows'] = '10';

        $tag = self::tag('textarea', $value, $params);
        return $tag;
    }

    /**
     * text
     *
     * @param string $name
     * @param object $value
     * @param array $params
     * @return string
     */
    static function text($name, $value = null, $params = null) {
        $params['type'] = "text";
        //if (!$params['class']) $params['class'] = 'col-4';
        $params['class'].= " form-control";

        $tag = self::input($params, $name, $value);
        return $tag;
    }

    /**
     * submit
     *
     * @param string $value
     * @param array $params
     * @return string $name
     * @param string $name
     */
    static function submit($value = null, $params = null, $name = null) {
        if (!$params['class']) $params['class'] = 'btn btn-primary';
        $params['type'] = "submit";
        $tag = self::input($params, $name, $value);
        return $tag;
    }

    /**
     * delete
     *
     * @param string $value
     * @param array $params
     * @param string $name
     * @return string $name
     */
    static function delete($value = null, $params = null, $name = null) {
        if (!$params['class']) $params['class'] = 'btn btn-danger';
        $tag = self::submit($value, $params, $name);
        return $tag;
    }

    /**
     * button
     *
     * @param string $label
     * @param array $params
     * @return string $name
     */
    static function button($label, $params = null) {
        if (!$params['class']) $params['class'] = 'btn btn-primary';
        $attribute = self::attribute($params);
        $tag = "<button {$attribute}>{$label}</button>\n";
        return $tag;
    }

    /**
     * groupButton
     *
     * @param string $label
     * @param array $params
     * @return string $name
     */
    static function groupButton($label, $params = null) {
        $tag = "<button {$attribute}>{$label}</button>\n";
        
        $attributes['class'] = 'input-group-btn';
        $label = self::tag('span', $attribues);
        return $tag;
    }

    /**
     * input(password)
     *
     * @param string $name
     * @param object $value
     * @param array $params
     * @return string
     */
    static function password($name, $value = null, $params = null) {
        $params['type'] = "password";
        $tag = self::input($params, $name);
        return $tag;
    }

    /**
     * input(hidden)タグ
     *
     * @param string $name
     * @param object $value
     * @param array $params
     * @return string
     */
    static function hidden($name, $value = null, $params = null) {
        $params['type'] = "hidden";
        $tag = self::input($params, $name, $value);
        return $tag;
    }

    /**
    * label
    *
    * @param Boolean $is_active
    * @return String
    */ 
    static function changeActiveLabelTag($action, $params, $is_active, $valid_label = LABEL_TRUE, $invalid_label = LABEL_FALSE) {
        if ($is_active) {
            $tag = "<span class=\"btn btn-success btn-sm\">{$valid_label}</span>";
        } else {
            $tag = "<span class=\"btn btn-secondary btn-sm\">{$invalid_label}</span>";
        }
        $href = url_for($action, $params);
        $tag = "<a href=\"{$href}\">{$tag}</a>";
        return $tag;
    }

    /**
     * label badge tag
     *
     * @param object $value
     * @param array $attributes
     * @param string $type
     * @return string
     */
    static function activeLabelTag($value, $tag = LABEL_TRUE, $attributes = null, $type = 'danger') {
        if ($value) {
            $badge_class = " badge badge-pill badge-{$type}";
            $attributes['class'].= $badge_class;
            $tag = self::labelTag($tag, $attributes);
            return $tag;
        }
    }

    /**
     * nav active
     *
     * @param string $key
     * @param string $selected
     * @return string
     */
    static function navActive($key, $selected) {
        $tag = "nav-link";
        if ($key == $selected) {
            $tag.=' active';
        }
        return $tag;
    }

    /**
     * label badge tag
     *
     * @param string $key
     * @param string $selected
     * @return string
     */
    static function linkActive($key, $selected) {
        if ($key == $selected) {
            $tag.=' active';
        }
        return $tag;
    }
    
}