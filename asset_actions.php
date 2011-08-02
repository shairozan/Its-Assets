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
// asset_format($query_data, $query_data2, $color, $class, $is_report);
// asset_summary($field, $name) // listings with count num
// asset_query($key, $field, $name) // listings with details for one type
// asset_view($key) // listing of one asset
// asset_menu_header($domain, $section, $title, $key)
// asset_tabs($assetid)
// asset_get_empid($key)
// asset_print_info($key)

//prints out one asset
function asset_format($query_data, $query_data2, $color, $class, $is_report) {
	global $print_screen;
	global $my_access_level;
	global $action;
	global $my_emp_id;
	global $key;
	global $temp;
	global $complete;
	global $hrcolor;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	// determine the owner
	if ($query_data2 != "") {
		$is_employee = true;
		if (($query_data2["Employees_ID"] == 0) || ($query_data2["Employees_ID"] == -1)) $allow_sign_out = true;
		else $allow_sign_out = false;
	} else {
		$is_employee = false;
	}

	// display start / end date, if it exists
	if (strlen($query_data["StartDate"]) > 0) {
    	$start_date = $query_data["StartDate"];
    } else {
    	$start_date = ""; 
	}
	if (strlen($query_data["EndDate"]) > 0) {
    	$end_date = $query_data["EndDate"];
    } else {
    	$end_date = ""; 
	}

	if (($start_date != "") && ($end_date != "")) {
		if ($is_report == true) {
			if ($query_data["StartDate"] == "0") $startdate_out = "On an unknown date";
			else $startdate_out = "On " . date("M d, Y", $query_data["StartDate"]);
			$transfer_date_text = $startdate_out;
		} else {
			if ($query_data["StartDate"] == "0") $startdate_out = "From an unknown install date";
			else $startdate_out = "From " . date("M d, Y", $query_data["StartDate"]);
			if ($query_data["EndDate"] == "0") $enddate_out = "";
			else $enddate_out = " to " . date("M d, Y", $query_data["EndDate"]);				
			$transfer_date_text = $startdate_out . $enddate_out;
		}
		
		echo "<table width=100% bgcolor='" . $color_header . "' class='" . $class . "'>";
		echo "<tr>";
		echo "<td class='text12bold'>";

		// rules for determining if cancel and sign in buttons should be shown	
		if (((($my_access_level > 1) || ($my_emp_id == $query_data["Employees_ID"]))) && ($print_screen == false)) {
			// user is an admin user or viewing their own profile
			if ($start_date > time()) {
				// start date is after today
				echo $transfer_date_text;
				echo "&nbsp;&nbsp;<a href='" . $PHP_SELF . "?action=assettransfererase&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $action ."&lastkey=" . html($key) . "'>(cancel)</a>&nbsp<br>";
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
						if ($my_access_level > 1) echo "&nbsp;&nbsp;<a href='" . $PHP_SELF . "?action=assettransfererase&key=" . $query_data["Assignments_ID"] . "&lastaction=" . $action ."&lastkey=" . html($key) . "'>(erase)</a>&nbsp";
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
		
	echo "<table width=100% cellspacing=5 border=0 bgcolor='" . $color . "' class='" . $class . "'>";
	echo "<tr>\n";
	echo "<td valign='top' width=10%>";
	
	//Asset Tag
	if ((strcmp($action,"assetview") == 0) || ($print_screen == true)) echo "<font class='text13bold'>" . $query_data["AssetTag"] . "<br></font>";
	else echo "<a href='" . $PHP_SELF . "?action=assetview&key=" . $query_data["Assets_ID"] . "' class='text13bold'>" . $query_data["AssetTag"] . "</a><font class='text13bold'><br></font>";
	
	echo "</td>\n";
	echo "<td class='text11' valign='top' width=35%>";
	
	//Asset Type
	echo "<font class='text10bold'>" . $query_data["AssetType"] . "</font>\n";
	
	//Asset OS
	if ((strlen($query_data["os"]) > 0) && (strcmp($action,"assetos") != 0)) echo " (" . $query_data["os"] . ")\n";
	echo "<font class='text11bold'><br></font>";

	// Supplier and Model


	$sql3 = "SELECT supplier, link FROM Links WHERE supplier='" . $query_data["AssetSupplier"] . "'";
	if (($result3 = doSql($sql3)) && (mysql_num_rows($result3)) && ($query_data3 = mysql_fetch_array($result3))) {
		echo "<font class='text13bold'><a href='" . $query_data3["link"] . "' class='text13bold'>" . $query_data["AssetSupplier"] . "</a> " . $query_data["AssetModel"] . "</font> ";
	} else {
		echo "<font class='text13bold'>" . $query_data["AssetSupplier"] . " " . $query_data["AssetModel"] . "</font> ";
	}
	
	// price
	if ($query_data["AssetPrice"] != 0) echo "<font class='text12'> ($" . number_format($query_data["AssetPrice"],2) . ")</font>\n";
	echo "<br>";
	
	// serial number
	if ($query_data["AssetSerial"]) echo "<font class='text12'>s/n " . $query_data["AssetSerial"] . "</font>\n";
	
	echo "</td>\n";
	echo "<td align=right class='text11' valign='top' width=25%>\n";

	//Employee Info
	if ($is_employee == true) {
		// Employee Name
		if ((strcmp($action,"employeeview") == 0)) {
			$sql3 = "SELECT IP FROM IP WHERE AssetId=" . $query_data["Assets_ID"] . " AND EmployeeId=" . $query_data2["Employees_ID"];
			if (($result3 = doSql($sql3)) && (mysql_num_rows($result3))) {
				while ($query_data3 = mysql_fetch_array($result3)) {
					echo "<font class='text11bold'>" . $query_data3["IP"] . "</font><br>";
				}
			}
		} else {
			if (($is_report == true) && ($query_data["Employees_Prev_ID"] != "")) {
				echo "<font class='text11bold'>From </font>";
				switch($query_data["Employees_Prev_ID"]) {
					case "0":
						if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_Prev_ID"] . "'><b>General Assets</b></a></font>\n";
						else echo "<font class='text11bold'><b>General Assets</b></font>\n";
						break;
					case "-1":
						if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_Prev_ID"] . "'><b>Surplus</b></a></font>\n";
						else echo "<font class='text11bold'><b>Surplus</b></font>\n";
						break;
					case "-2":
						if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_Prev_ID"] . "'><b>Retired</b></a></font>\n";
						else echo "<font class='text11bold'><b>Retired</b></font>\n";
						break;
					default:
						if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Employees_Prev_ID"] . "'><b>" . $query_data["Employees_Prev_LastName"] . ", " . $query_data["Employees_Prev_FirstName"] . "</b></a></font>";
						else echo "<font class='text11bold'><b>" . $query_data["Employees_Prev_LastName"] . ", " . $query_data["Employees_Prev_FirstName"] . "</b></font>\n";
						if (($query_data["Employees_Prev_Building"] != "") || ($query_data["Floor"] != "")) echo "<font class='text11'>  - " . $query_data["Employees_Prev_Building"] . " " . $query_data["Employees_Prev_Floor"] . "</font>";
						if ($query_data["Employees_Prev_Workstation"] != "") echo "<font class='text11'>-" . $query_data["Employees_Prev_Workstation"] . "</font>\n";
						break;
				}
				if ($is_report == true) echo "<br><font class='text11bold'>To: </font>";
			} else {
				if ($is_report == true) echo "<font class='text11bold'>Installed: </font>";
			}
	
			switch($query_data2["Employees_ID"]) {
				case "0":
					if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data2["Employees_ID"] . "'><b>General Assets</b></a></font>\n";
					else echo "<font class='text11bold'><b>General Assets</b></font>\n";
					break;
				case "-1":
					if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data2["Employees_ID"] . "'><b>Surplus</b></a></font>\n";
					else echo "<font class='text11bold'><b>Surplus</b></font>\n";
					break;
				case "-2":
					if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data2["Employees_ID"] . "'><b>Retired</b></a></font>\n";
					else echo "<font class='text11bold'><b>Retired</b></font>\n";
					break;
				default:
					if ($print_screen == false) echo "<font class='text11bold'><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data2["Employees_ID"] . "'><b>" . $query_data2["LastName"] . ", " . $query_data2["FirstName"] . "</b></a></font>";
					else echo "<font class='text11bold'><b>" . $query_data2["LastName"] . ", " . $query_data2["FirstName"] . "</b></font>\n";
					if (($query_data2["Building"] != "") || ($query_data2["Floor"] != "")) echo "<font class='text11'>  - " . $query_data2["Building"] . " " . $query_data2["Floor"] . "</font>";
					if ($query_data2["Workstation"] != "") echo "<font class='text11'>-" . $query_data2["Workstation"] . "</font>\n";

					break;
			}

			$day_now = date("d",time());
			$month_now = date("m",time());
			$year_now = date ("Y",time());
			$new_time = mktime(0,0,0,$month_now,$day_now,$year_now);

			if ($query_data2["Temp"] == "0") {
				if ($is_report == true) {
				} else {
					if ($query_data2["StartDate"] > $new_time) echo "<font class='text11bold'><br>(Upcoming Owner)</font>\n";
					elseif (($query_data2["EndDate"] < $new_time) && ($query_data2["EndDate"] != 0)) echo "<font class='text11bold'><br>(Previous Owner)</font>\n";
					else echo "<font class='text11bold'><br>(Current Owner)</font>\n";
				}
			}
		}
	}
	
	echo "</td></tr>\n";
	echo "</table>";
}

// generates a listing of all valid entries and the number of occurrences in one area
function asset_summary($field, $name) {
	global $action;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	// set whether to show active or inactive employees
	echo "<p>";
	echo "<table cellspacing=0 cellpadding=0 border=0><tr>";
	// types
	if (strcmp($action,"assets") == 0) echo "<td><font class='text11special2active' color='#000000'>Types</font></td>";
	else echo "<td><a href='" . $PHP_SELF . "?action=assets' class='text11special2'>Types</a></td>";
	// suppliers
	if (strcmp($action,"assetsupplier") == 0) echo "<td><font class='text11special2active' color='#000000'>Suppliers</font></td>";
	else echo "<td><a href='" . $PHP_SELF . "?action=assetsupplier' class='text11special2'>Suppliers</a></td>";
	// operating systems
//	if (strcmp($action,"assetos") == 0) echo "<td><font class='text11special2active' color='#000000'>Operating Systems</font></td>";
//	else echo "<td><a href='" . $PHP_SELF . "?action=assetos' class='text11special2'>Operating Systems</a></td>";
	echo "</tr></table>";
	echo "<table width=100% bgcolor='#ffeecc'><tr><td>\n";
	echo "</td></tr></table>\n";

	// do the summary
	$sql = "SELECT count(DISTINCT Assets.id) AS InvCount, Assets." . $field . " as InvField FROM Assets INNER JOIN Assignments ON Assets.Id = Assignments.AssetId WHERE Assignments.EmployeeId > -1 AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) GROUP BY Assets." . $field . " ORDER BY InvCount DESC";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$num_results = mysql_num_rows($result);
		$color = "#ffffee";
		while ($query_data = mysql_fetch_array($result)) {
			// if there is more than one of a certain type and the type is not blank, print it out
			if (($query_data["InvCount"] != "0") && (strlen($query_data["InvField"]) > 0)) {
			
				echo "<table width=100% bgcolor='" . $color . "' class='assetborder' cellpadding=5>";
				echo "<tr><td>";
				echo "<a href='" . $PHP_SELF . "?action=" . $action . "&key=" . html($query_data["InvField"]) . "' class='text11bold'>" . $query_data["InvField"] . " (" . $query_data["InvCount"] . ")</a>";
				echo "</td></tr>";
				echo "</table>";
				if ($color == "#ffffee") $color = "#ffffff";
				else $color = "#ffffee";

			}
			else $num_results--;
		}
		echo "<p><br><font class='text12'>" . $num_results . " " . $name . "(s) found.</font>";
	} else {
		echo "<table width=100% class='assetborder' bgcolor='#ffffee'><tr><td>";
		echo "<br><blockquote><font class='text12'>There are no " . $name. "s entered.</font></blockquote>";
		echo "</td></tr></table>\n";
	}
	
}

// List all assets for a specific type based on given assettype, supplier, or os
function asset_query($key, $field, $name) {
	global $action;
	global $hrcolor;
	global $emp_db;
		
	$PHP_SELF = $_SERVER['PHP_SELF'];
	// set whether to show active or inactive employees
	echo "<p>";
	echo "<table cellspacing=0 cellpadding=0 border=0><tr>";
	// types
	if (strcmp($action,"assets") == 0) echo "<td><font class='text11special2active' color='#000000'>Types</font></td>";
	else echo "<td><a href='" . $PHP_SELF . "?action=assets' class='text11special2'>Types</a></td>";
	// suppliers
	if (strcmp($action,"assetsupplier") == 0) echo "<td><font class='text11special2active' color='#000000'>Suppliers</font></td>";
	else echo "<td><a href='" . $PHP_SELF . "?action=assetsupplier' class='text11special2'>Suppliers</a></td>";
	// operating systems
	//if (strcmp($action,"assetos") == 0) echo "<td><font class='text11special2active' color='#000000'>Operating Systems</font></td>";
	//else echo "<td><a href='" . $PHP_SELF . "?action=assetos' class='text11special2'>Operating Systems</a></td>";
	//echo "</tr></table>";
	echo "<table width=100% bgcolor='#ffeecc'><tr><td>\n";
	echo "</td></tr></table>\n";

	$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id As Assets_ID FROM Assets WHERE Assets." . $field . "='" . $key . "' ORDER BY Assets.AssetType;";
	$color = "#ffffee";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$num_results = mysql_num_rows($result);

		while ($query_data = mysql_fetch_array($result)) {
			// prints out assets which are not surplus or retired
			$sql2 = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate FROM Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId WHERE Assignments.AssetId=" . $query_data["Assets_ID"] . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.Temp=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
			if (($result2 = doSql($sql2)) && (mysql_num_rows($result2)) && ($query_data2 = mysql_fetch_array($result2)) && ($query_data2["Employees_ID"] < 0)) {
				$num_results--;
			} else {
				$sql3 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $query_data["Assets_ID"] . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
				if (($result3 = doSql($sql3)) && (mysql_num_rows($result2))) $query_data3 = mysql_fetch_array($result3);
				asset_format($query_data,$query_data3,$color,"assetborder",false);
				if ($color == "#ffffee") $color = "#ffffff";
				else $color = "#ffffee";
			}
		}
		echo "<br>\n";
		echo "<p><font class='text12'>" . $num_results . " asset(s) listed.</font>";
	} else {
		echo "<font class='text12'><blockquote>There are no assets listed.</blockquote></font>";
	}
}

// List all the details of one asset
function asset_view($key) {
	global $hrcolor;
	global $print_screen;
	global $history;
	global $addlicense;
	global $removelicense;
	global $my_emp_id;
	global $my_access_level;
	global $addip;
	global $removeip;
	global $emp_db;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	// header, tabs, employee info
	if ($history != "1") $header_text = "Details";
	else $header_text = "History";
	$result_found_flag = false;

	// load the current owner of the asset
	$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
	if (($result2 = doSql($sql2)) && (mysql_num_rows($result2)) && $query_data2 = mysql_fetch_array($result2)) {
		$emp_owner = $query_data2["Employees_ID"];
		$is_editable = (($my_access_level > 1) || ($emp_owner == $my_emp_id));
	} else {
		$emp_owner = "0";
		$is_editable = false;
	}

	if ($is_editable) {
		if (strlen($addlicense) > 0) {
			$sql = "INSERT INTO LicenseOwners (AssetId, LicenseId) VALUES (" . $key . ",'" . $addlicense . "')";
			$result = doSql($sql);
		}
		if (strlen($removelicense) > 0) {
			$sql = "DELETE FROM LicenseOwners WHERE AssetId=" . $key . " AND Id=" . $removelicense;
			$result = doSql($sql);
		}
	}
		
	asset_menu_header(true,"",$header_text,$key);
	
	// select the desired assets
	$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assets.exp_date FROM Assets WHERE Assets.Id=" . $key . ";";
	if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
	

		asset_tabs($key);
		asset_format($query_data,$query_data2,"#ffffee","assetborder",false);
	
		if ($print_screen == false) echo "<p><table width=100%><tr>";

		if ($history != "1") $dates_sql = " AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) AND Assignments.Completed=0";
		else $dates_sql = "";
		$exp_date = $query_data["exp_date"];
		
		// transfers (move to another person)
		$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.LoginName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, " . $emp_db . "Employees.EMail, " . $emp_db . "Employees.Active, Assignments.Id AS Assignments_ID, Assignments.Temp, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $query_data["Assets_ID"] . $dates_sql . " AND Assignments.Temp=0 AND Assignments.Approve=0 ORDER BY Assignments.StartDate;";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) {
			if ($print_screen == false) echo "<td valign='top'>";
			echo "<p><font class='text11'><b>Assignments</b></font><br><hr size=0 color='" . $hrcolor . "'>";
			while ($query_data2 = mysql_fetch_array($result2)) {
				employee_format($query_data2,"#ffffee","#ffffdd","assetborder");
				if ($print_screen == true) echo "<p>";
			}
			if ($print_screen == false) echo "</td>";
			$found_transfer = true;
		}

		// sign outs
		$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.LoginName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, " . $emp_db . "Employees.Active, " . $emp_db . "Employees.EMail, Assignments.Id AS Assignments_ID, Assignments.Temp, Assignments.Completed, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $query_data["Assets_ID"] . $dates_sql . " AND Assignments.Temp=1 AND Assignments.Approve=0 ORDER BY Assignments.StartDate;";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) {
			if (($print_screen == false) && ($found_transfer == true)) echo "<td width=10>&nbsp;</td>";
			if ($print_screen == false) echo "<td valign='top'>";
			echo "<p><font class='text11'><b>Sign Outs</b></font><br><hr size=0 color='" . $hrcolor . "'>";
			while ($query_data2 = mysql_fetch_array($result2)) {
				employee_format($query_data2,"#ffffee","#ffffdd","assetborder");
				if ($print_screen == true) echo "<p>";
			}
			if ($print_screen == false) echo "</td>";
		}
		if ($print_screen == false) echo "</tr></table>";
		
		echo "<p>";

		// licenses
		echo "<p><font class='text11'><b>Licenses</b></font><br><hr size=0 color='" . $hrcolor . "'>";
		echo "<table bgcolor='#ffffee' cellpadding=5 class='assetborder' width=100%><tr><td>";
		$license_cnt = 1;
		$sql = "SELECT LicenseOwners.Id AS LicenseOwners_ID, Licenses.Manufacturer, Licenses.Product, COUNT(DISTINCT LicenseOwners.Id) AS CountLicenses FROM LicenseOwners LEFT JOIN Licenses ON LicenseOwners.LicenseId = Licenses.Product WHERE LicenseOwners.AssetId = " . $query_data["Assets_ID"] . " GROUP BY Product ORDER BY Product";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			echo "<table width=100%>";
			while ($query_data = mysql_fetch_array($result)) {
				if (($license_cnt % 2) == 1) echo "<tr>";
				echo "<td class='text12bold'>";
				echo "<li>" . $query_data["Manufacturer"] . " " . $query_data["Product"];
				if ($query_data["CountLicenses"] != "1") echo " (" . $query_data["CountLicenses"] . ")"; 
				if (($print_screen == false) && ($is_editable)) echo " <a href='" . $PHP_SELF . "?action=assetview&key=" . $key . "&removelicense=" . $query_data["LicenseOwners_ID"] . "'>(remove)</a>";
				echo "</td>";
				if (($license_cnt % 2) == 0) echo "</tr>";
				$license_cnt++;

			}
			echo "</table>";
		} else {
			echo "<li><font class='text12bold'>There are no licenses assigned to this asset.</font>";
		}
		
		// show the add license form
		if (($print_screen == false) && ($is_editable)) {
			echo "<p><table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
			echo "<input type='hidden' name='action' value='assetview'>";
			echo "<input type='hidden' name='key' value='" . $key . "'>";
			echo "<select name='addlicense' class='boxtext13'>";
			echo "<option value='' SELECTED>(Select A Product)</option>";
			$sql = "SELECT manufacturer, product FROM Licenses GROUP BY product ORDER BY product";
			if (($result = doSql($sql)) && (mysql_num_rows($result))) {
				while ($query_data = mysql_fetch_array($result)) {
					echo "<option value='" . $query_data["product"] . "' class='text12'>" . $query_data["manufacturer"] . " " . $query_data["product"] . "</option>";
				}
			}
			echo "</select>";
			echo "<br><input type='image' src='images/add.jpg' border=0 width=88 height=27>";
			echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
		}
		echo "</td></tr></table>";

		// make changes to ips
		if ($is_editable) {
			if ($addip != "") doSql("INSERT INTO IP (employeeid,assetid,ip) VALUES (" . $emp_owner . "," . $key . ",'" . $addip . "')");
			if ($removeip != "") doSql("DELETE FROM IP WHERE id=" . $removeip);
		}
		
		// show current ips
		echo "<p><font class='text12bold'>IP</font><br><hr size=0 color='" . $hrcolor . "'>";
		echo "<table bgcolor='#ffffee' cellpadding=5 class='assetborder' width=100%><tr><td>";
		$ip_cnt = 1;
		$sql = "SELECT id, ip FROM IP WHERE assetid=" . $key;
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			echo "<table width=100%>";
			while ($query_data = mysql_fetch_array($result)) {
				if (($ip_cnt % 2) == 1) echo "<tr>";
				echo "<td class='text12bold'>";
				echo $query_data["ip"];
				if ($print_screen == false) echo " <a href='" . $PHP_SELF . "?action=assetview&key=" . $key . "&removeip=" . $query_data["id"] . "'>(remove)</a>";
				echo "</td>";
				if (($ip_cnt % 2) == 0) echo "</tr>";
				$ip_cnt++;
			}
			if (($ip_cnt % 2) == 0) echo "<td>&nbsp;</td></tr>";
			echo "</table>";
		} else {
			echo "<table width=100%>";
			echo "<tr>";
			echo "<td class='text12bold'><li>No IPs are assigned to this asset";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}

		// show the add ip form
		if (($print_screen == false) && ($is_editable)) {
			echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
			echo "<input type='hidden' name='action' value='assetview'>";
			echo "<input type='hidden' name='key' value='" . $key . "'>";
			echo "<input type='text' name='addip' class='boxtext13' size=30><br><input type='image' src='images/add.jpg' border=0 width=88 height=27>";
			echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
		}

		echo "</td></tr></table>";

		if (strcmp($exp_date,"") != 0) {
			echo "<p><font class='text11'><b>exp_date</b></font><br><hr size=0 color='" . $hrcolor . "'>";
			echo "<table bgcolor='#ffffee' cellpadding=5 class='assetborder' width=100%><tr><td class='text13'>";
			echo $exp_date;
			echo "</td></tr></table>";
		}
		echo "<p><br>";

	} else {
		echo "<font class='text12'>The requested asset cannot be found.</font>";
	}
}

function asset_menu_header($domain, $section, $title, $key) {
	global $print_screen;
	global $hrcolor;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	// domain
	if ($print_screen == false) { 
		if ($domain == true) {
			if ($empid == "-1") $top = "<a href='" . $PHP_SELF . "?action=surplus' class='text10bold'>Surplus</a>";
			elseif ($empid == "-2") $top = "<a href='" . $PHP_SELF . "?action=retired' class='text10bold'>Retired</a>";
			else $top = "<a href='" . $PHP_SELF . "?action=assets' class='text10bold'>Assets</a>";
		} else {
			$top = "";
		}
	}


	// key lookup
	if (strlen($key) > 0) {
		$sql = "SELECT Assignments.EmployeeId, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel FROM Assignments LEFT JOIN Assets ON Assignments.AssetId = Assets.Id WHERE Assets.Id=" . $key;
		if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) {
			$empid = $query_data["EmployeeId"];
			if (($empid != "-1") && ($empid != "-2")) $top = $top . ": <a href='" . $PHP_SELF . "?action=assets&key=" . html($query_data["AssetType"]) . "' class='text10bold'>" . $query_data["AssetType"] . "</a>: <a href='" . $PHP_SELF . "?action=assetview&key=" . $key . "' class='text10bold'>" . $query_data["AssetSupplier"] . " " . $query_data["AssetModel"] . "</a>";
			else $top = $top . ": <a href='" . $PHP_SELF . "?action=assetview&key=" . $key . "' class='text10bold'>" . $query_data["AssetSupplier"] . " " . $query_data["AssetModel"] . "</a>";
		}
	}

	// section
	if ($section != "") $top = $top . ": <font class='text10bold'>" . $section . "</font>";

	// icon
	if ($empid == "-1") $icon = "surplus.jpg";
	elseif ($empid == "-2") $icon = "retired.jpg";
	else $icon = "assets.jpg";

	menu_header($top,$title,$icon);
}


// prints out the top tabs
function asset_tabs($assetid) {
	global $action;
	global $my_emp_id;
	global $temp;
	global $my_access_level;
	global $print_screen;
	global $history;
	global $emp_db;
	
	if (($assetid != "") && ($print_screen == false) && ($my_access_level > 0)) {
		echo "<p>";
		
		echo "<table cellpadding=0 cellspacing=0 border=0><tr>";
		// details
		if ((strcmp($action,"assetview") == 0) && ($history != "1")) echo "<td><font class='text11special2active' color='#000000'>Details</font></td>";
		else echo "<td><a href='" . $PHP_SELF . "?action=assetview&key=" . $assetid . "' class='text11special2'>Details</a></td>";

		// update
		if ((asset_get_empid($assetid) == $my_emp_id) || ($my_access_level > 1)) {
			if (strcmp($action,"assetupdate") == 0) echo "<td><font class='text11special2active' color='#000000'>Update</font></td>";
			else echo "<td><a href='" . $PHP_SELF . "?action=assetupdate&key=" . $assetid . "' class='text11special2'>Update</a></td>";
		} else {
			echo "<td><font class='text11special2' color='#cccccc'>Update</font></td>";
		}

		// delete
		if ((in_array($action,Array("assettransfer","assettransfererase","assettransfersignin"))) && ($temp != "1")) echo "<td><font class='text11special2active'>Transfer</font></td>";
		else echo "<td><a href='" . $PHP_SELF . "?action=assettransfer&key=" . $assetid . "' class='text11special2'>Transfer</a></td>";


		// transfer / sign out
		$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $assetid . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) {
			$query_data2 = mysql_fetch_array($result2);
			if (($query_data2["Employees_ID"] == 0) || ($query_data2["Employees_ID"] == -1)) $allow_sign_out = true;
			else $allow_sign_out = false;
		}
		if ($my_access_level < 2) $newowner = "&newowner=" . $my_emp_id;
		else $newowner = "";
		if ($allow_sign_out == true) {
			if ((in_array($action,Array("assettransfer","assettransfererase","assettransfersignin"))) && ($temp == "1")) echo "<td><font class='text11special2active' color='#000000'>Sign Out</font></td>";
			else echo "<td><a href='" . $PHP_SELF . "?action=assettransfer&key=" . $assetid . "" . $newowner . "&temp=1' class='text11special2'>Sign Out</a></td>";
		} else {
			echo "<td><font class='text11special2' color='#cccccc'>Sign Out</font></td>";
		}

		// calendar
		if ((strcmp($action,"assetcalendar") == 0) && ($temp != "1")) echo "<td><font class='text11special2active'>Calendar</font></td>";
		else echo "<td><a href='" . $PHP_SELF . "?action=assetcalendar&key=" . $assetid . "' class='text11special2'>Calendar</a></td>";
		// history
		if ((strcmp($action,"assetview") == 0) && ($history == "1") && ($temp != "1")) echo "<td><font class='text11special2active'>History</font></td>";
		else echo "<td><a href='" . $PHP_SELF . "?action=assetview&key=" . $assetid . "&history=1' class='text11special2'>History</a></td>";

		// dates
		if ($my_access_level > 1) {
			if (strcmp($action,"assetdates") == 0) echo "<td><font class='text11special2active'>Date Mgmt</font></td>";
			else echo "<td><a href='" . $PHP_SELF . "?action=assetdates&key=" . $assetid . "' class='text11special2'>Date Mgmt</a></td>";
		}


		// erase
		if ($my_access_level > 1) {
			if (strcmp($action,"asseterase") == 0) echo "<td><font class='text11special2active'>Erase</font></td>";
			else echo "<td><a href='" . $PHP_SELF . "?action=asseterase&key=" . $assetid . "' class='text11special2'>Erase</a></td>";
		}

		echo "</td></tr></table>";		
		echo "<table width=100% bgcolor='#ffeecc'><tr><td>\n";
		echo "</td></tr></table>\n";
	}
}

// returns the current owner employee id of an asset
function asset_get_empid($key) {
	global $emp_db;
	$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
	if (($result2 = doSql($sql2)) && (mysql_num_rows($result2)) && $query_data2 = mysql_fetch_array($result2)) {
		$emp_owner = $query_data2["Employees_ID"];
	} else {
		$emp_owner = "0";
	}
	return $emp_owner;
}

// prints out an asset based on a key
function asset_print_info($key) {
	global $emp_db;
	$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID FROM Assets WHERE Assets.Id=" . $key . ";";
	$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Temp, Assignments.Completed, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
	if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) {
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) $query_data2 = mysql_fetch_array($result2);
		asset_format($query_data,$query_data2,"#ffffee","assetborder",false);
	}
}

?>
