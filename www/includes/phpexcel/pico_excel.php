<?php
// require this file when exporting

include 'includes/phpexcel/PHPExcel.php';
include 'includes/phpexcel/PHPExcel/Writer/Excel2007.php';

class PicoExport extends PHPExcel
{
	var $rowCount = 1;
	var $filename;
	
	function __construct($title, $filename)
	{
		parent::__construct();
		
		$this->setActiveSheetIndex(0);
		$this->getProperties()->setCreator("Pico");
		$this->getProperties()->setLastModifiedBy("Pico");
		$this->getProperties()->setTitle($title);
		$this->getProperties()->setSubject($title);
		$this->getProperties()->setDescription("Pico Exported Document");
		$this->filename = $filename;
	}
	
	function addRow($row)
	{
		$rowNum = $this->rowCount;
		
		$counter = 0;
		
		foreach ($row as $cell)
		{
			$field = '';
			
			$firstLetterIndex = floor($counter / 26);
			if ($firstLetterIndex > 0)
			{
				$field .= chr(64+$firstLetterIndex);
			}
			$secondLetterIndex = $counter % 26;
			$field .= chr(65+$secondLetterIndex);
			$field .= $rowNum;
			// $field should produce "a1, b1, ... aa1, bb1 etc"
			
			$this->getActiveSheet()->SetCellValue($field, $cell);
			$counter++;
		}
		$this->rowCount++;
	}
	
	function output()
	{
		$tmpfile   = tempnam('/tmp', 'picoxlsx_');
		$objWriter = new PHPExcel_Writer_Excel2007($this);
		$objWriter->save($tmpfile);
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
		header('Content-Disposition: attachment;filename="'.$this->filename.'"'); 
		header('Cache-Control: max-age=0'); 
		echo file_get_contents($tmpfile);
	}
	
	function saveAsfile($path)
	{
		if (substr($path, -1) != '/')
		{
			$path .= '/';
		}
		$full_path = $path . $this->filename;
		
		$objWriter = new PHPExcel_Writer_Excel2007($this);
		$objWriter->save($full_path);
	}
}
?>