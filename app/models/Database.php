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
        $database = DB::model('Database')->where("name != '{$database_name}'")
                                         ->limit(1)
                                         ->all();
        return $database->values;
    }

    function checkProjectManager() {
        $pgsql_entity = new PwPgsql();
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
     * TODO: refectoring
     *
     * @param file_path $file_path
     * @return bool
     */
    function exportDatabase($file_path = '') {
        date_default_timezone_set('Asia/Tokyo');

        $autoloader_path = BASE_DIR.'vendor/autoload.php';
        if (!file_exists($autoloader_path)) {
            echo "Please, install PHPExcel on {$autoloader_path}.<br>".PHP_EOL;
            echo "composer require phpoffice/phpexcel".PHP_EOL;
            exit;
        }

        require BASE_DIR.'/vendor/autoload.php';

        $pgsql_entity = new PwPgsql($this->pgInfo());
        $pg_classes = $pgsql_entity->pgClassesArray();

        $file_name = "{$this->value['name']}.xlsx";
        $tmp_dir = BASE_DIR.'tmp/';
        //$export_path = "{$tmp_dir}{$file_name}";

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
            $is_numbering = PwPgsql::isNumberingName($pg_class['relname']);
            if (!$is_numbering) {
                $model = DB::model('Model')->where("name = '{$pg_class['relname']}'")->one();
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
            $is_numbering = PwPgsql::isNumberingName($pg_class['relname']);
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

        if ($file_path) {
            $writer->save($file_path);
        } else {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header("Content-Disposition: attachment;filename={$file_name}");
            header("Content-Transfer-Encoding: binary ");
            $writer->save('php://output');
        }
    }

    /**
     * excel sheet name
     *
     * @param  string $sheet_name
     * @return string
     */
    function excelSheetName($sheet_name) {
        if (mb_strlen($sheet_name) > 30) {
            $sheet_name = mb_substr($sheet_name, 0, 30);
        }
        return $sheet_name;
    }

    /**
     * excel draw border
     * @param  integer
     * @return void
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
     * @param  integer $numbers
     * @return void
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
     * @param  integer $numbers
     * @return void
     */
    function autoSize($numbers) {
        for ($col = 0; $col <= $numbers; $col++) {
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * create excel attributes
     *
     * @param integer $row
     * @param array $pg_attributes
     * @return void
     */
    function createExcelAttributes($row, $pg_attributes) {
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

    /**
     * create excel constraints
     *
     * @param integer $row
     * @param array $pg_class
     * @return void
     */
    function createExcelConstraints($row, $pg_class) {
        if ($pg_class['pg_constraint']) {
            $this->sheet->setCellValueByColumnAndRow(0, $row, 'constraint');
            $this->sheet->setCellValueByColumnAndRow(1, $row, 'type');
            $this->sheet->setCellValueByColumnAndRow(2, $row, 'attribute');
            $this->sheet->setCellValueByColumnAndRow(3, $row, '');

            $this->drawBorders($row, 3);
            $this->drawFillColor($row, 3, 'FFEEEEEE');
            $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);

            foreach ($pg_class['pg_constraint'] as $pg_constraints) {
                $index = 0;
                foreach ($pg_constraints as $pg_constraint) {
                    $is_numbering_constraint = PwPgsql::isNumberingName($pg_constraint['conname']);
                    if (!$is_numbering_constraint) {
                        $row++;
                        if ($index == 0) {
                            $this->sheet->setCellValueByColumnAndRow(0, $row, $pg_constraint['conname']);
                            $this->sheet->setCellValueByColumnAndRow(1, $row, PwPgsql::$constraint_keys[$pg_constraint['contype']]);
                        }
                        $this->sheet->setCellValueByColumnAndRow(2, $row, $pg_constraint['attname']);

                        $this->drawBorders($row, 3);
                        $this->sheet->getRowDimension($row)->setRowHeight($this->cell_height);
                        $index++;
                    }
                }
            }

        }
        return $row;
    }

    /**
     * PwPgsql
     *
     * @return PwPgsql
     */
    function pgsql() {
        if ($pg_info = $this->pgInfo()) {
            return new PwPgsql($pg_info);
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
        if ($this->value['user_name']) $values['user'] = $this->value['user_name'];
        if ($this->value['port']) $values['port'] = $this->value['port'];
        if ($this->value['password']) $values['password'] = $this->value['password'];

        if (!$values['dbname']) return;
        return $values;
    }

    /**
     * import
     * TODO $params structure
     *
     * @param array $params
     * @return void
     */
    static function import($params)
    {
        if (!$params['host']) $params['host'] = DB_HOST;
        $pgsql = new PwPgsql();
        $pg_database = $pgsql->setDBHost($params['host'])
                             ->setDBName($params['database_name'])
                             ->pgDatabase($params['database_name']);

        if ($pg_database) {
            $database = DB::model('Database')
                                    ->where('name', $params['database_name'])
                                    ->where('hostname', $params['host'])
                                    ->one();

            if (!$database->value) {
                $project_manager_pgsql = new PwPgsql();
                $posts['name'] = $pg_database['datname'];
                $posts['user_name'] = $project_manager_pgsql->user;
                $posts['hostname'] = $params['host'];
                $posts['port'] = $project_manager_pgsql->port;

                DB::model('Database')->insert($posts);
            }
        }
    }
}