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
// license_format($query_data, $color, $class) // format one license
// license_summary() // all manufacturers
// license_query($key) // all products for one manufacturer
// license_view($key) // one product
// license_menu_header($domain, $section, $title, $msg, $product)

// shows one license
function license_format($query_data, $color, $class) {
		$sql2 = "SELECT count(assetid) as countid FROM LicenseOwners WHERE licenseid='" . $query_data["product"] . "' GROUP BY licenseid";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2)) && ($query_data2 = mysql_fetch_array($result2))) {
			$used_license = $query_data2["countid"];
		} else {
			$used_license = 0;
		}

		$left_license = (($query_data["sumqty"]) - ($used_license));
		if ($left_license < 0) $left_license = "<font color='#ff0033'><b>" . $left_license . "</b></font>";

		echo "<table width=100% class='" . $class . "' bgcolor='" . $color . "'>";
		echo "<tr>";
		if ($print_screen == false) {
			echo "<td width=60%><a href='" . $PHP_SELF . "?action=licenseview&key=" . html($query_data["product"]) . "' class='text12bold'>" . $query_data["product"] . "</a></td>";
			echo "<td class='text12' align='right' width=10%>" . $used_license . "</td>";
			echo "<td class='text12' align='right' width=10%>" . $left_license . "</td>";
			echo "<td class='text12' align='right' width=10%>" . $query_data["sumqty"] . "</td>";
			echo "<td class='text12' align='right' width=10%>$" . number_format($query_data["sumprice"],2) . "</td>";
		} else {
			echo "<td width=60%><font class='text12bold'>" . $query_data["product"] . "</font></td>";
			echo "<td class='text12' align='right' width=10%>" . $used_license . "</td>";
			echo "<td class='text12' align='right' width=10%>" . $left_license . "</td>";
			echo "<td class='text12' align='right' width=10%>" . $query_data["sumqty"] . "</td>";
			echo "<td class='text12' align='right' width=10%>$" . number_format($query_data["sumprice"],2) . "</td>";
		}
		echo "</tr>";
		echo "</table>";
}

// shows a list of all manufacturers with licenses
function license_summary() {
	global $print_screen; 
	global $hrcolor;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	license_menu_header(false,"","Licenses","","");
	echo "<p>";

	$sql = "SELECT Count(DISTINCT product) AS countid, sum(qty) as sumqty, sum(price) as sumprice, manufacturer FROM Licenses GROUP BY manufacturer";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$num_results = mysql_num_rows($result);
		echo "<table width=100%>";
		echo "<tr>";
		echo "<td class='text12bold' width=55%>Manufacturer</td>";
		echo "<td class='text12bold' width=15% align='right'>Total Products</td>";
		echo "<td class='text12bold' width=15% align='right'>Total Licenses</td>";
		echo "<td class='text12bold' width=15% align='right'>Total Cost</td>";
		echo "</tr>";
		echo "</table>";
		$color = "#eeeeff";
		while ($query_data = mysql_fetch_array($result)) {
			echo "<table width=100% bgcolor='" . $color . "' class='licenseborder' cellpadding=5>";
			echo "<tr>";
			echo "<td class='text12bold' width=55%><a href='" . $PHP_SELF . "?action=licensequery&key=" . html($query_data["manufacturer"]) . "' class='text12bold'>" . $query_data["manufacturer"] . "</a></td>";
			echo "<td class='text12' align='right' width=15%>" . $query_data["countid"] . "</td>";
			echo "<td class='text12' align='right' width=15%>" . $query_data["sumqty"] . "</td>";
			echo "<td class='text12' align='right' width=15%>$" . number_format($query_data["sumprice"],2) . "</td>";
			echo "</tr>";
			echo "</table>";
			if ($color == "#ffffff") $color = "#eeeeff";
			else $color = "#eeeeff";
		}
		echo "<p><font class='text12'>" . $num_results . " manufacturer(s) found.</font>";
	} else {
		echo "<blockquote><font class='text12'>There are no manufacturers entered.</font></blockquote>";
	}
}

// shows a list of all products given a manufacturers with licenses
function license_query($key) {
	global $print_screen; 
	global $hrcolor;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	license_menu_header(true,"", $key, "", "");
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	$sql = "SELECT product, sum(qty) as sumqty, sum(qty*price) as sumprice FROM Licenses WHERE manufacturer='" . $key . "' GROUP BY product ORDER BY product";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) {
		$num_results = mysql_num_rows($result);
		echo "<table width=100% cellpadding=5>";
		echo "<tr>";
		echo "<td class='text12bold' width=60%>Product</td>";
		echo "<td class='text12bold' align='right' width=10%>Used</td>";
		echo "<td class='text12bold' align='right' width=10%>Left</td>";
		echo "<td class='text12bold' align='right' width=10%>Total</td>";
		echo "<td class='text12bold' align='right' width=10%>Total Cost</td>";
		echo "</tr>";
		echo "</table>";
		$color = "#eeeeff";
		while ($query_data = mysql_fetch_array($result)) {
			license_format($query_data, $color, "licenseborder");
			if ($color == "#eeeeff") $color = "#ffffff";
			else $color = "#eeeeff";
		}
		echo "<p><font class='text12'>" . $num_results . " purchase(s) found.</font>";
	} else {
		echo "<blockquote><font class='text12'>There are no purchases entered.</font></blockquote>";
	}
}

// shows the number and date of all licenses for a product
function license_view($key) {
	global $my_access_level;
	global $print_screen;
	global $removelicense;
	global $hrcolor;
	global $emp_db;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	// remove license if the delete link is clicked	
	if (($removelicense != "") && ($my_access_level > 1)) $result = doSql("DELETE FROM Licenses WHERE id=" . $removelicense);
	

	$sql = "SELECT count(assetid) as countid FROM LicenseOwners WHERE licenseid='" . $key . "' GROUP BY licenseid";
	if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
		$used_license = $query_data["countid"];
	} else {
		$used_license = 0;
	}

	// load data for a product
	$sql = "SELECT id, manufacturer, product, purchasedate, paymentmethod, price, qty, oem, (price*qty) as totalcost, expiredate, licensekey FROM Licenses WHERE product='" . $key . "' ORDER BY purchasedate DESC";
	if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
		$mfg = $query_data["manufacturer"];
		$product = $query_data["product"];
		license_menu_header(true,"", $product, $mfg, "");
		$total_cost = 0;
		echo "<table width=100%>";
		echo "<tr>";
		echo "<td class='text12bold' width=15%>Purchase Date</td>";
		echo "<td class='text12bold' width=5%>Type</td>";
		echo "<td class='text12bold' align='right' width=15%>Payment</td>";
		echo "<td class='text12bold' align='right' width=15%>Price</td>";
		echo "<td class='text12bold' align='right' width=10%>Tot Qty</td>";
		echo "<td class='text12bold' align='right' width=10%>Total Cost</td>";
		echo "<td class='text12bold' align='right' width=10%>Expiry Date</td>";
		echo "<td class='text12bold' align='right' width=15%>License Key</td>";
		if ($my_access_level > 1) echo "<td class='text12bold' align='right' width=5%>&nbsp;</td>";
		echo "</tr>";
		$color = "#eeeeff";
		$none_oem_count = 0;
		echo "</td>";
		while ($query_data) {
			echo "<table width=100% bgcolor='" . $color . "' class='licenseborder'>";

			// purchase date
			if ($query_data["purchasedate"] == 0) $purdate = "n/a";
			else $purdate = date("M d, Y", $query_data["purchasedate"]);
			echo "<td class='text12' width=15%>" . $purdate . "</td>";

			// oem type
			if ($query_data["oem"] == 1) $purtype = "OEM";
			else $purtype = "Retail";
			echo "<td class='text12' width=5%>" . $purtype . "</td>";

			// payment method
			if ($query_data["paymentmethod"] == "") $paymentmethod = "n/a";
			else $paymentmethod = $query_data["paymentmethod"];
			echo "<td class='text12' align='right' width=15%>" . $paymentmethod . "</td>";

			// price
			echo "<td class='text12' align='right' width=15%>$" . number_format($query_data["price"],2) . "</td>";

			// quantity
			echo "<td class='text12' align='right' width=10%>" . $query_data["qty"] . "</td>";

			// total cost (price * quantity)
			echo "<td class='text12' align='right' width=10%>$" . number_format($query_data["totalcost"],2) . "</td>";
			$total_cost = $total_cost + $query_data["totalcost"];
			
			// expiry date
			if ($query_data["expiredate"] == 0) $expdate = "n/a";
			else $expdate = date("M d, Y", $query_data["expiredate"]);
			echo "<td class='text12' align='right' width=10%>" . $expdate . "</td>";

			// oem type
			if ($query_data["licensekey"] == "") $licensekey = "(none)";
			else $licensekey = $query_data["licensekey"];
			echo "<td class='text12' align='right' width=15%>" . $licensekey . "</td>";

			// delete button
			if ($my_access_level > 1) echo "<td class='text12' align='right' width=5%><a href='" . $PHP_SELF . "?action=licenseview&key=" . $key . "&removelicense=" . $query_data["id"] . "' class='text12bold'>erase</a></td>";
			echo "</tr>";
			if ($color == "#eeeeff") $color = "#ffffff";
			else $color = "#eeeeff";
			$none_oem_count = $none_oem_count + $query_data["qty"];
			$query_data = mysql_fetch_array($result);
			echo "</table>";
		}

		$left_license = (($none_oem_count) - ($used_license));
		if ($left_license < 0) $left_license = "<font color='#ff0033'>" . $left_license . "</font>";
		
		// print out asset licenses
		$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, Assets.name, Assets.Id AS Assets_ID FROM (LicenseOwners INNER JOIN Assets ON LicenseOwners.AssetId = Assets.Id) WHERE LicenseOwners.LicenseId='" . $key . "';";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			echo "<p>";
			echo "<font class='text12bold'>Current License Assignments</font><br><hr size=2 color='" . $hrcolor . "'>";
			$license_cnt = 1;
			while ($query_data = mysql_fetch_array($result)) {
				$sql2 = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, Assignments.EmployeeID AS Employees_ID FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.AssetId=" . $query_data["Assets_ID"] . " AND Assignments.Temp=0 AND Assignments.Approve=0 AND (Assignments.EndDate >= " . time() . " OR Assignments.EndDate = 0) ORDER BY Assignments.StartDate;";
				if ($result2 = doSql($sql2)) $query_data2 = mysql_fetch_array($result2);
				$query_data["AssetTag"] = $license_cnt . ". " . $query_data["AssetTag"];
				asset_format($query_data,$query_data2,"#eeeeff","licenseborder",false);
				$license_cnt++;
			}
		}
		

		echo "<p><table>";
		echo "<tr><td align='right' class='text12bold'>Total Used:</td><td rowspan=4 width=5>&nbsp;</td><td class='text12bold'>" . $used_license . "</td></tr>";
		echo "<tr><td align='right' class='text12bold'>Total Left:</td><td class='text12bold'>" . $left_license . "</td></tr>";
		echo "<tr><td align='right' class='text12bold'>Total Licenses:</td><td class='text12bold'>" . $none_oem_count . "</td></tr>";
		echo "<tr><td align='right' class='text12bold'>Total Cost:</td><td class='text12bold'>$" . number_format($total_cost,2) . "</td></tr>";
		echo "</table>";
	} else {
		license_menu_header(true,"Delete License", "No Licenses For This Software", "", "");
		echo "<p><blockquote><font class='text12'>All licenses for this software have been removed.";
	}
}

// show the top bar header
function license_menu_header($domain, $section, $title, $mfg, $product) {
	global $print_screen;
	global $hrcolor;
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if ($print_screen == false) {
		if ($domain == true) $top = "<a href='" . $PHP_SELF . "?action=licenses' class='text10bold'>Licenses</a>";
		else $top = "";
		if (strlen($section) > 0) $top = $top . ": <font class='text10bold'>" . $section . "</font>";
		if (strlen($mfg) > 0) $top = $top . ": <a href='" . $PHP_SELF . "?action=licensequery&key=" . html($mfg) . "' class='text10bold'>" . $mfg . "</a>";
		if (strlen($product) > 0) $top = $top . ": <a href='" . $PHP_SELF . "?action=licenseview&key=" . html($product) . "' class='text10bold'>" . $product . "</a>";
	}
	menu_header($top,$title,"licenses.jpg");
}
?>
