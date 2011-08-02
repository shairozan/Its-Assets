<?
/*****************************************************************************
*	Its Assets - An online tool for managing assets, licenses, and IP addresses in the IT world
*	Copyright (C) 2011 Darrell Breeden (dj.breeden@ossfb.com). All rights reserved.
*	Public Works and Government Services Canada (PWGSC)
*   Architecture and Standards Directorate
*
*	Released July 2011
*
*	This program is a derivative work, based off of the simpleassets project originally
*	headed and maintained by Jeff Gordon <jgordon81@users.sourceforge.net>. 
*
*  	This program is free software licensed under the 
* 	GNU General Public License (GPL).
*
*	This file is part of Its Assets.
*
*	SimpleAssets is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
*	SimpleAssets is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with SimpleAssets; if not, write to the Free Software
*	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*******************************************************************************/


// contains functions:
// setup_complete($dbname)
// setup()

// setup a new database
function setup_complete($dbname) {
	global $print_screen;
	global $hrcolor;
	global $emp_db;
	$err_out = "";
	$success = true;
	$PHP_SELF = $_SERVER['PHP_SELF'];

	// attempt to select database, if unsuccessful create it
	if (!(mysql_select_db($dbname))) {
		$sql = "CREATE DATABASE " . $dbname . ";";
		$result = doSql($sql);
	}

	// attempt to select again now that it should be created, if fails, setup fails
	if (!(mysql_select_db($dbname))) {
		$err_out = $err_out . "<li>DATABASE CREATION / SELECTION ERROR: " . mysql_error() . " (" . $sql . ")";
		$success = false;
	} else {
		mysql_select_db($dbname);
		$sql = "CREATE TABLE `Assets` (`Id` int(14) unsigned NOT NULL auto_increment,`AssetTag` varchar(10) NOT NULL default '0',`AssetType` tinytext,`AssetSupplier` tinytext,`AssetModel` tinytext,`AssetSerial` tinytext,`AssetPrice` double default NULL,`name` tinytext,`exp_date` date,PRIMARY KEY  (`Id`),UNIQUE KEY `AssetTag` (`AssetTag`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>ASSETS Table Error: " . mysql_error() . " (" . $sql . ")";
		$sql = "CREATE TABLE `Assignments` (`id` int(10) unsigned NOT NULL auto_increment,`EmployeeId` int(14) default '0',`AssetId` int(14) default '0',`StartDate` bigint(14) unsigned default '0',`EndDate` bigint(14) unsigned default '0',`Approve` tinyint(3) unsigned default '0',`Temp` tinyint(3) unsigned default '0',`Completed` int(10) unsigned default '0',PRIMARY KEY  (`id`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>ASSIGNMENTS Table Error: " . mysql_error() . " (" . $sql . ")";
		if (strcmp($emp_db,"") == 0) {
			$sql = "CREATE TABLE `Employees` (`Id` bigint(14) unsigned NOT NULL auto_increment,`LoginName` varchar(30) default NULL,`UserPass` varchar(64) default NULL,`AccessLevel` tinyint(3) unsigned default NULL,`LastName` varchar(50) default NULL,`FirstName` varchar(50) default NULL,`EMail` varchar(150) default NULL,`Tel` tinytext,`Organization` tinytext,`Dept` tinytext,`Building` tinytext,`Floor` tinytext,`Workstation` tinytext,`Active` tinyint(3) default NULL,`Verified` bigint(14) unsigned NOT NULL default '0',`PerDiem` double NOT NULL default '0',`SessionId` varchar(50) default NULL,PRIMARY KEY  (`Id`)) TYPE=MyISAM;";
			if (!($result = doSql($sql))) $err_out = $err_out . "<li>EMPLOYEES Table Error: " . mysql_error() . " (" . $sql . ")";
		}
		$sql = "CREATE TABLE `IP` (`id` int(11) NOT NULL auto_increment,`assetid` int(10) unsigned default NULL,`employeeid` int(10) unsigned default NULL,`ip` varchar(20) default '0',PRIMARY KEY  (`id`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>IP Table Error: " . mysql_error() . " (" . $sql . ")";
		$sql = "CREATE TABLE `Licenses` (`id` int(10) unsigned NOT NULL auto_increment,`manufacturer` varchar(100) default NULL,`product` varchar(100) default NULL,`paymentmethod` varchar(50) default NULL,`price` double default NULL,`qty` int(10) unsigned default NULL,`oem` tinyint(3) unsigned default NULL,`purchasedate` bigint(20) unsigned default NULL,`expiredate` BIGINT(20)  UNSIGNED, `licensekey` TEXT,PRIMARY KEY  (`id`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>LICENSES Table Error: " . mysql_error() . " (" . $sql . ")";
		$sql = "CREATE TABLE `LicenseOwners` (`id` int(10) unsigned NOT NULL auto_increment,`assetid` int(10) unsigned default NULL,`licenseid` varchar(100) default NULL,PRIMARY KEY  (`id`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>LICENSEOWNERS Table Error: " . mysql_error() . " (" . $sql . ")";
		$sql = "CREATE TABLE `Links` (`id` int(10) unsigned NOT NULL auto_increment,`supplier` tinytext,`link` text,PRIMARY KEY  (`id`),KEY `id` (`id`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>LINKS Table Error: " . mysql_error() . " (" . $sql . ")";
		$sql = "CREATE TABLE `Msgs` (`id` int(10) unsigned NOT NULL auto_increment,`employeeid` int(11) default NULL,`assetid` int(11) default NULL,`date` int(14) unsigned default '0',`msgcode` tinyint(4) default NULL,`msg` text,PRIMARY KEY  (`id`)) TYPE=MyISAM;";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>MSGS Table Error: " . mysql_error() . " (" . $sql . ")";
		$sql = "INSERT INTO " . $emp_db . "Employees (LastName,FirstName,LoginName,UserPass,AccessLevel,Active) VALUES ('Admin','Admin','Admin',Password(''),2,1)";
		if (!($result = doSql($sql))) $err_out = $err_out . "<li>EMPLOYEES Table Error: " . mysql_error() . " (" . $sql . ")";
		if ($err_out > 0) $success = false;
	}
	if ($success == true) {
		menu_header("Setup","Installation Is Successful","setup.jpg");
		echo "<p><blockquote><font class='text12'>";
		echo "The installation has been successful. A new user with <b>Login: 'Admin'</b> and <b>no password</b> has been created. Click the button below to login and get started.";
		echo "</blockquote></font>";
		echo "<p><center><a href='" . $PHP_SELF . "?action=login'><img src='images/start.jpg' width=88 height=27 border=0></a></center>\n";
	} else {
		menu_header("Setup","<font color='#ff0033'>Installation Has Failed!</font>","setup.jpg");
		echo "<p><blockquote><font class='text12'>";
		echo "The installation has failed.  Please review the errors generated below to isolate the problem. If you continually receive the database already exists error, you may need to remove it manually from MySQL. <p>We also suggest consulting the <a href='" . $PHP_SELF . "?action=help' class='text12bold'>help</a> section<p>";
		echo "<p>The following errors were reported by MySQL:";
		echo "<ul>";
		echo $err_out;
		echo "</ul>";
		echo "</blockquote></font>";
		echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
	}
}

function setup() {
	global $install;
	global $server_status;
	global $hrcolor;
	global $sql_db;
	$PHP_SELF = $_SERVER['PHP_SELF'];

	if ($install == "1") {
		setup_complete($sql_db);
	} else {	
		menu_header("","Setup","setup.jpg");
		echo "<p><blockquote><font class='text12'>";
		echo "It appears this is your first time running this software. To install it, please click the button below. For additional assistance consult the <a href='" . $PHP_SELF . "?action=help' class='text12bold'>help</a> section";
		if (!$server_status) echo "<p><font color='#ff0033'><b>We have not detected a database with valid permissions.</b></font> To ensure a smooth install, please make sure you have MySQL Server running on port 3306 on the same server as this software.  Also ensure you have given write permissions to user account 'root' @ localhost.";
		echo "</blockquote></font>";
		if ($server_status) echo "<p><center><a href='" . $PHP_SELF . "?install=1'><img src='images/install.jpg' width=88 height=27 border=0></a></center>\n";
		else echo "<p><center><a href='" . $PHP_SELF . "'><img src='images/reload.jpg' width=88 height=27 border=0></a></center>\n";
	}
}
?>
