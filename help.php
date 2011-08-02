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
// help($key)
// help_faq_user($key)
// help_faq_admin($key)
// help_admin_user($key)
// help_features($key)
// help_processes_admin($key)
// help_processes_user($key)
// help_version($key)

// help main screen
function help($key) {
	global $print_screen;
	global $my_access_level;
	global $hrcolor;

	$PHP_SELF = $_SERVER['PHP_SELF'];
	menu_header("","Help","help.jpg");

	echo "<blockquote>";
	echo "<font class='text12'>";
	echo "<ul>";
	echo "<li><a href='" . $PHP_SELF . "?action=helpfaq' class='text12bold'>Frequently Asked Questions</a>";
	echo "<li><a href='docs/manual.pdf' class='text12bold'>Download Manual</a>";
	echo "<li><a href='" . $PHP_SELF . "?action=helpprocesses' class='text12bold'>Common Processes</a>";
	echo "<li><a href='" . $PHP_SELF . "?action=helpfeatures' class='text12bold'>Features</a>";
	echo "<li><a href='" . $PHP_SELF . "?action=helpversion' class='text12bold'>Version Information</a>";
	echo "</ul>";
	echo "</font>";
	echo "</blockquote>";
}

// shows frequently asked questions (user version)
function help_faq_user($key) {
	global $print_screen;
	global $hrcolor;

	menu_header("<a href='" . $PHP_SELF . "?action=help' class='text10bold'>Help</a></font>","Frequently Asked Questions (FAQ)","help.jpg");

	echo "<blockquote>";
	echo "<p><font class='text18'>Assets</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>How do I add an asset to my asset listing?</b></font><br>";
	echo "<font class='text12'>If the asset is not in the system, you can add it by clicking 'New Asset'.  If the asset exists, you can find it by browsing the listings or searching for it.  Once it is found, click the Transfer tab to request a change of ownership.  The transfer request is then submitted for approval by the administrator.</font>";
	echo "<p><font class='text12'><b>How do I remove an asset from my listing?</b></font><br>";
	echo "<font class='text12'>To remove the asset from your listing, click the asset tag number to bring you to assets screen.  Click the transfer tab and request that the asset be transferred to 'General Assets' or 'Surplus'. The transfer request is then submitted for approval by the administrator.</font>";
	echo "</blockquote>";
	echo "<p><font class='text18'>Transfers</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>I have made an asset transfer but it does not show up?</b></font><br>";
	echo "<font class='text12'>All asset changes are done in the form of requests and must be approved by the administrator. Once a request is formally approved, the desired changes will appear on the site.</font>";
	echo "<p><font class='text12'><b>Why can I cancel some transfers and not others?</b></font><br>";
	echo "<font class='text12'>Transfers can be cancelled up until the day before the transfer is scheduled to begin.  Once the transfer has begun, you can choose to transfer the asset to someone else in the case of a transfer or for sign outs, you choose to sign the item in early.</font>";
	echo "<p><font class='text12'><b>Why can some dates not be selected when requesting a transfer?</b></font><br>";
	echo "<font class='text12'>Only dates on or after today can be selected.  Also, when selecting an end date for a sign out, only dates after the start date and before the next scheduled sign out date are available.</font>";
	echo "<p><font class='text12'><b>Why can some assets not be signed out?</b></font><br>";
	echo "<font class='text12'>Only assets designated as 'General Assets' or 'Surplus' can be signed out.</font>";
	echo "</blockquote>";
}

// shows frequently asked questions (admin version)
function help_faq_admin($key) {
	global $print_screen;
	global $hrcolor;

	menu_header("<a href='" . $PHP_SELF . "?action=help' class='text10bold'>Help</a></font>","Frequently Asked Questions (FAQ)","help.jpg");

	echo "<blockquote>";
	echo "<p><font class='text18'>Assets</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>How do I add an asset to an employee's asset listing?</b></font><br>";
	echo "<font class='text12'>If the asset is not in the system, you can add it by clicking 'New Asset'.  If the asset exists, you can find it by browsing the listings or searching for it.  Once it is found, click the Transfer tab to request a change of ownership.  The transfer request is then submitted for approval by the administrator.</font>";
	echo "</blockquote>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>How do I remove an asset from an employee's asset listing?</b></font><br>";
	echo "<font class='text12'>To remove the asset from your listing, click the asset tag number to bring you to assets screen.  Click the transfer tab and request that the asset be transferred to 'General Assets' or 'Surplus'. The transfer request is then submitted for approval by the administrator.</font>";
	echo "</blockquote>";
	echo "<p><font class='text18'>Employees</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>What happens to the assets of an inactive employee?</b></font><br>";
	echo "<font class='text12'>Assets assigned to employees who are inactive remain assigned to them until you choose to make changes. To keep the information accurate, be sure to restore items from departed employees to surplus.</font>";
	echo "</blockquote>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>How do I delete an Administrator?</b></font><br>";
	echo "<font class='text12'>There must always be one administrator on the system.  To erase an administrator, ensure there are at least two administrators.  Since an administrator cannot erase or deactivate themselves, contact another administrator and have them deactivate or erase the desired 'admin' account.</font>";
	echo "</blockquote>";
	echo "<p><font class='text18'>Transfers</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>How do I reassign assets to different employees, to a general asset, or to surplus?</b></font><br>";
	echo "<font class='text12'>To reassign an asset, first locate the asset by either searching for it or browsing through the listings. Once you have located the asset you wish to reassign, use the transfer link next to it and follow the short wizard to make the desired changes.</font>";
	echo "<p><font class='text12'><b>Why can I cancel some transfers and not others?</b></font><br>";
	echo "<font class='text12'>Transfers can be cancelled up until the day before the transfer is scheduled to begin.  Once the transfer has begun, you can choose to transfer the asset to someone else in the case of a transfer or for sign outs, you choose to sign the item in early.</font>";
	echo "<p><font class='text12'><b>Why can some dates not be selected when performing a transfer?</b></font><br>";
	echo "<font class='text12'>Only dates on or after today can be selected.  Also, when selecting an end date for a sign out, only dates after the start date and before the next scheduled sign out date are available.</font>";
	echo "<p><font class='text12'><b>Why can some assets not be signed out?</b></font><br>";
	echo "<font class='text12'>Only assets designated as 'General Assets' or 'Surplus' can be signed out.</font>";
	echo "<p><font class='text12'><b>How do I make corrections to transfers?</b></font><br>";
	echo "<font class='text12'>For transfers, simply cancel the incorrect transfer and create a new transfer with the updated transfer date.</font>";
	echo "</blockquote>";
	echo "<p><font class='text18'>Licenses</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>How do I update the number of licenses for a piece of software?</b></font><br>";
	echo "<font class='text12'>Click 'New License' and select the existing product from the dropdown menu, then fill out the date, quantity and cost.  The new license will automatically be associated with the product.</font>";
	echo "<p><font class='text12'><b>How do I make corrections to licenses?</b></font><br>";
	echo "<font class='text12'>To correct errors in licensing information, the license must be erased and then re-entered.</font>";
	echo "</blockquote>";
	echo "<p><font class='text18'>Installation</font><br>";
	echo "<blockquote>";
	echo "<p><font class='text12'><b>What do I do if the installation screen continues to ask to be reloaded ?</b></font><br>";
	echo "<font class='text12'>This means your MySQL server is not configured properly.  By default, MySQL must be on the same server as your web server and scripting and be running on Port 3306 with full privileges for user 'root' and no password.</font>";
	echo "<p><font class='text12'><b>What do I do if the installation fails?</b></font><br>";
	echo "<font class='text12'>If the installation fails, attempt to run it again.  If it keeps failing, log in manually to MySQL and delete the database it created usually titled the same as last part of the url of this software.  Be sure you are deleting the correct database by verifying the table names.  Table names to look for include 'Assignments', 'Employees', 'Assets', 'Licenses' and, 'Msgs'.  Run the installation again.</font>";
	echo "</blockquote>";
	echo "</blockquote>";
}

// shows the features of this software
function help_features($key) {
	global $print_screen;
	global $hrcolor;

	menu_header("<a href='" . $PHP_SELF . "?action=help' class='text10bold'>Help</a></font>","Features","help.jpg");

	echo "<blockquote>";
	echo "<font class='text12'>";
	echo "<p><b>Regular User Features</b>";
	echo "<ul>";
	echo "<li>Browse / View / Search Employees, Assets and Licenses";
	echo "<li>New Employee (via Register)";
	echo "<li>Update Your Own Employee Details";
	echo "<li>Employee History";
	echo "<li>New Asset";
	echo "<li>Request Transfers (including postdated)";
	echo "<li>Sign In / Sign Out of 'General Assets' and 'Surplus'";
	echo "<li>Asset Calendar / History";
	echo "<li>Assets and Licenses Report (HTML Spreadsheet - Import into Excel)";
	echo "<li>Sign Out Report";
	echo "<li>Confirmation Report (without e-mail capability)";
	echo "<li>Print";
	echo "<li>Help";
	echo "</ul>";
	echo "<p><b>Administrator Features</b>";	
	echo "<ul>";
	echo "<li>Update Any Employee's Details";
	echo "<li>Change Employee Access Level or Reset Password";
	echo "<li>Update Asset";
	echo "<li>Approve and Perform Transfers";
	echo "<li>Erase Employees, Assets, Licenses and Sign Outs";
	echo "<li>Add/Remove Licenses";
	echo "<li>Add/Remove IPs";
	echo "<li>Employees Report (HTML Spreadsheet - Import into Excel)";
	echo "<li>Individual Employee Report (HTML Spreadsheet - Import into Excel)";
	echo "<li>IP Report";
	echo "<li>Confirmation Report (with e-mail capability)";
	echo "<li>Setup (On First Run Only)";
	echo "<li>Importing Employees and Asset from a spreadsheet or database";
	echo "</ul>";
	echo "<p>* each access level includes all the features of the one above it.";
	echo "</blockquote>";
}

// shows commonly used processes (admin)
function help_processes_admin($key) {
	global $print_screen;
	global $hrcolor;

	menu_header("<a href='" . $PHP_SELF . "?action=help' class='text10bold'>Help</a></font>","Common Processes","help.jpg");

	echo "<blockquote>";
	echo "<font class='text12'>";
	echo "<p><b>A new asset is received</b>";
	echo "<ul>";
	echo "<li>Purchaser receives the equipment and informs the Administrator";
	echo "<li>Administrator clicks 'New Asset' and enters the info into the system where it is intially assigned to general assets";
	echo "<li>Administrator assigns the asset to Employee";
	echo "<li>Purchaser, Employee or third party moves the asset to the employee's office";
	echo "</ul>";
	
	echo "<p><b>An asset becomes surplus</b>";
	echo "<ul>";
	echo "<li>Employee requests an 'Asset Transfer' to Surplus on the system";
	echo "<li>Administrator approves the request";
	echo "<li>Employee receives a confirmation message";
	echo "<li>Employee or third party moves asset into storage";
	echo "</ul>";
	
	echo "<p><b>An asset gets sent to NCR Warehouse</b>";
	echo "<ul>";
	echo "<li>Administrator sends an e-mail to <a href='mailto:NCR%20Warehouse'>'NCR Warehouse'</a> with surplus details";
	echo "<li>NCR Warehouse picks up the equipment";
	echo "<li>Administrator transfers the asset on the system to 'Retired'";
	echo "</ul>";
	
	echo "<p><b>An asset is signed out for a length of time</b>";
	echo "<ul>";
	echo "<li>Employee browses for valid dates and enters the sign in/sign out time on the system";
	echo "<li>Employee takes asset for specified time and returns it on the due date";
	echo "<li>Employee clicks 'Sign In' on the corresponding asset on the system on the system";
	echo "</ul>";
	
	echo "<p><b>An asset needs to be signed in</b>";
	echo "<ul>";
	echo "<li>Employee returns asset before or on scheduled due date";
	echo "<li>Employee clicks 'Sign In' on the corresponding asset on the system on the system";
	echo "</ul>";
	
	echo "<p><b>An asset changes ownership from one employee to another</b>";
	echo "<ul>";
	echo "<li>Employee requests an Asset Transfer on the system";
	echo "<li>Employee selects which licenses will remain with the asset and when the asset is to be transfered";
	echo "<li>Administrator approves the transfer";
	echo "<li>Employee receives confirmation of the transfer";
	echo "<li>Employee delivers/picks up asset to/from other employee";
	echo "</ul>";

	echo "<p><b>A new employee is hired</b>";
	echo "<ul>";
	echo "<li>Manager informs Administrator that a new employee is being hired";
	echo "<li>Administrator clicks 'New Employee' and enters the new information into the system";
	echo "</ul>";
	
	echo "<p><b>An employee leaves the organization</b>";
	echo "<ul>";
	echo "<li>Manager informs Administrator that an employee is leaving the organization";
	echo "<li>Administrator deactivates the Employee on system";
	echo "</ul>";
	
	echo "<p><b>A new license is purchased</b>";
	echo "<ul>";
	echo "<li>Purchaser informs Administrator of new license";
	echo "<li>Administrator enters new information into the system";
	echo "</ul>";
	
	echo "<p><b>A license is transferred out of the organization</b>";
	echo "<ul>";
	echo "<li>Employee informs Administrator of transfer";
	echo "<li>Administrator erases the software license";
	echo "</ul>";
	echo "</font>";
	echo "</blockquote>";
}

// shows commonly used processes (user)
function help_processes_user($key) {
	global $print_screen;
	global $hrcolor;

	menu_header("<a href='" . $PHP_SELF . "?action=help' class='text10bold'>Help</a></font>","Common Processes","help.jpg");

	echo "<blockquote>";
	echo "<font class='text12'>";
	echo "<p><b>An asset becomes surplus</b>";
	echo "<ul>";
	echo "<li>Employee requests an 'Asset Transfer' to Surplus on the system";
	echo "<li>Administrator approves the request";
	echo "<li>Employee receives a confirmation message";
	echo "<li>Employee or third party moves asset into storage";
	echo "</ul>";
	
	echo "<p><b>An asset is signed out for a length of time</b>";
	echo "<ul>";
	echo "<li>Employee browses for valid dates and enters the sign in/sign out time on the system";
	echo "<li>Employee takes asset for specified time and returns it on the due date";
	echo "<li>Employee clicks 'Sign In' on the corresponding asset on the system on the system";
	echo "</ul>";
	
	echo "<p><b>An asset needs to be signed in</b>";
	echo "<ul>";
	echo "<li>Employee returns asset before or on scheduled due date";
	echo "<li>Employee clicks 'Sign In' on the corresponding asset on the system on the system";
	echo "</ul>";
	
	echo "<p><b>An asset changes ownership from one employee to another</b>";
	echo "<ul>";
	echo "<li>Employee requests an Asset Transfer on the system";
	echo "<li>Employee selects which licenses will remain with the asset and when the asset is to be transfered";
	echo "<li>Administrator approves the transfer";
	echo "<li>Employee receives confirmation of the transfer";
	echo "<li>Employee delivers/picks up asset to/from other employee";
	echo "</ul>";
	
	echo "<p><b>A license is transferred out of the organization</b>";
	echo "<ul>";
	echo "<li>Employee informs Administrator of transfer";
	echo "<li>Administrator erases the software license";
	echo "</ul>";
	echo "</font>";
	echo "</blockquote>";
}

// shows commonly used processes
function help_version($key) {
	global $print_screen;
	global $hrcolor;
	global $version;
	global $versiondate;

	menu_header("<a href='" . $PHP_SELF . "?action=help' class='text10bold'>Help</a></font>","Version Information","help.jpg");

	echo "<img src='images/portage.jpg' width=450 height=190 align=right>";
	echo "<blockquote>";
	echo "<font class='text13'>";
	echo "<b>SimpleAssets Version " . $version . " Alpha</b><br>";
	echo "Released: " . $versiondate;
	echo "<p><b>Programming and Graphical Design by</b><br>";
	echo "Jeff Gordon, University of Ottawa Co-op Student (Summer 2002)<br>";
	echo "E-Mail: <a href='mailto:jgordon81@users.sourceforge.net'>jgordon81@users.sourceforge.net</a><br>";
	echo "Public Works and Government Services Canada (PWGSC)<br>";
	echo "Architecture and Standards Directorate<br>";
	echo "<p>SimpleAssets was created and released as part of a co-op assignment at GTIS, Government of Canada <a href='http://www.pwgsc.gc.ca/gtis/'>http://www.pwgsc.gc.ca/gtis/</a>.  The requirements team included Eric Mainville, Ian Reed, Richard Lessard, Robert MacPhail, and Mark Daniels.";
	echo "<p><b>Photo Location</b><br>";
	echo "Place du Portage Phase III, Hull Quebec Canada<br>";
	echo "Photographed by Jeff Gordon";
	echo "<p><b>Project Home Site</b><br>";
	echo "<a href='http://simpleassets.sourceforge.net/' class='text13bold'>http://simpleassets.sourceforge.net/</a>";
	echo "</font>";
	echo "</blockquote>";
}

?>
