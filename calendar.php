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
// calendar($day, $month, $year, $is_enddate, $temp, $read_only)

function calendar($day, $month, $year, $is_enddate, $temp, $read_only) {
	global $key;
	global $startdate;
	global $newowner;
	global $temp;
	global $my_access_level;
	global $emp_db;

	$temp_transfer_passed = false;
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if (strlen($startdate) > 0) {
		$new_time = $startdate;
	} else {
		$day_now = date("d",time());
		$month_now = date("m",time());
		$year_now = date ("Y",time());
		$new_time = mktime(0,0,0,$month_now,$day_now,$year_now);
	}

	if ($is_enddate == true) $date_field = "enddate";
	else $date_field = "startdate";				

	$month_now = date("m",$new_time);
	$year_now = date("Y",$new_time);

	if ($month == "") $month = $month_now;
	if ($year == "") $year = $year_now;
	$month_prev = $month - 1;
	$month_next = $month + 1;
	$year_prev = $year;
	$year_next = $year;
	if ($month_prev < 1) {
		$month_prev = 12;
		$year_prev--;
	}
	if ($month_next > 12) {
		$month_next = 1;
		$year_next++;
	}

	echo "<font class='text18bold'>";

	if ($read_only == false) $extra_link = "&action=assettransfer&newowner=" . $newowner . "&startdate=" . $startdate . "&temp=" . $temp;
	else $extra_link = "&action=assetcalendar";

	//if (((($month_prev >= $month_now) && ($year_prev == $year_now)) || ($year_prev > $year_now)) || ($read_only == true)) echo "<a href='" . $PHP_SELF . "?key=" . $key . "&month=" . $month_prev . "&year=" . $year_prev . "" . $extra_link . "' class='text12bold'>&laquo; " . date("F Y",mktime(0,0,0,$month_prev,1,$year_prev)) . "</a>&nbsp;&nbsp;<font class='text11bold'>&middot;</font>&nbsp;&nbsp;";
	//else echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<a href='" . $PHP_SELF . "?key=" . $key . "&month=" . $month_prev . "&year=" . $year_prev . "" . $extra_link . "' class='text12bold'>&laquo; " . date("F Y",mktime(0,0,0,$month_prev,1,$year_prev)) . "</a>&nbsp;&nbsp;<font class='text11bold'>&middot;</font>&nbsp;&nbsp;";
	echo date("F Y",mktime (0,0,0,$month,1,$year));
	echo "&nbsp;&nbsp;<font class='text12bold'>&middot;</font>&nbsp;&nbsp;<a href='" . $PHP_SELF . "?key=" . $key . "&month=" . $month_next . "&year=" . $year_next . "" . $extra_link . "' class='text12bold'>" . date("F Y",mktime(0,0,0,$month_next,1,$year_next)) . " &raquo;</a>";

	echo "</font>";
	echo "<p><table class='mytable' cellspacing=0 cellpadding=0 border=0>";
	echo "<tr>";
	echo "<td class='mytable' align='center'><font class='text11'>sunday</font></td>";
	echo "<td class='mytable' align='center'><font class='text11'>monday</font></td>";
	echo "<td class='mytable' align='center'><font class='text11'>tuesday</font></td>";
	echo "<td class='mytable' align='center'><font class='text11'>wednesday</font></td>";
	echo "<td class='mytable' align='center'><font class='text11'>thursday</font></td>";
	echo "<td class='mytable' align='center'><font class='text11'>friday</font></td>";
	echo "<td class='mytable' align='center'><font class='text11'>saturday</font></td>";
	echo "</tr>";
	$startdate = 1;
	$started = false;

	// for transfers, autoset the enddate
	if ($temp == "0") echo "<input type='hidden' name='enddate' value='0'>";

	// cycle through each day of the month
	for ($i=0;$i<6;$i++) {
		echo "<tr>";
		for ($j=0;$j<7;$j++) {

			$not_installed = false;

			// verify when we have started the first day of the month
			if ($j == date("w",mktime (0,0,0,$month,1,$year))) $started = true;

			if ((checkdate($month, $startdate, $year)) && ($started == true)) {

				// start dates are at 00:00:00, end dates are at 23:59:59
				$start_date_unix = mktime(0,0,0,$month,$startdate,$year);
				$end_date_unix = mktime(23,59,59,$month,$startdate,$year);
				if ($is_enddate == true) $date_unix = $end_date_unix;
				else $date_unix = $start_date_unix;

				// calendar box date is before today
				if (mktime(0,0,0,$month,$startdate,$year) < $new_time) $is_after = -1;
				elseif (mktime(0,0,0,$month,$startdate,$year) == $new_time) $is_after = 0;
				else $is_after = 1;
				
				// determine ownership of the item putting priority on sign outs
				$sql = "SELECT " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, Assignments.EmployeeId, Assignments.Temp, Assignments.Completed, Assignments.EndDate FROM Assignments LEFT JOIN " . $emp_db . "Employees ON Assignments.EmployeeID = " . $emp_db . "Employees.Id WHERE Assignments.StartDate <= " . $start_date_unix . " AND (Assignments.EndDate >= " . $end_date_unix . " OR Assignments.EndDate = 0) AND AssetId=" . $key . " ORDER BY Assignments.Temp DESC";
				if (($result = doSql($sql)) && (mysql_num_rows($result)) && $query_data = mysql_fetch_array($result)) {
					$empid = $query_data["EmployeeId"];
					$found_temp = $query_data["Temp"];
					$found_time = (($query_data["EndDate"]) + 1);

					// convert employee id into a name
					switch ($empid) {
						case 0:
							$full_name = "General Assets";
							break;				
						case -1:
							$full_name = "Surplus";
							break;				
						case -2:
							$full_name = "Retired";
							break;
						default:
							$full_name = $query_data["LastName"] . ", " . $query_data["FirstName"];
							break;
					}
				} else {
					$empid = 0;
					$found_temp = 0;
					$not_installed = true;
					$full_name = "Not Installed";
				}
				$full_name = "<center><font class='text11' color='#000000'>" . $full_name . "</font></center><br>";

				// determine coloring and information display based on the information above
				if ($read_only == true) {
					// read only non-transfer view
					if ($found_temp == 1) $calendar_class = "calendar_taken";
					else $calendar_class = "calendar_permok";
					$print_date = $startdate;
				} elseif ($temp == false) {
					// code removed here from version 1 so that transfers can be set in the past
					// date has not passed
					if ($found_temp == 1) {
						// a sign out exists
						$calendar_class = "calendar_taken";
						$print_date = $startdate;		
					} else {
						// a transfer exists
						$calendar_class = "calendar_permok";
						$print_date = "<input type='radio' name='" . $date_field . "' value='" . $date_unix . "'>" . $startdate;
					}
				} else {
					// sign out view
					if (($is_after < 0) || ($temp_transfer_passed == true)) {
						// date has passed
						if ($found_temp == 1) $calendar_class = "calendar_taken";
						else $calendar_class = "calendar_passed";
						$print_date = $startdate;		
					} else {
						// date has not passed
						if ($found_temp == 1) {
							// a sign out exists
							$calendar_class = "calendar_taken";
							$print_date = $startdate;
							if (($found_time > $new_time) && ($is_enddate == true)) $temp_transfer_passed = true;
						} else {
							// a transfer exists
							$calendar_class = "calendar_tempok";
							$print_date = "<input type='radio' name='" . $date_field . "' value='" . $date_unix . "'>" . $startdate;		
						}
					}
				}
				$startdate++;
			} else {
				$calendar_class = "calendar_invalid";
				$print_date = "";							
				$full_name = "&nbsp;";
			}
			if ($not_installed == true) $calendar_class = "calendar_passed";

			echo "<td class='" . $calendar_class . "' width=100 height=100 align='right' valign='top'><font class='text10bold' color='#000000'>" . $print_date . "</font><br>" . $full_name . "</td>";
		}
		// skip 5th week of the month if there are no days in it
		if (!checkdate($month, $startdate, $year)) $i++;
		echo "</tr>";
	}
	echo "</table>";
	echo "<p>";
	echo "<table><tr>";
	echo "<td>";
	echo "<table><tr><td class='calendar_permok'>&nbsp;&nbsp;&nbsp;</td><td><font class='text12'>Assigned to an Employee (via a Transfer)</font></td></tr></table>";
	echo "</td>";
	echo "<td>";
	echo "<table><tr><td class='calendar_tempok'>&nbsp;&nbsp;&nbsp;</td><td><font class='text12'>Available for Sign Out from General Assets</font></td></tr></table>";
	echo "</td>";
	echo "</tr><tr>";
	echo "<td>";
	echo "<table><tr><td class='calendar_taken'>&nbsp;&nbsp;&nbsp;</td><td><font class='text12'>Not Available (Asset is booked)</font></td></tr></table>";
	echo "</td>";
	echo "<td>";
	echo "<table><tr><td class='calendar_passed'>&nbsp;&nbsp;&nbsp;</td><td><font class='text12'>Cannot be selected (See the <a href='" . $PHP_SELF . "?action=help' class='text12'>Help</a> Section for details)</font></td></tr></table>";
	echo "</td>";
	echo "</td></tr></table>";	
	echo "<p>";
}

?>
