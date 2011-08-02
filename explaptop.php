<?

//
#  Expired Laptops Report
#  Written by Darrell James Breeden (dj.breeden@ossfb.com) for SATO America
#
#  Released 08-OCT 2010
# This program is free software licensed under the 
# 	GNU General Public License (GPL).
#
#	This file is part of Its Assets.
#
#	SimpleAssets is free software; you can redistribute it and/or modify
#	it under the terms of the GNU General Public License as published by
#	the Free Software Foundation; either version 2 of the License, or
#	(at your option) any later version.
#
#	SimpleAssets is distributed in the hope that it will be useful,
#	but WITHOUT ANY WARRANTY; without even the implied warranty of
#	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	GNU General Public License for more details.
#
#	You should have received a copy of the GNU General Public License
#	along with SimpleAssets; if not, write to the Free Software
#	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA   
#	02111-1307  USA
//  
                                 
  $con = mysql_connect("localhost","root","louro123");
  if (!$con) {                                        
    die('Could not connect: ' . mysql_error());       
  }                                                   

  mysql_select_db("simpleassets", $con);


$query="SELECT Assets.name,Assets.AssetTag,Assets.AssetSupplier,Assets.AssetModel,CONCAT(Employees.FirstName,', ',Employees.LastName) AS Employee, date(Assets.exp_date) AS Edate FROM Assets INNER JOIN Assignments on Assignments.AssetId = Assets.Id INNER JOIN Employees on Employees.Id = Assignments.EmployeeId WHERE Assignments.EndDate = 0 AND  date(Assets.exp_date) <= curdate() AND Assets.AssetType='Laptop' ORDER BY Assets.name";

$result = mysql_query($query);
if (!$result) {
    die("Query to show fields from table failed");
}

$fields_num = mysql_num_fields($result);

echo "<h1>Laptop Requiring Replacement {$table}</h1>";
echo "<table border='1'><tr>";
// printing table headers
for($i=0; $i<$fields_num; $i++)
{
    $field = mysql_fetch_field($result);
    echo "<td>{$field->name}</td>";
}
echo "</tr>\n";
// printing table rows
while($row = mysql_fetch_row($result))
{
    echo "<tr>";

    // $row is array... foreach( .. ) puts every element
    // of $row to $cell variable
    foreach($row as $cell)
        echo "<td>$cell</td>";

    echo "</tr>\n";
}
mysql_free_result($result);


  mysql_close($con);
?>
