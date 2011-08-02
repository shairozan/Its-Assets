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
// asset_import_get_index($field)
// asset_import_parse($submit)
// asset_import()

// assign an index to the imported columns
function asset_import_get_index($field) {
	$field = strtolower($field);
	$index = 0;
	if ((strcmp($field,"first name") == 0) || (strcmp($field,"firstname") == 0)) $index = 1;
	if ((strcmp($field,"last name") == 0) || (strcmp($field,"lastname") == 0)) $index = 2;
	if ((strcmp($field,"asset tag") == 0) || (strcmp($field,"assettag") == 0)) $index = 3;
	if ((strcmp($field,"description") == 0) || (strcmp($field,"assettype") == 0)) $index = 4;
	if ((strcmp($field,"supplier") == 0) || (strcmp($field,"assetsupplier") == 0)) $index = 5;
	if ((strcmp($field,"model") == 0) || (strcmp($field,"assetmodel") == 0)) $index = 6;
	if ((strcmp($field,"serial") == 0) || (strcmp($field,"assetserial") == 0)) $index = 7;
	if ((strcmp($field,"purchase price") == 0) || (strcmp($field,"price") == 0) || (strcmp($field,"assetprice") == 0)) $index = 8;
	if (strcmp($field,"name") == 0) $index = 13;
	return $index;
}

function asset_import_parse($submit) {
	global $HTTP_POST_FILES;
	global $_FILES;
	global $emp_db;
	
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
			$index = asset_import_get_index($data[$c]);
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
					$str_len = strlen($data[$c]);
					if ($newindex == 13) {
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
				// auto generate fields
				$import_data[0][9] = "0";
				$import_data[0][10] = "General Assets";
				$import_data[0][11] = "";
				for ($r=1;$r<$row+1;$r++) {

					// random 6-digit asset tag
					srand(make_seed());
					if (strlen($import_data[$r][3]) < 1) $import_data[$r][3] = rand(100000,999999);

					// price
					if (strlen($import_data[$r][8]) < 1) {
						$import_data[$r][8] = "0";
					} else {
						$import_data[$r][8] = str_replace("$","",$import_data[$r][8]);
						$import_data[$r][8] = str_replace(",","",$import_data[$r][8]);
					}
					
					if (is_numeric($import_data[$r][8]) == false) $import_data[$r][8] = "0";
					
					// employee assignment
					if ((strlen($import_data[$r][1]) > 0) || ($import_data[$r][2] > 0)) { 
						$sql = "SELECT " . $emp_db . "Employees.Id, " . $emp_db . "Employees.FirstName, " . $emp_db . "Employees.LastName FROM " . $emp_db . "Employees WHERE " . $emp_db . "Employees.FirstName LIKE '" . $import_data[$r][1]  . "%%%' AND " . $emp_db . "Employees.LastName LIKE '" . $import_data[$r][2] . "%%%'";
						if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) {
							$import_data[$r][9] = $query_data["Id"];
							$import_data[$r][10] = $query_data["FirstName"];
							$import_data[$r][11] = $query_data["LastName"];
						} else {
							if (strtolower($import_data[$r][1]) == "surplus") {
								$import_data[$r][9] = "-1";
								$import_data[$r][10] = "Surplus";
								$import_data[$r][11] = "";
							} elseif (strtolower($import_data[$r][1]) == "retired") {
								$import_data[$r][9] = "-2";
								$import_data[$r][10] = "Retired";
								$import_data[$r][11] = "";
							} else {
								$import_data[$r][9] = "0";
								$import_data[$r][10] = "General Assets";
								$import_data[$r][11] = "";
							}
						}
					} else {
						$import_data[$r][9] = $import_data[($r-1)][9];
						$import_data[$r][10] = $import_data[($r-1)][10];
						$import_data[$r][11] = $import_data[($r-1)][11];
					}
				}
			}
			
			
			if ($import_fail == true) {
				echo "<p><font class='text12bold' color='#ff0033'>" . $errmsg . "</font>";
			} else {
				if (strcmp($submit,"Upload") != 0) {
					// preview
					echo "<p><font class='text12'>" . $errmsg . " The following is a preview of the data to be imported.</font>";
					echo "<table border=1 width=100%>";
					echo "<tr>";
					echo "<td class='text9bold'>Matched Name</td>";
					echo "<td class='text9bold'>First Name</td>";
					echo "<td class='text9bold'>Last Name</td>";
					echo "<td class='text9bold'>Asset Tag</td>";
					echo "<td class='text9bold'>Type</td>";
					echo "<td class='text9bold'>Supplier</td>";
					echo "<td class='text9bold'>Model</td>";
					echo "<td class='text9bold'>Serial</td>";
					echo "<td class='text9bold'>Purchase Price</td>";
					echo "</tr>";
					for ($r=1;$r<$row+1;$r++) {
						if ($is_data_flag[$r] == true) {
							echo "<tr>";
							echo "<td class='text9'>";
							echo "<a href='" . $PHP_SELF . "?action=employeeview&key=" . $import_data[$r][9] . "'>" . $import_data[$r][10] . " " . $import_data[$r][11] . "</a>";
							echo "</td>";
							for ($c=1;$c<9;$c++) {
								echo "<td class='text9'>";
								if ($import_data[$r][$c] == "") echo "&nbsp;";
								echo $import_data[$r][$c];
								echo "</td>";
							}
							echo "</tr>";
						}
					}
					echo "</table>";
					echo "<p><center><font class='text13bold'>If this is correct, return to the previous page to complete the import.</font></center>";
				} else {
					// upload
					for ($r=1;$r<$row+1;$r++) {
						if ($is_data_flag[$r] == true) {
							$sql = "INSERT INTO Assets (assettag,assettype,assetsupplier,assetmodel,assetserial,assetprice) VALUES (";
							for ($c=3;$c<8;$c++) {
								$sql = $sql . "'" . addslashes($import_data[$r][$c]) . "',";
							}
							$sql = $sql . addslashes($import_data[$r][9]) . ")";
							if (!(doSql($sql))) $errmsg = $errmsg . "<p>Database Error: " . mysql_error() . "";

							$sql = "SELECT last_insert_id() as lastid FROM Assets";
							if (($result = doSql($sql)) && ($query_data = mysql_fetch_array($result))) {
								$last_insert_id = $query_data["lastid"];
							}
							$day = date("d",time());
							$month = date("m",time());
							$year = date ("Y",time());
							$newstartdate = mktime(0,0,0,$month,$day,$year);
							$sql = "INSERT INTO Assignments (employeeid,assetid,startdate) VALUES (" . $import_data[$r][9] . "," . $last_insert_id . "," . $newstartdate . ")";
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

function asset_import() {
	global $complete;
	global $filename;
	global $submit;
	
	menu_header("","Import Assets","setup.jpg");

	if ($complete == "1") asset_import_parse($submit);

	if (($complete != "1") || ($errcode > 0)) {
		echo "<font class='text12'>";
		echo "<ul>";
		echo "<li>Enter or input the asset information into a database or spreadsheet program capable of exporting CSV (Comma Seperated Values) such as Excel, Access or Lotus 1-2-3.";
		echo "<p><li>Arrange the document so that the column names are in the first row in any order and the spelling matches the following listing. (You may omit any field except those listed as required).  You may also <b>download</b> a sample template file <b><a href='templates/assettemplate.csv'>here</a></b> and open it with any spreadsheet or database program such as Excel.";
	
		echo "<p><ul>";
		echo "<li><b>'First Name'</b> and <b>'Last Name'</b> or just <b>'Name'</b> (the name of the employee to which the asset is assigned.  If blank, the name directly in the previous row is used or it is assigned to general assets)";
		echo "<li><b>'Asset Tag'</b> (an unique ID identifying the asset.  If blank, the asset tag is autogenerated)";
		echo "<li><b>'Description'</b> (the asset type like Monitor, Printer, or Desktop)";
		echo "<li><b>'Supplier'</b> (the name of the manufacturer)";
		echo "<li><b>'Model'</b>";
		echo "<li><b>'Serial'</b> (the serial number)";
		echo "<li><b>'Purchase Price'</b>";
		echo "</ul>";

		echo "<p><li>Once you have completed rearranging the file, export the file to CSV format via either File...Save As or File...Export";
		echo "<p><li>Upload the document here.  If you file is larger than 2MB, you'll need to break the file into multiple pieces, with each a size of 2MB or less.  Be sure to add the column names to the top of each file and perform the import wizard on each piece.";
		echo "<p><li>It is <b>STRONGLY</b> recommended to preview the file first as the upload command inserts the information directly into the database.";
		
		echo "<form enctype='multipart/form-data' action='" . $PHP_SELF . "?action=assetimport&complete=1' method='post'>";
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
