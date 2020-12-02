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
class MultipleTables extends Common
{
	protected $mateInstances = array('mate1_','mate2_');
	
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
		$html .= '	
			<br /><br /><br />
			
			<div id="'.$this->mateInstances[1].'information">
			</div>
			
			<div id="'.$this->mateInstances[1].'titleLayer" class="mateTitleDiv">
			</div>
			
			<div id="'.$this->mateInstances[1].'tableLayer" class="mateTableDiv">
			</div>
			
			<div id="'.$this->mateInstances[1].'recordLayer" class="mateRecordLayerDiv">
			</div>		
			
			<div id="'.$this->mateInstances[1].'searchButtonsLayer" class="mateSearchBtnsDiv">
			</div>';
			
		echo $html;
		
		// Set default session configuration variables here
		$defaultSessionData['orderByColumn'] = 'first_name';
		$defaultSessionData['displayNum'] = 5;

		$defaultSessionData = base64_encode(json_encode($defaultSessionData));
		
		$javascript = '	
			<script type="text/javascript">
				var ' . $this->mateInstances[0] . ' = new mate("' . $this->mateInstances[0] . '");
				' . $this->mateInstances[0] . '.setAjaxInfo({url: "MultipleTablesConfig1.php", history: true});
				' . $this->mateInstances[0] . '.init("' . $defaultSessionData . '");

				var ' . $this->mateInstances[1] . ' = new mate("' . $this->mateInstances[1] . '");
				' . $this->mateInstances[1] . '.setAjaxInfo({url: "MultipleTablesConfig2.php", history: true});
				' . $this->mateInstances[1] . '.init("' . $defaultSessionData . '");
				
				function startAutoComplete(mateInstance)
				{
					$("input[type=text]#"+mateInstance+"department").autocomplete({
						source: function(request, response) {
							$("#ajaxLoader1").css("display","block");
							var responseFun = function(data, textStatus, jqXHR) {
								response(data, textStatus, jqXHR);
								$("#ajaxLoader1").css("display","none");
							}
							$.getJSON("'.$_SERVER['PHP_SELF'].'", { dept: request.term }, responseFun);
						}
					});
				}
				
			</script>';
		echo $javascript;
	}

	public function getDeptSuggestions()
	{
		$depts = array();
		$query = "select distinct department from employees where department like :dept_var limit 20";
		$queryParams['dept_var'] = $_GET['dept'].'%';
		$stmt = DBC::get()->prepare($query);
		$stmt->execute($queryParams);
		while($row = $stmt->fetch())
		{
			$depts[] = $row['department'];
		}
		//throw new Exception(json_encode($depts));
		echo json_encode($depts);
	}
	
	function __construct()
	{
		session_start();
		ob_start();
        if(isset($_GET['dept']))
        {
        	$this->getDeptSuggestions();
        }
        else
        {
			$this->displayHeaderHtml();
			$this->displayHtml();
			$this->displayFooterHtml();
		}
	}
}
$page = new MultipleTables();
?>
