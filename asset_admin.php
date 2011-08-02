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
// asset_admin_rules($insert,$assettag,$assettype,$name,$assetsupplier,$assetmodel,$assetserial,$assetprice)
// asset_admin($key, $insert, $complete)
// asset_admin_calendar($key)
// asset_admin_dates($key)
// asset_admin_erase($key)

function asset_admin_rules($assetid, $insert,$assettag,$assettype,$name,$assetsupplier,$assetmodel,$assetserial,$assetprice,$employee,$day_new,$month_new,$year_new) {
	global $my_access_level;
	// check duplicate assettag
	if ($insert == true) $sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, name, Assets.Id AS Assets_ID FROM Assets WHERE Assets.AssetTag='" . q_replace(dehtml($assettag)) . "';";
	else $sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, name, Assets.Id AS Assets_ID FROM Assets WHERE Assets.AssetTag='" . q_replace(dehtml($assettag)) . "' AND Assets.Id <> " . $assetid . ";";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) $errcode = "1";
	else $errcode = "0";
	
	// check asset tag (must have length and be numeric)
	if ((strlen($assettag) < 1)) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";

	// check asset type (must have length)
	if (strlen($assettype) < 1) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";

	// check asset supplier (must have length)
	if (strlen($assetsupplier) < 1) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";

	// check asset price (must have length, at most one decimal and be a float or integer)
	if (substr_count($assetprice, ".") > 1) $errcode = $errcode . "1";
	elseif ((strlen($assetprice) > 0) && (is_float($assetprice)) || (is_int($assetprice))) $errcode = $errcode . "1";	
	else $errcode = $errcode . "0";
	
	// employee
	if (($insert == true) && (strlen($employee) < 1) && ($my_access_level > 1)) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";
	
	// date
	$day_now = date("d",time());
	$month_now = date("m",time());
	$year_now = date ("Y",time());
	$date_now = mktime(0,0,0,$month_now,$day_now,$year_now);
	$date_new = mktime(0,0,0,$month_new,$day_new,$year_new);
	
	if ((checkdate($month_new, $day_new, $year_new) == false) || ($date_now < $date_new)) {
		$errcode = $errcode . "1";
	} else {
		$errcode = $errcode . "0";
	}

	return $errcode;
}

// insert and/or update assets
function asset_admin($key, $insert) {
	global $print_screen;
	global $my_access_level;
	global $complete;
	global $hrcolor;
	global $my_emp_id;
	global $emp_db;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	// Set Page Title Based On Insert/Update/Register	
	if ($insert == true) $insertupdatetext = "New Employee";
	else $insertupdatetext = "Update";


	// load the current asset data (either from db, a form submit or just blank info)
	global $assettag, $assettype, $name, $assetsupplier, $assetmodel, $assetserial, $assetprice, $employee, $assetsupplierlink, $exp_date;
	global $day_new, $month_new, $year_new;
	if (($insert == false) && ($complete != "1")) {
		$sql = "SELECT Assets.AssetTag, Assets.AssetType, Assets.AssetSupplier, Assets.AssetModel, Assets.AssetSerial, Assets.AssetPrice, name, Assets.exp_date FROM Assets WHERE Assets.Id=" . $key . ";";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
			$assettag = substr($query_data["AssetTag"],1,6);
			$assettype = $query_data["AssetType"];
			$name = $query_data["name"];
			$assetsupplier = $query_data["AssetSupplier"];
			$assetmodel = $query_data["AssetModel"];
			$assetserial = $query_data["AssetSerial"];
			$assetprice = $query_data["AssetPrice"];
			$exp_date = $query_data["exp_date"];
		}
	}

	if ($complete != "1") {
		$sql2 = "SELECT supplier, link FROM Links WHERE supplier='" . $assetsupplier . "';";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2)) && ($query_data2 = mysql_fetch_array($result2))) {
			$assetsupplierlink = $query_data2["link"];
		}
	}
	
	if (is_numeric($assetprice) == false) $assetprice = "0";

	if (($insert == true) && ($complete == "1")) {
		$date_new = mktime(0,0,0,$month_new,$day_new,$year_new);
	} else {
		$day_new = date("d",time());
		$month_new = date("m",time());
		$year_new = date("Y",time());
		$date_new = mktime(0,0,0,$month_new,$day_new,$year_new);
	}
	
	// verify data so that all rules in asset_admin_rules are followed
	while (strlen($assettag) <= 0) {
		$assettag = rand(100000,999999);
		$sql = "SELECT * FROM Assets WHERE Assets.AssetTag='" . $assettag . "';";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) $assettag = "";
	}
	$errcode = asset_admin_rules($key,$insert,$assettag,$assettype,$name,$assetsupplier,$assetmodel,$assetserial,$assetprice,$employee,$day_new,$month_new,$year_new);
	if (($complete == "1") && ($errcode > 0)) {
		// print error messages
		$complete = "0";
		asset_menu_header(true,$insertupdatetext,"<font class=text18bold color='#ff0033'>ERROR: Incomplete or Invalid Data.</font>",$key);
		asset_tabs($key);
		asset_print_info($key);
		echo "<p><font class='text12bold' color='#ff0033'>";
		if ($errcode[0] == "1") echo "The asset tag you have entered is the same as another asset tag previously entered. ";
		if ($errcode[1] == "1") echo "The asset tag is missing. ";
		if ($errcode[2] == "1") echo "An asset type is missing. ";
		if ($errcode[3] == "1") echo "The asset supplier name is missing. ";
		if ($errcode[4] == "1") echo "The asset price must either be blank or consist of digits and one decimal point. ";
		if ($errcode[5] == "1") echo "A valid employee, general assets, surplus or retired must be selected. ";
		if ($errcode[6] == "1") echo "The initial date must be a valid date and before or on today. ";
		echo "</font><p>";
	} else {
		if ($complete != "1") {
			asset_menu_header(true,"",$insertupdatetext,$key);
			asset_tabs($key);
		}
	}
	
	// when asset_admin_rules is broken above, $complete is reset (above)
	// if rules were broken or this is the first time running the screen, print out the form
	if ($complete != "1") {	
		// html encode data for forms
		$assettag = q_replace($assettag);
		$assettype = q_replace($assettype);
		$name = q_replace($name);
		$assetsupplier = q_replace($assetsupplier);
		$assetmodel = q_replace($assetmodel);
		$assetserial = q_replace($assetserial);
		$assetprice = q_replace($assetprice);
		$assetsupplierlink = q_replace($assetsupplierlink);
		$exp_date = q_replace($exp_date);
		
		echo "<table width=100% bgcolor='#ffffee' class='assetborder'><tr><td>";
		echo "<form action='" . $PHP_SELF . "' method='get'>";
		if ($insert == true) echo "<input type='hidden' name='action' value='assetinsert'>";
		else echo "<input type='hidden' name='action' value='assetupdate'>";
		echo "<input type='hidden' name='complete' value='1'>";
		echo "<input type='hidden' name='key' value='" . $key . "'>";
		echo "<table width=100%>";
		echo "<tr>\n";			
		echo "<td valign='top'>";
		
		//Asset Tag
		echo "<font class='text11bold'>Asset Tag:<br> <input type='text' name='assettag' maxlength=6 size=6 value=\"" . $assettag . "\" class='boxtext13'><font color='#ff0033' face='arial' size='4'><b> *</b></font><br></font>";
		echo "</td>\n";
		echo "<td class='text11' valign='top'>";
		
		//Asset Type
		echo "<table>";
		echo "<tr><td align='right'><font class='text11bold'>Asset Type: </td><td><input type='text' name='assettype' class='boxtext13bold' size=20 value=\"" . $assettype . "\"></font><font color='#ff0033' face='arial' size='4'><b> *</b></font></td></tr>\n";
	
		//Asset OS
		echo "<tr><td align='right'><font class='text11bold'>System Name: </td><td><input type='text' name='name' class='boxtext13bold' size=20  value=\"" . $name . "\"></font></td></tr>\n";
	
		// Supplier and Model
		echo "<tr><td colspan=2><p></td></tr>";
		echo "<tr><td align='right'><font class='text11bold'>Supplier: </font></td><td><input type='text' name='assetsupplier' class='boxtext13bold' size=40 value=\"" . $assetsupplier . "\"><font color='#ff0033' face='arial' size='4'><b> *</b></font></td></tr>\n";
		echo "<tr><td align='right'><font class='text11bold'>Supplier Link: </font></td><td><input type='text' name='assetsupplierlink' class='boxtext13bold' size=40 value=\"" . $assetsupplierlink . "\"></td></tr>\n";
		echo "<tr><td align='right'><font class='text11bold'>Model No: </font></td><td><input type='text' name='assetmodel' class='boxtext13bold' size=40 value=\"" . $assetmodel . "\"></td></tr>\n";
		echo "<tr><td align='right'><font class='text11bold'>s/n: </font></td><td><input type='text' name='assetserial' class='boxtext13' size=20 value=\"" . $assetserial . "\"></td></tr>\n";

		// Price
		echo "<tr><td align='right'><font class='text11bold'>Price: <font class='text12'>$</font></font></td><td><input type='text' name='assetprice' class='boxtext13' size=7 value=\"" . $assetprice . "\"></td></tr>\n";

		// exp_date
		echo "<tr><td align='right' valign='top'><font class='text11bold'>exp_date: <font class='text12'></font></font></td><td><textarea cols='50' rows='5' name='exp_date' class='boxtext13'>" . $exp_date . "</textarea></td></tr>\n";


		// Intially Assigned To (Insert Admin Only)
		if ($my_access_level > 1) {
			if ($insert == true) {
				echo "<tr><td align='right'><font class='text11bold'>Initially Assigned To: </td>";
				echo "<td>";
				echo "<select name='employee' size='1' class='boxtext13'>";
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
				echo "</td></tr>\n";

				$month_selected = $month_new;
				$day_selected = $day_new;
				$year_selected = $year_new;

				if (($month_selected == "") || ($day_selected == "") || ($year_selected == "")) {
					$month_selected = date("m",time());
					$day_selected = date("d",time());
					$year_selected = date("Y",time());
				}
							
				$months_names = Array("","January","February","March","April","May","June","July","August","September","October","November","December");
				echo "<tr><td align='right'><font class='text11bold'>Purchase/Install Date: </td>";
				echo "<td>";
				echo "<select name='month_new' size='1' class='boxtext13'>";
				for ($i=1;$i<13;$i++) {
					if ($i == $month_selected) {
						echo "<option value='" . $i . "' SELECTED>" . $months_names[$i] . "</option>";
					} else {
						echo "<option value='" . $i . "'>" . $months_names[$i] . "</option>";
					}
				}
				echo "</select>";

				echo "<select name='day_new' size='1' class='boxtext13'>";
				for ($i=1;$i<32;$i++) {
					if ($i == $day_selected) {
						echo "<option value='" . $i . "' SELECTED>" . $i . "</option>";
					} else {
						echo "<option value='" . $i . "'>" . $i . "</option>";
					}
				}
				echo "</select>";
			
				echo "<select name='year_new' size='1' class='boxtext13'>";
				for ($i=date("Y",time())-30;$i<date("Y",time())+1;$i++) {
					if ($i == $year_selected) {
						echo "<option value='" . $i . "' SELECTED>" . $i . "</option>";
					} else {
						echo "<option value='" . $i . "'>" . $i . "</option>";
					}
				}
				echo "</select>";
				echo "</td></tr>\n";

			}
		}
		echo "</table>";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>";
		echo "</td></tr></table>";
		echo "<hr size=0 color='" . $hrcolor . "'>";
		echo "<font color='#ff0033' face='arial' size='4'><b> *</b></font> <font class='text10bold'>deexp_date a required field</font>";
		echo "<p><center>";
		echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
		if ($insert == true) echo "<input type='image' name='submit' src='images/add.jpg' border=0 width=88 height=27></center></form>";
		else echo "<input type='image' name='submit' src='images/update.jpg' border=0 width=88 height=27></center></form>";
		echo "<p><br>";
	} else {

		doSQL("DELETE FROM Links WHERE supplier='" . $assetsupplier . "'");
		if (strlen($assetsupplierlink) > 0) {
			doSQL("INSERT INTO Links (supplier,link) VALUES ('" . $assetsupplier . "','" .  $assetsupplierlink . "')");
		}
		
		// everything is ok and verfied, now perform the actual insert or update action
		if ($insert == true) {
			// insert asset
			$sql = "INSERT INTO Assets (assettag,assettype,name,assetsupplier,assetmodel,assetserial,assetprice,exp_date) VALUES ('A" . $assettag . "','" . $assettype . "','" . $name . "','" . $assetsupplier . "','" . $assetmodel . "','" . $assetserial . "'," . $assetprice . ",'" . $exp_date . "');";
			if ($result1 = doSql($sql)) {	
				if ($result2 = doSql("SELECT last_insert_id() as last FROM Assets;")) {
					$query_data = mysql_fetch_array($result2);
					$last_insert_id = $query_data[0];
					$newstartdate = $date_new;
					if ($my_access_level > 1) {
						$initial_employeeid = $employee;
					} else {
						$initial_employeeid = $my_emp_id;
					}
					if ($result3 = doSql("INSERT INTO Assignments (employeeid,assetid,startdate) VALUES (" . $initial_employeeid . "," . $last_insert_id . "," . $newstartdate . ")")) {
						asset_menu_header(true,"New Asset","Asset Added Successful",$key);
						asset_tabs($query_data[0]);
						asset_print_info($query_data[0]);
						echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
						echo "<blockquote><font class='text12'>The asset has been successfully added to the system</font></blockquote>";
						echo "<p></td></tr></table>";
						echo "<center>";
						echo "<a href='" . $PHP_SELF . "?action=assetview&key=" . $last_insert_id . "'><img src='images/next.jpg' width=88 height=27 border=0></a></center></form>";
						echo "<p><br>";
						$pass = true;
					} else {
						$sql_error = mysql_error();
					}
				} else {
					$sql_error = mysql_error();
				}
			} else {
				$sql_error = mysql_error();
			}
		} else {
			// update asset
			$sql = "UPDATE Assets SET assettag='A" . $assettag . "',assettype='" . $assettype . "',name='" . $name . "',assetsupplier='" . $assetsupplier . "',assetmodel='" . $assetmodel . "',assetserial='" . $assetserial . "',assetprice=" . $assetprice . ",exp_date='" . $exp_date . "' WHERE id=" . $key . ";";
			if ($result = doSql($sql)) {
				asset_menu_header(true,"Update","Asset Updated Successful",$key);
				asset_tabs($key);
				asset_print_info($key);
				echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
				echo "<blockquote><font class='text12'>The asset has been successfully updated</font></blockquote>";
				echo "<p></td></tr></table>";
				$pass = true;
			} else {
				$sql_error = mysql_error();
			}
		}
		if ($pass == false) {
			asset_menu_header(true,"New Asset","<font class=text18bold color='#ff0033'>ERROR: An error occurred while inserting.</font>",$key);
			echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
			echo "<blockquote><font class='text12'>An error occurred while attempting to update the database. Please contact the webmaster. This is action attempted: " . $sql . "<br><br>" . $sql_error . "</font></blockquote>";
			echo "<p></td></tr></table>";

		}
	}
}

// prints out a calendar version of the above
function asset_admin_calendar($key) {
	global $day, $month, $year;
	asset_menu_header(true,"","Calendar",$key);
	asset_tabs($key);
	asset_print_info($key);
	echo "<table width=100% bgcolor='#ffeecc' class='assetborder'><tr><td>";
	echo "<center>";
	calendar($day, $month, $year, false, false, true);
	echo "</center>";
	echo "</td></tr></table>";
}


function asset_admin_dates($key) {
	global $complete;
	global $emp_db;
	global $action;
	global $newrow_x;
	global $update_x;
	global $complete;
	global $id, $employee, $remove;
	global $day_start, $month_start, $year_start;
	global $day_end, $month_end, $year_end;
	//global $start_check, $end_check;
	
	if ($complete == "1") $post_changes = true;

	if ($newrow_x != "") $newrow = "1";
	if ($update_x != "") $update = "1";
	
	asset_menu_header(true,"","Date Management of Asset","");
	asset_tabs($key);
	asset_print_info($key);

	echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
	echo "<input type='hidden' name='action' value='" . $action . "'>";
	echo "<input type='hidden' name='key' value='" . $key . "'>";
	echo "<input type='hidden' name='complete' value='1'>";
	echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
	echo "<blockquote>";
	echo "<p><font class='text13'>";
	echo "This screen allows you to make changes to transfer dates. An blank date box for a start date indicates the initial purchase date is unknown. An blank date box for an end date indicates there is no end date and the transfer is permanent. Only <b>ONE</b> blank start date is allowed and one blank end date is <b>REQUIRED</b>.  Consecutive time periods assigned to the same employee will be automatically merged shortly after updating.";
	echo "<p><b>Date updating will not take place if any dates overlap or if there is not exactly ONE row with a blank end date.  Each row's start date must be the day after the previous' rows end date (e.g. Row 1 end date is Mar 15, Row 2 start date is Mar 16)</b>";
	echo "</font>";
	echo "<p><table border=1>";
	echo "<tr>";
	echo "<td class='text12bold'>Start Date</td>";
	echo "<td class='text12bold'>End Date</td>";
	echo "<td class='text12bold'>Employee</td>";
	echo "<td class='text12bold'>Remove</td>";
	echo "<td class='text12bold'>Error (if any)</td>";
	echo "</tr>";
	
	// load the array
	if ($complete != "1") {
		$rec_num = 0;
		$sql = "SELECT id, startdate, enddate, employeeid FROM Assignments WHERE assetid=" . $key . " ORDER BY startdate";
		if (($result = doSql($sql)) && (mysql_num_rows($result))) {
			while ($query_data = mysql_fetch_array($result)) {
				$arr_assign[$rec_num] = $query_data;
				$arr_assign[$rec_num]["error"] = 0;
				$rec_num++;
			}
		}
	} else {
		$rec_num = 0;
		for ($i=0;$i<count($id);$i++) {
			if ((is_array($remove) == false) || (in_array($id[$i],$remove) == false)) {
				// id
				$arr_assign_id = $id[$i];

				// start date
				if (($month_start[$i] == "") || ($day_start[$i] == "") || ($year_start[$i] == "")) $arr_assign_startdate = "0";
				else $arr_assign_startdate = mktime(0,0,0,$month_start[$i],$day_start[$i],$year_start[$i]);
				//if (!((is_array($start_check) == true) && (in_array($id[$i],$start_check) == true))) $arr_assign_startdate = "0";

				// end date
				if (($month_end[$i] == "") || ($day_end[$i] == "") || ($year_end[$i] == "")) $arr_assign_enddate = "0";
				else $arr_assign_enddate = mktime(23,59,59,$month_end[$i],$day_end[$i],$year_end[$i]);
				//if (!((is_array($end_check) == true) && (in_array($id[$i],$end_check) == true))) $arr_assign_enddate = "0";
				
				// employee assignments
				$arr_assign_employeeid = $employee[$i];

				// error checking
				$arr_assign_error = 0;
				if ((($arr_assign_enddate - $arr_assign_startdate) < 0) && ($arr_assign_enddate != 0)) {
					$arr_assign_error = 1;
				}
				
				if (($month_start[$i] != "") && ($day_start[$i] != "") && ($year_start[$i] != "")) {
					if (checkdate($month_start[$i],$day_start[$i],$year_start[$i]) == false) {
						$arr_assign_error = 2;
					}
				}

				if (($month_end[$i] != "") && ($day_end[$i] != "") && ($year_end[$i] != "")) {
					if (checkdate($month_end[$i],$day_end[$i],$year_end[$i]) == false) {
						$arr_assign_error = 3;
					}
				}

				$arr_assign[$rec_num]["id"] = $arr_assign_id;
				$arr_assign[$rec_num]["employeeid"] = $arr_assign_employeeid;
				$arr_assign[$rec_num]["startdate"] = $arr_assign_startdate;
				$arr_assign[$rec_num]["enddate"] = $arr_assign_enddate;
				$arr_assign[$rec_num]["error"] = $arr_assign_error;
				
				$rec_num++;
			}
		}
	}
	

	// add a new row, if requested
	if ($newrow == "1") {
		$time_start = mktime(0,0,0,date("m",time()),date("d",time()),date ("Y",time()));
		$time_end = mktime(23,59,59,date("m",time()),date("d",time()),date ("Y",time()));
		$arr_assign[$rec_num]["id"] = time() + (microtime() * 1000);
		$arr_assign[$rec_num]["startdate"] = $time_start;
		$arr_assign[$rec_num]["enddate"] = $time_end;
		$arr_assign[$rec_num]["employeeid"] = "0";
		$rec_num++;
	}
		
	// sort the dates array
	usort($arr_assign, "cmp_array_startdate");

	// loop through all the rows
	for ($i=0;$i<$rec_num;$i++) {

		// check overlaps and consecutive dates
		if ($i > 0) {
			if ((($arr_assign[$i]["startdate"] - $arr_assign[($i-1)]["enddate"]) != 1) && ($arr_assign[$i]["error"] == 0)) {
				$arr_assign[$i]["error"] = 4;
			}
		}

		// check overlaps and consecutive dates
		if ($i == ($rec_num-1)) {
			if ($arr_assign[$i]["enddate"] != 0) {
				$arr_assign[$i]["error"] = 5;
			}
		}

		echo "<input type='hidden' name='id[]' value='" . $arr_assign[$i]["id"] . "'>";
		if ($arr_assign[$i]["error"] > 0) {
			echo "<tr bgcolor='#ff0033'>";
			$post_changes = false;
		} else {
			echo "<tr>";
		}
		
		// start date
		$day_start = date("d",$arr_assign[$i]["startdate"]);
		$month_start = date("m",$arr_assign[$i]["startdate"]);
		$year_start = date ("Y",$arr_assign[$i]["startdate"]);

		echo "<td>";
		if (strcmp($arr_assign[$i]["startdate"],"0") == 0) $useunknown = true;
		else $useunknown = false;
		
		dateDropdown("start[]",$month_start,$day_start,$year_start,0,0,true,$useunknown);
		echo "&nbsp;&nbsp;&nbsp;";
		echo "</td>";

		// end date
		echo "<td>";

		$day_end = date("d",$arr_assign[$i]["enddate"]);
		$month_end = date("m",$arr_assign[$i]["enddate"]);
		$year_end = date ("Y",$arr_assign[$i]["enddate"]);
		if (strcmp($arr_assign[$i]["enddate"],"0") == 0) $useunknown = true;
		else $useunknown = false;
		
		dateDropdown("end[]",$month_end,$day_end,$year_end,0,0,false,$useunknown);
		echo "&nbsp;&nbsp;&nbsp;";
		echo "</td>";

		// employee
		echo "<td>";
		$sql2 = "SELECT id, LastName, FirstName FROM " . $emp_db . "Employees ORDER BY LastName";
		echo "<select name='employee[]' class='boxtext13'>";
		if (($result2 = doSql($sql2)) && (mysql_num_rows($result2))) {
			if ($arr_assign[$i]["employeeid"] == "0") echo "<option value='0' SELECTED>General Assets</option>";
			else echo "<option value='0'>General Assets</option>";
			if ($arr_assign[$i]["employeeid"] == "-1") echo "<option value='-1' SELECTED>Surplus</option>";
			else echo "<option value='-1'>Surplus</option>";
			if ($arr_assign[$i]["employeeid"] == "-2") echo "<option value='-2' SELECTED>Retired</option>";
			else echo "<option value='-2'>Retired</option>";
			while ($query_data2 = mysql_fetch_array($result2)) {
				if ($arr_assign[$i]["employeeid"] == $query_data2["id"]) {
					echo "<option value='" . $query_data2["id"] . "' SELECTED>" . $query_data2["LastName"] . ", " . $query_data2["FirstName"] . "</option>";
				} else {
					echo "<option value='" . $query_data2["id"] . "'>" . $query_data2["LastName"] . ", " . $query_data2["FirstName"] . "</option>";
				}
			}
		}
		echo "</select>";
		echo "</td>";
		echo "<td>";
		echo "<input type='checkbox' name='remove[]' value='" . $arr_assign[$i]["id"] . "'>";
		echo "</td>";

		echo "<td class='text13'>";
		switch ($arr_assign[$i]["error"]) {
			case 1:
				echo "<font color='#ffffff'><b>End Date is before Start Date.</b></font>";
				break;
			case 2:
				echo "<font color='#ffffff'><b>Invalid Start Date, Value has been corrected.</b></font>";
				break;
			case 3:
				echo "<font color='#ffffff'><b>Invalid End Date, Value has been corrected.</b></font>";
				break;
			case 4:
				echo "<font color='#ffffff'><b>Non-consecutive or overlap exist with previous row.</b></font>";
				break;
			case 5:
				echo "<font color='#ffffff'><b>The end date of last row must be blank.</b></font>";
				break;
			default;
				echo "<b>None</b>";
				break;
		}
		echo "</td>";
		echo "</tr>";
	}	
	echo "</table>";

	if ($post_changes == true) {
		doSql("DELETE FROM Assignments WHERE AssetId=" . $key . " AND Temp=0");
		for($k=0;$k<$rec_num;$k++) {
			doSql("INSERT INTO Assignments (employeeid,assetid,startdate,enddate,approve,temp,completed) VALUES (" . $arr_assign[$k]["employeeid"] ."," . $key . "," . $arr_assign[$k]["startdate"] . "," . $arr_assign[$k]["enddate"] . ",0,0,0)");
		}
		echo "<p><font class='text12bold' color='#ff0033'>Updating Complete!</font>";
	}

	echo "</blockquote>";
	echo "<p></td></tr></table>";
	echo "<p><br><center><input type='image' src='images/addrow.jpg' width=88 height=27 name='newrow' value='1' border=0><input type='image' src='images/update.jpg' width=88 height=27 name='update' value='1'></center>";
	echo "<table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
}

function asset_admin_erase($key) {
	global $complete;
	asset_menu_header(true,"","Erase Asset","");
	if ($complete == "1") {
		$result = doSql("DELETE FROM Assignments WHERE AssetId=" . $key);
		$result = doSql("DELETE FROM Assets WHERE Id=" . $key);
		asset_print_info($key);
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		echo "<blockquote><font class='text12'>Asset Erased Successfully</font></blockquote>";
		echo "<p></td></tr></table>";
		echo "<p><center>";
		echo "<a href='" . $PHP_SELF . "?action=assets'><img src='images/next.jpg' width=88 height=27 border=0></a>";
		echo "</center>";
		echo "<p><br>";
	} else {
		asset_tabs($key);
		asset_print_info($key);
		echo "<table width=100% bgcolor='#ffeecc'><tr><td><br>";
		echo "<blockquote><font class='text12'><font color='#ff0033'><b>WARNING!</b></font> This will erase all traces of the asset including all transfers and sign out.  Are you sure you want to do this?</font></blockquote>";
		echo "<p></td></tr></table>";
		echo "<p><center>";
		echo "<a href='" . $PHP_SELF . "?action=asseterase&key=" . $key . "&complete=1'><img src='images/yes.jpg' width=88 height=27 border=0></a>";
		echo "<a href='" . $PHP_SELF . "?action=assetview&key=" . $key . "'><img src='images/no.jpg' width=88 height=27 border=0></a>";
		echo "</center>";
		echo "<p><br>";

	}
}
?>
