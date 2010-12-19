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

$result = mysql_query("SELECT * FROM cookies ORDER BY time DESC LIMIT 1");
$data = mysql_fetch_array($result);
$my_cookie = $data['cookie'];

$page = $page = cURL($config['facebook']['url'], null, $my_cookie, null);
$count = getFriendCount($page);

if ($count == 0) {
	echo "Saved cookie is bad.<br>";
	$my_cookie = facebookConnect($config['facebook']['email'], $config['facebook']['password']);
	if (strlen($my_cookie) > 1) {
		echo "New cookie retrieved.<br>";
		mysql_query("INSERT INTO cookies (cookie) VALUES ('$my_cookie')");
	}
	$page = cURL($config['facebook']['url'], null, $my_cookie, null);
	$count = getFriendCount($page);
} else {
	echo "Saved cookie is good.<br>";
}

if ($count == 0){
	echo "Friend count not found.<br>";
	die;
} else {
	echo "Found $count friends.<br>";
}

$result = mysql_query("SELECT * FROM friend_count ORDER BY time DESC LIMIT 1");
$data = mysql_fetch_array($result);
$prev = intval($data['count']);

if ($count != $prev && $count != 0) {
	echo "Inserting new count.<br>";
	mysql_query("INSERT INTO friend_count (count) VALUES ('$count')");
}else{
	echo "Same friend count.<br>";
	die;
}

$now = date('Y-m-d H:i:s');

preg_match('%"post_form_id" value="([0-9a-f]+)"%', $page, $e);
preg_match('%"fb_dtsg" value="([^"]+)"%', $page, $f);

$id = $e[1];
$fbdtsg = $f[1];
$page = 0;
$limit = 100;
$fcount = 0;

while (true) {
	$post = "class=FriendManager&edge_type=everyone&fb_dtsg=$fbdtsg&limit=$limit&lsd=&node_id=639297648&page=$page&post_form_id=$id&post_form_id_source=AsyncRequest";
	$friends = cURL("http://www.facebook.com/ajax/social_graph/fetch.php?__a=1", null, $my_cookie, $post);
	$page++;
	preg_match_all('%"id":([0-9]+),"title":"([^"]+)","alternate_title":"[^"]*","href":"([^"]+)"%', $friends, $f);

	if (count($f[0]) == 0) {
		break;
	}

	foreach($f[1] as $index => $value){
		$fid = $value;
		$fname = preg_replace("/([^A-Za-z\s])/", "", $f[2][$index]);
		$furl = stripslashes($f[3][$index]);
		$fcount++;

		$results = mysql_query("SELECT id, active FROM friend_account WHERE id=$fid ");
		$data = mysql_fetch_array($results);
		if($data == false) {
			// insert into people database
			echo "New friend.<br>";
			$sql  = "INSERT INTO friend_account (id, name, url, time, active) ";
			$sql .= "VALUES ('$fid', '$fname', '$furl', '$now', 1)";
			mysql_query($sql);
			// log activity
			$sql  = "INSERT INTO friend_activity (friend_account_id, time, activated) ";
			$sql .= "VALUES ('$fid', '$now', 1)";
			mysql_query($sql);
		} else {
			//echo "Old friend.<br>";
			// update people database
			$sql  = "UPDATE friend_account ";
			$sql .= "SET time='$now', active=1 ";
			$sql .= "WHERE id=$fid";
			mysql_query($sql);
			// log activity if reactivated
			if ($data['active'] == 0) {
				echo "Reactivating friend.<br>";
				$sql  = "INSERT INTO friend_activity (friend_account_id, time, activated) ";
				$sql .= "VALUES ('$fid', '$now', 1)";
				mysql_query($sql);
			}
		}
	}
}

echo "$count friends expected.<br>";
echo "$fcount friends found.<br>";

$query = "SELECT count(id) AS count FROM friend_account WHERE time < '$now' AND active=1";
$results = mysql_query($query);

if (intval($data['count']) > 100) {
	echo "Something went wrong.  More than 100 friends shouldn't deactivate.";
	die;
}

$sql  = "SELECT id FROM friend_account WHERE ";
$sql .= "time < '$now' AND active=1";
$results = mysql_query($sql);

while($data = mysql_fetch_array($results)) {
	echo "Deactivated friend.<br>";
	// update people database
	$sql  = "UPDATE friend_account ";
	$sql .= "SET time='$now', active=0 ";
	$sql .= "WHERE id=".$data['id'];
	mysql_query($sql);
	// log activity
	$sql  = "INSERT INTO friend_activity (friend_account_id, time, deactivated) ";
	$sql .= "VALUES (".$data['id'].", '$now', 1)";
	mysql_query($sql);
}

die;
