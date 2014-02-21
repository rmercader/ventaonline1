<?PHP

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion-inicial.php');
include(DIR_BASE.'prendas/prenda.class.php');
require_once './excel/PHPExcel.php';

/*
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);*/
ini_set("memory_limit", "64M");

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setTitle("Planilla de stock de Prili")
							 ->setSubject("Planilla de stock de Prili")
							 ->setDescription("Planilla stock de Prili.");

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow(0, 1, 'Línea')
            ->setCellValueByColumnAndRow(1, 1, 'Categoría')
            ->setCellValueByColumnAndRow(2, 1, 'Nombre de Prenda')
            ->setCellValueByColumnAndRow(3, 1, 'Color')
            ->setCellValueByColumnAndRow(4, 1, 'Talle')
            ->setCellValueByColumnAndRow(5, 1, 'Cantidad');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Stock');

// Style the header
$objPHPExcel->getActiveSheet()
			->getStyle('A1:F1')
			->getFill()
			->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()
			->setARGB('FFD7E4BC');

$objPHPExcel->getActiveSheet()
			->getStyle('A1:F1')
			->getBorders()
			->getBottom()
			->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

$objPHPExcel->getActiveSheet()
			->getStyle('A1:F1')
			->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()
			->getStyle('A1:F1')
			->getFont()
			->setBold(true);

for($i = 0; $i <= 14; $i++){
	$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// We finished with the header, now it's time to bring the data
$objPrendas = new Prenda($Cnx);
$datosPrendas = $objPrendas->obtenerStockParaExcel();

$rowIdx = 2;
foreach ($datosPrendas as $row) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowIdx, $row['Linea']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowIdx, $row['Categoria']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowIdx, $row['Prenda']);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowIdx, $row['Color']);
	$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $rowIdx)->setValueExplicit($row['Talle'], PHPExcel_Cell_DataType::TYPE_STRING);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowIdx, $row['Cantidad']);
	$rowIdx++;
}
           
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Stock-Prendas.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>