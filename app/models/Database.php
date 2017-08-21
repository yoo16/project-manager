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
                                         ->select();
        return $database->values;
    }

    function checkProjectManager() {
        $pgsql_entity = new PgsqlEntity();
        $pg_database = $pgsql_entity->pgDatabase();
        if (!$pg_database) return false;
        
        $pg_tables = $pgsql_entity->pgTables();
        if (!$pg_tables) {
            return false;
        }
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
        $pg_classes = $pgsql_entity->pgClassArray();

        $file_name = "{$this->value['name']}.xlsx";
        $tmp_dir = BASE_DIR.'tmp/';
        $export_path = "{$tmp_dir}{$file_name}";

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
        $this->sheet = $book->removeSheetByIndex(0);
        foreach ($pg_classes as $pg_class) {
            $row = 3;

            $is_numbering = PgsqlEntity::isNumberingName($pg_class['relname']);
            if (!$is_numbering) {
                $this->sheet_name = $this->excelSheetName($pg_class['relname']);
                $this->sheet = $book->createSheet()->setTitle($this->sheet_name);

                $this->sheet->setCellValueByColumnAndRow(0, 1, 'Table Name');
                $this->sheet->setCellValueByColumnAndRow(1, 1, $pg_class['relname']);

                //attribute
                $pg_attributes = $pgsql_entity->attributeArray($pg_class['relname']);
                $row = $this->createExcelAttributes($row, $pg_attributes);

                //TODO pg_attributes index is attnum
                foreach ($pg_attributes as $pg_attribute) {
                    if ($pg_attribute['attnum'] > 0) $attributes[$pg_attribute['attnum']] = $pg_attribute;
                }

                //constraint 
                if ($pg_class['pg_constraint']) {
                    $row+= 3;
                    $row = $this->createExcelConstraints($row, $pg_class['pg_constraint'], $attributes);
                }
            }

            $this->autoSize(10);
        }
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

    function createExcelAttributes($row, $pg_attributes) {
        $this->sheet->setCellValueByColumnAndRow(0, $row, 'attribute');
        $this->sheet->setCellValueByColumnAndRow(1, $row, 'comment');
        $this->sheet->setCellValueByColumnAndRow(2, $row, 'type');
        $this->sheet->setCellValueByColumnAndRow(3, $row, 'length');
        $this->sheet->setCellValueByColumnAndRow(4, $row, 'primary key');
        $this->sheet->setCellValueByColumnAndRow(5, $row, 'not null');

        $this->drawBorders($row, 5);

        foreach ($pg_attributes as $pg_attribute) {
            $row++;
            $this->sheet->setCellValueByColumnAndRow(0, $row, $pg_attribute['attname']);
            $this->sheet->setCellValueByColumnAndRow(1, $row, $pg_attribute['comment']);
            $this->sheet->setCellValueByColumnAndRow(2, $row, $pg_attribute['udt_name']);
            $this->sheet->setCellValueByColumnAndRow(3, $row, $pg_attribute['character_maximum_length']);
            $this->sheet->setCellValueByColumnAndRow(4, $row, $pg_attribute['is_primary_key']);
            $this->sheet->setCellValueByColumnAndRow(5, $row, ($pg_attribute['attnotnull'] == 't'));

            $this->drawBorders($row, 5);
        }
        return $row;
    }

    function createExcelConstraints($row, $pg_constraints, $attributes) {
        if ($pg_constraints) {
            $this->sheet->setCellValueByColumnAndRow(0, $row, 'table');
            $this->sheet->setCellValueByColumnAndRow(1, $row, 'type');
            $this->sheet->setCellValueByColumnAndRow(2, $row, 'attribute');

            $this->drawBorders($row, 2);

            foreach ($pg_constraints as $pg_constraint) {
                $is_numbering_constraint = PgsqlEntity::isNumberingName($pg_constraint['conname']);
                if (!$is_numbering_constraint) {
                    foreach ($pg_constraint['conkey'] as $index => $attnum) {
                        $row++;
                        if ($index == 0) {
                            $this->sheet->setCellValueByColumnAndRow(0, $row, $pg_constraint['conname']);
                            $this->sheet->setCellValueByColumnAndRow(1, $row, PgsqlEntity::$constraint_keys[$pg_constraint['contype']]);
                        }
                        $this->sheet->setCellValueByColumnAndRow(2, $row, $attributes[$attnum]['attname']);
                        $this->drawBorders($row, 2);
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