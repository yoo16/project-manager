<?php

class Tag {
    var $value;
    var $condition;

    function __construct() {
    }

   /**
    * table
    *
    * @param
    * @return Class
    */
    static function init() {
        $instance = new self();
        return $instance;
    }

    function tableItemForAttribute($attribute) {
        $entity = '$values'."['{$attribute['name']}']";
        if ($attribute['type'] == 'bool') {
            $tag = "FormHelper::activeLabelTag({$entity})";
        } else {
            $tag = $entity;
        }
        if ($tag) $this->php($tag);
    }

    function formEditForModel($model, $attribute) {
        $entity = '$this->'.$model['entity_name'];
        if ($attribute['type'] == 'bool') {
            $method = "formCheckbox('{$attribute['name']}')";
        } else {
            $method = "formInput('{$attribute['name']}')";
        }
        $param = "{$entity}->{$method}";
        if ($method) $this->php($param);
    }

    function phpTag($value = null) {
        return '<?= '.$value.' ?>';
    }

    function php($value = null) {
        if ($value) $this->value = $value;
        $this->value = $this->phpTag($this->value);
        $this->output();
    }

    function tableItemUrlForAttribute($attribute, $page, $model, $params = null) {
        $entity = '$values'."['{$attribute['name']}']";
        if ($attribute['type'] == 'bool') {
            $tag = "FormHelper::activeLabelTag({$entity})";
        } else {
            $tag = $entity;
        }

        if ($model) {
            $key_name = "{$model['entity_name']}_id";
            $value_name = '$values[\'id\']';
            $params[] = "'{$key_name}' => {$value_name}";

            $param = implode('=>', $params);
        }

        $label = $this->phpTag($tag);
        $href = "url_for('{$page['entity_name']}/', [{$param}])";
        $href = $this->phpTag($href);
        $this->value = "<a href=\"{$href}\">{$label}</a>";

        $this->output();
    }

    function ifs($value = null) {
        if ($value) $this->value = $value;
        $this->value = '<? if ('.$this->value.'): ?>'.PHP_EOL;
        $this->output();
    }

    function ife() {
        $this->value = '<? endif ?>'.PHP_EOL;
        $this->output();
    }

    function foreachs($value = null) {
        if ($value) $this->value = $value;
        $this->value = '<? foreach ('.$this->value.'): ?>'.PHP_EOL;
        $this->output();
    }

    function foreache() {
        $this->value = '<? endforeach ?>'.PHP_EOL;
        $this->output();
    }

    function add($value) {
        $this->value.= $value;
        return $this;
    }

    function ifcondition($condition) {
        $this->condition = $condition;
        return $this;
    }

    function join($value) {
        $this->value.= $value;
        return $this;
    }

    function this($value) {
        $this->value = '$this->'.$value;
        return $this;
    }

    function outputPHP() {
        $params['is_php'] = true;
        $this->output($params);
    }

    function outputIf($params = null) {
        $this->ifs();
        $this->output($params);
    }

    function output($params = null) {
        echo($this->value);
    }

}