<?php
/**
 * PwForm
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 **/

class PwForm {
    //TODO refactoring
    static $radio_columns = ['value'];

    //TODO refactoring
    static $except_columns = ['csv',
                              'model',
                              'label',
                              'where',
                              'wheres',
                              'order',
                              'select_columns',
                              'value',
                              'values',
                              'label_convert_values',
                              'label_unit',
                              'label_separate',
                              'filter_values',
                              'unselect',
                              'unselct_label',
                              'unselct_value',
                              'unused_hidden',
                              'effective-digit',
                              'is_associative_array',
                              'is_key_value_array',
                            ];

    //TODO refactoring
    static $http_tag_columns = ['id', 'class'];

    /**
     * select tag
     *
     * @param array $params
     * @param object $selected
     * @return string
     */
    static function select($params, $selected = null) {
        if (!$params) return;
        if (!isset($params['class'])) $params['class'] = 'form-control';
        $tag = self::selectOptions($params, $selected);
        if ($tag) $tag = self::selectTag($tag, $params);
        return $tag;
    }

    /**
     * select tag for date
     *
     * @param array $params
     * @param object $seleted
     * @return string
     */
    static function selectDate($params, $selected=null) {
        if (!$params) return;
        
        $params['class'].= ' form-control';
        $tag = '';
        if (!$params['hide_year']) $tag.= PwForm::selectYear($params, $selected);
        if (!$params['hide_month']) $tag.= PwForm::selectMonth($params, $selected);
        if (!$params['hide_day']) $tag.= PwForm::selectDays($params, $selected);
        if ($params['show_hour']) $tag.= PwForm::selectTime($params, $selected);

        if ($params['one_day']) {
            $name = "{$params['name']}[day]";
            $tag.= PwForm::input(['type' => 'hidden', 'name' => $name, 'value' => 1]);
        }
        return $tag;
    }

    /**
     * label format
     *
     * @param string $formatter
     * @param string $type
     * @return string
     */
    static function labelFormatTag($formatter, $type) {
        if (!$formatter) return;
        if (!$type) return;
        $tag = '';
        $format_label = PwDate::formatters($formatter, $type);
        if ($format_label) $tag = "&nbsp;{$format_label[$type]}&nbsp;";
        return $tag;
    }

    /**
     * select date tag
     *
     * @param string $type
     * @param array $params
     * @param object $selected
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
     * select tag for year
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
     * select tag for month
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
     * select tag for days
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
     * select taf for hours
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
     * select taf for time
     *
     * @param array $params
     * @return string
     */
    function selectTime($params) {
        $hour = self::selectHours($params);
        $minute = self::selectMinutes($params);
        $tag = "&nbsp;{$hour}&nbsp;:&nbsp;{$minute}\n";
        return $tag;
    }

    /**
     * select tag for minutes
     *
     * @param array $params
     * @return string
     */
    function selectMinutes($params) {
        $current = self::selectDateValue($params, 'minute');

        $params['values'] = range(0, 59);
        $option_tag = self::dateOptions($params, $current);

        $attribute = self::selectAttributeDate($params, 'minute');
        $tag = self::selectTag($option_tag, $attribute);
        return $tag;
    }

    /**
     * select date value
     *
     * @param array $selected
     * @param string $type
     * @return string
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
        foreach (self::$except_columns as $except_column) {
            if (!in_array($except_column, self::$radio_columns)) {
                if ($attributes[$except_column]) unset($attributes[$except_column]);
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

    //TODO refactoring
    /**
     * option tag for hours
     *
     * @param array $params
     * @return string
     */
    static function optionHours($params) {
        $separator = ':';
        if ($params['selected']) {
            $current_hour = (int) date('H', strtotime($params['selected']));
            $current_minute = (int) date('i', strtotime($params['selected']));
        }

        $tag = "<select name=\"{$params['name']}[hour]\" class=\"{$params['class']}\">";
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

        $tag.= $separator;

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
     * values
     *
     * @param array $params
     * @return array
     */
    static function values($params) {
        if (isset($params['csv']) && $params['csv']) {
            $lang = PwLocalize::lang();
            $values = PwCsv::options($params['csv'], $lang);
        } else if (isset($params['model']) && $params['model']) {
            $instance = DB::model($params['model']);

            if (isset($params['select_columns'])) $instance->select($params['select_columns']);
            //TODO refectoring
            if (isset($params['where'])) {
                if (is_array($params['where'])) {
                    if (isset($params['where'][2])) {
                        $instance->where($params['where'][0], $params['where'][1], $params['where'][2]);
                    } else if (isset($params['where'][1])) {
                        $instance->where($params['where'][0], $params['where'][1]);
                    } else {
                        return;
                    }
                } else {
                    $instance->where($params['where']);
                }
            }
            if (isset($params['wheres'])) $instance->wheres($params['wheres']);
            if (isset($params['order'])) $instance->order($params['order'], $params['order_type'], $params['order_column_type']);
            $instance->all();

            $values = $instance->values;
        } else {
            $values = $params['values'];
        }

        if (isset($params['filter_values']) && $values && is_array($params['filter_values']) && $params['value']) {
            foreach ($values as $index => $value) {
                $_value = $value[$params['value']];
                if (in_array($_value, $params['filter_values'])) {
                    unset($values[$index]);
                }
            }
        }
        return $values;
    }

    /**
     * option tag
     *
     * @param array $params
     * @param object $selected
     * @return string
     */
    static function selectOptions($params, $selected = null) {
        $label = '';
        $values = self::values($params);
        if (!is_array($values)) return;

        $value_key = isset($params['value']) ? $params['value'] : 'value';

        $tag = self::unselectOption($params);
        foreach ($values as $key => $value) {
            if (isset($params['is_key_value_array']) && $params['is_key_value_array']) {
                $attributes['value'] = $key;
                $label = $value;
            } else {
                $attributes['value'] = $value[$value_key];
                $label = self::convertLabel($value, $params);
            }
            $selected_string = self::selectedTag($attributes['value'], $selected);
            unset($attributes['selected']);
            if ($selected_string) $attributes['selected'] = $selected_string;
            if (isset($params['label_unit'])) $label.= $params['label_unit'];
            $tag.= self::optionTag($label, $attributes);
        }
        return $tag;
    }

    /**
     * date option
     *
     * @param array $params
     * @param mixed $selected
     * @param string $label_formatter
     * @return string
     */
    static function dateOptions($params, $selected = null, $label_formatter='%02d') {
        $tag = '';
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
        if (isset($params['unselect']) && $params['unselect']) {
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
        $tag = '';
        $tag_id = ($params['id']) ? $params['id'] : $params['name'];
        $tag.= " id=\"{$tag_id}_{$date_key}\"";

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
     * select attribute
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
     * check params
     *
     * @param array $params
     * @param object $selected
     * @param array $escape_columns
     * @return string
     */
    static function checkParams($params, $selected=null, $value_key=null, $label_key=null, $values=null) {
        if (isset($values)) $params['values'] = $values;
        $params['selected'] = isset($selected)? $selected : null;
        if (isset($value_key)) $params['value'] = $value_key;
        if (isset($label_key)) $params['label'] = $label_key;
        return $params;
    }
        
    /**
     * convert label
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
            $label_separate = (isset($params['label_separate'])) ? $params['label_separate'] : ' ';
            $label = implode($label_separate, $labels);
        } else {
            $label = $values[$label_keys];
        }
        return $label;
    }

    /**
     * selectedTag
     *
     * @param object $value
     * @param object $seleted
     * @return string
     */
    static function selectedTag($value, $selected) {
        if (!is_null($selected) && $selected == $value) return 'selected';
    }

    /**
     * checkedTag
     *
     * @param object $value
     * @param object $seleted
     * @return string
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
     * input radio tag
     * 
     * TODO refectoring
     *
     * @param array $params
     * @param object $selected
     * @return string
     */
    static function radio($params, $selected = null) {
        $values = self::values($params);
        if (!is_array($values)) return;
        if (isset($params['selected'])) $selected = $params['selected'];

        $value_key = isset($params['value']) ? $params['value'] : 'value';
        $tag = '';
        foreach ($values as $value) {
            $params['value'] = $index = $value[$value_key];
            unset($params['checked']);
            $checked = self::checkedTag($params['value'], $selected);
            if ($checked) $params['checked'] = $checked;
            $params['id'] = "{$params['name']}_{$params['value']}";
            $tag.= self::radioTag($params, $value);

            $label_params['id'] = $params['id'];
            if (isset($params['label_convert_values'][$index])) {
                $label_params['label'] = $params['label_convert_values'][$index];
            } else {
                $label_params['label'] = self::convertLabel($value, $params);
                if ($params['label_unit']) $label_params['label'].= $params['label_unit'];
            }
            $label_params['class'] = 'radio inline';
            $tag.= PwForm::label($label_params);
        }
        return $tag;
    }

    /**
     * label tag
     * 
     * TODO attribute
     *
     * @param array $params
     * @return string
     */
    static function label($params) {
        $tag = "<label class=\"{$params['class']}\" for=\"{$params['id']}\">{$params['label']}</label>&nbsp;\n";
        return $tag;
    }

    /**
     * gender radio
     *
     * @param array $params
     * @param string $seleted
     * @return string
     */
    static function genderRadio($params = null, $selected = null) {
        if (!$params['csv_name']) $params['csv_name'] = 'gender';
        if (!$params['name']) $params['name'] = 'gender';
        $values = PwCsv::formOptions($params);
        $tag = self::radio($values, $selected);
        return $tag;
    }

    /**
     * checkbox
     *
     * @param array $params
     * @param object $seleted
     * @return string
     */
    static function checkbox($params, $selected = null) {
        if (is_array($params['label'])) {
            $label = implode(' ', $params['label']);
        } else {
            $label = (isset($params['label'])) ? $params['label'] : LABEL_TRUE;
        }
        if (!isset($params['value'])) $params['value'] = 1;

        $tag = '';
        if (!$params['unused_hidden']) $tag.= self::hidden($params['name'], 0);
        $id = ($params['id']) ? "{$params['id']}_{$params['value']}" : $params['value'];
        $checkbox_attributes = [
            'id' => $id,
            'class' => $params['class'],
            'name' => $params['name'],
            'type' => 'checkbox',
            'value' => $params['value']
        ];
        if ($params['disable']) $checkbox_attributes['class'].= ' d-none';
        if ($selected) $checkbox_attributes['checked'] = 'checked';
        $tag = '';
        $tag.= self::input($checkbox_attributes);

        $label_attributes['class'] = 'checkbox';
        $label_attributes['for'] = $id;
        $tag.= self::labelTag($label, $label_attributes);
        return $tag;
    }

    /**
     * muulti checkbox
     *
     * @param array $params
     * @param object $seleted
     * @param string $value_key
     * @param string $label_key
     * @param array $values
     * @return string
     */
    static function multiCheckbox($params, $selected=null, $value_key=null, $label_key=null, $values=null) {
        $params = self::checkParams($params, $selected, $value_key, $label_key, $values);

        $value_key = $params['value'];
        $label_key = $params['label'];

        $attribute = self::selectAttribute($params);
        if (is_array($params['values'])) {
            foreach ($params['values'] as $key => $option) {
                if ($params['is_key_value']) {
                    $value = $key;
                    $label = $option;
                } else {
                    $value = $option[$value_key];
                    $label = self::convertLabel($option, $params);
                }

                $id = ($params['id']) ? "{$params['id']}_{$value}" : $value;

                if ($params['is_checkbox_hidden']) {
                    $checkbox_class = "checkbox-hidden";
                } else {
                    $checkbox_class = "form-check-input {$params['class']}";
                }
                $checkbox_attributes = [
                    'id' => $id,
                    'class' => $checkbox_class,
                    'name' => $params['name'],
                    'type' => 'checkbox',
                    'value' => $value
                ];
                if ($selected) $checkbox_attributes['checked'] = 'checked';
                $checkbox = self::input($checkbox_attributes);

                //TODO function
                $label_class = ($params['label_class']) ? $params['label_class'] : 'checkbox form-check-label';
                $label_tag = "<label for=\"{$id}\" class=\"{$label_class}\">\n{$label}\n</label>";

                $checkbox_tag = "{$checkbox}\n{$label_tag}\n";
                $tag = '';
                if ($params['is_return']) {
                    $tag.= "<div class=\"{$params['div_class']}\">{$checkbox}</div>";
                } else {
                    $tag.= $checkbox_tag;
                }
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
            $param = trim($param);
            if (isset($param)) $attributes[] = "{$key}=\"{$param}\"";
        }
        if ($attributes) $attribute = implode(' ', $attributes);
        return $attribute;
    }

    /**
     * link button
     *
     * @param string $action
     * @param array $http_params
     * @return string
     */
    static function linkButton($params, $http_params = null) {
        if (is_array($http_params) && !$http_params['class']) $http_params['class'] = 'btn btn-outline-primary';
        $tag = $GLOBALS['controller']->linkTo($params, $http_params);
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
     * @param string $name
     * @param object $value
     * @param array $params
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
        if (isset($value)) {
            if ($params['date-formatter']) {
                $value = date($params['date-formatter'], strtotime($value));
            }
            if (is_numeric($value)) {
                if ($params['number-formatter']) {
                    $value = sprintf($params['number-formatter'], $value);
                } else if (is_numeric($params['effective-digit'])) {
                    $formatter = "%.{$params['effective-digit']}f";
                    $value = sprintf($formatter, $value);
                }
            }
        }

        $tag = self::input($params, $name, $value);
        return $tag;
    }

    /**
     * submit
     *
     * @param string $value
     * @param array $params
     * @param string $name
     * @return string
     */
    static function submit($value = null, $params = null, $name = null) {
        if (!$params['class']) $params['class'] = 'btn btn-primary';
        $params['type'] = "submit";
        $tag = self::input($params, $name, $value);
        return $tag;
    }

    /**
     * delete link
     *
     * @param array $params
     * @return string
     */
    static function delete($params = null) {
        if (!$params['class']) $params['class'] = 'btn btn-danger';
        if ($params['is_check_delete']) {
            $rel_name = "delete_link";
            $params['id'] = $rel_name;
            $params['disabled'] = 'disabled';
        }
        if ($params['is_confirm']) {
            $params['class'].= ' confirm-dialog'; 
            unset($params['is_confirm']);
        }

        $params['class'].= ' fa fa-erase';

        $tag = self::submit($params['label'], $params);

        if ($params['is_check_delete']) {
            $check_delete_tag = "<label for=\"delete_checkbox\"><input class=\"delete_checkbox\" type=\"checkbox\" rel=\"{$rel_name}\"></label>";
            $tag.= $check_delete_tag;
            unset($params['is_check_delete']);
        }
        return $tag;
    }

    /**
     * confirm delete link
     *
     * @param array $params
     * @return string
     */
    static function confirmDelete($params = null) {
        if (!isset($params['label'])) $params['label'] = LABEL_DELETE;
        if (!isset($params['class'])) $params['class'] = 'btn btn-danger';
        $params['class'].= ' confirm-delete fa fa-erase';
        $tag = PwTag::a($params);
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
        $attribute = self::attribute($params);
        $tag = "<button {$attribute}>{$label}</button>\n";
        
        $attributes['class'] = 'input-group-btn';
        $label = self::tag('span', $attributes);
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
        if ($params['is_show_value']) {
            $tag = self::input($params, $name, $value);
        } else {
            $tag = self::input($params, $name);
        }
        return $tag;
    }

    /**
     * input(hidden)
     * 
     * TODO $params
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

    //TODO move Controller or model function
    /**
    * change label
    *
    * @param array $params
    * @param boolean $is_active
    * @param string $valid_label
    * @param string $invalid_label
    * @return string
    */ 
    static function changeActiveLabelTag($params, $is_active, $valid_label = LABEL_TRUE, $invalid_label = LABEL_FALSE) {
        if ($is_active) {
            $icon_tag = PwTag::iconTag('check');
            $tag = "<span class=\"btn btn-sm btn-danger action-loading\">{$icon_tag}{$valid_label}</span>";
        } else {
            $icon_tag = PwTag::iconTag('times');
            $tag = "<span class=\"btn btn-sm btn-outline-primary action-loading\">{$icon_tag}{$invalid_label}</span>";
        }
        $href = Controller::url($params, $params['http_params']);
        $tag = "<a href=\"{$href}\">{$tag}</a>";
        return $tag;
    }

    /**
    * change label
    *
    * @param array $params
    * @param boolean $is_active
    * @param string $valid_label
    * @param string $invalid_label
    * @return string
    */ 
    static function changeActiveLabel($is_active, $params = null, $valid_label = LABEL_TRUE, $invalid_label = LABEL_FALSE) {
        if ($is_active) {
            $icon_tag = PwTag::iconTag('check');
            $tag = "<span class=\"btn btn-sm btn-danger\">{$icon_tag}{$valid_label}</span>";
        } else {
            $icon_tag = PwTag::iconTag('times');
            $tag = "<span class=\"btn btn-sm btn-outline-primary\">{$icon_tag}{$invalid_label}</span>";
        }
        return $tag;
    }

    /**
     * label badge tag
     *
     * @param object $value
     * @param string $tag
     * @param array $attributes
     * @param string $type
     * @return string
     */
    static function activeLabelTag($value, $tag = LABEL_TRUE, $attributes = null, $type = 'danger') {
        if ($value) {
            $html_class = " btn btn-sm btn-{$type}";
            $attributes['class'].= $html_class;
            $tag = self::labelTag($tag, $attributes);
            return $tag;
        }
    }

    /**
     * nav active
     *
     * @param string $key
     * @param object $selected
     * @return string
     */
    static function navActive($key, $selected) {
        $tag = "nav-link";
        if ($key == $selected) $tag.=' active';
        return $tag;
    }

    /**
     * label badge tag
     *
     * @param string $key
     * @param object $selected
     * @return string
     */
    static function linkActive($key, $selected = null) {
        $tag = '';
        if (!$selected) $selected = $GLOBALS['controller']->name;
        if ($key == $selected) $tag.=' active';
        return $tag;
    }
    
}