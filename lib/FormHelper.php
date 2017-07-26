<?php
/**
 * FormHelper
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class FormHelper {

    /**
     * selectタグ
     *
     * @param array $params
     * @param string $selected
     * @return string
     */
    static function select($params, $selected=null) {
        if (!$params) return;
        if (!isset($params['class'])) $params['class'] = 'form-control col-4';

        $tag = self::selectOptions($params, $selected);

        $attribues['id'] = $params['id'];
        $attribues['class'] = $params['class'];
        $attribues['name'] = $params['name'];
        $tag = self::selectTag($tag, $attribues);
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
        
        $params['class'].= ' form-control action-change-date';
        $params = self::checkParams($params);
        if ($params['formatter']) $format_label = DateHelper::formatters($params['formatter']);
        
        if (!$selected) $selected = $params['selected'];
        if ($selected && is_string($selected)) {
            $params['selected'] = null;
            $params['selected']['year'] = (int) date('Y', strtotime($selected));
            $params['selected']['month'] = (int) date('m', strtotime($selected));
            $params['selected']['day'] = (int) date('d', strtotime($selected));
            $params['selected']['hour'] = (int) date('H', strtotime($selected));
            $params['selected']['minute'] = (int) date('i', strtotime($selected));
        }

        if (!$params['hide_year']) $tag.= self::selectYear($params);
        if (!$params['hide_month']) $tag.= self::selectMonth($params);
        if (!$params['hide_day']) $tag.= self::selectDays($params);
        if ($params['show_hour']) $tag = self::selectTime($params);

        $tag = "<div class=\"form-group\">{$tag}</div>";
        $tag = "<div class=\"form-inline\">{$tag}</div>";

        $selected = DateHelper::convertAtArrayToAt($params['selected']);
        $hidden_tag = "<input type=\"hidden\" name=\"{$params['name']}\" value=\"{$selected}\">\n";
        $tag = "{$hidden_tag}{$tag}\n";
        return $tag;
    }

    /**
     * 日付ラベル
     *
     * @param array $params
     * @param string $selected
     * @return string
     */
    static function labelFormatTag($formatter, $type) {
        if (!$formatter) return;
        if (!$type) return;
        $tag = '';
        $format_label = DateHelper::formatters($formatter);
        if ($format_label) $tag = "&nbsp;{$format_label[$type]}&nbsp;";
        return $tag;
    }

    /**
     * selectタグ（誕生日）
     *
     * @param array $params
     * @param string $selected
     * @return string
     */
    static function selectBirthday($params = null, $selected = null) {
        $from_year = ($params['from'])? $params['from'] : 1900;
        $to_year = ($params['to'])? $params['to'] : date('Y') + 1;
        $params['years'] = range($to_year,  $from_year);

        //$params['id'] = 'birthday_at';
        $params['default_unselect'] = true;
        $params['unselect'] = true;
        if (!$params['formatter']) $params['formatter'] = 'j';
        if (!$params['name']) $params['name'] = "birthday_at";

        $tag = self::selectDate($params, $selected);
        return $tag;
    }

    /**
     * selectタグ（年）
     *
     * @param Array $params
     * @return String
     */
    static function selectYear($params) {
        $current = self::selectDateValue($params, 'year');

        if (is_array($params['years'])) {
            $params['values'] = $params['years'];
        } else {
            $params['values'] = range(date('Y'), 1900);
        }
        $tag = self::dateOptions($params, $current);

        $attribute = self::selectAttributeDate($params, 'year');
        $tag = self::selectTag($tag, $attribute);
        $tag.= self::labelFormatTag($params['formatter'], 'y');
        return $tag;
    }

    /**
     * selectタグ（月）
     *
     * @param Array $params
     * @return String
     */
    static function selectMonth($params) {
        $current = self::selectDateValue($params, 'month');

        $params['values'] = range(1, 12);
        $tag.= self::dateOptions($params, $current);

        $attribute = self::selectAttributeDate($params, 'month');
        $tag = self::selectTag($tag, $attribute);
        $tag.= self::labelFormatTag($params['formatter'], 'm');
        return $tag;
    }

    /**
     * selectタグ（日）
     *
     * @param Array $params
     * @return String
     */
    static function selectDays($params) {
        $current = self::selectDateValue($params, 'day');

        $params['values'] = range(1, 31);
        $tag.= self::dateOptions($params, $current);

        $attribute = self::selectAttributeDate($params, 'day');
        $tag = self::selectTag($tag, $attribute);
        $tag.= self::labelFormatTag($params['formatter'], 'd');
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
     * @param Array $params
     * @return String
     */
    static function selectDateValue($params, $type) {
        $types['year'] = 'Y';
        $types['month'] = 'm';
        $types['day'] = 'd';

        $formatter = $types[$type];
        $selected = $params['selected'];
        if ($selected) {
            $selected_value = $selected[$type];
            $value = ($selected_value)? (int) $selected_value :(int) date($formatter, strtotime($selected));
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
     * label tag
     *
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    static function formLabel($tag, $attributes=null) {
        if (!$attributes['class']) $attributes['class'] = 'col-2 col-form-label';
        $tag = self::labelTag($tag, $attributes);
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
    static function selectTag($tag, $attributes=null) {
       $tag = self::tag('select', $tag, $attributes);
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
            $entity = DB::table($params['model']);
            $values = $entity->select()->values;
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
            $tag.= "<option value=\"{$params['unselect']['value']}\">{$params['unselect']['label']}</option>\n";
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
        } elseif ($value && $value == $selected) {
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
    static function radio($params, $selected=null, $value_key=null, $label_key=null, $values=null) {
        if ($params['csv']) {
            $values = CsvLite::options($params['csv']);
        }
        if ($values) $params['values'] = $values;
        if ($selected) $params['selected'] = $selected;
        if ($value_key) $params['value'] = $value_key;
        if ($label_key) $params['label'] = $label_key;
        if (!$params['value']) $params['value'] = 'value';
        if (!$params['label']) $params['label'] = 'label';

        $name = $params['name'];

        $unselect_label = $params['unselect_label'];
        $unselect_value = $params['unselect_value'];

        $values = $params['values'];
        $id = $params['id'];
        $class_name = $params['class'];
        $attribute = self::selectAttribute($params);

        if (is_array($values)) {
            foreach ($values as $key => $option) {
                $value = $option[$params['value']];
                $label = self::convertLabel($option, $params);
                $id_name = "{$id}_{$key}";

                $checked = self::checkedTag($value, $selected);

                $input_tag = "&nbsp;<input type=\"radio\" id=\"{$id_name}\" class=\"{$class_name}\" name=\"{$name}\" value=\"{$value}\"{$checked}{$attribute}>\n";
                $tag.= "<label class=\"radio inline\" for=\"{$id_name}\">{$input_tag}&nbsp;{$label}</label>&nbsp;\n";

            }
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

        $tag.= self::hidden($params['name'], -1);
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
        $attribute = implode(' ', $attributes);
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
     * text
     *
     * @param string $name
     * @param object $value
     * @param array $params
     * @return string
     */
    static function text($name, $value = null, $params = null) {
        $params['type'] = "text";
        if (!$params['class']) $params['class'] = 'col-4';
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
        $tag = self::input($params, $name, $value);
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

}