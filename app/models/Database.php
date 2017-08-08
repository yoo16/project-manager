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

    function checkProjectManager() {
        $pgsql_entity = new PgsqlEntity();
        $pg_database = $pgsql_entity->pgDatabase();
        return $pg_database;
    }
   
    function drawBorders($sheet, $row, $numbers) {
        for ($col = 0; $col <= $numbers; $col++) {
            $sheet->getStyleByColumnAndRow($col, $row)
                  ->getBorders()
                  ->getAllBorders()
                  ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        }
        return $sheet;
    }

    /**
     * export database
     *
     * @return bool
     */
    function exportDatabase() {
        date_default_timezone_set('Asia/Tokyo');
        require BASE_DIR.'/vendor/autoload.php';

        $database = DB::table('Database')->fetch($this->value['id']);
        $pgsql_entity = new PgsqlEntity($database->pgInfo());
        //$pg_database = $pgsql_entity->pgDatabase();
        $pg_classes = $pgsql_entity->pgClassArray();

        $file_name = "{$database->value['name']}.xlsx";
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

        $sheet = $book->getActiveSheet();
        $startedOn = time();

        $sheet = $book->removeSheetByIndex(0);
        foreach ($pg_classes as $pg_class) {
            $row = 3;

            $is_numbering = PgsqlEntity::isNumberingName($pg_class['relname']);
            if (!$is_numbering) {
                $sheet_name = $pg_class['relname'];
                if (mb_strlen($sheet_name) > 30) {
                    $sheet_name = mb_substr($sheet_name,0, 30);
                }
                $sheet = $book->createSheet()->setTitle($sheet_name);
                $pg_attributes = $pgsql_entity->attributeArray($pg_class['relname']);

                $sheet->setCellValueByColumnAndRow(0, 1, 'Table Name');
                $sheet->setCellValueByColumnAndRow(1, 1, $pg_class['relname']);

                $sheet->setCellValueByColumnAndRow(0, $row, 'attribute');
                $sheet->setCellValueByColumnAndRow(1, $row, 'type');
                $sheet->setCellValueByColumnAndRow(2, $row, 'length');
                $sheet->setCellValueByColumnAndRow(3, $row, 'primary key');
                $sheet->setCellValueByColumnAndRow(4, $row, 'not null');
                $sheet->setCellValueByColumnAndRow(5, $row, 'comment');

                $this->drawBorders($sheet, $row, 5);

                $attributes = null;
                foreach ($pg_attributes as $pg_attribute) {
                    $row++;
                    $sheet->setCellValueByColumnAndRow(0, $row, $pg_attribute['attname']);
                    $sheet->setCellValueByColumnAndRow(1, $row, $pg_attribute['udt_name']);
                    $sheet->setCellValueByColumnAndRow(2, $row, $pg_attribute['character_maximum_length']);
                    $sheet->setCellValueByColumnAndRow(3, $row, $pg_attribute['is_primary_key']);
                    $sheet->setCellValueByColumnAndRow(4, $row, ($pg_attribute['attnotnull'] == 't'));
                    $sheet->setCellValueByColumnAndRow(5, $row, $pg_attribute['comment']);

                    $this->drawBorders($sheet, $row, 5);
                    $attributes[$pg_attribute['attnum']] = $pg_attribute;
                }
            }


            if ($pg_class['pg_constraint']) {
                $row+= 3;
                $sheet->setCellValueByColumnAndRow(0, $row, 'table');
                $sheet->setCellValueByColumnAndRow(1, $row, 'attribute');

                $this->drawBorders($sheet, $row, 1);

                foreach ($pg_class['pg_constraint'] as $pg_constraint) {
                    $is_numbering_constraint = PgsqlEntity::isNumberingName($pg_constraint['conname']);
                    if (!$is_numbering_constraint) {
                        foreach ($pg_constraint['conkey'] as $index => $attnum) {
                            $row++;
                            if ($index == 0) $sheet->setCellValueByColumnAndRow(0, $row, $pg_constraint['conname']);
                            $attribute_name = $pg_attributes[$attnum]['attname'];
                            $sheet->setCellValueByColumnAndRow(1, $row, $attributes[$attnum]['attname']);
                            $this->drawBorders($sheet, $row, 1);
                        }
                    }

                }
            }
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