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
		echo '<h1>NeoVision CAS Studio</h1>';
		echo '<h3>Version 0.01a</h3>';
		echo '<p><a href="customers.php">Edit Customers</a></p>';
		echo '<p><a href="cards.php">Edit Smart Cards</a></p>';
		echo '<p><a href="providers.php">Edit Providers</a></p>';
		echo '<p><a href="subscript.php">Edit Subscriptions</a></p>';
                echo '<p><a href="systemkey.php">Edit Systemkey</a></p>';
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
