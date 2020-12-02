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
class JoinExample extends Common
{
	protected $mateInstances = array('mate1_');

	protected function displayHtml()
	{
		$html = '

			<br />

			<div class="mateAjaxLoaderDiv"><div id="ajaxLoader1"><img src="images/ajax_loader.gif" alt="Loading..." /></div></div>

			<br /><br />

			<div id="'.$this->mateInstances[0].'information">
			</div>

			<div id="mateTooltipErrorDiv" style="display: none;"></div>


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
		$defaultSessionData['orderByColumn'] = 'employee_id';

		$defaultSessionData = base64_encode($this->Editor->jsonEncode($defaultSessionData));


		$javascript = '
			<script type="text/javascript">
				var ' . $this->mateInstances[0] . ' = new mate("' . $this->mateInstances[0] . '");
				' . $this->mateInstances[0] . '.setAjaxInfo({url: "' . $_SERVER['PHP_SELF'] . '", history: true});
				' . $this->mateInstances[0] . '.init("' . $defaultSessionData . '");
			</script>';
		echo $javascript;
	}

	public function updateLogin($empId,$instanceName)
	{
		$query = "select first_name, last_name from employees where id = :empId";
		$queryParams = array('empId' => $empId);
		$result = $this->Editor->doQuery($query,$queryParams);
		if($row = $result->fetch())
		{
			$suggestedLogin = strtolower(substr($row['first_name'],0,1).$row['last_name']).rand(100,999);
			$this->Editor->setHtmlValue('login',$suggestedLogin);
		}
	}

	public function checkForPassword($col,$val,$row)
	{
		if(strlen(trim($row['password'])) == 0)
		{
			return false;
		}
		return $val;
	}

	public function removePassword($col,$val,$row)
	{
		return '';
	}

	protected function initiateEditor()
	{
		$tableColumns['id'] = array(
			'display_text' => 'ID',
			'perms' => ''
		);
		$tableColumns['employee_id'] = array(
			'display_text' => 'Name', 'perms' => 'EVCTAXQS',
			'join' => array(
				'table' => 'employees',
				'column' => 'id',
				'display_mask' => "concat(employees.first_name,' ',employees.last_name)",
				'type' => 'left',
			),
			'input_info' => 'onchange="'.$this->mateInstances[0].'.toAjaxTableEditor(\'update_login\',$(this).val());"'
		);
		$tableColumns['login'] = array(
			'display_text' => 'Login',
			'perms' => 'EVCTAXQS'
		);
		$tableColumns['password'] = array(
			'display_text' => 'Password',
			'perms' => 'EVCAXQT',
			'input_type' => 'password',
			'on_edit_fun' => array(&$this,'checkForPassword'),
			'mysql_add_fun' => 'PASSWORD(:password)',
			//'mysql_add_fun' => 'PASSWORD(#VALUE#)',
			//'mysql_edit_fun' => 'PASSWORD(:password)',
			'mysql_edit_fun' => 'PASSWORD(#VALUE#)',
			'edit_fun' => array(&$this,'removePassword')
		);
		$tableColumns['account_type'] = array(
			'display_text' => 'Account Type',
			'perms' => 'EVCTAXQS',
			'select_array' => array('Admin' => 'Admin', 'User' => 'User'),
			'default' => 'User'
		);

		$tableName = 'login_info';
		$primaryCol = 'id';
		$errorFun = array(&$this,'logError');
		$permissions = 'EAVDQCSMXU';

		require_once('php/AjaxTableEditor.php');
		$this->Editor = new AjaxTableEditor($tableName,$primaryCol,$errorFun,$permissions,$tableColumns);
		$this->Editor->setConfig('tableInfo','cellpadding="1" width="900" class="mateTable"');
		$this->Editor->setConfig('tableTitle','Multiple Edit / User Action / Join Example');
		$this->Editor->setConfig('addRowTitle','Add Login Info');
		$this->Editor->setConfig('editRowTitle','Edit Login Info');
		$this->Editor->setConfig('viewRowTitle','View Login Info');
		$userActions = array('update_login' => array(&$this,'updateLogin'));
		$this->Editor->setConfig('userActions',$userActions);
		//$this->Editor->setConfig('viewQuery',true);
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
			echo chr(0xEF).chr(0xBB).chr(0xBF);
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
$page = new JoinExample();
?>