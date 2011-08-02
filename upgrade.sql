ALTER TABLE Employees` ADD `SessionId` VARCHAR(50)
CREATE TABLE `Links` (`id` int(10) unsigned NOT NULL auto_increment,`supplier` tinytext,`link` text,PRIMARY KEY  (`id`),KEY `id` (`id`)) TYPE=MyISAM;
ALTER TABLE `Assets` ADD `Notes` TEXT
ALTER TABLE `Licenses` ADD `expiredate` BIGINT(20)  UNSIGNED, `licensekey` TEXT
