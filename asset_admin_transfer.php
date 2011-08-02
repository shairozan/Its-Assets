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
// asset_admin_transfer_erase($key)
// asset_admin_transfer_sign_in($key)
// asset_admin_transfer_squeeze()
// asset_admin_transfer_approvals($key)
// asset_admin_transfer_approvals_approve($key, $new)
// asset_admin_transfer_approvals_deny($key)
// asset_admin_transfer_choose_owner($key)
// asset_admin_transfer_dates($key)
// asset_admin_transfer_licenses($key)
// asset_admin_transfer($key)


// deletes an upcoming asset transfer or sign out
function asset_admin_transfer_erase($key) {
	global $my_access_level;
	global $my_emp_id;
	global $print_screen;
	
	// get info for transfer id
	if ($my_access_level < 2) $sql2 = "SELECT StartDate, EndDate, EmployeeId, AssetId, Temp FROM Assignments WHERE Id=" . $key . " AND StartDate > " . time() . " AND EmployeeId=" . $my_emp_id;
	else $sql2 = "SELECT StartDate, EndDate, EmployeeId, AssetId, Temp FROM Assignments WHERE Id=" . $key;

	if (($result2 = doSql($sql2)) && (mysql_num_rows($result2) && ($query_data2 = mysql_fetch_array($result2)))) {
		// found and may be deleted
		$startdate = $query_data2["StartDate"];
		$enddate = $query_data2["EndDate"];
		$assetid = $query_data2["AssetId"];
		$temp = $query_data2["Temp"];
		$err_out = "You cannot cancel the only owner or any transfers which have already taken place.";
	} else {
		// not found, cannot be deleted
		$err_out = "This transfer has already been deleted or its start date has already passed.  See the help for options about changing transfer dates.";
	}

	// for permanent transfers only, verify number of entries is greater than 1, otherwise the asset will be assigned to no one if it was deleted
	$sql = "SELECT StartDate, EndDate, EmployeeId AS Employees_ID, AssetId, Temp FROM Assignments WHERE AssetId=" . $assetid . " AND Temp=0 AND Approve=0";
	if ($result = doSql($sql)) $num_results = mysql_num_rows($result);
	else $num_results = 0;
	
	// check if either the transfer no longer exists OR if it's the only transfer
	if ((mysql_num_rows($result2) == 0) || (($temp == "0") && ($num_results < 2))) {
		asset_menu_header(true,"Cancel Request","<font class=text18bold color='#ff0033'>ERROR: Cancellation Refused.</font>",$assetid);
		asset_tabs($assetid);
		asset_print_info($assetid);
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		echo "<blockquote><font class='text12'>" . $err_out . "</font></blockquote>";
		echo "<p></td></tr></table>";
	} else {

		if ($temp == 0) {
			// select the asset transfer right after the asset transfer being deleted and get start date
			$sql = "SELECT StartDate FROM Assignments WHERE AssetId=" . $assetid . " AND Temp=0 AND StartDate > " . $enddate . " AND Approve=0 AND id <> " . $key . " ORDER BY StartDate";
			$result = doSql($sql);
			if (($result = doSql($sql)) && (mysql_num_rows($result) && ($query_data = mysql_fetch_array($result)))) $newenddate = ($query_data["StartDate"] - 1);
			else $newenddate = "0";

			// if current end date is 0, the new end date will be 0
			if ($enddate == "0") $newenddate = "0";

			// select the asset transfer right before the asset transfer being deleted
			// update its enddate with saved startdate - 1
			$sql = "SELECT id FROM Assignments WHERE AssetId=" . $assetid . " AND Temp=0 AND StartDate < " . $startdate . " AND Approve=0 AND id <> " . $key . " ORDER BY StartDate DESC";
			if (($result = doSql($sql)) && (mysql_num_rows($result) && ($query_data = mysql_fetch_array($result)))) {
				$sql = "UPDATE Assignments SET EndDate=" . $newenddate . " WHERE id=" . $query_data["id"];
				$result = doSql($sql);	
			}
		}
			
		// delete the requested transfer
		$sql = "DELETE FROM Assignments WHERE Id=" . $key;
		$result = doSql($sql);

		// join two adjacent transfers assigned to the same employee and asset
		asset_admin_transfer_squeeze();	
		asset_menu_header(true,"Cancel Request","Cancellation Accepted</font>",$assetid);
		asset_tabs($assetid);
		asset_print_info($assetid);
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		echo "<blockquote><font class='text12'>The request has been successfully cancelled.</font></blockquote>";
		echo "<p></td></tr></table>";
	}
}

// does an early sign in of an asset previously signed out
function asset_admin_transfer_sign_in($key) {
	global $lastaction;
	global $lastkey;
	$sql = "SELECT EmployeeId AS Employees_ID, AssetId, EndDate FROM Assignments WHERE id=" . $key;
	if (($result = doSql($sql)) && (mysql_num_rows($result) && ($query_data = mysql_fetch_array($result)))) {
		$day = date("d",time());
		$month = date("m",time());
		$year = date ("Y",time());
		$new_time =  mktime(23,59,59,$month,$day,$year);
		if ($new_time < $query_data["EndDate"]) $sql = "UPDATE Assignments SET EndDate=" . $new_time . ",Completed=1 WHERE id=" . $key;
		else $sql = "UPDATE Assignments SET Completed=1 WHERE id=" . $key;
		$result = doSql($sql);
		asset_menu_header(true,"Early Sign In","Request for Early Sign In Received",$query_data["AssetId"]);
		asset_tabs($query_data["AssetId"]);
		asset_print_info($query_data["AssetId"]);
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		echo "<blockquote><font class='text12'>Asset has been successfully signed in.</font></blockquote>";
		echo "<p></td></tr></table>";
		echo "<p><center>";
		echo "<a href='" . $PHP_SELF . "?action=" . $lastaction . "&key=" . $lastkey . "'><img src='images/next.jpg' border=0 width=88 height=27></a>";
		echo "</center>";
	} else {
		asset_menu_header(true,"Early Sign In","<font color='#ff0033'>ERROR: Request for Early Sign In Failed</font>",$query_data["AssetId"]);
		asset_tabs($query_data["AssetId"]);
		asset_print_info($query_data["AssetId"]);
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		echo "<blockquote><font class='text12'>The transfer information about the asset you are trying to sign in could not be located.</font></blockquote>";
		echo "<p></td></tr></table>";
		echo "<p><center>";
		echo "<a href='" . $PHP_SELF . "?action=" . $lastaction . "&key=" . $lastkey . "'><img src='images/next.jpg' border=0 width=88 height=27></a>";
		echo "</center>";
	}
}

// join two adjacent transfers assigned to the same employee and asset
function asset_admin_transfer_squeeze() {
	$sql = "SELECT Assignments.id AS id_Main, Assignments_Alias.id AS id_Alias, Assignments.StartDate AS StartDate, Assignments.EndDate AS EndDate, Assignments_Alias.StartDate AS StartDate_Alias, Assignments_Alias.EndDate AS EndDate_Alias FROM Assignments LEFT JOIN Assignments AS Assignments_Alias ON ((Assignments.EndDate = ((Assignments_Alias.StartDate)-1)) AND (Assignments.EmployeeId = Assignments_Alias.EmployeeId) AND (Assignments.AssetId = Assignments_Alias.AssetId) AND (Assignments.Temp = Assignments_Alias.Temp)) WHERE Assignments.Approve=0 AND Assignments_Alias.Approve=0";
	$result = doSql($sql);
	while (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
		if ($query_data["StartDate"] == 0) {
			$newsql = "UPDATE Assignments SET StartDate=" . $query_data["StartDate_Alias"] . " WHERE Id=" . $query_data["id_Main"];
			$result2 = doSql($newsql);
		}
		$newsql = "UPDATE Assignments SET EndDate=" . $query_data["EndDate_Alias"] . " WHERE Id=" . $query_data["id_Main"];
		$result2 = doSql($newsql);
		$newsql = "DELETE FROM Assignments WHERE id=" . $query_data["id_Alias"];
		$result2 = doSql($newsql);
	}
}

// approval screen for unapproved transfers
function asset_admin_transfer_approvals($key) {
	global $my_emp_id;
	global $my_access_level;
	global $temp;
	global $approve;
	global $deny;
	global $print_screen;
	global $hrcolor;
	global $emp_db;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if (is_numeric($approve)) $err_out = asset_admin_transfer_approvals_approve($approve, true);
	if (is_numeric($deny)) $err_out = asset_admin_transfer_approvals_deny($deny);

	asset_menu_header(true,"","Approve Transfers","");

	$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Id, Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Id AS Assignments_ID FROM ((Assets LEFT JOIN Assignments ON Assets.ID = Assignments.AssetId) LEFT JOIN " . $emp_db . "Employees ON " . $emp_db . "Employees.ID = Assignments.EmployeeID) WHERE Assignments.Approve=1 ORDER BY Assignments.StartDate DESC;";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		echo "<table width=100% border=0 class='approvals_dark'>";
		echo "<td class='text12bold'>Transfer Date</td>";
		echo "<td class='text12bold'>Current Owner</td>";
		echo "<td class='text12bold'>New Owner</td>";
		echo "<td class='text12bold'>Asset Tag</td>";
		echo "<td class='text12bold'>Type</td>";
		echo "<td class='text12bold'>Supplier/Model</td>";
		echo "<td class='text12bold'>Approve</td>";
		echo "<td class='text12bold'>Deny</td>";
		
		while ($query_data = mysql_fetch_array($result)) {
			$assetid = $query_data["Assets_ID"];
			$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.Completed, Assignments.EndDate, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $assetid . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.StartDate <= " . time() . " ORDER BY Assignments.StartDate DESC;";
			$result2 = doSql($sql2);
			if (($result2) && (mysql_num_rows($result2)) && ($query_data2 = mysql_fetch_array($result2)) ) {
				$cur_owner_id = $query_data2["Employees_ID"];
				switch($query_data2["Employees_ID"]) {
					case "0":
						$cur_owner_name = "General Assets";
						break;
					case "-1":
						$cur_owner_name = "Surplus";
						break;
					case "-2":
						$cur_owner_name = "Retired";
						break;
					default:
						$cur_owner_name = $query_data2["LastName"] . ", " . $query_data2["FirstName"];
						break;
				}
			} else {
				$cur_owner_name = "Unknown";
			}
			$new_owner_name = $query_data["Employees_ID"];
			switch($query_data["Employees_ID"]) {
				case "0":
					$new_owner_name = "General Assets";
					break;
				case "-1":
					$new_owner_name = "Surplus";
					break;
				case "-2":
					$new_owner_name = "Retired";
					break;
				default:
					$new_owner_name = $query_data["LastName"] . ", " . $query_data["FirstName"];
					break;
			}
			echo "<tr>";
			echo "<td class='approvals'><font class='text12'>";
			echo date("l, M d, Y", $query_data["StartDate"]);
			echo "</font></td>";
			echo "<td class='approvals'>";
			echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $cur_owner_id . "' class='text12'>" . $cur_owner_name . "</a>";
			echo "</td>";
			echo "<td class='approvals'>";
			echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $new_owner_id . "' class='text12'>" . $new_owner_name . "</a>";
			echo "</td>";
			echo "<td class='approvals'>";
			echo "<a href='" . $PHP_SELF . "?action=assetview&key=" . $query_data["Assets_ID"] . "' class='text12'>" . $query_data["AssetTag"] . "</a>";
			echo "</td>";
			echo "<td class='approvals'><font class='text12'>";
			echo $query_data["AssetType"];
			echo "</font></td>";
			echo "<td class='approvals'><font class='text12'>";
			echo $query_data["AssetSupplier"] . " " . $query_data["AssetModel"];
			echo "</font></td>";
			echo "<td class='approvals'><a href='" . $PHP_SELF . "?action=assetapprovals&approve=" . $query_data["Assignments_ID"] . "' class='text12bold'>Approve</td>";
			echo "<td class='approvals'><a href='" . $PHP_SELF . "?action=assetapprovals&deny=" . $query_data["Assignments_ID"] . "' class='text12bold'>Deny</td>";
			echo "</tr>";
		}
		echo "</table>";
	} else {
		echo "<p><blockquote><font class='text12'>There are no pending transfers to be approved.</blockquote></font>";
	}
	if (($approve != "") || ($deny != "")) {
		if (strlen($err_out) > 10) {
			echo "<blockquote><font class='text12'>" . $err_out . "</font></blockquote>\n";
		} else {
			if (strlen($deny) > 0) echo "<blockquote><font class='text12'>The last request was successfully denied and removed from the database.</font></blockquote>\n";
			else echo "<blockquote><font class='text12'>The last request was approved successfully.</font></blockquote>\n";
		}
	}
	echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
}

// approves a requested transfer
function asset_admin_transfer_approvals_approve($id, $new) {
	$err_out = "";

	// get info for transfer id
	$sql = "SELECT StartDate, EndDate, AssetId, EmployeeId, Temp FROM Assignments WHERE Id=" . $id;
	$result = doSql($sql);
	if (($result = doSql($sql)) && (mysql_num_rows($result) && ($query_data = mysql_fetch_array($result)))) {
		$startdate = $query_data["StartDate"];
		$enddate = $query_data["EndDate"];
		$assetid = $query_data["AssetId"];
		$employeeid = $query_data["EmployeeId"];
		$temp = $query_data["Temp"];
	} else {
		$err_out = $err_out . "Internal Error: The id supplied within the application was not found.<br>";
	}
		
	// for a transfer adjust end dates of others
	if ($temp == "0") {
		// remove transfers with same start date
		$sql = "DELETE FROM Assignments WHERE AssetId=" . $assetid . " AND id <> " . $id . " AND StartDate = " . $startdate . " AND Temp=0 AND Approve=0";
		$result = doSql($sql);
	
		// find all startdates greater than my startdate and select the one just after my start date, update my enddate with its startdate
		$sql = "SELECT StartDate FROM Assignments WHERE AssetId=" . $assetid . " AND StartDate >= " . $startdate . " AND Temp=0 AND id <> " . $id . " AND Approve=0 ORDER BY StartDate";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
			$sql = "UPDATE Assignments SET enddate=" . ($query_data["StartDate"]-1) . " WHERE id=" . $id;
			$result = doSql($sql);
		}
	
		// find all startdates before my startdate select the last startdate just before my start date, update its enddate with my startdate
		$sql = "SELECT id FROM Assignments WHERE AssetId=" . $assetid . " AND StartDate <= " . $startdate . " AND Temp=0 AND id <> " . $id . " AND Approve=0 ORDER BY StartDate DESC";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
			$sql = "UPDATE Assignments SET enddate=" . ($startdate-1) . " WHERE id=" . $query_data["id"];
			$result = doSql($sql);
		}
	}


	// approve the transfer
	$sql = "UPDATE Assignments SET approve=0 WHERE id=" . $id;
	$result = doSql($sql);
	if (!$result) $err_out = $err_out . "<br>" . mysql_error() . "<br>";

	if ($new == true) {
		$sql = "INSERT INTO Msgs (employeeid,assetid,date,msgcode,msg) VALUES (" . $employeeid . "," . $assetid . "," . time() . ",1,'transfer request approved')";
		$result = doSql($sql);
	}

	// join adjacent time frames
	asset_admin_transfer_squeeze();

	return $err_out;
}

// denies and deletes a requested transfer
function asset_admin_transfer_approvals_deny($id) {
	$err_out = "";
	$sql = "SELECT AssetId, EmployeeId FROM Assignments WHERE Id=" . $id;
	$result = doSql($sql);
	if (($result = doSql($sql)) && (mysql_num_rows($result) && ($query_data = mysql_fetch_array($result)))) {
		$assetid = $query_data["AssetId"];
		$employeeid = $query_data["EmployeeId"];
		$sql = "DELETE FROM Assignments WHERE id=" . $id;
		$result = doSql($sql);
		$sql = "INSERT INTO Msgs (employeeid,assetid,date,msgcode,msg) VALUES (" . $employeeid . "," . $assetid . "," . time() . ",2,'transfer request denied')";
		$result = doSql($sql);
	} else {
		$err_out = $err_out . "<br>" . mysql_error() . "<br>";
	}
	return $err_out;
}


// select a new owner for a transfer about to be inserted
function asset_admin_transfer_choose_owner($key,$curOwnerName,$curOwnerId) {
	global $my_emp_id;
	global $my_access_level;
	global $temp;
	global $hrcolor;
	global $emp_db;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	//administrator
	if ((strcmp($curOwnerId,$my_emp_id) == 0) || ($my_access_level > 1)) {
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		if ($my_access_level < 2) echo "<center><font class='text12bold'>You are currently the owner of this asset.</font></center>";
		echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
		// employee drop down
		echo "<center>";
		echo "<input type='hidden' name='action' value='assettransfer'>";
		echo "<input type='hidden' name='key' value='" . $key . "'>";


		if ($temp == "1") echo "<font class='text12'>Sign out this asset to </font>";
		else echo "<font class='text12'>Transfer this asset from <b>" . $curOwnerName . "</b>  to </font>";
		echo "<input type='hidden' name='temp' value='" . $temp . "'>";
		echo "<select name='newowner' size='1' class='boxtext13'>";
		$sql2 = "SELECT LastName, FirstName, Tel, Organization, Dept, Building, Floor, Workstation, Id AS Employees_ID FROM " . $emp_db . "Employees ORDER BY LastName;";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) {
			echo "<option value='0'>General Assets</option>";
			echo "<option value='-1'>Surplus</option>";
			echo "<option value='-2'>Retired</option>";
			echo "<option value=''>----------------------------</option>";
			while ($query_data2 = mysql_fetch_array($result2)) {
				if (strcmp($curOwnerId,$query_data2["Employees_ID"]) == 0) $selected = " SELECTED";
				else $selected = "";
				echo "<option value='" . $query_data2["Employees_ID"] . "'" . $selected . ">" . $query_data2["LastName"] . ", " . $query_data2["FirstName"] . "</option>";
			}
		}
		echo "</select>";
		echo "</center>";
		echo "<p></td></tr></table>";	
		echo "<hr size=0 color='" . $hrcolor . "'>";
		echo "<center>";
		echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
		echo "<input type='image' name='submit' src='images/next.jpg' border=0 width=88 height=27></center></form>";
		echo "<br></center>";
		echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
	} else {
		// non-administrator users
		echo "<table width=100% bgcolor='#ffeecc'><tr><td>";
		echo "<br><font class='text12bold'>You are not currently the owner of this asset. Would you like to request a transfer of this item?</font>";
		echo "<form action='" . $PHP_SELF . "' method='get'><blockquote>";
		echo "<input type='hidden' name='action' value='assettransfer'>";
		echo "<input type='hidden' name='key' value='" . $key . "'>";
		echo "<input type='radio' name='newowner' value='" . $my_emp_id . "'><font class='text12'>Yes</font><br>";
		echo "<input type='radio' name='newowner' value='N'><font class='text12'>No</font><br>";
		echo "</blockquote>";
		echo "<p></td></tr></table>";	
		echo "<hr size=0 color='" . $hrcolor . "'>";
		echo "<center>";
		echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
		echo "<input type='image' name='submit' src='images/next.jpg' border=0 width=88 height=27></center>";
		echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
		echo "</center>";
	}
}

// select dates for a transfer about to be inserted
// sign outs uses startdate and enddate
// transfers use startdate but are labelled onscreen as "Transfer Date"
function asset_admin_transfer_dates($key) {
	global $key, $newowner, $temp;
	global $startdate, $enddate;
	global $day, $month, $year;
	global $hrcolor;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	echo "<table cellspacing=0 cellpadding=0 border=0><form action='" . $PHP_SELF . "' method=get></td></tr></table>";
	echo "<input type='hidden' name='action' value='assettransfer'>";
	echo "<input type='hidden' name='key' value='" . $key . "'>";
	echo "<input type='hidden' name='newowner' value='" . $newowner . "'>";
	echo "<input type='hidden' name='startdate' value='" . $startdate . "'>";
	echo "<input type='hidden' name='temp' value='" . $temp . "'>";
	
	echo "<table width=100% bgcolor='#ffeecc' class='assetborder'><tr><td>";
	echo "<center>";
	
	if ($temp == "1") {
		if (strlen($startdate) < 1) {
			$is_enddate = 0;
		} else {
			$is_enddate = 1;
		}
	} else {
		$is_enddate = 0;	
		$temp = "0";
	}

	calendar($day, $month, $year, $is_enddate, $temp, false);
	echo "</center>";
	echo "</td></tr></table>";	
	echo "<hr size=0 color='" . $hrcolor . "'>";
	echo "<center>";
	echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
	echo "<input type='image' name='submit' src='images/next.jpg' border=0 width=88 height=27></center></form>";
	echo "<br></center>";
	echo "<table cellspacing=0 cellpadding=0 border=0></form></td></tr></table>";
}

// decide what to do with transfers
function asset_admin_transfer_licenses($key,$curOwnerName,$curOwnerId) {
	global $key, $newowner, $temp;
	global $startdate, $enddate;
	global $day, $month, $year;
	global $hrcolor;
	global $newowner;
	global $my_emp_id;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	$sql = "SELECT LicenseOwners.Id AS LicenseOwners_ID, Licenses.Manufacturer, Licenses.Product FROM LicenseOwners LEFT JOIN Licenses ON LicenseOwners.LicenseId = Licenses.Product WHERE AssetId=" . $key . " GROUP BY LicenseOwners.Id";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$transfer_header_text = "Make Licenses Changes";
		asset_menu_header(true,$section_text,$transfer_header_text,$key);
		asset_tabs($key);
		asset_print_info($key);

		echo "<table width=100% bgcolor='#ffeecc'><tr><td>";
		echo "<blockquote>";
		echo "<form action='" . $PHP_SELF . "' method='get'>";
		echo "<font class='text12'>";
		echo "<p><br>For all checked licenses, I would like to ";

		echo "<select name='license_action' class='text12' size='1'>";
		echo "<option value='0'>return the licenses to surplus</option>";

		if ($my_emp_id == $curOwnerId) $my_the = "my";
		else $my_the = "this employee's";
		
		$sql2 = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Id As Assignments_ID FROM Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId WHERE Assignments.EmployeeId=" . $curOwnerId . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND Assignments.Temp=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) AND Assignments.Completed=0 AND Assets.Id <> " . $key . " ORDER BY Assignments.StartDate;";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) {
			while ($query_data2 = mysql_fetch_array($result2)) {
				echo "<option value='" . $query_data2["Assets_ID"] . "'>Move to " . $my_the . " " . $query_data2["AssetType"] . " (" . $query_data2["AssetSupplier"] . " " . $query_data2["AssetModel"] . ")</option>";
			}
		}
		echo "</select>";		
		echo "</font>";
		echo "<font class='text12'><br><i>All unchecked licenses will remain assigned to this asset after the transfer.</i></font>";
		echo "<input type='hidden' name='action' value='assettransfer'>";
		echo "<input type='hidden' name='key' value='" . $key . "'>";
		echo "<input type='hidden' name='startdate' value='" . $startdate . "'>";
		echo "<input type='hidden' name='enddate' value='" . $enddate . "'>";
		echo "<input type='hidden' name='temp' value='" . $temp . "'>";
		echo "<input type='hidden' name='newowner' value='" . $newowner . "'>";
		$license_cnt = 1;
		echo "<p><table width=100%>";
		while ($query_data = mysql_fetch_array($result)) {
			if (($license_cnt % 2) == 1) echo "<tr>";
			echo "<td class='text12bold'>";
			echo "<input type='checkbox' name='licenses[]' value='" . $query_data["LicenseOwners_ID"] . "'><font class='text12'>" . $query_data["Manufacturer"] . " " . $query_data["Product"] . "</font><br>";
			echo "</td>";
			if (($license_cnt % 2) == 0) echo "</tr>";
			$license_cnt++;
		}
		echo "</table>";
		echo "</blockquote>";
		echo "<p></td></tr></table>";	
		echo "<hr size=0 color='" . $hrcolor . "'>";
		echo "<center>";
		echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
		echo "<input type='image' name='submit' src='images/next.jpg' border=0 width=88 height=27></center>";
		echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
		echo "</center>";
		return true;
	} else {
		return false;
	}
}

function asset_admin_transfer_complete($key,$transfer_header_text) {
	global $startdate,$enddate, $temp;
	global $my_access_level;
	global $newowner;
	global $license_action;
	global $licenses;
	
	$err_out = "";

	// set the temp flag
	if ($enddate == "0") $temp = 0;				
	else $temp = 1;
	
	// check in case this page is accidentally reloaded
	if ($my_access_level > 1) $approve_val = "0";
	else $approve_val = "1";
	
	// update the license information
	if ($license_action == "0") {
		for ($i=0;$i<count($licenses);$i++) {
			doSql("DELETE FROM LicenseOwners WHERE id=" . $licenses[$i]);
		}
	} else {
		for ($i=0;$i<count($licenses);$i++) {
			doSql("UPDATE LicenseOwners SET AssetId=" . $license_action ." WHERE id=" . $licenses[$i]);
		}
	}
	
	$sql = "SELECT * FROM Assignments WHERE employeeid=" . $newowner . " AND assetid=" . $key . " AND startdate=" . $startdate . " AND enddate=" . $enddate . " AND temp=" . $temp . " AND Approve=" . $approve_val;
	$result = doSql($sql);
	if (($result) && (mysql_num_rows($result))) {
		// if the link exists, do nothing
	} else {
		// if not, create a new link, temporarily unapproved
		$sql = "INSERT INTO Assignments (employeeid, assetid, startdate, enddate, approve, temp, completed) VALUES (" . $newowner . "," . $key . "," . $startdate . "," . $enddate . ",1," . $temp . ",0);";
		$result = doSql($sql);
		if (!($result)) $err_out = $err_out . mysql_error() . "<br>";

		// if you are logged in as an Admin OR this is a sign out, automatically approve the sign out.
		if ((strlen($err_out) < 1) && (($my_access_level > 1) || (($my_access_level < 2) && ($enddate != 0)))) {
			$sql = "SELECT last_insert_id() as last FROM Assignments;";
			if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) $err_out = asset_admin_transfer_approvals_approve($query_data[0],false);
			else $err_out = "INTERNAL ERROR: No id inserted.";
		}
	}
				
	// verify if any errors occurred
	if (strlen($err_out) > 0) {
		// errors occurred
		asset_menu_header(true,$transfer_header_text,"<font class=text18bold color='#ff0033'>" . $transfer_header_text . " Failed</font>",$key);
		asset_tabs($key);
		asset_print_info($key);
		echo "<table width=100% bgcolor='#ffeecc' class='assetborder'><tr><td class='text12'>";
		echo "<blockquote>";
		echo "<font class='text12'><blockquote>The request did not complete successfully.<p>The following errors occurred:<br>" . $err_out . "</blockquote></font>";
		echo "</blockquote></td></tr></table>";
	} else {
		// everything is ok, print a confirmation message
		if ($enddate == 0) $enddate_full = "";
		else $enddate_full = " to " . date("M-d-Y", $enddate);
		asset_menu_header(true,$transfer_header_text,"<font class=text18bold>" . $transfer_header_text . " Completed Successfully</font>",$key);
		asset_tabs($key);
		asset_print_info($key);
		echo "<table width=100% bgcolor='#ffeecc' class='assetborder'><tr><td class='text12'>";
		echo "<blockquote>";
		echo "<br>The request for this asset has been entered for dates: " . date("M-d-Y", $startdate) . "" . $enddate_full;
		if ($temp == "1") echo "<p><font class='text12bold' color='#ff0033'>Please ensure to 'sign in' this asset on the system before or on the due date or it will be marked as overdue.</font>";
		if (($my_access_level < 2) && ($temp != "1")) echo "<p><font class='text12bold'>This request must still be approved by an administrator.</font>";
		echo "</blockquote></td></tr></table>";
	}
}

// central function that decides what to do when requesting the insertion of a new transfer
function asset_admin_transfer($key) {
	global $my_emp_id;
	global $my_access_level;
	global $startdate;
	global $newowner;
	global $enddate;
	global $temp;
	global $licenses;
	global $license_action;	
	global $emp_db;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if ($temp == 1) $transfer_header_text = "Sign Out";
	else $transfer_header_text = "Transfer";
	
	// N is set when regular users answer to NO to whether to acquire an asset
	if ($newowner != "N") {
		$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $key . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
		if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) {
			$curOwnerId = $query_data["Employees_ID"];
			if ($curOwnerId == "0") $curOwnerName = "General Assets";
			elseif ($curOwnerId == "-1")  $curOwnerName = "Surplus";
			elseif ($curOwnerId == "-2")  $curOwnerName = "Retired";
			else $curOwnerName = $query_data["LastName"] . ", " . $query_data["FirstName"];
		} else {
			$curOwnerId = 0;
			$curOwnerName = "Unknown";
		}	
		if (strlen($newowner) < 1) {
			// if no owner is entered, prompt for an owner
			asset_menu_header(true,"",$transfer_header_text,$key);
			asset_tabs($key);
			asset_print_info($key);
			asset_admin_transfer_choose_owner($key,$curOwnerName,$curOwnerId);
		} else {
			if ((strlen($startdate) < 1) || (strlen($enddate) < 1)) {
				// if there is no startdate or enddate, prompt for either
				$section_text = $transfer_header_text;
				if ($temp == "1") {
					if (strlen($startdate) < 1) $transfer_header_text = "Select A Sign Out Date";
					else $transfer_header_text = "Select A Return Due Date";
				} else {
					$transfer_header_text = "Select A Transfer Date";
				}
				asset_menu_header(true,$section_text,$transfer_header_text,$key);
				asset_tabs($key);
				asset_print_info($key);
				asset_admin_transfer_dates($key);
			} else {
				if ((strlen($license_action) < 1) && ($temp == 0)) {
					$is_licenses = asset_admin_transfer_licenses($key,$curOwnerName,$curOwnerId);
				} else {
					$is_licenses = false;
				}
				if ($is_licenses == false) {
					asset_admin_transfer_complete($key,$transfer_header_text);
				}
			}
		}
	} else {
		// User chose not to make changes, print a confirmation message
		asset_menu_header(true,"Transfer","No Changes Have Been Made",$key);
		echo "<font class='text12'><blockquote>You may continue working.</blockquote></font>";
	}	
}
?>
