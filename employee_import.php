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
// employee_import_get_index($field)
// employee_import_parse($submit)
// employee_import()

// assign an index to the imported columns
function employee_import_get_index($field) {
	$field = strtolower($field);
	$index = 0;
	if ((strcmp($field,"first name") == 0) || (strcmp($field,"firstname") == 0)) $index = 1;
	if ((strcmp($field,"last name") == 0) || (strcmp($field,"lastname") == 0)) $index = 2;
	if ((strcmp($field,"login name") == 0) || (strcmp($field,"loginname") == 0)) $index = 3;
	if (strcmp($field,"password") == 0) $index = 4;
	if ((strcmp($field,"telephone") == 0) || (strcmp($field,"tel") == 0)) $index = 5;
	if ((strcmp($field,"email") == 0) || (strcmp($field,"e-mail") == 0)) $index = 6;
	if (strcmp($field,"organization") == 0) $index = 7;
	if ((strcmp($field,"dept") == 0) || (strcmp($field,"department") == 0)) $index = 8;
	if (strcmp($field,"building") == 0) $index = 9;
	if (strcmp($field,"floor") == 0) $index = 10;
	if (strcmp($field,"workstation") == 0) $index = 11;
	if (strcmp($field,"name") == 0) $index = 12;
	if (strcmp($field,"access level") == 0) $index = 13;
	return $index;
}

function employee_import_parse($submit) {
	global $HTTP_POST_FILES;
	global $_FILES;
	global $emp_db;
	global $errmsg;
	
	$filename = $HTTP_POST_FILES['userfile']['tmp_name'];
	if ($filename == "") $filename = $_FILES['userfile']['tmp_name'];
	$import_fail = false;
	
	if ((strcmp($filename,"none") == 0) || (strcmp($filename,"") == 0)) {
		echo "<font class='text12bold' color='#ff0033'>No file was uploaded or your permissions for uploading files via PHP have been set incorrectly. Be sure the file_uploads = On line is set in the /etc/php.ini file and that you restart Apache after the change. PHP needs a writable area designated for uploads which may not have been set by default.  Ensure there is also a filename that appears in the submit box on the previous page.</font> ";
		$import_fail = true;
	} else {
		$errmsg = "";
		$fp = fopen($filename,"rb");

		// get fields
		$data = fgetcsv ($fp, 1000, ",");
		$num = count($data);
		for ($c=0; $c < $num; $c++) {
			$index = employee_import_get_index($data[$c]);
			$import_fields[$c] = $index;
			if ($index == 0) {
				$errmsg = $errmsg . "The field name '" . $data[$c] . "' is not recognized and was skipped. ";
			} else {
				$foundfield = true;
			}
		}

		// check if at least one field was found
		if ($foundfield == false) {
			echo "<font class='text12bold' color='#ff0033'>No field names were found.  This may not be a CSV file or you did not put the field names as the first row.</font> ";
			$import_fail = true;
		} else {
			// get data
			$row = 0;
			while ($data = fgetcsv ($fp, 1000, ",")) {
				$num = count($data);
				$row++;
				$is_data_flag[$row] = false;
				for ($c=0; $c < $num; $c++) {
					$newindex = $import_fields[$c];
					// if the current field is listed as 'name', seperate based on space or comma
					if ($newindex == 12) {
						if ($commapos = strpos($data[$c],",")) {
							$commapos--;
							if ($commapos < 0) $commapos = 0;
							$import_data[$row][1] = substr($data[$c],$commapos+3,$str_len-$commapos-1);
							$import_data[$row][2] = substr($data[$c],0,$commapos+1);
						} else {
							if ($spacepos = strpos($data[$c]," ")) {
								$import_data[$row][1] = substr($data[$c],0,$spacepos+1);
								$import_data[$row][2] = substr($data[$c],$spacepos+1,$str_len-$spacepos);
							} else {
								$import_data[$row][1] = $data[$c];
								$import_data[$row][2] = "";
							}
						}
					} else {
						$import_data[$row][$newindex] = $data[$c];
					}

					if (strlen($import_data[$row][$newindex]) > 0) $is_data_flag[$row] = true;
				}

				
			}
			fclose ($fp);
	
			// check for data
			if ($row < 1) {
				$errmsg = $errmsg . " No data was found in the file.";
				$import_fail = true;
			} else {
				// check for required fields	
				for ($r=1;$r<$row+1;$r++) {

					// check first names
					if (strlen($import_data[$r][1]) < 1) {
						$import_fail = true;
						$no_names = true;
					}
					// check login
					if (strlen($import_data[$r][3]) < 1) $import_data[$r][3] = substr($import_data[$r][1],0,1) . $import_data[$r][2];
					for ($y=1;$y<$row+1;$y++) {
						$result = doSql("SELECT " . $emp_db . "Employees.Id FROM " . $emp_db . "Employees WHERE LoginName='" . $import_data[$r][3] . "'");
						if ((mysql_numrows($result) > 0) || ((strcmp($import_data[$y][3],$import_data[$r][3]) == 0) && ($y != $r))) {
							$import_data[$r][3] = $import_data[$r][3] . "1";
							$y=1;
						}
					}
				}
				if ($no_names == true) $errmsg = $errmsg . " Some rows do not have a first name entered.";
			}
			
	

			if ($import_fail == true) {
				echo "<p><font class='text12bold' color='#ff0033'>" . $errmsg . "</font>";
			} else {
				if (strcmp($submit,"Upload") != 0) {
					// preview
					echo "<p><font class='text12'>" . $errmsg . " The following is a preview of the data to be imported.</font>";
					echo "<table border=1 width=100%>";
					echo "<tr>";
					echo "<td class='text9bold'>First Name</td>";
					echo "<td class='text9bold'>Last Name</td>";
					echo "<td class='text9bold'>Login Name</td>";
					echo "<td class='text9bold'>Telephone</td>";
					echo "<td class='text9bold'>E-Mail</td>";
					echo "<td class='text9bold'>Organization</td>";
					echo "<td class='text9bold'>Department</td>";
					echo "<td class='text9bold'>Building</td>";
					echo "<td class='text9bold'>Floor</td>";
					echo "<td class='text9bold'>Workstation</td>";
					echo "</tr>";

					for ($r=1;$r<$row+1;$r++) {
						echo "<tr>";
						for ($c=1;$c<12;$c++) {
							if ($is_data_flag[$r] == true) {
								if ($c == 4) $c++;
								echo "<td class='text9'>";
								if ($import_data[$r][$c] == "") echo "&nbsp;";
								echo $import_data[$r][$c];
								echo "</td>";
							}
						}
						echo "</tr>";
					}
					echo "</table>";
					echo "<p><center><font class='text13bold'>If this is correct, return to the previous page to complete the import.</font></center>";
				} else {
					// upload
					for ($r=1;$r<$row+1;$r++) {
						if ($is_data_flag[$r] == true) {
							$sql = "INSERT INTO " . $emp_db . "Employees (FirstName,LastName,LoginName,UserPass,Tel,EMail,Organization,Dept,Building,Floor,Workstation,Active,AccessLevel) VALUES (";
							for ($c=1;$c<12;$c++) {
								if ($c == 4) $sql = $sql . "Password('" . addslashes($import_data[$r][$c]) . "'),";
								else $sql = $sql . "'" . addslashes($import_data[$r][$c]) . "',";
							}
							$sql = $sql . "1,1)";
							if (!(doSql($sql))) $errmsg = $errmsg . "<p>Database Error: " . mysql_error() . "";
						}
					}
					echo "<p><font class='text12'>" . $errmsg . "</font>";
					if ($err == false) echo "<p><font class='text12'>The information has been imported successfully.</font>";
				}
			}


		}
	}


	echo "<p><center>";
	echo "<a href='javascript:history.back()'><img src='images/back.jpg' width=88 height=27 border=0></a>";
	echo "</center>";
	return 0;
}

function employee_import() {
	global $complete;
	global $filename;
	global $submit;
	global $emp_db;

	menu_header("","Import Employees","setup.jpg");

	if ($complete == "1") employee_import_parse($submit);

	if (($complete != "1") || ($errmsg != "")) {
		echo "<font class='text12'>";
		echo "<ul>";
		echo "<li>Enter or input the employee information into a database or spreadsheet program capable of exporting CSV (Comma Seperated Values) such as Excel, Access or Lotus 1-2-3.";
		echo "<p><li>Arrange the document so that the column names are in the first row in any order and the spelling matches the following listing. (You may omit any field except those listed as required). You may also <b>download</b> a sample template file <b><a href='templates/employeetemplate.csv'>here</a></b> and open it with any spreadsheet or database program such as Excel.";
	
		echo "<p><ul>";
		echo "<li>(<b>'First Name'</b> AND <b>'Last Name'</b>) OR just <b>'Name'</b> (which splits the name at the last space or the first comma). <font color='#ff0033'><b>(REQUIRED)</b></font>";
		echo "<li><b>'Login Name'</b> (if empty, they are autogenerated based on the employee's name)";
		echo "<li><b>'Password'</b>";
		echo "<li><b>'Telephone'</b>";
		echo "<li><b>'EMail'</b>";
		echo "<li><b>'Organization'</b>";
		echo "<li><b>'Department'</b>";
		echo "<li><b>'Building'</b>";
		echo "<li><b>'Floor'</b>";
		echo "<li><b>'Workstation'</b>";
		echo "</ul>";
		
		echo "<p><li>Once you have completed rearranging the file, export the file to CSV format via either File...Save As or File...Export";
		echo "<p><li>Upload the document here.  If you file is larger than 2MB, you'll need to break the file into multiple pieces, with each a size of 2MB or less.  Be sure to add the column names to the top of each file and perform the import wizard on each piece.";
		echo "<p><li>It is <b>STRONGLY</b> recommended to preview the file first as the upload command inserts the information directly into the database.";
		
		echo "<form enctype='multipart/form-data' action='" . $PHP_SELF . "?action=employeeimport&complete=1' method='post'>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='2048000'>";
		echo "Send this file: <input name='userfile' type='file'>";
		echo "<input type='submit' name='submit' value='Preview'>";
		echo "<input type='submit' name='submit' value='Upload'>";
		echo "</form>";
		echo "</font>";
	
		echo "</ul>";
	}
}


?>
