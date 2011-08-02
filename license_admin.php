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
// license_admin_rules($product,$mfg,$qty,$oem,$paymentmethod,$price,$year,$month,$day)
// license_admin()

// verifies that valid entries were entered for licenses
function license_admin_rules($product,$mfg,$qty,$oem,$paymentmethod,$price,$year,$month,$day) {
	// check product
	if (strlen($product) < 2) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";
	// check mfg
	if (strlen($mfg) < 2) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";
	// check quantity
	if (!is_numeric($qty)) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";
	// check date and price when not an OEM purchase
	if ($oem == 1) {
		$errcode = $errcode . "00";
	} else {
		if (!checkdate($month,$day,$year)) $errcode = $errcode . "1";
		else $errcode = $errcode . "0";

		if ((!is_numeric($price)) || (strlen($price) < 1)) $errcode = $errcode . "1";
		else $errcode = $errcode . "0";
	}
	return $errcode;
}

// create a new license
function license_admin() {
	global $complete;
	global $product_form, $mfg, $qty, $oem, $paymentmethod, $price, $year, $month, $day;
	global $expired_year, $expired_month, $expired_day, $licensekey;
	global $print_screen; 
	global $hrcolor;

	// set OEM and complete flags	
	if (strcmp($oem,"on") == 0) $oem = 1;
	else $oem = 0;
	if ($complete == "") $complete = "0";

	// if we are attempting to complete the insert
	if ($complete == "1") {
		// looks up to see if a matching product is found for submitted item
		$sql = "SELECT manufacturer, product FROM Licenses WHERE product='" . $product_form[0] . "' OR product='" . $product_form[1] . "'";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result)) ) {
			$mfg = $query_data["manufacturer"];
			$product = $product_form[0];
			if ($product == "") $product = $product_form[1];
		} else {
			$product = $product_form[1];
		}

		// validate the data
		$errcode = license_admin_rules($product,$mfg,$qty,$oem,$paymentmethod,$price,$year,$month,$day);
		if ($errcode > 0) {
			// print error messages
			$err_msg = "<font class=text18bold color='#ff0033'>ERROR: Incomplete or Invalid Data</font>";
			license_menu_header(true,"<a href='" . $PHP_SELF . "?action=licenseinsert' class='text10bold'>New License</a>", $err_msg, $mfg, $product);
			echo "<p><font class='text12bold' color='#ff0033'>";
			if ($errcode[0] == "1") echo "A product name must be selected or entered. ";
			if ($errcode[1] == "1") echo "A manufacturer name must be entered. ";
			if ($errcode[2] == "1") echo "The quantity must be a number. ";
			if ($errcode[3] == "1") echo "Non-OEM purchases require a valid date. ";
			if ($errcode[4] == "1") echo "Non-OEM purchases require a numeric price. ";
			echo "</font><p>";
			$complete = "0";
		}	
	} else {
		license_menu_header(true,"","New License", "", "");
	}
	
	// either this is the first time or an error occurred during submitting, reprint the form
	if ($complete == "0") {
		echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method=get></td></tr></table>";
		echo "<input type='hidden' name='action' value='licenseinsert'>";
		echo "<input type='hidden' name='complete' value='1'>";
		echo "<table bgcolor='#eeeeff' width=100% cellpadding=15 cellspacing=0 border=0 class='licenseborder'><tr>";
		echo "<td valign='top'>";

		// existing products dropdown
		echo "<center>";
		echo "<font class='text12bold'>Select an existing product:</font><br>";
		echo "<select name='product_form[]' class='boxtext13'>";
		echo "<option value=''>- Existing Products -</option>";
		$sql = "SELECT manufacturer, product FROM Licenses GROUP BY product ORDER BY product";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			while ($query_data = mysql_fetch_array($result)) {
				if (strcmp($product,$query_data["product"]) == 0) {
					echo "<option value='" . $query_data["product"] . "' class='text12' selected>" . $query_data["manufacturer"] . " " . $query_data["product"] . "</option>";
				} else {
					echo "<option value='" . $query_data["product"] . "' class='text12'>" . $query_data["manufacturer"] . " " . $query_data["product"] . "</option>";
				}
			}
		}
		$product = q_replace($product);
		$mfg = q_replace($mfg);
		echo "</select>";
		echo "</center>";
		echo "</td>";
		echo "<td width=30>";
		echo "<center><font class='text18bold'>&nbsp;&nbsp;&nbsp;OR&nbsp;&nbsp;&nbsp;</font></center>";
		echo "</td>";
		echo "<td valign='top'>";

		// new product
		echo "<center>";
		echo "<font class='text12bold'>Enter a NEW manufacturer and product:</font><br>";
		echo "<font class='text12bold'>Manufacturer:<br></font><input type='text' name='mfg' value=\"" . $mfg . "\" class='boxtext13'><br>";
		echo "<font class='text12bold'>Product:<br></font><input type='text' name='product_form[]' value=\"" . $product . "\" class='boxtext13'>";
		echo "</center>";
		echo "</td>";
		echo "</tr>";
		echo "<tr><td bgcolor='#ddddff' colspan=3><center>";

		// quantity
		echo "<table bgcolor='#ddddff' width=550 border=0>";
		echo "<tr>";
		echo "<td align='right'><font class='text12bold'>Quantity:</font></td>";
		echo "<td><input type='text' name='qty' size=3 class='boxtext13' value=" . $qty . "><font color='#ff0033' face='arial' size='4'><b> *</b></font></td>";

		// purchase date dropdowns
		echo "<td align='right'><font class='text12bold'>Purchased:</font></td>";
		echo "<td>";
		$day_now = date("d",time());
		$month_now = date("m",time());
		$year_now = date ("Y",time());
		$months = Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		// year
		echo "<select name='year' size='1' class='boxtext13'>";
		for ($i=($year_now-10);$i<($year_now+90);$i++) {
			if ($year == $i) {
				echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
			} elseif ($year != "") {
				echo "<option value='" . $i . "'>" . $i . "</option>\n";
			} else {	
				if ($year_now == $i) echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
				else echo "<option value='" . $i . "'>" . $i . "</option>\n";
			}
		}
		echo "</select>";

		// month
		echo "<select name='month' size='1' class='boxtext13'>";
		for ($i=1;$i<13;$i++) {
			if ($month == $i) {
				echo "<option value='" . $i . "' selected>" . $months[$i-1] . "</option>\n";
			} elseif ($month != "") {
				echo "<option value='" . $i . "'>" . $months[$i-1] . "</option>\n";
			} else {	
				if ($month_now == $i) echo "<option value='" . $i . "' selected>" . $months[$i-1] . "</option>\n";
				else echo "<option value='" . $i . "'>" . $months[$i-1] . "</option>\n";
			}
		}
		echo "</select>";

		// day
		echo "<select name='day' size='1' class='boxtext13'>";
		for ($i=1;$i<32;$i++) {
			if ($day == $i) {
				echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
			} elseif ($day != "") {
				echo "<option value='" . $i . "'>" . $i . "</option>\n";
			} else {	
				if ($day_now == $i) echo "<option value='" . $i . "' selected>" . $i . "</option>\n";
				else echo "<option value='" . $i . "'>" . $i . "</option>\n";
			}
		}
		echo "</select>";
		echo "</td>";
		echo "</tr>";

		// price and oem checkbox	
		echo "<tr>";
		echo "<td align='right'><font class='text12bold'>Price per Quantity: $</font></td>";
		echo "<td><input type='text' name='price' size=5 class='boxtext13'></td>";
		// purchase date dropdowns
		echo "<td align='right'><font class='text12bold'>Expires:</font></td>";
		echo "<td>";
		$day_now = date("d",time());
		$month_now = date("m",time());
		$year_now = date ("Y",time());
		$months = Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

		// year
		echo "<select name='expired_year' size='1' class='boxtext13'>";
		echo "<option value='' selected></option>\n";
		for ($i=($year_now-10);$i<($year_now+90);$i++) {
			echo "<option value='" . $i . "'>" . $i . "</option>\n";
		}
		echo "</select>";

		// month
		echo "<select name='expired_month' size='1' class='boxtext13'>";
		echo "<option value='' selected></option>\n";
		for ($i=1;$i<13;$i++) {
			echo "<option value='" . $i . "'>" . $months[$i-1] . "</option>\n";
		}
		echo "</select>";

		// day
		echo "<select name='expired_day' size='1' class='boxtext13'>";
		echo "<option value='' selected></option>\n";
		for ($i=1;$i<32;$i++) {
			echo "<option value='" . $i . "'>" . $i . "</option>\n";
		}
		echo "</select>";
		echo "</td>";
		echo "</tr>";

		// payment method
		echo "<tr>";
		echo "<td align='right'><font class='text12bold'>Payment Method:</font></td>";
		echo "<td>";
		echo "<select name='paymentmethod' size=1 class='boxtext12'>";
		if (strcmp($paymentmethod,"MasterCard") == 0) echo "<option value='MasterCard' selected>MasterCard</option>";
		else echo "<option value='MasterCard'>MasterCard</option>";
		if (strcmp($paymentmethod,"MSelect") == 0) echo "<option value='MSelect' selected>MSelect</option>";
		else echo "<option value='MSelect'>MSelect</option>";
		if (strcmp($paymentmethod,"Visa") == 0) echo "<option value='Visa' selected>Visa</option>";
		else echo "<option value='Visa'>Visa</option>";
		if (strcmp($paymentmethod,"Other") == 0) echo "<option value='Other' selected>Other</option>";
		else echo "<option value='Other'>Other</option>";
		echo "</select>";
		echo "</td>";
		echo "<td align='right'><font class='text12bold'>OEM:</font></td>";
		if ($oem == 1) echo "<td><input type='checkbox' name='oem' checked></td>";
		else echo "<td><input type='checkbox' name='oem'></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td align='right' nowrap><font class='text12bold'>License Key: </font></td>";
		echo "<td colspan='3'><input type='text' name='licensekey' size=30 class='boxtext13'></td>";
		echo "</tr>";

		echo "</table>";	
		echo "</center></td></tr></table>";
		echo "<hr size=0 color='" . $hrcolor . "'>";
		echo "<font color='#ff0033' face='arial' size='4'><b> *</b></font> <font class='text10bold'>denotes a required field</font>";
		echo "<p><center>";
		echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
		echo "<input type='image' name='submit' src='images/add.jpg' border=0 width=88 height=27></center>";
		echo "</form>";
	} else {
		// reset the price if necessary
		if (($price == "") || (!is_numeric($price))) $price = "0";
		$licensekey = q_replace(dehtml($licensekey));
		
		// data has been successfully validated, insert license into db
		$sql = "INSERT INTO Licenses (manufacturer,product,paymentmethod,price,qty,oem,purchasedate,licensekey) VALUES ('" . $mfg . "','" . $product . "','" . $paymentmethod . " '," . $price . "," . $qty . "," . $oem . "," . mktime(0,0,0,$month,$day,$year) . ",'" . $licensekey . "')";
		if ($result = doSql($sql)) {
			// data entered successfully
			$sql = "SELECT last_insert_id() as lastid FROM Licenses";
			if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) $newkey = $query_data["lastid"];
			if (checkdate($expired_month,$expired_day,$expired_year)) {
				$expiredate = mktime(0,0,0,$expired_month,$expired_day,$expired_year);
				$sql = "UPDATE Licenses SET expiredate=" . $expiredate . " WHERE id=" . $newkey;
				doSql($sql);
			}
			license_menu_header(true,"<a href='" . $PHP_SELF . "?action=licenseinsert' class='text10bold'>New License</a>", "New License Added Successfully", $mfg, $product);
			echo "<table width=100% bgcolor='#ccccff'><tr><td><br>";
			echo "<blockquote><font class='text12'>The New License has been added.</font></blockquote>";
			echo "<p></td></tr></table>";
			echo "<hr size=0 color='" . $hrcolor . "'>";
			echo "<p><center>";
			echo "<a href='" . $PHP_SELF . "?action=licenseview&key=" . html($product) . "'><img src='images/next.jpg' border=0 width=88 height=27></a>";
			echo "</center>";

		} else {
			// data NOT entered successfully
			$sql_error = mysql_error();
			$err_msg = "<font class=text18bold color='#ff0033'>ERROR: An database error occurred while attempting to insert into the database.</font>";
			license_menu_header(true,"New License", $err_msg, $mfg, $product);
			echo "<p><blockquote><font class='text12'>An error occurred while attempting to update the database. Please contact the webmaster. <p>This is action attempted: " . $sql . "<p>" . $sql_error . "</blockquote></font>";
		}
	}
}

?>
