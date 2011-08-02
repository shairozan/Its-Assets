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

// database
// ----------
// db_connect($ip,$sql_login,$sql_pass)
// db_select($user_db)
// doSql($sql)

// security
// ----------
// login()
// loginbox()
// securityError()

// display functions
// ------------------
// menu_header($top,$title,$icon)
// import()
// doStylise($field, $name, $link)
// dateDropdown($ext,$month,$day,$year,$month_now_offset,$default_day)
// getMsg()

// misc string functions
// ---------------------
// q_replace($txt);
// html($txt);
// dehtml($txt);
// is_alphanum($input)
// is_alphanum_str($input)
// make_seed()

// connects to the database.
function db_connect($ip,$sql_login,$sql_pass) {
	@$db = mysql_pconnect($ip,$sql_login,$sql_pass);
	return $db;
}

// select the database from the server
function db_select($user_db) {
	return mysql_select_db($user_db);
}

// performs an sql statement and logs it
function doSql($sql) {
	$result = mysql_query($sql);
//	if ($result) echo "<p>" . $sql . " <b>number of rows=" . mysql_num_rows($result) . "</b>";
//	else " <b>no results</b>";
	return $result;
}




// determines login state
function login() {
	global $lastaction, $lastkey, $loginout, $login, $pass, $my_access_level, $activelogin, $activepass;
	global $my_emp_id;
	global $db_status;
	global $action;
	global $resetpassword;
	global $key;
	global $loginname;
	global $password;
	global $stored_login, $stored_pass;
	global $emp_db;
	global $HTTP_SERVER_VARS;
	global $HTTP_COOKIE_VARS;
	
	if (isset($_SESSION['sessionid'])) $sessionid = $_SESSION['sessionid'];
	else $sessionid = "";

	// Cookie domain
	$domain = $HTTP_SERVER_VARS['HTTP_HOST'];
    if (empty($domain)) $domain = getenv('HTTP_HOST');
	$domain = preg_replace('/:.*/', '', $domain);

	
	// check for at least one active user;
	$sql = "SELECT AccessLevel, Id FROM " . $emp_db . "Employees WHERE Active=1;";
	$is_user = (($result = doSql($sql)) && (mysql_num_rows($result)));
	if ((!$db_status) || (!$is_user)) $loginout = 2;

	if ($loginout == 2) {

		// logout
		$my_access_level = 0;
		$activelogin = "";
		$activepass = "";
		$my_emp_id = "";

		// remove the session id
		session_start();
		$sessionid = session_id();
		$sql = "UPDATE " . $emp_db . "Employees SET SessionId='' WHERE SessionId='" . $sessionid . "';";
		doSql($sql);
		$cookietime = time()+31536000;
		setcookie("sessionid", "", $cookietime, "/",$domain,1);
		$_SESSION['sessionid'] = "";
		session_unset();
		session_destroy();
		
	} elseif ($loginout == 1) {

		// login
		$sql = "SELECT AccessLevel, Id FROM " . $emp_db . "Employees WHERE LoginName='" . $login . "' and UserPass=password('" . $pass . "') AND Active=1;";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			//correct login
			$query_data = mysql_fetch_row($result);
			$my_access_level = $query_data[0];
			$my_emp_id = $query_data[1];
			$activelogin = $login;
			$activepass = $pass;
			
			// add the new session id
			session_start();
			$sessionid = session_id();
			doSql("UPDATE " . $emp_db . "Employees SET SessionId='" . $sessionid . "' WHERE LoginName='" . $login . "';");
			$cookietime = time()+31536000;
			$_SESSION['sessionid'] = $sessionid;
			setcookie("sessionid", $sessionid, $cookietime, "/",$domain,1);
		} else {
			//incorrect login
			$my_access_level = 0;
			$activelogin = "";
			$activepass = "";
			$my_emp_id = "";

			if (strcmp($lastaction,"login") == 0) $lastaction = "";
			header("Location: " . $PHP_SELF . "?action=login&lastaction=" . $lastaction . "&lastkey=" . html($lastkey) ."&loginfail=1");
			exit;
		}
	} else {
		// no login or logout

		// if sessionid not in cookies, try PHP sessions
		session_start();
		if ($sessionid == "") $sessionid = session_id();
		
		$sql = "SELECT " . $emp_db . "Employees.LoginName as LoginName, " . $emp_db . "Employees.UserPass as UserPass, " . $emp_db . "Employees.AccessLevel as AccessLevel, " . $emp_db . "Employees.Id as Id FROM " . $emp_db . "Employees WHERE Active=1 AND SessionId='" . $sessionid . "';";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
			// login name verified
			$my_access_level = $query_data["AccessLevel"];
			$my_emp_id = $query_data["Id"];
			$activelogin = $query_data["LoginName"];
			$activepass = $query_data["UserPass"];
		} else {
			// login name not verified
			$my_access_level = 0;
			$activelogin = "";
			$activepass = "";
			$my_emp_id = "";
		}
	}

	if ($is_user) return $my_emp_id;
	else return "E";
}

function loginbox($key) {
	global $action;
	global $lastaction;
	global $lastkey;
	global $loginfail;
	global $print_screen;
	global $hrcolor;
	global $demo_mode;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	menu_header("","Login","security.jpg");
	if ($loginfail == 1) echo "<center><font class='text11bold' color='#ff0033'>Login incorrect.</font></center>";
	echo "<form action='" . $PHP_SELF . "?action=" . $lastaction . "&lastkey=" . $lastkey . "&loginout=1' method='post'><blockquote>";
	echo "<center><table><tr>";
	echo "<td class='text12bold' align='right'>User:</td>";
	echo "<td class='text12'><input name='login' type='text' class='boxtext13' size=30></td>";
	echo "</tr></tr>";
	echo "<td class='text12bold' align='right'>Password:</td>";
	echo "<td class='text12'><input name='pass' type='password' class='boxtext13' size=30></td>";
	echo "</tr>";
	echo "<tr><td class='text12' colspan=2>";
	echo "<center>";
	echo "<a href='" . $PHP_SELF . "?action=employeeregister'><img src='images/register.jpg' width=88 height=27 border=0></a>";
	echo "<input type=image src='images/login.jpg' width=88 height=27 border=0>";
	if ($demo_mode == true) echo "<p><font color='#ff0033' class='text12bold'>Use login 'demo' and no password to login.</font>";
	echo "</center>";
	echo "</td></tr></table>";
	echo "</center></blockquote></form>";
}

// displays an error message when attempting to access admin features without logging in
function securityError() {
	global $loginout;
	global $hrcolor;
	global $key;
	global $action;
	global $key;
	global $lastaction;
	global $lastkey;
	$lastaction = $action;
	$lastkey = $key;
	if ($loginout != "") {
		echo "<p><font class=text18bold>Login Successful</font><hr size=0 color='" . $hrcolor . "'>";
		echo "<p><blockquote><font class='text12'>You have been logged in.  Click next below to begin browsing.</blockquote></font>";
		echo "<p><center><a href='" . $PHP_SELF . "'><img src='images/next.jpg' width=88 height=27 border=0></a></center>\n";
	} else {
		loginbox($key);
	}
}

// centralized function used to display the title headers
function menu_header($top,$title,$icon) {
	global $print_screen;
	global $hrcolor;
	global $key, $action;
	global $my_access_level;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	echo "<p><table cellspacing=0 cellpadding=0 border=0 width=100%><tr>";
	echo "<td valign='bottom'>";

	echo "<table cellspacing=0 cellpadding=0 border=0><tr>";
	echo "<td valign='bottom'><img src='images/" . $icon . "' width=32 height=32 align=left hspace=5></td>";
	echo "<td valign='bottom'>";
	if ($print_screen == false) echo "<font class='text10bold'><a href='" . $PHP_SELF . "' class='text10bold'>Home</a> &raquo; ";
	echo $top . "</font><br>";
	echo "<font class=text18bold>" . $title . "</font></td></tr></table>";

	echo "</td>";

	if ($print_screen == false) {
		echo "<td valign='bottom' align='right'>";
		if ($my_access_level > 1) {
			echo "<a href='" . $PHP_SELF . "?action=import' class='text10bold'>import</a> ";
			echo "<font class='text10bold'>&middot;</font> ";
		}
		echo "<a href='" . $PHP_SELF . "?action=help' class='text10bold'>help</a> ";
		echo "<font class='text10bold'>&middot;</font> ";
		echo "<a href='javascript:openwin()' class='text10bold'>print</a>";
		echo "<font class='text10bold'>&middot;</font> ";
		if ($my_access_level > 0) echo "<a href='" . $PHP_SELF . "?action=login&lastaction=" . $action . "&lastkey=" . html($key) . "&loginout=2' class='text10bold'>logout</a>";
		else echo "<a href='" . $PHP_SELF . "?action=login&lastaction=" . $action . "&lastkey=" . html($key) . "' class='text10bold'>login</a>";
		echo "</td>";
	}
	echo "</tr></table>";
	echo "<hr size=2 color='" . $hrcolor . "'>\n";
}

function import() {
	menu_header("","Import","setup.jpg");
	echo "<blockquote><font class='text12'>";
	echo "<p><font class='text12bold'>Step 1:</font> <a href='" . $PHP_SELF . "?action=employeeimport' class='text12bold'>Import Employees</a>";
	echo "<br>Import employees from a spreadsheet or database";
	echo "<p><font class='text12bold'>Step 2:</font> <a href='" . $PHP_SELF . "?action=assetimport' class='text12bold'>Import Assets</a>";
	echo "<br>Import assets from a spreadsheet or database";
	echo "</font></blockquote>";
}

// generates a yahoo style list of links and sublinks for one section in the centre menu
function doStylise($field, $name, $link) {
	$sql = "SELECT " . $field . " FROM Assets INNER JOIN Assignments ON Assets.Id = Assignments.AssetId WHERE Assignments.EmployeeId > -1 AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) GROUP BY Assets." . $field . " ORDER BY " . $field;
	echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><a href='?action=" . $link . "' class=text18bold><img src='images/". $link .".jpg' width=32 height=32 align=left border=0></a></td><td valign='bottom'><a href='?action=" . $link . "' class=text18bold>" . $name . "</a></td></tr></table>\n";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$commatrack = false;
		while ($query_data = mysql_fetch_array($result)) {
			if ($commatrack == true) echo ", ";
			else $commatrack = true;
			if (strlen($query_data[0]) > 1) echo "<a href='" . $PHP_SELF . "?action=" . $link . "&key=" . html($query_data[0]) . "'>" . $query_data[0] . "</a>";
			else $commatrack = false;
		}
	}
	echo "<p>\n";
}

// generates a dropdown box for dates
function dateDropdown($ext,$month,$day,$year,$month_now_offset,$default_day,$begend, $useunknown) {
	if ($month != "") $month_now = $month;
	else $month_now = date("m",time());

	if ($day != "") $day_now = $day;
	else $day_now = date("d",time());
	
	if ($year != "") $year_now = $year;
	else $year_now = date ("Y",time());

	$months = Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

	$month_now = $month_now + $month_now_offset;
	while ($month_now > 12) {
		$month_now = $month_now - 12;
		$year_now++;
	}

	while ($month_now < 1) {
		$month_now = $month_now + 12;
		$year_now--;
	}

	if ($default_day != "0") $day_now = $default_day;

	// year
	echo "<select name='year_" . $ext . "' size='1' class='boxtext13'>";
	if ($useunknown == true) echo "<option value='' SELECTED></option>";
	else echo "<option value=''></option>";
	for ($i=1970;$i<(date ("Y",time())+10);$i++) {
		if (($year_now == $i) && ($useunknown == false)) echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
		else echo "<option value='" . $i . "'>" . $i . "</option>\n";
	}
	echo "</select>";

	// month
	echo "<select name='month_" . $ext . "' size='1' class='boxtext13'>";
	if ($useunknown == true) echo "<option value='' SELECTED></option>";
	else echo "<option value=''></option>";
	for ($i=1;$i<13;$i++) {
		if (($month_now == $i) && ($useunknown == false)) echo "<option value='" . $i . "' selected>" . $months[$i-1] . "</option>\n";
		else echo "<option value='" . $i . "'>" . $months[$i-1] . "</option>\n";
	}
	echo "</select>";

	// day
	echo "<select name='day_" . $ext . "' size='1' class='boxtext13'>";
	if ($useunknown == true) echo "<option value='' SELECTED></option>";
	else echo "<option value=''></option>";
	for ($i=1;$i<32;$i++) {
		if (($day_now == $i) && ($useunknown == false)) echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
		else echo "<option value='" . $i . "'>" . $i . "</option>\n";
	}
	echo "</select>";
	if ($begend == true) return mktime(0,0,0,$month_now,$day_now,$year_now);
	else return mktime(23,59,59,$month_now,$day_now,$year_now);
}

function getMsg() {
	global $QUERY_STRING;
	global $my_emp_id;
	global $msgread;
	global $key;
	global $action;	
	if ($msgread != "") {
		$sql = "DELETE FROM Msgs WHERE id=" . $msgread;
		$result = doSql($sql);
	}

	$sql = "SELECT Assignments.EndDate, Assignments.Id AS Assignments_ID, Assets.AssetSupplier, Assets.AssetModel FROM Assignments LEFT JOIN Assets ON Assignments.AssetId = Assets.Id WHERE Assignments.EmployeeId=" . $my_emp_id . " AND Assignments.EndDate <= " . time() . " AND Assignments.Temp=1 AND Assignments.Completed=0";
	if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
		$day_now = date("d",time());
		$month_now = date("m",time());
		$year_now = date ("Y",time());
		$new_time = mktime(0,0,0,$month_now,$day_now,$year_now);
		$time_diff = ceil((($query_data["EndDate"]) - $new_time) / 86400);
		$time_diff--;
		if ($time_diff == -1) $due_text = "<font color='#ff0033'>1 day overdue!</font>";
		else $due_text = "<font color='#ff0033'>" . abs($time_diff) . " days overdue!</font>";
		$msg = $due_text . ": " . $query_data["AssetSupplier"] . " " . $query_data["AssetModel"] . " [ <a href='" . $PHP_SELF . "?action=assettransfersignin&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $lastaction . "&lastkey=" . $key . "'><font color='#ffcc00' class='text11'>SIGN IN!</font></a> ]";
	} else {
		$sql = "SELECT Msgs.Id AS Msgs_ID, Msgs.EmployeeId, Msgs.AssetId, Msgs.Date, Msgs.MsgCode, Msgs.Msg, Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel FROM Msgs LEFT JOIN Assets ON Msgs.AssetId = Assets.Id WHERE Msgs.Employeeid=" . $my_emp_id . " ORDER BY Msgs.Date DESC";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
			switch ($query_data["MsgCode"]) {
				case "1":
					$color= "Aqua";
					break;
				case "2":
					$color = "#ff0033";
					break;
				default:
					$color = "#ffffff";
					break;
			}
			$msg = "<font color='#ffcc00'>new!</font> " . $query_data["date"] . " - " . $query_data["AssetSupplier"] . " " . $query_data["AssetModel"] . " (" . $query_data["AssetType"] . ") - <font color='" . $color . "'>" . $query_data["Msg"] . "</font>&nbsp;&nbsp; [ <a href='" . $PHP_SELF . "?" . $QUERY_STRING . "&msgread=" . $query_data["Msgs_ID"] ."'><font color='#ffcc00' class='text11'>OK</font></a> ]";
		} else {
			$msg = "you have no new messages.";
		}
	}
	return $msg;
}

// replace quote with &quot;
function q_replace($txt) {
	$txt = str_replace("\"","&quot;",$txt);
	$txt = stripslashes($txt);
	return $txt;
}

// replace escape characters with html equivalent;
function html($txt) {
	$txt = str_replace(" ","%20",$txt);
	$txt = str_replace("\"","%22",$txt);
	$txt = str_replace("'","%27",$txt);	
	return $txt;
}

// replace html with original escape characters;
function dehtml($txt) {
	$txt = str_replace("%20"," ",$txt);
	$txt = str_replace("%22","\"",$txt);
	$txt = str_replace("%27","'",$txt);	
	return $txt;
}

// checks if a character is between A-Z or 0-9
function is_alphanum($input) {
	return (("a" <= $input && $input <="z") || ("A" <= $input && $input <="Z") || ("0" <= $input && $input <="9"))?true:false;
}

// checks if a string is between A-Z or 0-9
function is_alphanum_str($input) {
	$alphanum_str = false;
	for ($i=0;$i<strlen($input);$i++) {
		if (is_alphanum(substr($input,$i,1)) == false) $alphanum_str = true;
	}
	return $alphanum_str;
}

// generates a random seed
function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function cmp_array_startdate($a, $b) {
    if ($a["startdate"] == $b["startdate"]) return 0;
    return ($a["startdate"] < $b["startdate"]) ? -1 : 1;
}





?>
