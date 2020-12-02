<?php
/*
 * Mysql Ajax Table Editor
 *
 * Copyright (c) 2014 Chris Kitchen <info@mysqlajaxtableeditor.com>
 * All rights reserved.
 *
 * See COPYING file for license information.
 *
 * Download the latest version from
 * http://www.mysqlajaxtableeditor.com
 */
require_once('DBC.php');
require_once('Common.php');
class DisplayFileFromDb extends Common
{
	protected $empId;

	protected function setVars()
	{
		if(isset($_GET['emp_id']))
		{
			$this->empId = $_GET['emp_id'];
		}
	}

	protected function displayFile()
	{
		$query = "select file_name, file_type, file_data, octet_length(file_data) as file_size from emp_upload_db where id = :emp_id";
		$stmt = DBC::get()->prepare($query);
		$stmt->execute(array('emp_id' => $this->empId));
		if($row = $stmt->fetch())
		{
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-type: '.$row['file_type']);
			header('Content-length: '.$row['file_size']);
			header('Content-Disposition: filename="'.$row['file_name'].'"');
			echo $row['file_data'];
		}
		else
		{
			echo 'There was an error finding the specified file.';
		}
	}

	function __construct()
	{
		$this->setVars();
		$this->displayFile();
	}
}

$page = new DisplayFileFromDb();

?>
