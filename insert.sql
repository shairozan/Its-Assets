$employee=@@employee_name;
$asset=@@chosen_asset;
$sdate=@@loan_date;
// End Dates Current Active Assignment
$update="UPDATE Assignments set EndDate = unix_timestamp(now()) where AssetId=$asset and EndDate=0";
// Inserts record into Assignments Table
$insert="INSERT INTO Assignments (EmployeeId,AssetId,StartDate,EndDate,Approve,Temp,Completed) VALUES($employee,$asset,unix_timestamp('$sdate'),0,0,0,0)";

$db='2431201164d1ca3403ca766047026944';

executeQuery( $update, $db);
executeQuery( $insert, $db);

