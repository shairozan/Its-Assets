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
// employee_admin_rules($empid, $insert, $lastname, $firstname, $tel, $organization, $dept, $building, $floor, $workstation, $accesslevel, $active, $loginname, $password, $passwordagain, $email)
// employee_admin($key, $insert, $complete)
// employee_admin_change_password($key)
// employee_admin_erase($key)

// validate the data
function employee_admin_rules($empid, $insert, $lastname, $firstname, $tel, $organization, $dept, $building, $floor, $workstation, $accesslevel, $active, $loginname, $password, $passwordagain, $email) {
	global $my_access_level;
	global $my_emp_id;
	global $emp_db;
	
	// check duplicate login
	if ($insert == true) $sql = "SELECT " . $emp_db . "Employees.Id AS " . $emp_db . "Employees_ID FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.LoginName='" . q_replace(dehtml($loginname)) . "';";
	else $sql = "SELECT " . $emp_db . "Employees.Id AS Employees_ID FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.LoginName='" . q_replace(dehtml($loginname)) . "' AND " . $emp_db . "Employees.Id <> " . $empid . ";";
	if (($result = doSql($sql)) && (mysql_num_rows($result))) $errcode = "1";
	else $errcode = "0";
	
	// check login
	if (strlen($loginname) < 1) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";
	
	// check password
	if ($insert == true) {
		if (strcmp($password,$passwordagain) != 0) $errcode = $errcode . "1";
		else $errcode = $errcode . "0";
	} else {
		$errcode = $errcode . "0";
	}

	// check password alpha
	if (is_alphanum_str(stripslashes($password))) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";

	// check first name
	if (strlen($firstname) < 1) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";

	// check last name
	if (strlen($lastname) < 1) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";

	
	// check access level change
	if (($my_access_level > 1) && ($my_access_level != $accesslevel) && ($my_emp_id == $empid)) $errcode = $errcode . "1";
	else $errcode = $errcode . "0";
	
	return $errcode;
}

// inserts or updates an employee
function employee_admin($key, $insert, $register) {
	global $action, $key, $lastaction, $lastkey;
	global $my_access_level;
	global $my_emp_id;
	global $complete;
	global $print_screen;
	global $hrcolor;
	global $activelogin;
	global $activepass;
	global $emp_db;
	global $demo_mode;
	
	$PHP_SELF = $_SERVER['PHP_SELF'];
	// Set Page Title Based On Insert/Update/Register	
	if ($insert == true) $insertupdatetext = "New Employee";
	else $insertupdatetext = "Update";
	if ($register == true) $insertupdatetext = "Register";

	// load incoming form data and set some flags
	global $empid, $lastname, $firstname, $tel, $organization, $dept, $building, $floor, $workstation, $accesslevel, $active, $loginname, $password, $passwordagain, $resetpassword, $email;
	if ((strcmp($active,"on") == 0) || ($complete != "1")) $active = "1";
	else $active = "0";

	// if this is an update and it's not complete, load the information from the database
	if (($insert == false) && ($complete != "1")) {
		$sql = "SELECT " . $emp_db . "Employees.Id, " . $emp_db . "Employees.LastName, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.Tel, " . $emp_db . "Employees.Organization, " . $emp_db . "Employees.Dept, " . $emp_db . "Employees.Building, " . $emp_db . "Employees.Floor, " . $emp_db . "Employees.Workstation, " . $emp_db . "Employees.AccessLevel, " . $emp_db . "Employees.Active, " . $emp_db . "Employees.EMail, " . $emp_db . "Employees.LoginName FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.Id=" . $key . ";";
		if (($result = doSql($sql)) && (mysql_num_rows($result)) && ($query_data = mysql_fetch_array($result))) {
			$empid = $query_data["Id"];
			$lastname = $query_data["LastName"];
			$firstname = $query_data["FirstName"];
			$tel = $query_data["Tel"];
			$organization = $query_data["Organization"]; 
			$dept = $query_data["Dept"]; 
			$building = $query_data["Building"]; 
			$floor = $query_data["Floor"]; 
			$workstation = $query_data["Workstation"];	
			$accesslevel = $query_data["AccessLevel"];
			$active = $query_data["Active"];
			$loginname = $query_data["LoginName"];
			$email = $query_data["EMail"];
		}
	}

	// preset the access level checkbox
	switch ($accesslevel) {
		case 0:
			$user_select = " SELECTED";
			break;
		case 2:
			$admin_select = " SELECTED";
			break;
	}

	// preset the active checkbox
	if ($active == "1") $active_text = " CHECKED";
	else $active_text = "";

	// validate the data for any errors
	$errcode = employee_admin_rules($empid, $insert, $lastname, $firstname, $tel, $organization, $dept, $building, $floor, $workstation, $accesslevel, $active, $loginname, $password, $passwordagain, $email);
	if (($complete == "1") && ($errcode > 0)) {
		// print error messages
		$complete = "0";
		employee_menu_header(true,$insertupdatetext, "<font class=text18bold color='#ff0033'>ERROR: Incomplete or Invalid Data.</font>", $key);
		echo "<p><font class='text12bold' color='#ff0033'>";
		if ($errcode[0] == "1") echo "The login name you have entered is the same as another login name previously entered. ";
		if ($errcode[1] == "1") echo "A login name must be entered. ";
		if ($errcode[2] == "1") echo "The password box must match the retyped password box. ";
		if ($errcode[3] == "1") echo "Password must only use letters A to Z and digits 0 to 9. ";
		if ($errcode[4] == "1") echo "A first name must be entered. ";
		if ($errcode[5] == "1") echo "A last name must be entered. ";
		if ($errcode[6] == "1") echo "You cannot change your own access level. ";
		echo "</font><p>";
	} else {
		if ($complete != "1") employee_menu_header(true,"", $insertupdatetext, $key);
	}

	// reset the accesslevel if none exists
	if (is_numeric($accesslevel) == false) $accesslevel = "0";

	// either this is the first time or an error occurred during submitting, reprint the form
	if ($complete != "1") {	
		// html encode data for the form
		$lastname = q_replace($lastname);
		$firstname = q_replace($firstname);
		$loginname = q_replace($loginname);
		$tel = q_replace($tel);
		$organization = q_replace($organization); 
		$dept = q_replace($dept); 
		$building = q_replace($building); 
		$floor = q_replace($floor); 
		$workstation = q_replace($workstation);	
		$accesslevel = q_replace($accesslevel);
		$email = q_replace($email);

		employee_tabs($key);
		echo "<table width=100% bgcolor='#ffeeee' class='employeeborder'><tr><td>";
		echo "<table cellspacing=0 cellpadding=0 border=0><tr><td><form action='" . $PHP_SELF . "' method='get'></td></tr></table>";
		echo "<input type='hidden' name='action' value='" . $action . "'>";
		echo "<input type='hidden' name='complete' value='1'>";
		echo "<input type='hidden' name='key' value=" . $key . ">";
		echo "<input type='hidden' name='empid' value='" . $empid . "'>";
		echo "<table width=100%>";
		echo "<tr><td valign='top'>";
		echo "<table><tr>";

		// first name
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>First Name:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='firstname' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $firstname . "\"><font color='#ff0033' face='arial' size='4'><b> *</b></font></td>";
		echo "</tr><tr>";

		// last name
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>Last Name:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='lastname' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $lastname . "\"><font color='#ff0033' face='arial' size='4'><b> *</b></font></td>";
		echo "</tr><tr>";

		// login name
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>Login Name:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='loginname' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $loginname . "\"><font color='#ff0033' face='arial' size='4'><b> *</b></font></td>";
		echo "</tr><tr>";

		// email
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>E-Mail:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='email' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $email . "\"></td>";
		echo "</tr><tr>";

		// password options
		if ($insert == true) {
			// password
			echo "<td class='text13bold' align='right'><font class='text13bold'><b>Password:&nbsp;</b></font></td>";
			echo "<td class='text13bold'><input name='password' type='password' class='boxtext13bold' size='20' class='text13bold' size=30></td>";
			echo "</tr><tr>";

			// password again
			echo "<td class='text13bold' align='right'><font class='text13bold'><b>Retype Password:&nbsp;</b></font></td>";
			echo "<td class='text13bold'><input name='passwordagain' type='password' size='20' class='boxtext13bold' size=30></td>";
		} else {
			if ($my_access_level > 1) {
				// reset password checkbox
				echo "<td class='text13bold' align='right' rowspan=2><font class='text12bold'><b>New Password:&nbsp;</b></font></td>";
				echo "<td class='text13bold' rowspan=2><input name='password' type='password' size='10' class='boxtext13bold'><font class='text12bold'><b><input type='checkbox' name='resetpassword'> Reset</b></font></td>";
			} else {
				echo "<td class='text13bold' align='center' colspan=2>&nbsp;</td>";
				echo "</tr><tr>";
				echo "<td class='text13bold' align='right'>&nbsp;</td>";
				echo "<td class='text13bold'>&nbsp;</td>";
			}
		}
		echo "</tr>";
		echo "</table>";
		echo "</td><td valign='top'>";
		echo "<table><tr>";

		// phone number
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>Phone Number:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='tel' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $tel . "\"></td>";
		echo "</tr><tr>";

		// organization
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>Organization:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='organization' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $organization . "\"></td>";
		echo "</tr><tr>";

		// dept
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>Dept:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='dept' type='text' size='20' class='boxtext13bold' size=30 value=\"" . $dept . "\"></td>";
		echo "</tr><tr>";

		// location
		echo "<td class='text13bold' align='right'><font class='text13bold'><b>Location:&nbsp;</b></font></td>";
		echo "<td class='text13bold'><input name='building' type='text' size='3' class='boxtext13bold' value=\"" . $building . "\"> <input name='floor' type='text' size='3' class='boxtext13bold' value=\"" . $floor . "\">-<input name='workstation' type='text' size='2' class='boxtext13bold' value=\"" . $workstation . "\"></td>";
		echo "</tr><tr>";

		// active checkbox
		if ($my_access_level > 1) {
			echo "<td class='text13bold' align='right' valign='top' rowspan=2><font class='text13bold'><b>Active:&nbsp;</b></font></td>";
			echo "<td class='text13bold'><input type='checkbox' name='active' " . $active_text . " valign='top' rowspan=2>&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<font class='text13bold'><b>Access: <select name='accesslevel' size='1' class='boxtext13bold'>";
			echo "<option value='1'" . $user_select . ">User</option>";
			echo "<option value='2'" . $admin_select . ">Admin</option>";
			echo "</select> </b></font>";
			echo "</td>";
		} else {
			echo "<td class='text13bold' valign='right' rowspan=2>&nbsp;</td>";
			echo "<td class='text13bold' rowspan=2>&nbsp;</td>";
		}
		echo "</tr>";
		echo "</table>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "</td></tr></table>";
		echo "<hr size=0 color='" . $hrcolor . "'>";
		echo "<font color='#ff0033' face='arial' size='4'><b> *</b></font> <font class='text10bold'>denotes a required field</font>";
		echo "<p><center>";
		echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
		if ($insert == true) echo "<input type='image' name='submit' src='images/add.jpg' border=0 width=88 height=27></center></form>";
		else echo "<input type='image' name='submit' src='images/update.jpg' border=0 width=88 height=27></center></form>";
		echo "<p><br>";
	} else {
		// complete the transaction (either insert or update)
		if ($insert == true) {
			// insert
			if ($my_access_level < 2) {
				$accesslevel = 1;
				$active = 1;
			}
			$sql = "INSERT INTO " . $emp_db . "Employees (lastname,firstname,tel,organization,dept,building,floor,workstation,loginname,email,accesslevel,active,userpass) VALUES ('" . $lastname . "','" . $firstname . "','" . $tel . "','" . $organization . "','" . $dept . "','" . $building . "','" . $floor . "','" . $workstation . "','" . $loginname . "','" . $email . "',1,1,Password('" . $password . "'))";
			if ($result = doSql($sql)) {
				if ($register == true) {
					employee_menu_header(true,$insertupdatetext,"Registration Succesful", $key);
					employee_tabs($key);
					employee_print_info($key);
					echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
					echo "<blockquote><font class='text12'>You have now been registered in the system.  Click Next To Login.</font></blockquote>";
					echo "<p></td></tr></table>";
					echo "<p><center>";
					echo "<a href='" . $PHP_SELF . "?action=login&lastaction=" . $lastaction . "&lastkey=" . $lastkey . "'><img src='images/next.jpg' border=0 width=88 height=27></a>";
					echo "</center>";
				} else {
					employee_menu_header(true,$insertupdatetext,"New Employee Added Succesfully", $key);
					employee_tabs($key);
					employee_print_info($key);
					echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
					echo "<blockquote><font class='text12'>The employee has been successfully added to the system</font></blockquote>";
					echo "<p></td></tr></table>";
				}
			} else {
				employee_menu_header(true,$insertupdatetext,"<font class=text18bold color='#ff0033'>ERROR: An error occurred while inserting", $key);
				employee_tabs($key);
				echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
				echo "<blockquote><font class='text12'>An error occurred while attempting to update the database. Please contact the webmaster. <p>This is action attempted: " . $sql . "<p>" . mysql_error() . "</font></blockquote>";
				echo "<p></td></tr></table>";
			}
		} else {
			// update
			if ((strcmp($resetpassword,"on") == 0) && ($demo_mode == false)) {
				$password_reset = ",UserPass=Password('" . $password . "') ";
				if ($key == $my_emp_id)	$activepass = $password;
			} else {
				$password_reset = "";
			}
			if ($key == $my_emp_id)	$activelogin = $loginname;
			if ($my_access_level > 1) $extra_sql = ",accesslevel=" . $accesslevel . ",active=" . $active . "" . $password_reset;
			else $extra_sql = "";
			$sql = "UPDATE " . $emp_db . "Employees SET lastname='" . $lastname . "',firstname='" . $firstname . "',tel='" . $tel . "',organization='" . $organization . "',dept='" . $dept . "',building='" . $building . "',floor='" . $floor . "',workstation='" . $workstation . "',loginname='" . $loginname . "',email='" . $email . "'" . $extra_sql . " WHERE id=" . $key . ";";
			if ($result = doSql($sql)) {
				employee_menu_header(true,"Update","Employee Updated Succesfully", $key);
				employee_tabs($key);
				employee_print_info($key);
				echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
				echo "<blockquote><font class='text12'>The employee has been successfully updated</font></blockquote>";
				echo "<p></td></tr></table>";
			} else {
				employee_menu_header(true,"Update","<font class=text18bold color='#ff0033'>ERROR: An error occurred while updating", $key);
				employee_tabs($key);
				echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
				echo "<blockquote><font class='text12'>An error occurred while attempting to update the database. Please contact the webmaster. <p>This is action attempted: " . $sql . "<p>" . mysql_error() . "</font></blockquote>";
				echo "<p></td></tr></table>";
			}
		}
	}
}

// the user changes their password
function employee_admin_change_password($key) {
	global $oldpassword, $password, $passwordagain, $activelogin, $activepass, $complete;
	global $print_screen;
	global $hrcolor;
	global $emp_db;
	global $cancel_form;
	global $demo_mode;
	// check if the change password is completed, the old password matches, the new password matches, and valid characters are used
	if (($complete == "1") && ($demo_mode == false)) {
		if (strcmp($oldpassword,$activepass) == 0) {
			if (strcmp($password,$passwordagain) == 0) {
				if (is_alphanum_str($password) == false) {			
					$sql = "UPDATE " . $emp_db . "Employees SET UserPass=Password('" . $password . "') WHERE " . $emp_db . "Employees.Id=" . $key . ";";
					if ($result = doSql($sql)) {
						$top_text = "Change Password";
						$header_text = "Password Changed Successfully";
						$msg_text = "<p><blockquote><font class='text12'>Your password has been changed successfully.</blockquote></font>";
						$activepass = $password;
						$cancel_form = true;
					} else {
						$top_text = "Change Password";
						$header_text = "<font color='#ff0033'>ERROR: An error occurred while trying to update the database.</font>";
						$msg_text = "<p><blockquote><font class='text12'>Please contact the webmaster. This is action attempted: " . $sql . "<p>" . $sql_error . "</blockquote></font>";
						$cancel_form = false;
						$sql_error = mysql_error();
					}
				} else {
					$top_text = "Change Password";
					$header_text = "<font color='#ff0033'>ERROR: New password contains invalid characters.</font>";
					$msg_text = "<p><blockquote><font class='text12'>Password must only use letters A to Z and digits 0 to 9. Please re-enter the information again.</blockquote></font>";
				}
			} else {
				$top_text = "Change Password";
				$header_text = "<font color='#ff0033'>ERROR: New Password was retyped incorrectly.</font>";
				$msg_text = "<p><blockquote><font class='text12'>Please re-enter the information again.</blockquote></font>";
			}
		} else {
			$top_text = "Change Password";
			$header_text = "<font color='#ff0033'>ERROR: Incorrect Old Password.</font>";
			$msg_text = "<p><blockquote><font class='text12'>Please re-enter the information again.</blockquote></font>";
		}
	} else {
		$top_text = "";
		$header_text = "Change Password";
		$msg_text = "";
	}

	employee_menu_header(true, $top_text, $header_text, $key);
	
	// if there's a problem or this is the first time running the screen, print out the form
	if ($cancel_form == false) {
		echo "<form action='" . $PHP_SELF . "' method=get><blockquote>";
		echo "<input type='hidden' name='action' value='employeepassword'>";
		echo "<input type='hidden' name='complete' value='1'>";
		echo "<input type='hidden' name='key' value='" . $key . "'>";
		echo "<center><table><tr>";
		echo "<td class='text12bold' align='right'>Old Password:</td>";
		echo "<td class='text12'><input name='oldpassword' type='password' size=30 class='boxtext13'></td>";
		echo "</tr></tr>";
		echo "<td class='text12bold' align='right'>New Password:</td>";
		echo "<td class='text12'><input name='password' type='password' size=30 class='boxtext13'></td>";
		echo "</tr></tr>";
		echo "<td class='text12bold' align='right'>New Password Again:</td>";
		echo "<td class='text12'><input name='passwordagain' type='password' size=30 class='boxtext13'></td>";
		echo "</tr>";
		echo "<tr><td class='text12' colspan=2>";
		echo "<center><input type=image src='images/update.jpg' width=88 height=27 border=0></center>";
		echo "</td></tr></table></center>";
		echo "</blockquote></form>";
	}
}

// completes erases an employee and all their assignments
function employee_admin_erase($key) {
	global $complete;
	global $my_emp_id;
	global $emp_db;

	if ($my_emp_id == $key) {
		employee_menu_header(true,"","<font color='#ff0033'>ERROR: Cannot Erase Yourself</font>",$key);
		employee_tabs($key);
		employee_print_info($key);
		echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
		echo "<blockquote><font class='text12'><font color='#ff0033'><b>For security reasons, you cannot erase yourself.</b></font></font></blockquote>";
		echo "<p></td></tr></table>";
		echo "<p><center>";
		echo "<a href='" . $PHP_SELF . "?action=employees'><img src='images/next.jpg' width=88 height=27 border=0></a>";
		echo "</center>";
		echo "<p><br>";
	} else {
		if ($complete == "1") {
			employee_menu_header(true,"","Erase Employee","");
			$result = doSql("DELETE FROM Assignments WHERE EmployeeId=" . $key);
			$result = doSql("DELETE FROM " . $emp_db . "Employees WHERE Id=" . $key);
			echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
			echo "<blockquote><font class='text12'>Employee Erased Successfully</font></blockquote>";
			echo "<p></td></tr></table>";
			echo "<p><center>";
			echo "<a href='" . $PHP_SELF . "?action=employees'><img src='images/next.jpg' width=88 height=27 border=0></a>";
			echo "</center>";
			echo "<p><br>";
		} else {
			employee_menu_header(true,"","Erase Employee",$key);
			employee_tabs($key);
			employee_print_info($key);
			echo "<table width=100% bgcolor='#ffdddd'><tr><td><br>";
			echo "<blockquote><font class='text12'><font color='#ff0033'><b>WARNING!</b></font> This will erase all traces of the employee including all transfers and sign outs.  Are you sure you want to do this?</font></blockquote>";
			echo "<p></td></tr></table>";
			echo "<p><center>";
			echo "<a href='" . $PHP_SELF . "?action=employeeerase&key=" . $key . "&complete=1'><img src='images/yes.jpg' width=88 height=27 border=0></a>";
			echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $key . "'><img src='images/no.jpg' width=88 height=27 border=0></a>";
			echo "</center>";
			echo "<p><br>";
		}
	}
}
?>
