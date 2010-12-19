<?php

include 'config.php';

if (file_exists('config_local.php')) {
	include 'config_local.php';
}

$con = mysql_connect($config['db']['server'], $config['db']['user'], $config['db']['password']);
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db($config['db']['database'], $con);

$query = "CREATE TABLE IF NOT EXISTS `cookies` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`cookie` text NOT NULL,
	PRIMARY KEY (`id`)
)";
$result = mysql_query($query);

$query = "CREATE TABLE IF NOT EXISTS `friend_account` (
	`id` bigint(32) unsigned NOT NULL,
	`name` varchar(128) NOT NULL,
	`url` varchar(256) NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`active` int(1) NOT NULL,
	PRIMARY KEY (`id`)
)";
$result = mysql_query($query);

$query = "CREATE TABLE IF NOT EXISTS `friend_activity` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`friend_account_id` bigint(32) NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`activated` int(1) NOT NULL,
	`deactivated` int(1) NOT NULL,
	PRIMARY KEY (`id`)
)";
$result = mysql_query($query);

$query = "CREATE TABLE IF NOT EXISTS `friend_count` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`count` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`)
)";
$result = mysql_query($query);

echo "table creation complete";
