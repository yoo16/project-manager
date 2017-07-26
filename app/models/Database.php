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
        $pg_database = $pgsql_entity->pgDatabase();
        $pg_classes = $pgsql_entity->tableArray();

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
            $relnames = explode('_', $pg_class['relname']);
            $last_relname = end($relnames);

            if (!is_numeric($last_relname)) {
                $sheet_name = $pg_class['relname'];
                if (mb_strlen($sheet_name) > 30) {
                    $sheet_name = mb_substr($sheet_name,0, 30);
                }
                $sheet = $book->createSheet()->setTitle($sheet_name);
                $pg_attributes = $pgsql_entity->attributeArray($pg_class['relname']);

                $sheet->setCellValueByColumnAndRow(0, 1, 'Table Name');
                $sheet->setCellValueByColumnAndRow(1, 1, $pg_class['relname']);

                $row = 3;
                $sheet->setCellValueByColumnAndRow(0, $row, 'attribute');
                $sheet->setCellValueByColumnAndRow(1, $row, 'type');
                $sheet->setCellValueByColumnAndRow(2, $row, 'length');
                $sheet->setCellValueByColumnAndRow(3, $row, 'primary key');
                $sheet->setCellValueByColumnAndRow(4, $row, 'not null');
                $sheet->setCellValueByColumnAndRow(5, $row, 'comment');
                for ($col = 0; $col <= 5; $col++) {
                    $sheet->getStyleByColumnAndRow($col, $row)
                          ->getBorders()
                          ->getAllBorders()
                          ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $sheet->calculateColumnWidths();
                }

                foreach ($pg_attributes as $pg_attribute) {
                    $row++;
                    $sheet->setCellValueByColumnAndRow(0, $row, $pg_attribute['attname']);
                    $sheet->setCellValueByColumnAndRow(1, $row, $pg_attribute['udt_name']);
                    $sheet->setCellValueByColumnAndRow(2, $row, $pg_attribute['character_maximum_length']);
                    $sheet->setCellValueByColumnAndRow(3, $row, $pg_attribute['is_primary_key']);
                    $sheet->setCellValueByColumnAndRow(4, $row, ($pg_attribute['attnotnull'] == 't'));
                    $sheet->setCellValueByColumnAndRow(5, $row, $pg_attribute['comment']);

                    for ($col = 0; $col <= 5; $col++) {
                        $sheet->getStyleByColumnAndRow($col, $row)
                              ->getBorders()
                              ->getAllBorders()
                              ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
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
        $result['dbname'] = $this->value['name'];
        $result['host'] = 'localhost';
        $result['port'] = '5432';
        $result['user'] = 'postgres';

        if ($this->value['hostname']) $result['host'] = $this->value['hostname'];
        if ($this->value['port']) $result['port'] = $this->value['port'];
        if ($this->value['user_name']) $result['user'] = $this->value['user_name'];

        if (!$result['dbname']) return;
        return $result;
    }

}