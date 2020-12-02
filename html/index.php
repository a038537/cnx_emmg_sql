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
require_once('Common.php');
class HomePage extends Common
{
	
	protected function displayHtml()
	{
		echo '<h2>Example Scripts</h2>';
		echo '<p><a href="AutoComplete.php">Auto Complete</a></p>';
		echo '<p><a href="CkEditor.php">CK Editor</a></p>';
		echo '<p><a href="InternationalExample.php">International Example</a></p>';
		echo '<p><a href="JoinExample.php">Multiple Row / User Action / Join Example</a></p>';
		echo '<p><a href="MultipleTables.php">Multiple Tables In Page</a></p>';
	}
	
	function __construct()
	{
		$this->showBackLink = false;
		$this->displayHeaderHtml();
		$this->displayHtml();
		$this->displayFooterHtml();
	}
}
$page = new HomePage();
?>