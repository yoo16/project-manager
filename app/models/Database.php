<?php
/**
 * Database
 *
 * @package 
 * @author  Yohei Yoshikawa
 * @create  2013-04-15 16:33:13
 */
require_once 'vo/_Database.php';

class Database extends _Database {

    function __construct($params=null) {
        parent::__construct($params);        
    }
    
   /**
    * validate
    *
    * @param 
    * @return void
    */ 
    function validate() {
        parent::validate();
    }

    
    function myList() {
        $database_name = DB_NAME;
        $database = DB::table('Database')->where("name != '{$database_name}'")
                                         ->limit(1)
                                         ->all();
        return $database->values;
    }

    function checkProjectManager() {
        $pgsql_entity = new PgsqlEntity();
        $pg_database = $pgsql_entity->pgDatabase();
        if (!$pg_database) {
            return false;
        }
        
        $pg_tables = $pgsql_entity->pgTables();
        if (!$pg_tables) {
            return false;
        }
        $this->is_success = true;
        return true;
    }

    /**
     * export database
     *
     * @return bool
     */
    function exportDatabase() {
        date_default_timezone_set('Asia/Tokyo');
        require BASE_DIR.'/vendor/autoload.php';

        $pgsql_entity = new PgsqlEntity($this->pgInfo());
        $pg_classes = $pgsql_entity->pgClassesArray();

        $file_name = "{$this->value['name']}.xlsx";
        $tmp_dir = BASE_DIR.'tmp/';
        $export_path = "{$tmp_dir}{$file_name}";

        $this->cell_height = 25;

        $book = new PHPExcel();
        $book->getProperties()
                ->setCreator("")
                ->setLastModifiedBy("")
                ->setCompany('')
                ->setCreated(date('Y-m-d H:i'))
                ->setManager('')
                ->setTitle("Title")
                ->setSubject("Subject")
                ->setDescription("Description");

        $this->sheet = $book->getActiveSheet();
        $this->sheet->setTitle('tables');
        $this->sheet->getDefaultStyle()
                    ->getAlignment()
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $row = 1;
        $this->sheet->setCellValueByColumnAndRow(0, $row, 'Table Name');
        $this->sheet->setCellValueByColumnAndRow(1, $row, 'Comment');
        $this->sheet->setCellValueByColumnAndRow(2, $row, 'Note');

        $this->drawBorders(1, 2);
        $this->drawFillColor(1, 2, 'FFEEEEEE');
        $this->sheet->getRowDimension(1)->setRowHeight($this->cell_height);

        foreach ($pg_classes as $index => $pg_class) {
            $is_numbering = PgsqlEntity::isNumberingName($pg_class['relname']);
            if (!$is_numbering) {
                $model = DB::table('Model')->where("name = '{$pg_class['relname']}'")->one();
                $pg_classes[$index]['model'] = $model;

                $row++;
                $this->sheet->setCellValueByColumnAndRow(0, $row, $pg_class['relname']);
                $this->sheet->setCellValueByColumnAndRow(1, $row, $pg_class['comment']);
                $this->sheet->setCellValueByColumnAndRow(2, $row, $model->value['note']);

                $sheet_name = $this->excelSheetName($pg_class['relname']);
                $url = "sheet://{$sheet_name}!A1";
                $this->sheet->getCellByColumnAndRow(0, $row)->getHyperlink()->setUrl($url);

                $this->drawBorders($row, 2);
                $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);
            }
        }
        //$this->autoSize(10);
        $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(0))->setWidth(40);
        $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(1))->setWidth(30);
        $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(2))->setWidth(100);

        foreach ($pg_classes as $pg_class) {
            $is_numbering = PgsqlEntity::isNumberingName($pg_class['relname']);
            if (!$is_numbering) {
                $this->sheet_name = $this->excelSheetName($pg_class['relname']);
                $this->sheet = $book->createSheet()->setTitle($this->sheet_name);
                $this->sheet->getDefaultStyle()
                            ->getAlignment()
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $this->sheet->setCellValueByColumnAndRow(0, 1, 'Table Name');
                $this->sheet->setCellValueByColumnAndRow(1, 1, $pg_class['relname']);

                $this->drawBorders(1, 1);
                $this->drawFillColor(1, 0, 'FFEEEEEE');
                $this->sheet->getRowDimension(1)->setRowHeight($this->cell_height);

                //attribute
                $row = 3;
                $pg_attributes = $pgsql_entity->attributeArray($pg_class['relname']);

                //TODO
                $model = $pg_class['model'];
                $_attributes = $model->hasMany('Attribute')->values;
                if ($_attributes) {
                    foreach ($_attributes as $attribute) {
                        if ($attribute['attnum'] > 0) {
                            if ($pg_attributes[$attribute['attnum']]) {
                                $pg_attributes[$attribute['attnum']]['attribute'] = $attribute;
                            }
                        }
                    }
                }

                $row = $this->createExcelAttributes($row, $pg_attributes);

                //constraint 
                if ($pg_class['pg_constraint']) {
                    $row+= 3;
                    $row = $this->createExcelConstraints($row, $pg_class);
                }
            }

            //$this->autoSize(10);
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(0))->setWidth(40);
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(1))->setWidth(30);
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(2))->setWidth(20);
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(3))->setWidth(20);
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(4))->setWidth(10);
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex(5))->setWidth(100);
        }

        $book->setActiveSheetIndex(0);
        $writer = PHPExcel_IOFactory::createWriter($book, 'Excel2007');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");
        $writer->save('php://output');

        //FileManager::createDir($tmp_dir);
        //$writer->save($export_path);
    }

    /**
     * excel sheet name
     *
     * @param  [type] $sheet_name [description]
     * @return [type]             [description]
     */
    function excelSheetName($sheet_name) {
        if (mb_strlen($sheet_name) > 30) {
            $sheet_name = mb_substr($sheet_name, 0, 30);
        }
        return $sheet_name;
    }

    /**
     * excel draw border
     * @param  [type] $numbers [description]
     * @return [type]          [description]
     */ 
    function drawBorders($row, $numbers) {
        for ($col = 0; $col <= $numbers; $col++) {
            $this->sheet->getStyleByColumnAndRow($col, $row)
                  ->getBorders()
                  ->getAllBorders()
                  ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
    }

    /**
     * excel draw border
     * @param  [type] $numbers [description]
     * @return [type]          [description]
     */ 
    function drawFillColor($row, $numbers, $color) {
        for ($col = 0; $col <= $numbers; $col++) {
            $this->sheet->getStyleByColumnAndRow($col, $row)
                  ->getFill()
                  ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                  ->getStartColor()
                  ->setARGB($color);
        }
    }

    /**
     * excel auto size
     *
     * @param  [type] $numbers [description]
     * @return [type]          [description]
     */
    function autoSize($numbers) {
        for ($col = 0; $col <= $numbers; $col++) {
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    function createExcelAttributes($row, $pg_attributes, $attributes) {
        //TODO array setting
        $this->sheet->setCellValueByColumnAndRow(0, $row, 'attribute');
        $this->sheet->setCellValueByColumnAndRow(1, $row, 'comment');
        $this->sheet->setCellValueByColumnAndRow(2, $row, 'type');
        $this->sheet->setCellValueByColumnAndRow(3, $row, 'length');
        $this->sheet->setCellValueByColumnAndRow(4, $row, 'not null');
        $this->sheet->setCellValueByColumnAndRow(5, $row, 'Note');

        $this->drawBorders($row, 5);
        $this->drawFillColor($row, 5, 'FFEEEEEE');
        $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);

        foreach ($pg_attributes as $pg_attribute) {
            $row++;
            $this->sheet->setCellValueByColumnAndRow(0, $row, $pg_attribute['attname']);
            $this->sheet->setCellValueByColumnAndRow(1, $row, $pg_attribute['comment']);
            $this->sheet->setCellValueByColumnAndRow(2, $row, $pg_attribute['udt_name']);
            $this->sheet->setCellValueByColumnAndRow(3, $row, $pg_attribute['character_maximum_length']);
            if ($pg_attribute['attnotnull'] == 't') {
                $this->sheet->setCellValueByColumnAndRow(4, $row, ($pg_attribute['attnotnull'] == 't'));
            }
            if ($pg_attribute['attribute']['note']) {
                $this->sheet->setCellValueByColumnAndRow(5, $row, $pg_attribute['attribute']['note']);
            }

            $this->drawBorders($row, 5);
            $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);
        }
        return $row;
    }

    function createExcelConstraints($row, $pg_class) {
        if ($pg_class['pg_constraint']) {
            $this->sheet->setCellValueByColumnAndRow(0, $row, 'constraint');
            $this->sheet->setCellValueByColumnAndRow(1, $row, 'type');
            $this->sheet->setCellValueByColumnAndRow(2, $row, 'attribute');
            $this->sheet->setCellValueByColumnAndRow(3, $row, '');

            $this->drawBorders($row, 3);
            $this->drawFillColor($row, 3, 'FFEEEEEE');
            $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);

            foreach ($pg_class['pg_constraint'] as $type => $pg_constraints) {
                foreach ($pg_constraints as $pg_constraint) {
                    $is_numbering_constraint = PgsqlEntity::isNumberingName($pg_constraint['conname']);
                    if (!$is_numbering_constraint) {
                        $row++;
                        if ($index == 0) {
                            $this->sheet->setCellValueByColumnAndRow(0, $row, $pg_constraint['conname']);
                            $this->sheet->setCellValueByColumnAndRow(1, $row, PgsqlEntity::$constraint_keys[$pg_constraint['contype']]);
                        }
                        $this->sheet->setCellValueByColumnAndRow(2, $row, $pg_constraint['attname']);

                        if ($pg_constraint['foreign_relname'] && $pg_constraint['foreign_attname']) {
                            $foreign_key = "{$pg_constraint['foreign_relname']} : {$pg_constraint['foreign_attname']}";
                            $this->sheet->setCellValueByColumnAndRow(3, $row, $foreign_key);
                        }

                        $this->drawBorders($row, 3);
                        $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);
                    }
                }
            }
        }
        return $row;
    }

    /**
     * PgsqlEntity
     *
     * @return PgsqlEntity
     */
    function pgsql() {
        if ($pg_info = $this->pgInfo()) {
            return new PgsqlEntity($pg_info);
        }
    }

    /**
     * pg_connect info
     *
     * @return array
     */
    function pgInfo() {
        $values['dbname'] = $this->value['name'];
        $values['host'] = 'localhost';
        $values['port'] = '5432';
        $values['user'] = 'postgres';

        if ($this->value['hostname']) $values['host'] = $this->value['hostname'];
        if ($this->value['port']) $values['port'] = $this->value['port'];
        if ($this->value['user_name']) $values['user'] = $this->value['user_name'];

        if (!$values['dbname']) return;
        return $values;
    }

}