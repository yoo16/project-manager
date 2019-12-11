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

    /**
     * hidden vlaue
     *
     * @param ViewItem $view_item
     * @return void
     */
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
     * get by attribute
     * 
     * TODO: view is instance by setView() ?
     *
     * @param View $view
     * @param Attribute $attribute
     * @return void
     */
    function getByAttribute($view, $attribute)
    {
        if (!$view->value) return;
        if (!$attribute->value) return;

        $view_item = DB::model('ViewItem');
        $view_item->where('view_id', $view->value['id'])
                  ->where('attribute_id', $attribute->value['id'])
                  ->one();
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
        $view = DB::model('View')->where('page_id', $page->value['id'])->all();
        if (!$view->values) return;
        foreach ($view->values as $view->value) {
            $this->createByView($page->model, $view);
        }
    }

    /**
     * create by view
     *
     * @return void
     */
    function createByView($model, $view)
    {
        $attribute = $model->relation('Attribute')->idIndex()->all();
        if (!$attribute->values) return;
        if (!$view->value) return;

        foreach ($attribute->values as $attribute->value) {
            if (!in_array($attribute->value['name'], PwEntity::$app_columns)) {
                $view_item = DB::model('ViewItem')->getByAttribute($view, $attribute);
                if (!$view_item->value['id']) {
                    $posts = [];
                    $posts['view_id'] = $view->value['id'];
                    $posts['attribute_id'] = $attribute->value['id'];

                    if ($view->value['name'] == 'edit') {
                        if ($attribute->value['type'] == 'bool') {
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