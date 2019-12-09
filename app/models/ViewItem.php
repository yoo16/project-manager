<?php
/**
 * ViewItem 
 *
 * @create   
 */

require_once 'models/vo/_ViewItem.php';

class ViewItem extends _ViewItem {

   /**
    * validate
    *
    * @access public
    * @param
    * @return void
    */ 
    function validate() {
        parent::validate();
    }

    static function hiddenValue($view_item) {
        if ($view_item['attribute_id']) {
            $attribute = DB::model('Attribute')->fetch($view_item['attribute_id']);

            if ($attribute->value['fk_attribute_id']) {
                $fk_attribute = DB::model('Attribute')->fetch($attribute->value['fk_attribute_id']);
                $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']);

                if (!$fk_attribute->value) {
                    $view = DB::model('View')->fetch($view_item['view_id']);
                    $page = DB::model('Page')->fetch($view->value['page_id']);
                    var_dump($attribute->value['fk_attribute_id']);
                    var_dump($attribute->value['name']);
                    var_dump($view->value['name']);
                    var_dump($page->value['class_name']);
                    exit;
                }
                $value = '$this->'."{$fk_model->value['entity_name']}->value['{$fk_attribute->value['name']}']";
                $value = '<?= ' . $value . ' ?>';
                return $value;
            }
        }
    }


    /**
     * create all by page
     *
     * @param Page $page
     * @return void
     */
    function createAllByPage($page)
    {
        $page->bindBelongsTo('Model');
        $attribute = $page->model->relationMany('Attribute')->idIndex()->all();

        if (!$attribute->values) return;
        foreach ($attribute->values as $attribute) {
            if (!in_array($attribute['name'], PwEntity::$app_columns)) {
                $view_item = DB::model('ViewItem');
                $view_item->where('view_id', $this->view->value['id'])
                          ->where('attribute_id', $attribute['id'])
                          ->one();

                $posts = [];
                if (!$view_item->value['id']) {
                    $posts['view_id'] = $this->view->value['id'];
                    $posts['attribute_id'] = $attribute['id'];

                    if ($this->view->value['name'] == 'edit') {
                        if ($attribute['type'] == 'bool') {
                            $posts['csv'] = 'active';
                            $posts['form_type'] = 'radio';
                        }
                    }
                    //TODO define label
                    //$posts['label'] = $attribute['label'];
                    DB::model('ViewItem')->insert($posts);
                }
            }
        }
    }

}