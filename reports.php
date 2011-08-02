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
// reports_summary()
// reports_ip()
// reports_verify()
// reports_sign_out()
// reports_assets_details($key, $full_name)
// reports_assets()
// reports_licenses_detailed_details($key, $manufacturer)
// reports_licenses()
// reports_licenses_detailed()
// reports_employees()

// shows a menu of all available reports
function reports_summary() {
	global $emp_db;
	global $print_screen;
	global $hrcolor;
	global $my_access_level;
	menu_header("","Reports","reports.jpg");
	echo "<blockquote><font class='text12'>";
	echo "<p><a href='" . $PHP_SELF . "?action=reportsassets' class='text12bold'>Download Asset Report (in Spreadsheet Format)</a>";
	echo "<br>This report lists all assets and their current assignments in one spreadsheet.";
	echo "<p><a href='" . $PHP_SELF . "?action=reportslicensessummary' class='text12bold'>Download Summary License Report (in Spreadsheet Format)</a>";
	echo "<br>This report lists a summary of licensing information in one spreadsheet.";
	echo "<p><a href='" . $PHP_SELF . "?action=reportslicensesdetailed' class='text12bold'>Download Detailed License Report (in Spreadsheet Format)</a>";
	echo "<br>This report lists all detailed licensing information in one spreadsheet.";
	if ($my_access_level > 1) {
		echo "<p><a href='" . $PHP_SELF . "?action=reportsemployees' class='text12bold'>Download Employees Report (in Spreadsheet Format)</a>";
		echo "<br>This report lists all the employee information in one spreadsheet.";
	}
	echo "<p><a href='" . $PHP_SELF . "?action=reportssignout' class='text12bold'>Transfer Report</a>";
	echo "<br>This report lists all transfers based on the date you select.";
	echo "<p><a href='" . $PHP_SELF . "?action=reportssignout&temp=1' class='text12bold'>Sign Out Report</a>";
	echo "<br>This report lists all sign outs based on the date you select.";
	echo "<p><a href='" . $PHP_SELF . "?action=reportsverify' class='text12bold'>Verification Report</a>";
	echo "<br>This report lists when each employee last verified their own information.";
	if ($my_access_level > 1) {
		echo "<p><a href='" . $PHP_SELF . "?action=reportsip' class='text12bold'>IP Report</a>";
		echo "<br>This report lists all the IPs of the directorate and which employee they belong.";
	}
	//echo "<p><a href='expdesktop.php'class='text12bold'> Desktops that Require Replacement </a>";
	//echo "<br>This report shows all non retired desktops that have passed their 5 year life";
	//echo "<p><a href='explaptop.php'class='text12bold'> Laptops that Require Replacement </a>";
        //echo "<br>This report shows all non retired desktops that have passed their 3 year life";
	echo "</font></blockquote>";
}

// shows a list of all IPs and which employee they are assigned to
function reports_ip() {
	global $print_screen;
	global $hrcolor;
	global $emp_db;
	menu_header("<a href='" . $PHP_SELF . "?action=reports' class='text10bold'>Reports</a>","IP Report","reports.jpg");

	$color = "#eeffee";
	$sql = "SELECT IP.ip, IP.employeeid, " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName FROM IP LEFT JOIN " . $emp_db . "Employees ON IP.employeeid = " . $emp_db . "Employees.Id ORDER BY " . $emp_db . "Employees.LastName";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		echo "<table width=100%><tr bgcolor='#ffffff'><td class='text12bold' width=20%>IP</td><td class='text12bold' width=80%>Employee</td></tr></table>";
		while ($query_data = mysql_fetch_array($result)) {
			echo "<table width=100% bgcolor='" . $color . "' class='reportsborder'>";
			echo "<tr>";
			echo "<td class='text12bold' width=20%>" . $query_data["ip"] . "</td>";
			echo "<td width=80%><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["employeeid"] . "' class='text12bold'>" . $query_data["LastName"] . ", " . $query_data["FirstName"] . "</a></td>";
			echo "</tr>";
			echo "</table>";
			if ($color == "#eeffee") $color = "#ffffff";
			else $color = "#eeffee";
		}
	} else {
		echo "<blockquote><font class='text12'>No IPs were found.</font></blockquote>";
	}
	echo "</table>";

}

// shows a report of when all employees last verified their information
function reports_verify() {
	global $print_screen;
	global $hrcolor;
	global $emailrequest;
	global $length;
	global $HTTP_HOST;
	global $SCRIPT_NAME;
	global $my_access_level;
	global $emp_db;
	
	// top header bar
	menu_header("<a href='" . $PHP_SELF . "?action=reports' class='text10bold'>Reports</a>","Verification Report","reports.jpg");
	
	// do the actual e-mailing
	if (($emailrequest == "1") && ($my_access_level > 1)) {
		echo "<font class='text12bold'>E-Mail Verification Requests have been sent out.</font><p>";
		$sql = "SELECT " . $emp_db . "Employees.Id, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.EMail, " . $emp_db . "Employees.Verified FROM " . $emp_db . "Employees WHERE ((" . time() . " - " . $emp_db . "Employees.Verified) > " . ($length * 86400) . ")";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			while ($query_data = mysql_fetch_array($result)) {
				if ($query_data["EMail"] != "") {
					if ($query_data["Verified"] == "0") $date_text = "Your assets have never been verified.";
					else $date_text = "Your assets were last verified on " . date("F d, Y", $query_data["Verified"]) . " at " . date("h:i a", $query_data["Verified"]) . ".";
					if (strlen($query_data["EMail"]) > 0) {
						mail($query_data["EMail"], "SimpleAssets Verfication Required", $query_data["FirstName"] . ",\n\n" . $date_text . " The administrator has requested that you verify your assets as soon as possible by visiting this url:\nhttp://" . $HTTP_HOST . $SCRIPT_NAME . "?action=employeeview&key=" . $query_data["Id"] . " \n\nThank you for your co-operation,\nThe SimpleAssets Administrator","From: SimpleAssets Administrator\nX-Priority: 1\n");
					}
				}
			}
		}
	}

	// show all the statuses
	$color = "#eeffee";
	$sql = "SELECT " . $emp_db . "Employees.Id, " . $emp_db . "Employees.Verified, " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName FROM " . $emp_db . "Employees ORDER BY " . $emp_db . "Employees.LastName";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		echo "<table width=100%><tr bgcolor='#ffffff'><td class='text12bold' width=50%>Employee</td><td class='text12bold' width=50%>Last Verified</td></tr></table>";
		while ($query_data = mysql_fetch_array($result)) {
			if ($query_data["Verified"] == "0") $date_text = "Not Verified";
			else $date_text = date("F d, Y", $query_data["Verified"]) . " at " . date("h:i a", $query_data["Verified"]);
			echo "<table width=100% bgcolor='" . $color . "' class='reportsborder'>";
			echo "<tr>";
			echo "<td width=50%><a href='" . $PHP_SELF . "?action=employeeview&key=" . $query_data["Id"] . "' class='text12bold'>" . $query_data["LastName"] . ", " . $query_data["FirstName"] . "</a></td>";
			echo "<td class='text12' width=50%>" . $date_text . "</td>";
			echo "</tr>";
			echo "</table>";
			if ($color == "#eeffee") $color = "#ffffff";
			else $color = "#eeffee";
		}
		// show the e-mail form
		if ($my_access_level > 1) {
			echo "<form action='" . $PHP_SELF . "' method='get'>";
			echo "<input type='hidden' name='action' value='reportsverify'>";
			echo "<input type='hidden' name='emailrequest' value='1'>";
			echo "<font class='text12'><b>E-Mail Verification Request</b> to employees who last verified: ";
			echo "<select name='length'>";
			echo "<option value='0'>anytime</option>";
			echo "<option value='5'>more than 5 days ago</option>";
			echo "<option value='15'>more than 15 days ago</option>";
			echo "<option value='30' selected>more than 30 days ago</option>";
			echo "<option value='60'>more than 60 days ago</option>";
			echo "<option value='90'>more than 90 days ago</option>";
			echo "</select>";
			echo "&nbsp;<input type='image' name='submit' src='images/go_white.jpg' width=20 height=20 border=0>";
			echo "<br><i>Note: E-mailing may take several minutes to process, please be patient.</i></font>";
			echo "</form>";	
		}
	} else {
		echo "<blockquote><font class='text12'>No employees were found.</font></blockquote>";
	}
}

// shows a report of all past, present and future sign outs
function reports_sign_out() {
	global $print_screen;
	global $hrcolor;
	global $my_access_level;
	global $month_start;
	global $day_start;
	global $year_start;
	global $month_end;
	global $day_end;
	global $year_end;
	global $action;	
	global $temp;
	global $emp_db;
	
	if ($temp == "1") {
		$header_text = "Sign Out";
		$is_report = false;
	} else {
		$temp = "0";
		$header_text = "Transfer";
		$is_report = true;
	}

	// prints header
	menu_header("<a href='" . $PHP_SELF . "?action=reports' class='text10bold'>Reports</a>",$header_text . " Report","reports.jpg");

	// set start and end dates
	$start_date = mktime(0,0,0,$month_start,$day_start,$year_start);
	$end_date = mktime(23,59,59,$month_end,$day_end,$year_end);
	
	// verify given dates
	if (($month_start != "") && ($day_start != "") && ($year_start != "") && ($month_end != "") && ($day_end != "") && ($year_end != "")) {
		if ((($end_date - $start_date) < 0) || (!checkdate($month_start,$day_start,$year_start)) || (!checkdate($month_end,$day_end,$year_end))) {
			echo "<center><font class='text13bold' color='#ff0033'>Invalid Date Selection.</font></center>";
			$invalid_date = true;
		} else {
			$invalid_date = false;
		}
	} else {
		$invalid_date = false;
	}

	// date form
	echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
	echo "<input type='hidden' name='action' value='reportssignout'>";
	echo "<input type='hidden' name='temp' value='" . $temp . "'>";
	echo "<table bgcolor='#eeffee' width=100% cellpadding=10 class='reportsborder'><tr><td><center><font class='text13'>";
	echo "<b>From </b>";
	$new_start_date = dateDropdown("start",$month_start,$day_start,$year_start,-1,0,true,false);
	if (($start_date == "-1") || ($start_date == "")) $start_date = $new_start_date;
	echo "<b> To </b>";
	$new_end_date = dateDropdown("end",$month_end,$day_end,$year_end,0,0,false,false);
	if (($end_date == "-1") || ($start_date == "")) $end_date = $new_end_date;
	echo "<input type='image' name='submit' src='images/go_white.jpg' width=20 height=20 border=0>";
	echo "</font></center></td></tr></table>";

	// Get data for either Transfers (Permanent) or Sign Outs (Temporary)
	if ($temp == 1) $sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.Id AS Assignments_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, Assignments.Temp FROM ((Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId) LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeId = " . $emp_db . "Employees.Id) WHERE Assignments.Approve=0 AND Assignments.Temp=1 AND ((Assignments.StartDate <= " . $end_date . ") AND (Assignments.EndDate >= " . $start_date . " OR Assignments.EndDate=0)) ORDER BY Assignments.StartDate;";
	else $sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID, Assignments.Id AS Assignments_ID, Assignments.EmployeeId AS Employees_ID, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, Assignments.Temp, Assignments_Prev.EmployeeId AS Employees_Prev_ID, Employees_Prev.LastName AS Employees_Prev_LastName, Employees_Prev.FirstName AS Employees_Prev_FirstName, Employees_Prev.EMail AS Employees_Prev_EMail, Employees_Prev.Building AS Employees_Prev_Building, Employees_Prev.Floor AS Employees_Prev_Floor, Employees_Prev.Workstation AS Employees_Prev_Workstation FROM ((((Assignments LEFT JOIN Assets ON Assets.Id = Assignments.AssetId) LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeId = " . $emp_db . "Employees.Id) LEFT JOIN Assignments AS Assignments_Prev ON Assignments_Prev.EndDate = (Assignments.StartDate - 1)) LEFT JOIN " . $emp_db . "Employees AS Employees_Prev ON Assignments_Prev.EmployeeId = Employees_Prev.Id) WHERE Assignments.Approve=0 AND Assignments.Temp=0 AND (((Assignments.StartDate <= " . $end_date . ") AND (Assignments.StartDate >= " . $start_date . "))) ORDER BY Assignments.StartDate;";

	// print out results
	echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
	echo "<br><font class='text12'>";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$color = "#eeffee";
		while ($query_data = mysql_fetch_array($result)) {
			$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.Id=" . $query_data["Assignments_ID"] . " ORDER BY Assignments.StartDate;";
			if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) $query_data2 = mysql_fetch_array($result2);
			asset_format($query_data,$query_data2,$color,"reportsborder",$is_report);
			if ($color == "#ffffff") $color = "#eeffee";
			else $color = "#eeffee";
		}
	} else {
		if ($temp == 1) echo "<blockquote>There are no sign outs active during the specific dates.</blockquote>";
		else echo "<blockquote>There are no transfers taking place during the specified dates.</blockquote>";
	}
	echo "</font>";
}

// prints out the assets for one employee (linked with reports_assets())
function reports_assets_details($key, $full_name) {
	global $emp_db;
	$sql2 = "SELECT Assets.name, Assets.Id AS Assets_ID, Assets.AssetTag, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetType, Assets.AssetSerial, Assets.AssetPrice, Assets.exp_date, Assignments.StartDate FROM Assets INNER JOIN Assignments ON Assignments.AssetId = Assets.Id WHERE Assignments.EmployeeId =" . $key . " AND Temp=0 AND Approve=0 AND StartDate < " . time() . " AND (EndDate > " . time() . " OR EndDate=0) ORDER BY Assets.AssetType";
	if ($result2 = doSQL($sql2)) {
		if (mysql_num_rows($result2) > 0) echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		$found_flag = 0;
		while ($query_data2 = mysql_fetch_array($result2)) {
			if ($query_data2["StartDate"] > "0") $date_text = date("d-M-Y", $query_data2["StartDate"]);
			else $date_text = "&nbsp;";
			if ($query_data2["AssetPrice"] > "0") $price_text = "$" . $query_data2["AssetPrice"] . ".00";
			else $price_text = "&nbsp;";

			$sql3 = "SELECT ip FROM IP WHERE assetid=" . $query_data2["Assets_ID"];
			if (($result3 = doSQL($sql3)) && (mysql_num_rows($result3) > 0) && ($query_data3 = mysql_fetch_array($result3)) ) {
				$ip_text = $query_data3["ip"];
			} else {
				$ip_text = "&nbsp;";
			}
			
			if ($found_flag == 1) echo "<tr><td>&nbsp;</td>";
			else echo "<tr><td><font face='arial' size=2><b>" . $full_name . "</b></font></td>";
		      	echo "<td><font face='arial' size=2>" . $query_data2["os"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["AssetType"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["AssetTag"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["AssetSerial"] . "&nbsp;</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["AssetSupplier"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["AssetModel"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $price_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $date_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $ip_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["Notes"] . "</font></td>";
			echo "</tr>";

			$found_flag = 1;
		}
	}
}


// generate an excel report for all employees and their assets
function reports_assets() {
	global $download;
	global $emp_db;
	
	if ($download == 1) {
		header("Content-type: application/force-download: name=assets.csv");
		header("Content-Disposition: filename=assets.csv");
	} else {
		echo "<p><font class='text12bold' face='arial'><b><a href='" . $PHP_SELF . "?action=reportsassets&download=1'>Autoopen this report in Excel (HTML Format with a .CSV extension)</a> or save this file and import it to any speadsheet program.</b></font><p>";
	}
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td><font face='arial' size=2><b>Assignee</b></font></td>";
	echo "<td><font face='arial' size=2><b>Machine Name</b></font></td>";
	echo "<td><font face='arial' size=2><b>Description</b></font></td>";
	echo "<td><font face='arial' size=2><b>Asset Tag</b></font></td>";
	echo "<td><font face='arial' size=2><b>Serial</b></font></td>";
	echo "<td><font face='arial' size=2><b>Supplier</b></font></td>";
	echo "<td><font face='arial' size=2><b>Model</b></font></td>";
	echo "<td><font face='arial' size=2><b>Purchase Price</b></font></td>";
	echo "<td><font face='arial' size=2><b>Install Date</b></font></td>";
	echo "<td><font face='arial' size=2><b>IP</b></font></td>";
	echo "<td><font face='arial' size=2><b>Notes</b></font></td>";
	echo "</tr>";

	// general assets
	reports_assets_details("0","General Assets");

	// surplus
	reports_assets_details("-1","Surplus");

	// all employees
	$sql = "SELECT id, LastName, FirstName FROM " . $emp_db . "Employees ORDER BY LastName";
	if ($result = doSQL($sql)) {
		while ($query_data = mysql_fetch_array($result)) {
			reports_assets_details($query_data["id"], $query_data["LastName"] . ", " . $query_data["FirstName"]);
		}
	}
	echo "</table>";

}


// prints out the licenses for one product (linked with reports_licenses_detailed()
function reports_licenses_detailed_details($key, $manufacturer) {
	$sql2 = "SELECT qty, product, paymentmethod, price, purchasedate, oem, expiredate, licensekey FROM Licenses WHERE product='" . addslashes($key) . "'";
	if ($result2 = doSQL($sql2)) {
		$found_flag = 0;
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		while ($query_data2 = mysql_fetch_array($result2)) {
			if ($query_data2["purchasedate"] > "0") $date_text = date("d-M-Y", $query_data2["purchasedate"]);
			else $date_text = "";
			if ($query_data2["price"] > "0") $price_text = "$" . $query_data2["price"] . ".00";
			else $price_text = "";
			if ($query_data2["oem"] == "1") $oem_text = "Yes";
			else $oem_text = "No";
			if ($query_data2["expiredate"] > "0") $expdate_text = date("d-M-Y", $query_data2["expiredate"]);
			else $expdate_text = "";

			if ($found_flag == 1) echo "<tr><td>&nbsp;</td>";
			else echo "<tr><td><font face='arial' size=2><b>" . $manufacturer . " " . $key . "</b></font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["qty"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $date_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["purchasemethod"] . "&nbsp;</font></td>";
			echo "<td><font face='arial' size=2>" . $price_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $oem_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $expdate_text . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data2["licensekey"] . "&nbsp;</font></td>";
			echo "</tr>";

			$found_flag = 1;
		}
	}
}

// generate a summary excel report for all licenses
function reports_licenses_detailed() {
	global $download;
	
	if ($download == 1) {
		header("Content-type: application/force-download: name=licenses.csv");
		header("Content-Disposition: filename=licenses.csv");
	} else {
		echo "<p><font class='text12bold' face='arial'><b><a href='" . $PHP_SELF . "?action=reportslicensesdetailed&download=1'>Autoopen this report in Excel (HTML Format with a .CSV extension)</a> or save this file and import it to any speadsheet program.</b></font><p>";
	}
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "<td><font face='arial' size=2><b># of Copies</b></font></td>";
	echo "<td><font face='arial' size=2><b>Purchase Date</b></font></td>";
	echo "<td><font face='arial' size=2><b>Purchase Method</b></font></td>";
	echo "<td><font face='arial' size=2><b>Purchase Price</b></font></td>";
	echo "<td><font face='arial' size=2><b>OEM?</b></font></td>";
	echo "<td><font face='arial' size=2><b>Expiry Date</b></font></td>";
	echo "<td><font face='arial' size=2><b>License Key</b></font></td>";
	echo "</tr>";

	$sql = "SELECT manufacturer, product FROM Licenses GROUP BY product ORDER BY manufacturer, product";
	if ($result = doSQL($sql)) {
		while ($query_data = mysql_fetch_array($result)) {
			reports_licenses_detailed_details($query_data["product"], $query_data["manufacturer"]);
		}
	}
	echo "</table>";
}

// generate an detailed excel report for all licenses
function reports_licenses_summary() {
	global $download;
	
	if ($download == 1) {
		header("Content-type: application/force-download: name=licenses.csv");
		header("Content-Disposition: filename=licenses.csv");
	} else {
		echo "<p><font class='text12bold' face='arial'><b><a href='" . $PHP_SELF . "?action=reportslicensessummary&download=1'>Autoopen this report in Excel (HTML Format with a .CSV extension)</a> or save this file and import it to any speadsheet program.</b></font><p>";
	}
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "<td><font face='arial' size=2><b>Qty Used</b></font></td>";
	echo "<td><font face='arial' size=2><b>Qty Left</b></font></td>";
	echo "<td><font face='arial' size=2><b>Qty Total</b></font></td>";
	echo "</tr>";

	$sql = "SELECT manufacturer, product, SUM(qty) as sum_qty FROM Licenses GROUP BY product ORDER BY manufacturer, product";
	if ($result = doSQL($sql)) {
		while ($query_data = mysql_fetch_array($result)) {
			$sql2 = "SELECT count(assetid) as countid FROM LicenseOwners WHERE licenseid='" . $query_data["product"] . "' GROUP BY licenseid";
			if (($result2 = doSql($sql2)) && (mysql_num_rows($result2)) && ($query_data2 = mysql_fetch_array($result2))) {
				$used_license = $query_data2["countid"];
			} else {
				$used_license = 0;
			}
			$tot_license = $query_data["sum_qty"];
			$left_license = (($tot_license) - ($used_license));
		
			echo "<tr><td><font face='arial' size=2><b>" . $query_data["manufacturer"] . " " . $query_data["product"] . "</b></font></td>";
			echo "<td><font face='arial' size=2>" . $used_license . "</font></td>";
			echo "<td><font face='arial' size=2>" . $left_license . "</font></td>";
			echo "<td><font face='arial' size=2>" . $tot_license . "</font></td>";
			echo "</tr>";
		}
	}
	echo "</table>";
}


// generate an excel report for all employees and their assets
function reports_employees() {
	global $download;
	global $emp_db;
	
	if ($download == 1) {
		header("Content-type: application/force-download: name=employees.csv");
		header("Content-Disposition: filename=employees.csv");
	} else {
		echo "<p><font class='text12bold' face='arial'><b><a href='" . $PHP_SELF . "?action=reportsemployees&download=1'>Autoopen this report in Excel (HTML Format with a .CSV extension)</a> or save this file and import it to any speadsheet program.</b></font><p>";
	}
	
	echo "<table border=1>";
	echo "<tr>";
	echo "<td><font face='arial' size=2><b>Last Name</b></font></td>";
	echo "<td><font face='arial' size=2><b>First Name</b></font></td>";
	echo "<td><font face='arial' size=2><b>Login Name</b></font></td>";
	echo "<td><font face='arial' size=2><b>EMail</b></font></td>";
	echo "<td><font face='arial' size=2><b>Telephone</b></font></td>";
	echo "<td><font face='arial' size=2><b>Organization</b></font></td>";
	echo "<td><font face='arial' size=2><b>Department</b></font></td>";
	echo "<td><font face='arial' size=2><b>Building</b></font></td>";
	echo "<td><font face='arial' size=2><b>Floor</b></font></td>";
	echo "<td><font face='arial' size=2><b>Workstation</b></font></td>";
	echo "<td><font face='arial' size=2><b>Access Level</b></font></td>";
	echo "</tr>";

	// all employees
	$sql = "SELECT LastName, FirstName, LoginName, EMail, Tel, Organization, Dept, Building, Floor, Workstation, AccessLevel FROM " . $emp_db . "Employees ORDER BY LastName";
	if ($result = doSQL($sql)) {
		while ($query_data = mysql_fetch_array($result)) {
			if ($query_data["AccessLevel"] == "2") $accesslevel_text = "Admin";
			elseif ($query_data["AccessLevel"] == "2") $accesslevel_text = "User";
			else $accesslevel_text = "Non-User";
			echo "<tr>";
			echo "<td><font face='arial' size=2>" . $query_data["LastName"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["FirstName"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["LoginName"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["EMail"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["Tel"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["Organization"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["Dept"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["Building"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["Floor"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["Workstation"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $accesslevel_text . "</font></td>";
			echo "</tr>";
		}
	}
	echo "</table>";

}


// generate an excel report for all employees and their assets
function reports_individual($key) {
	global $download;
	global $emp_db;
	
	if ($download == 1) {
		header("Content-type: application/force-download: name=employees.csv");
		header("Content-Disposition: filename=employees.csv");
	} else {
		echo "<p><font class='text12bold' face='arial'><b><a href='" . $PHP_SELF . "?action=reportsindividual&key=" . $key . "&download=1'>Autoopen this report in Excel (HTML Format with a .CSV extension)</a> or save this file and import it to any speadsheet program.</b></font><p>";
	}
	

	switch ($key) {
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
			$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.Id=" . $key;
			if (($result = doSQL($sql)) && ($query_data = mysql_fetch_array($result))) {
				$full_name = $query_data["LastName"] . ", " . $query_data["FirstName"];
			}
	}
	echo "<table border=1>";
	echo "<tr>";
	echo "<td colspan=6><center><font face='arial' size=2><b>Asset Details for " . $full_name . "</b></font></center></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><font face='arial' size=2><b>Date</b></font></td>";
	echo "<td><font face='arial' size=2><b>Asset Tag</b></font></td>";
	echo "<td><font face='arial' size=2><b>Type</b></font></td>";
	echo "<td><font face='arial' size=2><b>Supplier</b></font></td>";
	echo "<td><font face='arial' size=2><b>Model</b></font></td>";
	echo "<td><font face='arial' size=2><b>Serial</b></font></td>";
	echo "</tr>";

	// all employees
	$sql = "SELECT Assets.name, Assets.AssetTag, Assets.AssetModel, Assets.AssetSerial, Assets.AssetType, Assets.AssetSupplier, Assignments.StartDate FROM Assignments LEFT JOIN Assets ON Assignments.AssetId = Assets.Id WHERE Assignments.EmployeeId=" . $key . " ORDER BY Assignments.StartDate DESC, Assets.AssetType";
	if ($result = doSQL($sql)) {
		while ($query_data = mysql_fetch_array($result)) {
			if ($query_data["StartDate"] == "0") $startdate_out = "Unknown Date";
			else $startdate_out = date("M d, Y", $query_data["StartDate"]);
			echo "<tr>";
			echo "<td><font face='arial' size=2>" . $startdate_out . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["os"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["AssetTag"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["AssetType"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["AssetSupplier"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["AssetModel"] . "</font></td>";
			echo "<td><font face='arial' size=2>" . $query_data["AssetSerial"] . "</font></td>";
			echo "</tr>";
		}
	}
	echo "</table>";

}


?>
