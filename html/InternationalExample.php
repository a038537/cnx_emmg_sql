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
require_once('php/lang/LangVars-es.php');
require_once('php/AjaxTableEditor.php');
class InternationalExample extends Common
{
	protected $Editor;
	protected $mateInstances = array('mate1_');

	protected function setHeaderFiles()
	{
		$this->headerFiles[] = '<script type="text/javascript" src="js/jquery/js/lang/jquery-ui-datepicker-es.js"></script>';
		$this->headerFiles[] = '<script type="text/javascript" src="js/lang/lang_vars-es.js"></script>';
	}

	protected function displayHtml()
	{
		$html = '

			<br />

			<div class="mateAjaxLoaderDiv"><div id="ajaxLoader1"><img src="images/ajax_loader.gif" alt="Loading..." /></div></div>

			<br /><br />

			<div id="'.$this->mateInstances[0].'information">
			</div>


			<div id="'.$this->mateInstances[0].'titleLayer" class="mateTitleDiv">
			</div>

			<div id="'.$this->mateInstances[0].'tableLayer" class="mateTableDiv">
			</div>

			<div id="'.$this->mateInstances[0].'recordLayer" class="mateRecordLayerDiv">
			</div>

			<div id="'.$this->mateInstances[0].'searchButtonsLayer" class="mateSearchBtnsDiv">
			</div>';

		echo $html;

		// Set default session configuration variables here
		$defaultSessionData['orderByColumn'] = 'first_name';

		$defaultSessionData = base64_encode($this->Editor->jsonEncode($defaultSessionData));

		$javascript = '
			<script type="text/javascript">
				var ' . $this->mateInstances[0] . ' = new mate("' . $this->mateInstances[0] . '");
				' . $this->mateInstances[0] . '.setAjaxInfo({url: "' . $_SERVER['PHP_SELF'] . '", history: true});
				' . $this->mateInstances[0] . '.init("' . $defaultSessionData . '");
			</script>';
		echo $javascript;
	}

	public function formatFileSize($col,$size,$row)
	{
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$retstring = '%01.2f %s';
		$lastsizestring = end($sizes);
		foreach ($sizes as $sizestring)
		{
				if ($size < 1024) { break; }
				if ($sizestring != $lastsizestring) { $size /= 1024; }
		}
		if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; }
		return sprintf($retstring, $size, $sizestring);
	}

	public function formatImage($col,$val,$row)
	{
		$html = '';
		if(strlen($val) > 0)
		{
			$html .= '<a target="_blank" href="DisplayFileFromDb.php?emp_id='.$row['id'].'"><img style="border: none;" src="DisplayFileFromDb.php?emp_id='.$row['id'].'" alt="'.$val.'" width="100" /></a>';
		}
		return $html;
	}

	public function deleteFile($info)
	{
		$query = "update emp_upload_db set file_data = '', file_type = '', file_name = '' where id = :id limit 1";
		$result = $this->Editor->doQuery($query,array('id' => $info['id']));
		if($result)
		{
			return true;
		}
		$this->Editor->warnings[] = 'There was an error deleting the file.';
		return false;
	}

	protected function initiateEditor()
	{
		$tableColumns['id'] = array(
			'display_text' => 'ID',
			'perms' => 'ETVQSFXO',
			'input_info' => 'style="width: 30px;"'
		);
		$tableColumns['first_name'] = array(
			'display_text' => 'Primer Nombre',
			'perms' => 'EVCTAXQSFHO',
			'req' => true,
			'input_info' => 'size="10"'
		);
		$tableColumns['last_name'] = array('display_text' => 'Apellido',
			'perms' => 'EVCTAXQSFHO',
			'val_fun' => array(&$this,'validateFun'),
			'input_info' => 'size="10"'
		);
		$tableColumns['email'] = array(
			'display_text' => 'Correo Electrónico',
			'perms' => 'EVCTAXQSFHO',
			'input_info' => 'size="20"',
			'req' => true,
		);
		$tableColumns['department'] = array(
			'display_text' => 'Departamento',
			'perms' => 'EVCAXQSFHOT',
			'select_array' => array(
				'Accounting' => 'Accounting',
				'Marketing' => 'Marketing',
				'Sales' => 'Sales',
				'Production' => 'Production'
			)
		);
		$tableColumns['file_name'] = array(
			'perms' => 'C'
		);
		$tableColumns['hire_date'] = array(
			'display_text' => 'Fecha de Contratación',
			'perms' => 'EVCTAXQSHOF',
			'display_mask' => 'date_format(hire_date,"%d %M %Y")',
			'order_mask' => 'date_format(hire_date,"%Y-%m-%d %T")',
			'range_mask' => 'date_format(hire_date,"%Y-%m-%d %T")',
			'calendar' => array('js_format' => 'dd MM yy'),
			'col_header_info' => 'style="width: 250px;"',
			'null_array' => array('', '0000-00-00 00:00:00'),
		);

		$tableName = 'emp_upload_db';
		$primaryCol = 'id';
		$errorFun = array(&$this,'logError');
		$permissions = 'AEVDCXHOUFQSI';

		$this->Editor = new AjaxTableEditor($tableName,$primaryCol,$errorFun,$permissions,$tableColumns);
		$this->Editor->setConfig('tableInfo','cellpadding="1" width="1200" class="mateTable"');
		$this->Editor->setConfig('tableTitle','International Example [Spanish]<br /><span style="font-size: 12px;">NOTE: In order for international dates to work properly the language pack needs to be installed on the server.</span>');
		$this->Editor->setConfig('addRowTitle','Add Employee');
		$this->Editor->setConfig('editRowTitle','Edit Employee');
		$this->Editor->setConfig('paginationLinks',true);
		$this->Editor->setConfig('instanceName',$this->mateInstances[0]);
	}

	protected function setMysqlLocale()
	{
		$query = "SET lc_time_names = 'es_ES'";
		$result = DBC::get()->query($query);
	}

	function __construct()
	{
		session_start();
		ob_start();
		$this->setMysqlLocale();
		$this->initiateEditor();
		if(isset($_POST['json']))
		{
			if(get_magic_quotes_gpc())
			{
				$_POST['json'] = stripslashes($_POST['json']);
			}
			$this->Editor->data = $this->Editor->jsonDecode($_POST['json'],true);
			$this->Editor->setDefaults();
			$this->Editor->main();
			//echo $this->Editor->jsonEncode($this->Editor->retArr);
		}
		else if(isset($_GET['mate_export']))
		{
			$this->Editor->data['sessionData'] = $_GET['session_data'];
			$this->Editor->setDefaults();
			ob_end_clean();
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Content-type: application/x-msexcel');
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.$this->Editor->tableName.'.csv"');
			// Add utf-8 signature for windows/excel
			echo chr(0xEF).chr(0xBB).chr(0xBF);
			echo $this->Editor->exportInfo();
			exit();
		}
		else
		{
			$this->setHeaderFiles();
			$this->displayHeaderHtml();
			$this->displayHtml();
			$this->displayFooterHtml();
		}
	}
}
$page = new InternationalExample();
?>