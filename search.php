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
// search($key)

// performs a search
function search($key) {
	global $print_screen;
	global $hrcolor;
	global $emp_db;
	$PHP_SELF = $_SERVER['PHP_SELF'];
	
	if (strlen($key) > 0) { 
		menu_header("","Search","search.jpg");

		$result_found_flag = false;
		
		// search employees
		$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.LoginName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, " . $emp_db . "Employees.Active, " . $emp_db . "Employees.EMail, " . $emp_db . "Employees.Id AS Employees_ID FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.Tel LIKE '%%%" . $key . "%%%' OR " . $emp_db . "Employees.FirstName LIKE '" . $key . "%%%' OR " . $emp_db . "Employees.LastName LIKE '" . $key . "%%%' ORDER BY " . $emp_db . "Employees.LastName;";
		$result_employees = doSql($sql);
		// search assets
		$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID FROM Assets WHERE Assets.AssetTag LIKE '%%%" . $key . "%%%' OR Assets.AssetType LIKE '%%%" . $key . "%%%' OR Assets.AssetSupplier LIKE '%%%" . $key . "%%%' OR Assets.AssetModel LIKE '%%%" . $key . "%%%' OR Assets.AssetSerial LIKE '%%%" . $key . "%%%' OR Assets.name LIKE '%%%" . $key . "%%%' ORDER BY Assets.AssetType;";
		$result_assets = doSql($sql);
		// search licenses
		$sql = "SELECT product, sum(qty) as sumqty, sum(qty*price) as sumprice FROM Licenses WHERE product LIKE '%%%" . $key . "%%%' OR Manufacturer LIKE '%%%" . $key . "%%%' GROUP BY product ORDER BY product";
		$result_licenses = doSql($sql);
	
		$search_header = "";
		if (mysql_num_rows($result_employees) > 0) $search_header = $search_header . "<a href='#employee' class='text11bold'>Employees</a> &middot; ";
		if (mysql_num_rows($result_assets) > 0) $search_header = $search_header . "<a href='#assets' class='text11bold'>Assets</a> &middot; ";
		if (mysql_num_rows($result_licenses) > 0) $search_header = $search_header . "<a href='#licenses' class='text11bold'>Licenses</a> &middot; ";
	
		$crop_header = strlen($search_header) - 9;
		if ($crop_header < 0) $crop_header = 0;	
	
		$search_header = substr($search_header,0,$crop_header);
		
		if (($print_screen == false) && (strlen($search_header) > 1)) echo "<font class='text11bold'>Find results by:</font> " . $search_header . "<p><br>";
		
		// print out employee results
		if ($result_employees) {
			if (mysql_num_rows($result_employees)) {
				echo "<a name='employees'>";
				echo "<font class='text12'><b>Employee Results</b></font>";
				echo "<hr size=2 color='#ff0033'>";
				$result_found_flag = true;
				$color = "#ffeeee";
				while ($query_data = mysql_fetch_array($result_employees)) {
					employee_format($query_data,$color,"#ffdddd", "employeeborder");
					if ($color == "#ffeeee") $color = "#ffffff";
					else $color = "#ffeeee";
				}
				echo "<hr size=2 color='#ff0033'>";
				echo "<p><br>";
			}
		}
	
		// print out asset results
		if ($result_assets) {
			if (mysql_num_rows($result_assets)) {
				echo "<a name='assets'>";
				echo "<font class='text12'><b>Asset Results</b></font><hr size=2 color='#ffcc00'>";
				$result_found_flag = true;
	
				// print out asset
				while ($query_data = mysql_fetch_array($result_assets)) {
					$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.StartDate, Assignments.EndDate, Assignments.Completed, Assignments.Temp, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $query_data["Assets_ID"] . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
					if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) $query_data2 = mysql_fetch_array($result2);
					asset_format($query_data,$query_data2,"#ffffee","assetborder",false);
				}
				echo "<p><br>";
			}
		}
		
		// print out license results
		if ($result_licenses) {
			if (mysql_num_rows($result_licenses)) {
				echo "<a name='licenses'>";
				echo "<font class='text12'><b>License Results</b></font><hr size=2 color='#cc3399'>";
				$result_found_flag = true;
				echo "<table width=100%>";
				echo "<tr>";
				echo "<td class='text12bold' width=60%>Product</td>";
				echo "<td class='text12bold' align='right' width=10%>Used</td>";
				echo "<td class='text12bold' align='right' width=10%>Left</td>";
				echo "<td class='text12bold' align='right' width=10%>Total</td>";
				echo "<td class='text12bold' align='right' width=10%>Total Cost</td>";
				echo "</tr>";
				echo "</table>";
				$color = "#eeeeff";
				while ($query_data = mysql_fetch_array($result_licenses)) {
					license_format($query_data, $color, "licenseborder");
					if ($color == "#eeeeff") $color = "#ffffff";
					else $color = "#eeeeff";
				}
				echo "<table width=100%>";
				echo "<tr><td>";
				echo "<hr size=2 color='#cc3399'>";
				echo "</td></tr></table>";
				echo "<p><br>";
			}
		}
	
		// no results from assets or employees
		if ($result_found_flag == false) echo "<font class='text12'><blockquote>No Results Found.</blockquote></font>";
	
	} else {
		menu_header("","Search","search.jpg");
	}

	if ($print_screen == false) {
		echo "<center>";
		echo "<table><tr>";
		echo "<td><form action='" . $PHP_SELF . "' method='get'><img src='images/search.png' width=20 height=20></td>";
		echo "<td><input type='hidden' name='action' value='search'><input name='key' type='text' value=\"" . q_replace($key) . "\" class='boxtext13' size=30>";
		echo "</td><td><input type='image' name='submit' src='images/go_48.png' width=20 height=20 border=0><table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
		echo "</td></tr></table>";
		echo "</center>";
		echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
	}
}

?>
