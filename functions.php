<?php

// requests a url and returns the string.
//
// $url String    : a well formed url
// $header String : header to include
// $cookie String : cookie to include
// $p String      : post data to include

function cURL($url, $header=NULL, $cookie=NULL, $p=NULL)
{
    $BROWSER    = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_NOBODY, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_USERAGENT, $BROWSER);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    if ($p) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
    }
    $result = curl_exec($ch);

    if ($result) {
        return $result;
    } else {
        return curl_error($ch);
    }
    curl_close($ch);
}

// logs user into facebook (or at least tries) and retrieves the proper cookie data
//
// $EMAIL String    : facebook user's email address
// $PASSWORD String : facebook user's password

function facebookConnect($EMAIL, $PASSWORD){
    $cookie = "";

    //incorrect username and password here, we just want cookie data.
    $a = cURL("https://login.facebook.com/login.php?login_attempt=1",true,null,"email=steve@apple.com&pass=ipod");
    preg_match('%Set-Cookie: ([^;]+);%',$a,$b);
    if (count($b) == 0){
        echo "Cookie phase one failed.";
        die;
    }
    $c = cURL("https://login.facebook.com/login.php?login_attempt=1",true,$b[1],"email=$EMAIL&pass=$PASSWORD");
    $fail = strpos($c, "Your account has a high number of invalid login attempts.");
    if ($fail !== false){
        echo "Failed due to password abuse.";
        die;
    }
    preg_match_all('%Set-Cookie: ([^;]+);%',$c,$d);
    for($i=0;$i<count($d[0]);$i++)
        $cookie.=$d[1][$i].";";

    return $cookie;
}

// returns friend count from string resembling "(25) friends"
//
// $input String : string as described above

function getFriendCount($input)
{
    preg_match('%([0-9]+) friends%', $input, $out);

    if(count($out) == 0) {
        return 0;
    } else {
        return intval($out[1]);
    }
}

