<?
include 'misc.php';
include 'config.php';

if (strlen($sql_db) == 0) $sql_db = str_replace("/","",strrchr(str_replace("/upgrade.php","",$PHP_SELF),"/"));

if (strlen($sql_db) < 1) $sql_db = "SimpleAssets";
$server_status = db_connect($ip,$sql_login,$sql_pass);
if ($server_status) $db_status = db_select($sql_db);

if (($server_status) && ($db_status)) {
	doSQL("ALTER TABLE `" . $emp_db . "Employees` ADD `SessionId` VARCHAR(50)");
	doSQL("CREATE TABLE `Links` (`id` int(10) unsigned NOT NULL auto_increment,`supplier` tinytext,`link` text,PRIMARY KEY  (`id`),KEY `id` (`id`)) TYPE=MyISAM;");
	doSQL("ALTER TABLE `Assets` ADD `Notes` TEXT");
	doSQL("ALTER TABLE `Licenses` ADD `expiredate` BIGINT(20)  UNSIGNED, `licensekey` TEXT");
	echo "Upgrade Complete.";
	echo "<p>Please verify that:";
	echo "<ul>";
	echo "<li>A new field labelled 'SessionId' was added to your Employees table. (upgrading from 0.11 and below only)";
	echo "<li>A new table labelled 'Links' was added. (upgrading from 0.11 and below only)";
	echo "<li>A new field labelled 'Notes' was added to your Assets table.";
	echo "<li>Two new fields labelled 'expiredate' and 'licensekey' were added to your Licenses table.";
	echo "</ul>";
} else {
	echo "Unable to connect to DB. SimpleAssets is either not installed or misconfigured.  Please review your config.php file.";
}

?>