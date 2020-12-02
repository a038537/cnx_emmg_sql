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
require_once('php/lang/LangVars-en.php');
require_once('php/AjaxTableEditor.php');
class MultipleTablesConfig1 extends Common
{
	protected $Editor;
	protected $instanceName = 'mate1_';

	public function autoComplete()
	{
		$this->Editor->addJavascript('startAutoComplete(\''.$this->instanceName.'\');');
	}

	protected function initiateEditor()
	{
		$tableColumns['id'] = array(
			'display_text' => 'ID',
			'perms' => 'TVQSXO'
		);
		$tableColumns['first_name'] = array(
			'display_text' => 'First Name',
			'perms' => 'EVCTAXQSHOF'
		);
		$tableColumns['last_name'] = array(
			'display_text' => 'Last Name',
			'perms' => 'EVCTAXQSHOF'
		);
		$tableColumns['email'] = array(
			'display_text' => 'Email',
			'perms' => 'EVCTAXQSHOF'
		);
		$tableColumns['department'] = array(
			'display_text' => 'Department',
			'perms' => 'EVCTAXQSHOF'
		);
		$tableColumns['hire_date'] = array(
			'display_text' => 'Hire Date',
			'perms' => 'EVCTAXQSHO',
			'display_mask' => 'date_format(hire_date,"%d %M %Y")',
			'order_mask' => 'date_format(hire_date,"%Y-%m-%d %T")',
			'range_mask' => 'date_format(hire_date,"%Y-%m-%d %T")',
			'calendar' => array('js_format' => 'dd MM yy'),
			'col_header_info' => 'style="width: 250px;"',
			'null_array' => array('', '0000-00-00 00:00:00'),
		);

		$tableName = 'employees';
		$primaryCol = 'id';
		$errorFun = array(&$this,'logError');
		$permissions = 'EAVDQCSXHOI';

		$this->Editor = new AjaxTableEditor($tableName,$primaryCol,$errorFun,$permissions,$tableColumns);
		$this->Editor->setConfig('tableInfo','cellpadding="1" width="1100" class="mateTable"');
		$this->Editor->setConfig('orderByColumn','first_name');
		$this->Editor->setConfig('tableTitle','Auto Complete <div style="font-size: 12px; font-weight: normal;">on the department field</div>');
		$this->Editor->setConfig('addRowTitle','Add Employee');
		$this->Editor->setConfig('editRowTitle','Edit Employee');
		$this->Editor->setConfig('addScreenFun',array(&$this,'autoComplete'));
		$this->Editor->setConfig('editScreenFun',array(&$this,'autoComplete'));
		$this->Editor->setConfig('instanceName',$this->instanceName);
		$this->Editor->setConfig('paginationLinks',true);
		//$this->Editor->setConfig('iconTitle','Edit Employee');
	}

	function __construct()
	{
		session_start();
		ob_start();
		$this->initiateEditor();
		if(isset($_POST['json']))
		{
			if(ini_get('magic_quotes_gpc'))
			{
				$_POST['json'] = stripslashes($_POST['json']);
			}
			$this->Editor->data = $this->Editor->jsonDecode($_POST['json'],true);
			$this->Editor->setDefaults();
			$this->Editor->main();
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
			//echo chr(0xEF).chr(0xBB).chr(0xBF);
			echo $this->Editor->exportInfo();
			exit();
		}
		else
		{
			$this->displayHeaderHtml();
			$this->displayHtml();
			$this->displayFooterHtml();
		}
	}
}
$page = new MultipleTablesConfig1();
?>
