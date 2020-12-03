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
class DBC
{
	private static $instance = null;
	private static $dbcInfo = array(
		'host' => 'localhost'
		, 'db' => 'neovision'
		, 'user' => 'root'
		, 'password' => 'gftty2478'
	);
	private static $iniFile = '../../dbc.ini';
	
	public static function get()
	{
		if(self::$instance == null)
		{
			try
			{
				if(file_exists(self::$iniFile)) {
					self::$dbcInfo = parse_ini_file(self::$iniFile);
				}
				self::$instance = new PDO(
					'mysql:host=' . self::$dbcInfo['host'] . ';dbname=' . self::$dbcInfo['db'], 
					self::$dbcInfo['user'], 
					self::$dbcInfo['password'],
					array(
						PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					)
				);
			} 
			catch(PDOException $e)
			{
				echo "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
		}
		return self::$instance;
	}
	
	public function getPrepareSets($arr)
	{
		$prepareSets = array();
		foreach($arr as $column => $value)
		{
			$prepareSets[] = "`$column` = :".$column;
		}
		return $prepareSets;
	}
	
}
?>
