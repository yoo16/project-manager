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
            $attribute = DB::table('Attribute')->fetch($view_item['attribute_id']);

            if ($attribute->value['fk_attribute_id']) {
                $fk_attribute = DB::table('Attribute')->fetch($attribute->value['fk_attribute_id']);
                $fk_model = DB::table('Model')->fetch($fk_attribute->value['model_id']);

                $value = '$this->'."{$fk_model->value['entity_name']}->value['{$fk_attribute->value['name']}']";
                $value = '<?= ' . $value . ' ?>';
                return $value;
            }
        }
    }

}