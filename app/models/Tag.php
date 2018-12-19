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

    function tableItemForAttribute($attribute, $view_item) {
        $entity = '$values'."['{$attribute['name']}']";

        if ($attribute['fk_attribute_id']) {
            $fk_attribute = DB::model('Attribute')->fetch($attribute['fk_attribute_id']);
            $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']); 

            if ($fk_model->value) $tag = '$this->'.$fk_model->value["entity_name"]."->values[{$entity}]['{$view_item['label_column']}']";
        } else if ($view_item['csv']) {
            $tag = '$this->csv_options'."['{$view_item['csv']}'][{$entity}]";
        } else {
            if ($attribute['type'] == 'bool') {
                $tag = "PwForm::activeLabelTag({$entity})";
            } else {
                $tag = $entity;
            }
        }
        if ($tag) $this->php($tag);
    }

    function formInput($view_item, $model, $attribute) {
        $entity = '$this->'.$model['entity_name'];
        if ($attribute['type'] == 'bool') {
            $method = "formCheckbox('{$attribute['name']}')";
        } else if (in_array($attribute['type'], ['int2', 'int4', 'int8', 'float', 'float8', 'double', 'double precision', 'real'])) {
            $method = "formInput('{$attribute['name']}', ['class' => 'form-control col-4 number'])";
        } else {
            $method = "formInput('{$attribute['name']}', ['class' => 'form-control'])";
        }
        $param = "{$entity}->{$method}";
        if ($method) $this->php($param);
    }

    function phpTag($value = null, $has_equal = true) {
        if ($has_equal) {
            return '<?= '.$value.' ?>';
        } else {
            return '<? '.$value.' ?>';
        }
    }

    function php($value = null) {
        if ($value) $this->value = $value;
        $this->value = $this->phpTag($this->value);
        $this->output();
    }

    //TODO function param
    function tableItemUrlForAttribute($attribute, $page, $model, $view_item, $params = null) {
        $entity = '$values'."['{$attribute['name']}']";
        if ($attribute['type'] == 'bool') {
            $label = "PwForm::activeLabelTag({$entity})";
        } else if ($view_item['localize_string_id']) {
            //TODO
            $localize_string = DB::model('LocalizeString')->fetch($view_item['localize_string_id']);
            $label = $localize_string->value['name'];
        } else {
            $label = $entity;
        }

        if ($view_item['link_param_id_attribute_id']) {
            $link_param_id_attribute = DB::model('Attribute')->fetch($view_item['link_param_id_attribute_id']);

            $key_name = 'id';
            $value_name = "\$values['{$link_param_id_attribute->value['name']}']";

            $params[] = "'{$key_name}' => {$value_name}";
        }
        if ($model) {
            $key_name = "{$model['entity_name']}_id";
            $value_name = '$values[\'id\']';
            $params[] = "'{$key_name}' => {$value_name}";
        }

        if (is_array($params) && $params) {
            $param = implode(',', $params);
        }

        $label = $this->phpTag($label);
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

    function hidden($view_item, $model, $attribute) {
        $name = "{$model['entity_name']}[{$attribute[$view_item['attribute_id']]['name']}]";
        $value = ViewItem::hiddenValue($view_item);

        $tag = "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
        return $tag;
    }

    function formPassword($view_item, $model, $attribute) {
        if ($params) {
            $tag = '$this->'.$model['entity_name']."->formPassword('{$attribute['name']}', $params)";
        } else {
            $tag = '$this->'.$model['entity_name']."->formPassword('{$attribute['name']}')";
        }
        return $tag;
    }

    function formParams($view_item, $model, $attribute) {
        //model
        if ($view_item['csv']) {
            $params = "['csv' => '{$view_item['csv']}', 'unselect' => true]";
        } else if ($view_item['form_model_id']) {
            $fk_model = DB::model('Model')->fetch($view_item['form_model_id']); 
        } else if ($attribute['fk_attribute_id']) {
            $fk_attribute = DB::model('Attribute')->fetch($attribute['fk_attribute_id']);
            $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']); 
        }

        //where
        if ($view_item['where_model_id']) {
            $where_model = DB::model('Model')->fetch($view_item['where_model_id']); 
            $where_column = "{$where_model->value["entity_name"]}_id";
            $where_value = '{$this->'.$where_model->value["entity_name"]."->value['id']}";
            $where = "'where' => \"{$where_column} = {$where_value}\",";
        } else if ($view_item['where_attribute_id']) {
            $where_attribute = DB::model('Attribute')->fetch($view_item['where_attribute_id']); 
            $where_column = $where_attribute->value["name"];
            $where_value = '{$this->'.$model["entity_name"]."->value['{$where_attribute->value['name']}']}";
            $where = "'where' => \"{$where_column} = {$where_value}\",";
        }

        //order
        if ($view_item['where_order']) {
            $order = "'order' => '{$view_item['where_order']}',";
        }

        //label
        if ($view_item['label_column']) {
            $label_columns = explode(',', $view_item['label_column']);
            foreach ($label_columns as $label_column) {
                $labels[] = "'{$label_column}'";
            }
            $label = implode(',', $labels);
        }

        //value
        if ($view_item['value_column']) {
            $value_column = "'value_column' => '{$view_item['value_column']}',";
        }

        //params
        if ($fk_model->value) {
            $params = "[
                        'unselect' => true,
                        'label_separate' => '-',
                        'label' => [{$label}],
                        'model' => '{$fk_model->value["class_name"]}',
                        {$where}
                        {$order}
                        {$value_column}
                        ]";
        }
        return $params;
    }

    function formSelect($view_item, $model, $attribute) {
        $params = $this->formParams($view_item, $model, $attribute);

        if ($params) {
            $tag = '$this->'.$model['entity_name']."->formSelect('{$attribute['name']}', $params)";
        } else {
            $tag = '$this->'.$model['entity_name']."->formSelect('{$attribute['name']}')";
        }
        $tag = $this->phpTag($tag);
        echo($tag);
    }

    function formRadio($view_item, $model, $attribute) {
        $params = $this->formParams($view_item, $model, $attribute);

        if ($params) {
            $tag = '$this->'.$model['entity_name']."->formRadio('{$attribute['name']}', $params)";
        } else {
            $tag = '$this->'.$model['entity_name']."->formRadio('{$attribute['name']}')";
        }
        $tag = $this->phpTag($tag);
        echo($tag);
    }


    function requestInstance($page_model) {
        if ($page_model->values) {
            foreach ($page_model->values as $page_model_value) {
                if ($page_model_value['is_request_session']) {
                    $instance = '$this->'.$page_model_value['entity_name'];
                    $tag = "{$instance} = DB::model('{$page_model_value['class_name']}')->requestSession();";
                    echo($tag).PHP_EOL;
                }
            }
        }
    }

    function pageFilters($page_filter) {
        if ($page_filter->values) {
            foreach ($page_filter->values as $value) {
                $attribute = DB::model('Attribute')->fetch($value['attribute_id']);
                $eq = '=';
                if ($value['eq']) $eq = $value['eq'];
                if ($attribute->value) $filters[] = "'{$attribute->value['name']}' => ['value' => '{$value['value']}', 'eq' => '{$eq}']";
            }
            $filter = implode(', ', $filters);
            $name = '$filters';
            $tag = "public {$name} = [{$filter}];";

            echo($tag).PHP_EOL;
        }

    }

    /**
     * Undocumented function
     *
     * @param Model $model
     * @param Page $page
     * @return void
     */
    function listValues($model, $page) {
        $instance = '$this->'.$model->value['entity_name'];

        $return_space = PHP_EOL.'                                ';

        $filter_entity = '$this->filters';
        $filter = "->filter({$filter_entity})";

        if ($page->value['list_sort_order_columns']) {
            $sort_order_columns = explode(',', $page->value['list_sort_order_columns']);
            //TODO asc desc
            foreach ($sort_order_columns as $sort_order_column) {
                $sort_order_column = trim($sort_order_column);
                $order.= "->order('{$sort_order_column}')";
            }
        }
        if ($page->value['where_model_id']) {
            $parent_model = DB::model('Model')->fetch($page->value['where_model_id']);

            $parent_instance = '$this->'.$parent_model->value['entity_name'];
            $relation_many = "{$parent_instance}->relationMany";
            $redirect = '$this->redirectTo(\'root\')';

            $tag = "if (!{$parent_instance}->value) {$redirect};".PHP_EOL;
            $tag.= "        {$instance} = {$relation_many}('{$model->value['class_name']}')";
        } else {
            $tag = "{$instance} = DB::model('{$model->value['class_name']}')";
        }

        if ($order) $tag.= "{$return_space}{$order}";
        $tag.= "->all();";
        echo($tag).PHP_EOL;
    }

    /**
     * model values
     *
     * @param PageModel $page_model
     * @return void
     */
    function modelValues($page_model) {
        if ($page_model->values) {
            foreach ($page_model->values as $page_model_value) {
                if ($page_model_value['is_fetch_list_values']) {
                    $instance = '$this->'.$page_model_value['model_entity_name'];
                    $tag = "{$instance} = DB::model('{$page_model_value['model_class_name']}')->idIndex()->all();";
                    echo($tag).PHP_EOL;
                }
            }
        }
    }

    function sortbleLink() {
        $tag = $this->phpTag("include('views/components/lib/sortable.phtml')", false);
        echo($tag).PHP_EOL;
    }

    function valuesId() {
        $tag = '<?= $values[\'id\'] ?>';
        return $tag;
    }

}