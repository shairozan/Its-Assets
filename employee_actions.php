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
// employee_format($query_data, $color, $color_header, $class)
// employee_summary()
// employee_view($key)
// employee_menu_header($domain, $section, $title, $key)
// employee_tabs($empid)
// employee_print_info($key)


//prints out one employee
function employee_format($query_data, $color, $color_header, $class) {
	global $print_screen;
	global $lastaction;
	global $action;
	global $key;
	global $my_access_level;
	global $my_emp_id;
	global $emp_db;
	
	// display start / end date, if it exists
	if (($query_data["StartDate"] != "") && ($query_data["EndDate"] != "")) {
		echo "<table width=100% bgcolor='" . $color_header . "' class='" . $class . "'>";
		echo "<tr>";
		echo "<td class='text12bold'>";
		if ($query_data["StartDate"] == "0") $startdate_out = "From an unknown install date";
		else $startdate_out = "From " . date("l, F d, Y", $query_data["StartDate"]);
		if ($query_data["EndDate"] == "0") $enddate_out = "";
		else $enddate_out = " to " . date("l, F d, Y", $query_data["EndDate"]);				
		$transfer_date_text = $startdate_out . $enddate_out;

		// rules for determining if cancel and sign in buttons should be shown	
		if (((($my_access_level > 1) || ($my_emp_id == $query_data["Employees_ID"]))) && ($print_screen == false)) {
			// user is an admin user or viewing their own profile
			if ($query_data["StartDate"] > time()) {
				// start date is after today
				echo $transfer_date_text;
				echo "&nbsp;&nbsp;<a href='" . $PHP_SELF . "?action=assettransfererase&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $action ."&lastkey=" . html($key) . "'>(cancel)</a>&nbsp";
			} else {
				// start date is before or on today
				if ($query_data["Temp"] == "1") {
					// A signout is being performed
					if ($query_data["Completed"] == "0") {
						// asset not signed in
						$day_now = date("d",time());
						$month_now = date("m",time());
						$year_now = date ("Y",time());
						$new_time = mktime(0,0,0,$month_now,$day_now,$year_now);
						$time_diff = ceil((($query_data["EndDate"]) - $new_time) / 86400);
						$time_diff--;
						if ($time_diff == 0) $due_text = "<font color='#ff6600'>due today</font>";
						elseif ($time_diff == 1) $due_text = "due tommorow";
						elseif ($time_diff == -1) $due_text = "<font color='#ff0033'>overdue by 1 day</font>";
						elseif ($time_diff < 0) $due_text = "<font color='#ff0033'>overdue by " . abs($time_diff) . " days</font>";
						else $due_text = "due in " . $time_diff . " days";
						echo "<table width=100% cellspacing=0 cellpadding=0 border=0><tr>";
						echo "<td class='text12bold'>";
						echo $transfer_date_text . "&nbsp;&nbsp;<a href='" . $PHP_SELF . "?action=assettransfersignin&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $action ."&lastkey=" . html($key) . "'>(sign in)</a>&nbsp";
						if ($my_access_level > 1) echo "<a href='" . $PHP_SELF . "?action=assettransfererase&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $action ."&lastkey=" . html($key) . "'>(erase)</a>&nbsp";
						echo "</td>";
						echo "<td class='text12bold' align=right>" . $due_text . "</td>";
						echo "</tr></table>";
					} else {
						// asset signed in
						echo $transfer_date_text;
						if ($my_access_level > 1) echo "&nbsp;&nbsp;<a href='" . $PHP_SELF . "?action=assettransfererase&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $action ."&lastkey=" . html($key) . "'>(cancel)</a>&nbsp";
					}
				} else {
					// A transfer is occurring
					echo $transfer_date_text;
				}
			}
		} else {
			echo $transfer_date_text;
		}

		echo "</td>";
		echo "</tr>";
		echo "</table>";
	}	
	


	echo "<table width=100% bgcolor='" . $color . "' class='" . $class . "'><tr><td>";
	echo "<table width=100%>";
	echo "<tr>";
	echo "<td class='text13bold'>";

	// employee's name (or general assets/surplus)
	// if not employeeview or not printscreen, use links
	if ((strcmp($action,"employeeview") != 0) && ($print_screen == false)) {
		switch($query_data["Employees_ID"]) {
			case "0":
				echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_ID"] . "' class='text13bold'><b>General Assets</b></a></font><font class='text11'> &nbsp; </font>\n";
				break;
			case "-1":
				echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_ID"] . "' class='text13bold'><b>Surplus</b></a></font><font class='text11'> &nbsp; </font>\n";
				break;
			case "-2":
				echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_ID"] . "' class='text13bold'><b>Retired</b></a></font><font class='text11'> &nbsp; </font>\n";
				break;
			default:
				echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_ID"] . "'class='text13bold'><b>" . $query_data["LastName"] . ", " . $query_data["FirstName"];
				if ($query_data["Active"] == 0) echo " (Inactive)";
				echo "</b></a></font>\n";
				break;
		}
	} else {
		switch($query_data["Employees_ID"]) {
			case "0":
				echo "<font class='text13bold'><b>General Assets</b></font><font class='text11'> &nbsp; </font>\n";
				break;
			case "-1":
				echo "<font class='text13bold'><b>Surplus</b></font><font class='text11'> &nbsp; </font>\n";
				break;
			case "-2":
				echo "<font class='text13bold'><b>Retired</b></font><font class='text11'> &nbsp; </font>\n";
				break;
			default:
				echo "<font class='text13bold'><b>";
				echo $query_data["LastName"] . ", " . $query_data["FirstName"];
				if ($query_data["Active"] == 0) echo " (Inactive)";
				echo "</b></font>\n";
				break;
		}
	}
	
	echo "</td>";
	echo "<td align=right class='text11bold'>";

	//org and dept
	$orgdept = "";
	if ($query_data["Employees_ID"] > 0) {
		$orgdept = $query_data["Organization"];
		if ((strlen($query_data["Dept"]) > 0) && (strlen($query_data["Organization"]) > 0)) $orgdept = $orgdept . ", ";
		$orgdept = $orgdept . $query_data["Dept"] . "<br>";
	}
	if (strlen($orgdept) > 1) echo $orgdept;
	else echo "&nbsp;";

	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td class='text12'>";

	// login
	if ($my_access_level > 1) echo "<font class='text12'>" . $query_data["LoginName"] .  "</font> &nbsp;";

	echo "</td>";
	echo "<td align=right class='text11' valign='top'>";

	// telephone number
	if (strlen($query_data["Tel"]) > 1) $print_tel = $query_data["Tel"];

	// email
	if ($query_data["EMail"] != "") $print_email = "<a href='mailto:" . $query_data["EMail"] . "'><b>email</b></a>";
	
	// location
	$print_location = $query_data["Building"] . " " . $query_data["Floor"];
	if ($query_data["Workstation"] != "") $print_location = $print_location . "-" . $query_data["Workstation"];

	// print out above three
	if (strlen($print_tel) > 2) echo $print_tel;
	if ((strlen($print_tel) > 2) && (strlen($print_email) > 2)) echo "<font class='text13'>&nbsp;&middot;&nbsp;</font>";
	if (strlen($print_email) > 2) echo $print_email;
	if ((strlen($print_email) > 2) && (strlen($print_location) > 2)) echo "<font class='text13'>&nbsp;&middot;&nbsp;</font>";
	if (strlen($print_location) > 2) echo $print_location;

	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</td></tr></table>";
}

// List all employees (employee summary)
function employee_summary() {
	global $inactive;
	global $print_screen;
	global $hrcolor;
	global $emp_db;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	employee_menu_header(false,"","Employees","");

	// tabs to show active or inactive employees
	echo "<p><table cellspacing=0 cellpadding=0 border=0><tr>";

	if ($inactive == "1") {
		$active = "0";
		if ($print_screen == false) echo "<td><a href='?action=employees' class='text11special'>Active</a></td>";
		else echo "<td><a href='?action=employees' class='text11special'><font color='#ffffff'>Active</font></a></td>";
		echo "<td><font class='text11specialactive' color='#000000'>Inactive</font></font></td>";
		$altcolor = false;
	} else {
		$active = "1";
		echo "<td><font class='text11specialactive' color='#000000'>Active</font></td>";
		if ($print_screen == false) echo "<td><a href='?action=employees&inactive=1' class='text11special'>Inactive</a></font></td>";
		$altcolor = true;
	}
	echo "</tr></table>";
	echo "<table width=100% bgcolor='#ffdddd'><tr><td>\n";
	echo "</td></tr></table>\n";
	


	echo "<table cellspacing=0 cellpadding=0 border=0 width=100% class='employeeborder' bgcolor='#ffeeee'><tr><td>";
	// display first row as General Assets for the active screen
	if ($active == 1) {
		echo "<table width=100% bgcolor='#ffeeee'><tr><td>\n";
		echo "<table width=100%><tr><td>\n";
		if ($print_screen == false) echo "<a href='" . $PHP_SELF . "?action=employeeview&key=0' class='text13bold'>General Assets</a><br>\n";
		else echo "<font class='text13bold'>General Assets<br>\n";
		echo "</td></tr></table>\n";
		echo "</td></tr></table>\n";	
	}
	
	// get all employees
	$color = "#ffffff";
	$sql = "SELECT LastName, FirstName, LoginName, Tel, Organization, Dept, Building, Floor, Workstation, EMail, Active, Id AS Employees_ID FROM " . $emp_db . "Employees WHERE Active=" . $active ." ORDER BY LastName;";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		while ($query_data = mysql_fetch_array($result)) {
			// employee listing			
			employee_format($query_data, $color, "#dddddd", "employeeborder");
			if ($color == "#ffffff") $color = "#ffeeee";
			else $color = "#ffffff";
		}
	}

	if (mysql_num_rows($result) != "0") echo "</td></tr></table><p><font class='text12'>" . mysql_num_rows($result) . " employee(s) listed.</font>";
	else echo "<br><blockquote><font class='text12'>There are no employees entered.</font></blockquote></td></tr></table>";

}



// List all assets for a specific employee
function employee_view($key) {
	global $action;
	global $hrcolor;
	global $addip;
	global $removeip;
	global $my_emp_id;
	global $my_access_level;
	global $print_screen;
	global $history;
	global $verify;
	global $useasset;
	global $addlicense;
	global $removelicense;
	global $emp_db;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	// header, tabs, employee info
	if ($history != "1") $header_text = "Details";
	else $header_text = "History";

	$is_editable = (($my_access_level > 1) || ($my_emp_id == $key));

	// make changes to licenses
	if ($is_editable) {
		if ((strlen($addlicense) > 0) && ($useasset != "") && ($useasset != "0")) {
			$sql = "INSERT INTO LicenseOwners (AssetId, LicenseId) VALUES (" . $useasset . ",'" . $addlicense . "')";
			$result = doSql($sql);
		}
		if (strlen($removelicense) > 0) {
			$sql = "DELETE FROM LicenseOwners WHERE Id=" . $removelicense;
			$result = doSql($sql);
		}
	}


	// make changes to ips
	if ($is_editable) {
		if ($useasset == "") $useasset = "0";
		if ($addip != "") doSql("INSERT INTO IP (employeeid,assetid,ip) VALUES (" . $key . "," . $useasset . ",'" . $addip . "')");
		if ($removeip != "") doSql("DELETE FROM IP WHERE id=" . $removeip);
	}

	// update verified date, if a verification date took place
	if (($verify == 1) && ($my_emp_id == $key)) $result2 = doSql("UPDATE " . $emp_db . "Employees SET Verified=" . time() . " WHERE id=" . $key);
	$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.LoginName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, " . $emp_db . "Employees.Active, " . $emp_db . "Employees.EMail, " . $emp_db . "Employees.Id AS Employees_ID, " . $emp_db . "Employees.Verified FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.ID=" . $key . ";";

	if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) {
		employee_menu_header(true,"", $header_text, $key);
		employee_tabs($key);
		employee_format($query_data,"#ffeeee","#ffdddd", "employeeborder");

		// last verified box
		echo "<table width=100% bgcolor='#ffdddd'><tr>";
		if ($query_data["Verified"] == "0") $date_text = "Not Verified";
		else $date_text = date("F d, Y", $query_data["Verified"]) . " at " . date("h:i a", $query_data["Verified"]);
		echo "<td><font class='text11bold'>&nbsp;Last Verified:</font> <font class='text11'>" . $date_text . "</font></td>";
		if (($key == $my_emp_id) && ($print_screen == false)) echo "<td align='right'><font class='text11bold'>If the asset listing below is accurate, <a href='" . $PHP_SELF . "?action=employeeview&key=" . $key . "&verify=1' class='text11bold'>Verify Now</a>&nbsp;</font></td>";
		echo "</tr></table>\n";

	} else {
		if ($key == "-1") menu_header("","Surplus","surplus.jpg");
		elseif ($key == "-2") menu_header("","Retired","retired.jpg");
		else employee_menu_header(true,"", $header_text, $key);
	}

	// restrict content to dates on or after today for non-History view
	if ($history != "1") $dates_sql = " AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) AND Assignments.Completed=0";
	else $dates_sql = "";

	// add the word 'My' if we are the user is looking at their own assets	
	if ($my_emp_id == $key) $my_text = "My ";
	else $my_text = "";
	
	// seek transfers
	if (($key > -1) && ($print_screen == false)) echo "<p><table width=100%><tr>";
	$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Id As Assignments_ID FROM Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId WHERE Assignments.EmployeeId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.Temp=0" . $dates_sql ." ORDER BY Assignments.StartDate;";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		if (($key > -1) && ($print_screen == false)) echo "<td valign='top'>";
		echo "<p><font class='text11'><b>" . $my_text . "Assets</b></font><br><hr size=0 color='" . $hrcolor . "'>";
		while ($query_data = mysql_fetch_array($result)) {
			$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.id=" . $query_data["Assignments_ID"];
			if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) $query_data2 = mysql_fetch_array($result2);
			asset_format($query_data,$query_data2,"#ffeeee","employeeborder",false);
			if ($print_screen == true) echo "<p>";
		}
		if ($print_screen == false) echo "<br><a href='" . $PHP_SELF . "?action=assetinsert' class='text11'><b>&raquo; New Asset...</b></a>";
		if ($print_screen == false) echo "<br><a href='" . $PHP_SELF . "?action=assets' class='text11'><b>&raquo; Find Asset...</b></a>";
		if (($print_screen == false) && ($my_access_level > 1)) echo "<br><a href='" . $PHP_SELF . "?action=reportsindividual&key=" . $key . "' class='text11'><b>&raquo; Download Report...</b></a>";
		if (($key > -1) && ($print_screen == false)) echo "</td>";
		$found_employee = true;
	} else {
		if (strcmp($key,"0") == 0) echo "<font class='text12'><blockquote>There are no general assets.</blockquote></font>";		
		elseif (strcmp($key,"-1") == 0) echo "<font class='text12'><blockquote>There are no assets assigned to surplus.</blockquote></font>";
		elseif (strcmp($key,"-2") == 0) echo "<font class='text12'><blockquote>There are no assets which are retired.</blockquote></font>";		
		else echo "<font class='text12'><blockquote>There are no assets assigned to this employee.</blockquote></font>";
		if ($print_screen == false) echo "<a href='" . $PHP_SELF . "?action=assetinsert' class='text11'><b>&raquo; New Asset...</b></a>";
		if ($print_screen == false) echo "<br><a href='" . $PHP_SELF . "?action=assets' class='text11'><b>&raquo; Find Asset...</b></a>";
		if (($print_screen == false) && ($my_access_level > 1)) echo "<br><a href='" . $PHP_SELF . "?action=reportsindividual&key=" . $key . "' class='text11'><b>&raquo; Download Report...</b></a>";
	}
	
	// seek signed out assets
	$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.Completed, Assignments.EndDate, Assignments.Temp, " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, Assignments.Id AS Assignments_ID FROM ((Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId) LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeId = " . $emp_db . "Employees.Id) WHERE Assignments.EmployeeId=" . $key . " AND Assignments.Approve=0 AND Assignments.Temp=1" . $dates_sql . " ORDER BY Assignments.StartDate;";
	if (($key > 0) && ($result = doSql($sql)) && (mysql_num_rows($result))) {
		if (($found_employee == true) && ($key > -1) && ($print_screen == false)) echo "<td width=10>&nbsp;</td>";
		if (($key > -1) && ($print_screen == false)) echo "<td valign='top'>";
		echo "<p><font class='text11'><b>" . $my_text . "Sign Outs</b></font><br><hr size=0 color='" . $hrcolor . "'>";
		while ($query_data = mysql_fetch_array($result)) {
			$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.Id=" . $query_data["Assignments_ID"] . " ORDER BY Assignments.StartDate;";
			if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) $query_data2 = mysql_fetch_array($result2);
			asset_format($query_data,$query_data2,"#ffeeee","employeeborder",false);
			if ($print_screen == true) echo "<p>";
		}
		if (($key > -1) && ($print_screen == false)) echo "</td>";
	}
	if (($key > -1) && ($print_screen == false)) echo "</tr></table>";
	


	if ($history != "1") {


	// seek out licenses
		echo "<p><br>";
		echo "<table bgcolor='#ffeeee' cellpadding=5 class='employeeborder' width=100%><tr><td>";

		
		$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Id As Assignments_ID, COUNT(DISTINCT LicenseOwners.Id) AS CountLicenses, LicenseOwners.licenseid, LicenseOwners.Id AS LicenseOwners_ID FROM (Assignments INNER JOIN Assets ON Assets.Id = Assignments.AssetId) INNER JOIN LicenseOwners ON LicenseOwners.assetid = Assets.Id WHERE Assignments.EmployeeId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.Temp=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) AND Assignments.Completed=0 GROUP BY LicenseOwners.licenseid, Assets.Id ORDER BY LicenseOwners.licenseid";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			echo "<font class='text12bold'>" . $my_text . " Licenses</font><br><hr size=0 color='" . $hrcolor . "'>";
			echo "<table width=100% class='employeeborder'>";
			while ($query_data = mysql_fetch_array($result)) {
				echo "<tr><td class='text12bold' width=30%>";
				echo $query_data["licenseid"];
				if ($query_data["CountLicenses"] != "1") echo " (" . $query_data["CountLicenses"] . ")"; 
				echo "</td><td class='text12bold' width=40%>";
				echo "[" . $query_data["AssetType"] . " - " . $query_data["AssetModel"] . " " . $query_data["AssetSupplier"] . "]";
				echo "</td><td class='text12bold' width=30% align='right'>";
				if (($print_screen == false) && ($is_editable)) echo " <a href='" . $PHP_SELF . "?action=employeeview&key=" . $key . "&removelicense=" . $query_data["LicenseOwners_ID"] . "'>(remove)</a>";
				echo "</td></tr>";
			}
			echo "</table><p>";
		} else {
			echo "<font class='text12bold'>" . $my_text . " Licenses</font><br><hr size=0 color='" . $hrcolor . "'>";
			echo "<table width=100%>";
			echo "<tr>";
			echo "<td class='text12bold'>";
			echo "No licenses are assigned.";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}

		// show the add license form
		if (($print_screen == false) && ($is_editable)) {
			echo "<center>";
			echo "<p><table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
			echo "<input type='hidden' name='action' value='employeeview'>";
			echo "<input type='hidden' name='key' value='" . $key . "'>";
			echo "<font class='text13bold'>New: </font>";
			echo "<select name='addlicense' class='boxtext13'>";
			$sql = "SELECT manufacturer, product FROM Licenses GROUP BY product ORDER BY product";
			echo "<option value='' SELECTED>(Select A Product)</option>";
			if (($result = doSql($sql)) && (mysql_num_rows($result))) {
				while ($query_data = mysql_fetch_array($result)) {
					echo "<option value='" . $query_data["product"] . "' class='text12'>" . $query_data["manufacturer"] . " " . $query_data["product"] . "</option>";
				}
			}
			echo "</select>";
			echo "<font class='text13'>&nbsp;</font>";
			echo "<select name='useasset' class='boxtext13'>";
			echo "<option value='' SELECTED>(Select An Asset)</option>";
			$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Id As Assignments_ID FROM Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId WHERE Assignments.EmployeeId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.Temp=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) AND Assignments.Completed=0 ORDER BY Assignments.StartDate;";
			if (($result = doSql($sql)) && (mysql_num_rows($result))) {
				while ($query_data = mysql_fetch_array($result)) {
					echo "<option value='" . $query_data["Assets_ID"] . "'>is licensed to " . strtolower($my_text) . " " . $query_data["AssetType"] . " (" . $query_data["AssetSupplier"] . ") " . $query_data["AssetModel"] . "</option>";
				}
			}
			echo "</select>";
			echo "<input type='image' src='images/add.jpg' border=0 width=88 height=27>";
			echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
			echo "</center>";
		}

		echo "</td></tr></table>";



	// seek out ips
		
		echo "<p><br>";
		echo "<table bgcolor='#ffeeee' cellpadding=5 class='employeeborder' width=100%><tr><td>";

		// show current ips
		$sql = "SELECT IP.id as IP_ID, Assets.id as Assets_ID, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, IP.ip, IP.assetid FROM IP LEFT JOIN Assets ON IP.assetid = Assets.Id WHERE employeeid=" . $key;
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			echo "<font class='text12bold'>" . $my_text . " IPs</font><br><hr size=0 color='" . $hrcolor . "'>";
			echo "<table width=100% class='employeeborder'>";
			while ($query_data = mysql_fetch_array($result)) {
				echo "<tr><td class='text12bold' width=30%>";
				echo $query_data["ip"];
				echo "</td><td class='text12bold' width=40%>";
				if (strcmp($query_data["assetid"], "0") == 0) echo "<font class='text12'> [Unused IP]</font>";
				else echo "<font class='text12'> [" . $query_data["AssetType"] . " - " . $query_data["AssetSupplier"] . " " . $query_data["AssetModel"] . "]</font>";
				echo "</td><td class='text12bold' width=30% align='right'>";
				if ($print_screen == false) echo " <a href='" . $PHP_SELF . "?action=employeeview&key=" . $key . "&removeip=" . $query_data["IP_ID"] . "'>(remove)</a>";
				echo "</td></tr>";
			}
			echo "</table><p>";
		} else {
			echo "<font class='text12bold'>" . $my_text . " IPs</font><br><hr size=0 color='" . $hrcolor . "'>";
			echo "<table width=100%>";
			echo "<tr>";
			echo "<td class='text12bold'>";
			echo "No IPs are assigned.";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}



		// show the add ip form
		if (($print_screen == false) && (($my_access_level > 1) || ($my_emp_id == $key))) {
			echo "<center>";
			echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
			echo "<input type='hidden' name='action' value='employeeview'>";
			echo "<input type='hidden' name='key' value='" . $key . "'>";
			echo "<font class='text13bold'>New: </font>";
			echo "<input type='text' name='addip' class='boxtext13' size=15>";
			echo "<font class='text13'>&nbsp;</font>";
			echo "<select name='useasset' class='boxtext13'>";
			echo "<option value='0'>is an unused IP Address</option>";
			$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Id As Assignments_ID FROM Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId WHERE Assignments.EmployeeId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.Temp=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) AND Assignments.Completed=0 ORDER BY Assignments.StartDate;";
			if (($result = doSql($sql)) && (mysql_num_rows($result))) {
				while ($query_data = mysql_fetch_array($result)) {
					echo "<option value='" . $query_data["Assets_ID"] . "'>is assigned to " . strtolower($my_text) . " " . $query_data["AssetType"] . " (" . $query_data["AssetSupplier"] . ") " . $query_data["AssetModel"] . "</option>";
				}
			}
			echo "</select>";
			echo "<font class='text13'>&nbsp;</font>";
			echo "<input type='image' src='images/add.jpg' border=0 width=88 height=27>";
			echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
			echo "</center>";
		}

		echo "</td></tr></table>";

	}
	echo "<p><br>";
}

// shows the top header bar
function employee_menu_header($domain, $section, $title, $key) {
	global $print_screen;
	global $hrcolor;
	global $emp_db;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	// section
	if ($print_screen == false) {
		if ($domain == true) $top = "<a href='" . $PHP_SELF . "?action=employees' class='text10bold'>Employees</a>";
		else $top = "";

		// look up active employee	
		if (strlen($key) > 0) {
			switch($key) {
				case "0":
					$full_name = "General Assets";
					break;
				case "-1":
					$full_name = "Surplus";
					break;
				case "-2":
					$full_name = "Retired";
					break;
				default:
					$sql = "SELECT id, LastName, FirstName FROM " . $emp_db . "Employees WHERE id=" . $key;
					if ($result = doSql($sql)) {
						$query_data = mysql_fetch_array($result);
						$full_name = $query_data["LastName"] . ", " . $query_data["FirstName"];
					} else {
						$full_name = "";
					}
					break;
			}
			$top = $top . ": <a href='" . $PHP_SELF . "?action=employeeview&key=" . $key . "' class='text10bold'>" . $full_name . "</a>";
		}

		if (strlen($section) > 0) $top = $top . ": " . $section;
	}
	
	menu_header($top,$title,"employees.jpg");
}

// prints out the top tabs
function employee_tabs($empid) {
	global $action;
	global $history;
	global $print_screen;
	global $my_access_level;
	global $my_emp_id;
	global $emp_db;
	
	if (($empid != "") && ($print_screen == false) && ($my_access_level > 0)) {
		echo "<p>";
		
		echo "<table cellspacing=0 cellpadding=0 border=0><tr>";

		// details
		if ((strcmp($action,"employeeview") == 0) && ($history != "1")) echo "<td><font class='text11specialactive' color='#000000'>Details</font></td>";
		else echo "<td><a href='" . $PHP_SELF . "?action=employeeview&key=" . $empid . "' class='text11special'>Details</a></td>";

		// update
		if (($my_access_level > 1) || (($my_access_level > 0) && ($my_emp_id == $empid))) {
			if (strcmp($action,"employeeupdate") == 0) echo "<td><font class='text11specialactive' color='#000000'>Update</font></td>";
			else echo "<td><a href='" . $PHP_SELF . "?action=employeeupdate&key=" . $empid . "' class='text11special'>Update</a></font></td>";
		} else {
			echo "<td><font class='text11special2' color='#cccccc'>Update</font></td>";
		}

		// history		
		if ((strcmp($action,"employeeview") == 0) && ($history == "1")) echo "<td><font class='text11specialactive' color='#000000'>History</font></td>";
		else echo "<td><a href='" . $PHP_SELF . "?action=employeeview&key=" . $empid . "&history=1' class='text11special'>History</a></td>";

		// erase
		if (($my_access_level > 1) && ($empid > 0)) {
			if (strcmp($action,"employeeerase") == 0) echo "<td><font class='text11specialactive' color='#000000'>Erase</font></td>";
			else echo "<td><a href='" . $PHP_SELF . "?action=employeeerase&key=" . $empid . "' class='text11special'>Erase</a></td>";
		}
		echo "</tr></table>";
		
		echo "<table width=100% bgcolor='#ffdddd'><tr><td>\n";
		echo "</td></tr></table>\n";
	}
}

// load the data for one employee
function employee_print_info($key) {
	global $emp_db;
	$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.LoginName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, " . $emp_db . "Employees.Active, " . $emp_db . "Employees.EMail, " . $emp_db . "Employees.Id AS Employees_ID FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.ID=" . $key . ";";
	if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) employee_format($query_data,"#ffeeee","#ffdddd", "employeeborder");
}

?>
