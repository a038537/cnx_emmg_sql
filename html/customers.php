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
class AutoComplete extends Common
{
	protected $Editor;
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
		$defaultSessionData['orderByColumn'] = 'first_name';

		$defaultSessionData = base64_encode($this->Editor->jsonEncode($defaultSessionData));

		$javascript = '
			<script type="text/javascript">
				var ' . $this->mateInstances[0] . ' = new mate("' . $this->mateInstances[0] . '");
				' . $this->mateInstances[0] . '.setAjaxInfo({url: "' . $_SERVER['PHP_SELF'] . '", history: true});
				' . $this->mateInstances[0] . '.init("' . $defaultSessionData . '");

				function startAutoComplete()
				{
					$("input[type=text]#'.$this->mateInstances[0].'ppua").autocomplete({
						source: function(request, response) {
							$("#ajaxLoader1").css("display","block");
							var responseFun = function(data, textStatus, jqXHR) {
								response(data, textStatus, jqXHR);
								$("#ajaxLoader1").css("display","none");
							}
							$.getJSON("'.$_SERVER['PHP_SELF'].'", { dept: request.term }, responseFun);
						}
					});

					$("input[type=text]#'.$this->mateInstances[0].'chid").autocomplete({
						source: function(request, response) {
							$("#ajaxLoader1").css("display","block");
							var responseFun = function(data, textStatus, jqXHR) {
								response(data, textStatus, jqXHR);
								$("#ajaxLoader1").css("display","none");
							}
							$.getJSON("'.$_SERVER['PHP_SELF'].'", { chid: request.term }, responseFun);
						}
					});

				}

			</script>';
		echo $javascript;
	}

	public function autoCompleteCallback()
	{
		$this->Editor->addJavascript('startAutoComplete();');
	}

	protected function initiateEditor()
	{

		$tableColumns['id'] = array(
			'display_text' => 'ID',
			'perms' => 'T'
		);

		$tableColumns['temp'] = array(
			'display_text' => 'Customer',
			'display_mask' => "concat(firstname,' ',lastname,', ',street,', ',zipcode,', ',city,', ',state)",
			//'view_fun' => 'concat(firstname,' ',lastname)',
			'perms' => 'T'
		);


		$tableColumns['firstname'] = array(
			'display_text' => 'First Name',
			'perms' => 'EVCAXQSHOF',
			'req' => true
		);

		$tableColumns['lastname'] = array(
                        'display_text' => 'Last Name',
                        'perms' => 'EVCAXQSHOF',
			'req' => true
                );

		$tableColumns['street'] = array(
                        'display_text' => 'Street',
                        'perms' => 'EVCAXQSHOF',
			'req' => true
                );

                $tableColumns['city'] = array(
                        'display_text' => 'City',
                        'perms' => 'EVCAXQSHOF',
			'req' => true
                );

		$tableColumns['zipcode'] = array(
                        'display_text' => 'ZIP-Code',
                        'perms' => 'EVCAXQSHOF',
			'req' => true
                );

		$tableColumns['state'] = array(
                        'display_text' => 'State',
                        'perms' => 'EVCAXQSHOF',
                        'req' => true
                );

		$tableColumns['phone'] = array(
			'display_text' => 'Phone Number',
			'perms' => 'EVCAXQSHOF'
		);

		$tableColumns['email'] = array(
			'display_text' => 'E-Mail',
			'perms' => 'EVCAXQSHOF', 'req' => true
		);


		$tableColumns['changed'] = array(
			'display_text' => 'edited',
			'perms' => 'T',
			'order_mask' => 'changed',
			'range_mask' => 'changed',
			'calendar' => array('js_format' => 'dd MM yy',
			'options' => array('showButtonPanel' => true)),
			'col_header_info' => 'style="width: 250px;"',
		);


		$tableName = 'customers';
		$primaryCol = 'id';
		$errorFun = array(&$this,'logError');
		$permissions = 'EAVDQCSXHOI';

		$this->Editor = new AjaxTableEditor($tableName,$primaryCol,$errorFun,$permissions,$tableColumns);
		$this->Editor->setConfig('tableInfo','cellpadding="1" cellspacing="1" align="center" width="1100" class="mateTable"');
		$this->Editor->setConfig('orderByColumn','ppua');
		$this->Editor->setConfig('tableTitle','Customers <div style="font-size: 12px; font-weight: normal;"></div>');
		$this->Editor->setConfig('addRowTitle','Add Customer');
		$this->Editor->setConfig('editRowTitle','Edit Customer');
		$this->Editor->setConfig('addScreenFun',array(&$this,'autoCompleteCallback'));
		$this->Editor->setConfig('editScreenFun',array(&$this,'autoCompleteCallback'));
		$this->Editor->setConfig('instanceName',$this->mateInstances[0]);
		$this->Editor->setConfig('paginationLinks',true);
		//$this->Editor->setConfig('viewQuery',true);
	}

	public function getDeptSuggestions()
	{
		$depts = array();
		$query = "select ppua from cards";
		$stmt = DBC::get()->prepare($query);
		$stmt->execute(array('dept' => $_GET['dept'].'%'));
		while($row = $stmt->fetch())
		{
			$depts[] = $row['ppua'];
		}
		echo $this->Editor->jsonEncode($depts);
	}


	public function getchidSuggestions()
	{
		$depts = array();
		$query = "select chid from providers";
		$stmt = DBC::get()->prepare($query);
		$stmt->execute(array('chid' => $_GET['chid'].'%'));
		while($row = $stmt->fetch())
		{
			$depts[] = $row['chid'];
		}
		echo $this->Editor->jsonEncode($depts);
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
			//var_dump('jsdflk');
			//exit();
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
        else if(isset($_GET['dept']))
        {
        	$this->getDeptSuggestions();
        }
        else if(isset($_GET['chid']))
        {
                $this->getchidSuggestions();
        }
		else
		{
			$this->displayHeaderHtml();
			$this->displayHtml();
			$this->displayFooterHtml();
		}
	}
}
$page = new AutoComplete();
?>
