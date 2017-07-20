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
        $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
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
            $sheet = $book->createSheet()->setTitle($pg_class['relname']);

            $pg_attributes = $pgsql_entity->attributeValues($pg_class['relname']);

            $row = 1;
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
        $writer = PHPExcel_IOFactory::createWriter($book, 'Excel2007');

        FileManager::createDir($tmp_dir);
        $writer->save($export_path);
        //var_dump($pg_classes);
    }

    /**
     * pg_connect info
     *
     * @return string
     */
    function convertPgConnectionString() {
        $dbname = '';
        $host = 'localhost';
        $port = '5432';
        $user = 'postgres';

        if ($this->value['name']) $dbname = $this->value['name'];
        if ($this->value['hostname']) $host = $this->value['hostname'];
        if ($this->value['port']) $port = $this->value['port'];
        if ($this->value['user_name']) $user = $this->value['user_name'];

        if (!$dbname) return;

        $result = "host={$host} port={$port} dbname={$dbname} user={$user}";

        return $result;
    }


    /**
     * pg_connect info
     *
     * @return array
     */
    function pgConnectArray() {
        $result['dbname'] = '';
        $result['host'] = 'localhost';
        $result['port'] = '5432';
        $result['user'] = 'postgres';

        if ($this->value['name']) $result['dbname'] = $this->value['name'];
        if ($this->value['hostname']) $result['host'] = $this->value['hostname'];
        if ($this->value['port']) $result['port'] = $this->value['port'];
        if ($this->value['user_name']) $result['user'] = $this->value['user_name'];

        if (!$result['dbname']) return;
        return $result;
    }

}