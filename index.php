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

$version = 0.2;
$versiondate = "Saturday, October 11th, 2003";

// required to make SimpleAssets run in PHP 4.2.0 and up
include 'patch.php';
include 'config.php';

if (strlen($sql_db) == 0) $sql_db = str_replace("/","",strrchr(str_replace("/index.php","",$PHP_SELF),"/"));

include 'setup.php';

include 'asset_actions.php';
include 'asset_admin.php';
include 'asset_admin_transfer.php';
include 'asset_import.php';
include 'employee_actions.php';
include 'employee_admin.php';
include 'employee_import.php';
include 'license_actions.php';
include 'license_admin.php';
include 'reports.php';

include 'calendar.php';
include 'help.php';
include 'search.php';
include 'misc.php';

global $action, $key, $lastaction, $lastkey; // requested and stored action and key
global $login, $pass, $loginfail, $loginout; // login and logout values
global $oldpass, $passagain; // extra change password values
global $print; // print flag
global $QUERY_STRING; // entire query string
global $HTTP_SESSION_VARS;
global $mylogin, $mypass;
global $my_access_level;

// FORM CLEANUP
// clear extra html form characters from the key
$key = dehtml($key);
$lastkey = dehtml($lastkey); 

// SET PRINT SCREEN
// set whether we are printing the screen
if ($print == "1") $print_screen = true;
else $print_screen = false;


// CONNECT TO DB
// attempt to establish a connection with the server and database
if (strlen($sql_db) < 1) $sql_db = "SimpleAssets";
$server_status = db_connect($ip,$sql_login,$sql_pass);
if ($server_status) $db_status = db_select($sql_db);

// JUMP TO EXCEL REPORT
if (strcmp($action,"reportsassets") == 0) {
	reports_assets();
	exit;
}
if (strcmp($action,"reportslicensessummary") == 0) {
	reports_licenses_summary();
	exit;
}
if (strcmp($action,"reportslicensesdetailed") == 0) {
	reports_licenses_detailed();
	exit;
}

if (strcmp($action,"reportsemployees") == 0) {
	reports_employees();
	exit;
}

if (strcmp($action,"reportsindividual") == 0) {
	reports_individual($key);
	exit;
}

if ((strcmp($action,"login") == 0) && ($my_access_level > 0)) $action = "";
if ((strcmp($action,"setup") == 0)) $action = "";

// CHECK FOR NO DB
// set the setup flag if no db exists or its userbase is empty
if ((!$server_status) || (!$db_status)) {
	$setup = true;
	if (!(in_array($action,Array("help","helpfaq","helpprocesses","helpfeatures","helpversion")))) {
		$action = "setup";
	}
} else {
	// LOGIN USER
	// obtain the logged in employee id

	$my_emp_id = login();
	
	// display login box for users who have not logged in and are not setting up
	if (($my_access_level == 0) && (strcmp($action,"employeeregister") != 0)) {
		$lastaction = $action;
		$lastkey = $key;
		$action = "login";
	} else {
		if (strcmp($action,"login") == 0) $action = "";
	}
}


// default colors (blue)
$header_ext = "_blue";
$hrcolor = "#4ec8f0";

// assets (orange)
if (in_array($action,Array("assets","assetos","assetsupplier","surplus","retired","assetview","assetupdate","assetinsert","assettransfer","assettransfererase","assettransfersignin","assetcalendar","assetapprovals"))) {
	$header_ext = "_orange";
	$hrcolor = "#eea872";
}
// employees (red)
if (in_array($action,Array("employees","employeeregister","employeeview","employeeinsert","employeeinsertcomplete","employeeupdate","employeeupdatecomplete","employeepassword"))) {
	$header_ext = "_red";
	$hrcolor = "#fa0e31";
}
// licenses (purple)
if (in_array($action,Array("licenses","licensequery","licenseview","licensedelete","licenseinsert"))) {
	$header_ext = "_purple";
	$hrcolor = "#d772ee";
}
// reports (green)
if (in_array($action,Array("reports","reportssignout","reportsip","reportsverify"))) {
	$header_ext = "_green";
	$hrcolor = "#61ef8f";
}

// used for internal purposes
// include '../network.html';

// print out html headers
echo "<html>\n";
echo "<head>\n";
echo "<title>ITs Assets :: " . $org_name . "</title>\n";
?>
	<script language="Javascript">
		function openwin() {
			myWindow = window.open('<? echo $PHP_SELF . "?" . $QUERY_STRING . "&print=1"; ?>', 'tinyWindow', 'scrollbars=yes,toolbar=no,width=600,height=400') 
		}
	</script>
	<script>
	<!--
		if(navigator.appName.indexOf("Netscape")!=-1)  
		   document.writeln("<LINK REL='Stylesheet' TYPE='text/css' HREF='css/ns.css'>\n");
		else
		   document.writeln("<LINK REL='Stylesheet' TYPE='text/css' HREF='css/ie.css'>\n");
	//-->
	</script>
	<noscript><link rel='stylesheet' TYPE='text/css' HREF='css/ie.css'></noscript>
<?
echo "</head>\n";

if ($print_screen == false) echo "<body marginwidth=0 marginheight=0 topmargin=0 leftmargin=0 rightmargin=0 bottommargin=0 border=0 link='#0000cc' vlink='#0000cc' alink='#0000cc'>\n";
else echo "<body marginwidth=0 marginheight=0 topmargin=0 leftmargin=0 border=0 link='#0000cc' vlink='#0000cc' alink='#0000cc' onload='window.print()'>\n";

//==========================================================
//==========================================================
// 1. draw the top header menus
//==========================================================
//==========================================================

if ($print_screen == false) {	


	/////////////////////////////////////////////////////////////////
	// draw the top menu and the graphical header (BLACK AND GRAPHIC)
 	
	echo "<table width=100% cellspacing=0 cellpadding=0 border=0 bgcolor='#FFFFFF'>";
	echo "<tr>";
//	echo "<td><img src='images/right_head_top" . $header_ext . ".jpg' width=439 height=27 usemap='#Map' border=0></td>";
	echo "<td align=left ><a href='" . $web_url . "'><img src='images/blue.png' border=0 height=20 width=108 border=0 ></a>";
	echo "<a href='" . $PHP_SELF . "?action=employees'><img src='images/red.png' height=20 width=108 border=0 ></a>";
	echo "<a href='" . $PHP_SELF . "?action=assets'><img src='images/orange.png' height=20 width=108 border=0 ></a>";
	echo "<a href='" . $PHP_SELF . "?action=licenses'><img src='images/purple.png' height=20 width=108 border=0 ></a>";
	echo "<a href='" . $PHP_SELF . "?action=reports'><img src='images/green.png' height=20 width=108  border=0 ></a></td>";
	echo "</tr><tr>";
	echo "<td width=100% background='images/center_head" . $header_ext . ".jpg'><a href='" . $web_url . "'><img src='images/left_head" . $header_ext . ".jpg' width=312 height=58 border=0></a></td>";
	echo "<td><img src='images/right_head" . $header_ext . ".jpg' width=439 height=58></td></tr></table>";

	/////////////////////////////////////
	// draw the admin menu (BLUE)
	
	if ($my_access_level > 0) {
		echo "<table width=100% cellspacing=0 cellpadding=0 border=0 bgcolor='#FFFFFF'><tr><td>";
		echo "<table width=100% bgcolor='#FFFFFF'><tr>";
		$msg = getMsg();
		if (($my_access_level > 1) && (strcmp($msg,"you have no new messages.") == 0)) {
			echo "<td align='left'><font class='text10bold'' color='" . $hrcolor . "'><b>&nbsp;new:&nbsp;";
			echo " <a href='" . $PHP_SELF . "?action=assetinsert' class='text10bold'><font color='" . $hrcolor . "'>asset</font></a> &middot;";
			echo " <a href='" . $PHP_SELF . "?action=employeeinsert' class='text10bold'><font color='" . $hrcolor . "'>employee</font></a> &middot;";
			echo " <a href='" . $PHP_SELF . "?action=licenseinsert' class='text10bold'><font color='" . $hrcolor . "'>license</font></a> ";
			echo "</b></font><font class='text12'>&nbsp;</font></td>";
		} else {
			echo "<td align='left'><font class='text10bold' color='" . $hrcolor . "'><b>&nbsp;" . $msg . " &nbsp;";
			echo "</b></font></td>";
		}
		echo "<td align='right'><font class='text10bold' color='#ffffff'><b>" . stripslashes($activelogin) . ":&nbsp;";
		if ($my_access_level > 1) echo " <a href='" . $PHP_SELF . "?action=assetapprovals' class='text10bold'><font color='" . $hrcolor . "'>approve transfers</font></a> &middot;";
		else echo " ";
		echo " <a href='" . $PHP_SELF . "?action=employeeview&key=" . $my_emp_id . "' class='text10bold'><font color='" . $hrcolor . "'>view my assets</font></a> &middot;";
		echo " <a href='" . $PHP_SELF . "?action=employeeupdate&key=" . $my_emp_id . "' class='text10bold'><font color='" . $hrcolor . "'>edit my profile</font></a> &middot;";
		echo " <a href='" . $PHP_SELF . "?action=employeepassword&key=" . $my_emp_id . "' class='text10bold'><font color='" . $hrcolor . "'>change my password</font></a>";
		echo "</b></font>";
		echo "</td></tr></table>\n";
		echo "</td></tr></table>\n";
	} else {
		echo "<table width=100% cellspacing=0 cellpadding=0 border=0 bgcolor='#FFFFFF'><tr><td>";
		echo "<img src='images/spacer.gif' width=100 height=7>";
		echo "</td></tr></table>\n";
	}
	
	/////////////////////////////////////
	// draw the top search box (GREY)
	
	if ($setup == false) {
		$searchbox_color = "#FFFFFF";
		if (strcmp($action,"search") == 0) $search_key = $key;
		else $search_key = "";
		echo "<table width=100% cellspacing=0 cellpadding=0 border=0 bgcolor='" . $searchbox_color . "'><tr><td>";
		echo "<center>";
		echo "<table bgcolor='" . $searchbox_color . "'><tr>";
		echo "<td><form action='" . $PHP_SELF . "' method='get'><img src='images/search.png' width=20 height=20></td>";
		echo "<td><input type='hidden' name='action' value='search'><input name='key' type='text' value=\"" . q_replace($search_key) . "\" size=30 class='boxtext13'>";
		echo "</td><td><input type='image' name='submit' src='images/go_48.png' width=20 height=20 border=0><table cellspacing=0 cellpadding=0 border=0><tr><td></form></td></tr></table>";
		echo"</td></tr></table></center>";
		echo "</td></tr></table>";
	}
}

//==========================================================
//==========================================================
// 2. draw the centre portion based on the action
//==========================================================
//==========================================================
echo "<p><table width=100% cellspacing=10><tr><td>";
switch($action) {

//////////////////////////////////
// main features (read-only)

	// provides a listing of assets, associated to surplus
	case "surplus":
		employee_view(-1);
		break;
	// provides a listing of assets, which have been retired
	case "retired":
		employee_view(-2);
		break;	
	// shows the search screen
	case "search":
		search($key);
		break;
	// shows the login box
	case "login":
		loginbox($key);
		break;

//////////////////////////////////
// asset-based features

	// provides a listing of assets, by type
	case "assets":
		if (strlen($key) > 0) {
			asset_menu_header(true,"",stripslashes($key),"");
			asset_query($key, "assettype", "asset");
			if ($print_screen == false) echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
		} else {
			asset_menu_header(false,"","Assets","");
			asset_summary("AssetType","asset type");
		}
		break;
	// provides a listing of assets, by supplier
	case "assetsupplier":
		if (strlen($key) > 0) {
			asset_menu_header(true,"Suppliers",stripslashes($key),"");
			asset_query($key, "assetsupplier", "supplier");
			if ($print_screen == false) echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
		} else {
			asset_menu_header(false,"","Assets","");
			asset_summary("AssetSupplier","supplier");
		}
		break;
	// provides a listing of assets, by operating systems
	case "assetos":
		if (strlen($key) > 0) {
			asset_menu_header(true,"Operating System",stripslashes($key),"");
			asset_query($key, "os", "operating system");
			if ($print_screen == false) echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
		} else {
			asset_menu_header(false,"","Assets","");
			asset_summary("os","operating system");
		}
		break;
	// shows only one asset based on a given key
	case "assetview":
		asset_view($key);
		if ($print_screen == false) echo "<p><center><a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a></center>\n";
		break;
	// shows the asset update form, based on a key
	case "assetupdate":
		if ((asset_get_empid($key) == $my_emp_id) || ($my_access_level > 1)) asset_admin($key, false);
		else securityError();
		break;
	// shows the blank asset insert form, based on a key
	case "assetinsert":
		if ($my_access_level > 0) asset_admin($key, true);
		else securityError();
		break;
	// shows the screen where assets can be linked to employees
	case "assettransfer":
		if ($my_access_level > 0) asset_admin_transfer($key);
		else securityError();
		break;
	// deletes a given transfer
	case "assettransfererase":
		if ($my_access_level > 0) asset_admin_transfer_erase($key);
		else securityError();
		break;
	case "assettransfersignin":
		if ($my_access_level > 0) asset_admin_transfer_sign_in($key);
		else securityError();
		break;
	// shows the asset calendar
	case "assetcalendar":
		if ($my_access_level > 0) asset_admin_calendar($key);
		else securityError();
		break;
	// view approvals screen
	case "assetapprovals":
		if ($my_access_level > 1) asset_admin_transfer_approvals($key);
		else securityError();
		break;
	// shows the dates asset mgmt screen for one asset
	case "assetdates":
		asset_admin_dates($key);
		break;
	// delete an employee
	case "asseterase":
		if ($my_access_level > 1) asset_admin_erase($key);
		else securityError();
		break;

//////////////////////////////////
// employee-based features

	// shows a summary listing of all employees
	case "employees":
		employee_summary();
		break;
	// provides a summary of employees or views just one employee
	case "employeeview":
		if ($my_access_level > 0) employee_view($key);
		else securityError();
		break;

	// provides a summary of employees or views just one employee (force login)
	case "employeeviewlogin":
		if ($my_access_level > 0) employee_view($key);
		else securityError();
		break;
	// shows the blank employee insert form, based on a key
	case "employeeinsert":
		if ($my_access_level != 1) employee_admin($key, true, false);
		else securityError();
		break;
	case "employeeregister":
		if ($my_access_level != 1) employee_admin($key, true, true);
		else securityError();
		break;
	// shows the employee update form, based on a key
	case "employeeupdate":
		if (($my_access_level > 1) || (($my_access_level > 0) && ($my_emp_id == $key))) employee_admin($key, false, false);
		else securityError();
		break;
	// shows the screen where a user can change their password
	case "employeepassword":
		if (($my_access_level > 0) && ($my_emp_id == $key)) employee_admin_change_password($my_emp_id);
		else securityError();
		break;
	// delete an employee
	case "employeeerase":
		if ($my_access_level > 1) employee_admin_erase($key);
		else securityError();
		break;
	

//////////////////////////////////
// license-based features

	// all manufacturers
	case "licenses":
		license_summary();
		break;
	// all products for one manufacturers
	case "licensequery":
		license_query($key);
		break;
	// all licenses for one product
	case "licenseview":
		license_view($key);
		break;
	// create a new license
	case "licenseinsert":
		if ($my_access_level > 1) license_admin();
		else securityError();
		break;

//////////////////////////////////
// reports

	// summary report
	case "reports":
		reports_summary();
		break;
	case "reportssignout":
		reports_sign_out();
		break;	
	case "reportsip":
		if ($my_access_level > 1) reports_ip();
		else securityError();
		break;
	case "reportsverify":
		reports_verify();
		break;
		
//////////////////////////////////
// help

	// shows the help screen
	case "help":
		help($key);
		break;
	// shows the FAQ for admins
	case "helpfaq":
		if (($my_access_level > 1) || ($setup == true)) help_faq_admin($key);
		else help_faq_user($key);
		break;
	// shows the page describing misc process for admins
	case "helpprocesses":
		if (($my_access_level > 1) || ($setup == true)) help_processes_admin($key);
		else help_processes_user($key);
		break;
	// shows features
	case "helpfeatures":
		help_features($key);
		break;
	// shows version information
	case "helpversion":
		help_version($key);
		break;

//////////////////////////////////
// import

	// imports menu
	case "import":
		if ($my_access_level > 1) import();
		else securityError();
		break;
	// imports employees
	case "employeeimport":
		if ($my_access_level > 1) employee_import();
		else securityError();
		break;
	// imports assets
	case "assetimport":
		if ($my_access_level > 1) asset_import();
		else securityError();
		break;
		
//////////////////////////////////
// general setup

	// setup in cases when this is the first time run
	case "setup":
		setup();
		break;

//////////////////////////////////
// general browse

	// the home page - browse screen
	case "":
		menu_header("","Browse","folder.jpg");
		echo "<table width=100%><tr><td valign='top' width=50%>\n";
	
		doStylise("assettype", "Assets", "assets");
	//	doStylise("os", "Operating Systems", "assetos");
		doStylise("retired", "Retired", "retired");
		doStylise("surplus", "Surplus", "surplus");
	
		echo "</td>\n";
		echo "<td width=15>&nbsp;</td>\n";
		echo "<td valign='top' width=50%>\n";

		doStylise("summary", "Employees", "employees");
		doStylise("assetsupplier", "Manufacturers", "assetsupplier");
		doStylise("licenses", "Licenses", "licenses");
		doStylise("reports", "Reports", "reports");

		echo "</td></tr></table>\n";
		break;

	default:
		echo "<p><font class=text18bold color='#ff0033'>ERROR: Action Not Found</font><hr size=0 color='" . $hrcolor . "'>";
		echo "<p><blockquote><font class='text12'>The action you attempted to perform does not exist.</blockquote></font>";

}

if ($print_screen == false) {
	echo "<p><hr size=0 color='#cccccc'>";
	echo "<center>";
	if ($my_access_level > 0) {
		echo "<font class='text10'>[ ";
		echo "<a href='" . $web_url . "' class='text10'>Home</a> | ";
		echo "<a href='" . $PHP_SELF . "?action=employees' class='text10'>Employees</a> | ";
		echo "<a href='" . $PHP_SELF . "?action=assets' class='text10'>Assets</a> | ";
		echo "<a href='" . $PHP_SELF . "?action=licenses' class='text10'>Licenses</a> | ";
		echo "<a href='" . $PHP_SELF . "?action=reports' class='text10'>Reports</a> | ";
		echo "<a href='" . $PHP_SELF . "?action=help' class='text10'>Help</a> ]</font>";
		echo "<br>";
	}
	echo "<p><font class='text10bold'>Questions? Problems? Comments? E-Mail " . $contact_name . " at: <a href='mailto:" . $contact_email . "' class='text10bold'>" . $contact_email . "</a></font>";
	echo "<p><br>&nbsp;";
	echo "</center>";
}
echo "</td></tr></table>\n";
echo "</body>\n";
echo "<map name='Map'>";
echo "<area shape='rect' coords='78,5,163,26' href='" . $PHP_SELF . "?action=employees'>";
echo "<area shape='rect' coords='174,6,231,30' href='" . $PHP_SELF . "?action=assets'>";
echo "<area shape='rect' coords='236,5,301,33' href='" . $PHP_SELF . "?action=licenses'>";
echo "<area shape='rect' coords='312,6,375,35' href='" . $PHP_SELF . "?action=reports'>";
echo "<area shape='rect' coords='386,5,439,37' href='" . $web_url . "'>";
echo "</map>";
echo "</html>\n";

?>

