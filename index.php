<?php
include 'config.php';

if (file_exists('config_local.php')) {
        include 'config_local.php';
}

include 'functions.php';

$my_cookie  = "";
$bad_cookie = false;

$con = mysql_connect($config['db']['server'], $config['db']['user'], $config['db']['password']);
if (!$con) {
        die('Could not connect: ' . mysql_error());
}
mysql_select_db($config['db']['database'], $con);

$query  = "SELECT AVG(activity) AS avg FROM (";
$query .= "SELECT SUM(activated + deactivated) AS activity FROM ";
$query .= "friend_activity GROUP BY friend_account_id) AS a";
$result = mysql_query($query);
$data = mysql_fetch_array($result);

$average = $data['avg'];

$query  = "SELECT friend_account_id, name, url, activity FROM (";
$query .= "SELECT friend_account_id, SUM(activated + deactivated) AS activity FROM ";
$query .= "friend_activity GROUP BY friend_account_id) AS a ";
$query .= "JOIN friend_account ON a.friend_account_id=friend_account.id ";
$query .= "WHERE activity > $average ORDER BY activity DESC";
$result = mysql_query($query);

echo "<h1>Most Volatile Friends</h1>".PHP_EOL;
echo "<ul>".PHP_EOL;
while ($data = mysql_fetch_array($result)) {
	echo "<li><a href='{$data['url']}'>{$data['name']}</a></li>".PHP_EOL;
}
echo "</ul>".PHP_EOL;

$query  = "SELECT friend_account_id, name, url, activated, deactivated FROM (";
$query .= "SELECT friend_account_id, activated, deactivated FROM ";
$query .= "friend_activity ORDER BY time DESC LIMIT 20) AS a ";
$query .= "JOIN friend_account ON a.friend_account_id=friend_account.id ";
$result = mysql_query($query);

echo "<h1>Most Recent Activity</h1>".PHP_EOL;
echo "<ul>".PHP_EOL;
while ($data = mysql_fetch_array($result)) {
	if ($data['activated'] == 1) {
		echo "<li><a href='{$data['url']}'>{$data['name']}</a> Activated</li>".PHP_EOL;
	} else if ($data['deactivated'] == 1) {
		echo "<li><a href='{$data['url']}'>{$data['name']}</a> Deactivated</li>".PHP_EOL;
	}
}
echo "</ul>".PHP_EOL;

