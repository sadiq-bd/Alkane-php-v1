<?php

function get_ip_address() {
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP'])){
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_FORWARDED'])) {
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	} else {
		$ipaddress = 'Unknown';
	}
	$ip = explode(',', str_replace(' ','',$ipaddress))[0];
	if (is_array($ip)) {
		$ipadd = $ip[0];
	} else {
	    $ipadd = $ip;
	}
	return trim($ipadd);
}


function get_browser_info() { 
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";

	//First get the platform?
	if (preg_match('/android/i', $u_agent)) {
	  $platform = 'Android';
	} elseif (preg_match('/linux/i', $u_agent)) {
	  $platform = 'Linux';
	} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	  $platform = 'Mac';
	} elseif (preg_match('/windows|win32/i', $u_agent)) {
	  $platform = 'Windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
	  	$bname = 'Internet Explorer';
	  	$ub = "MSIE";
	} elseif(preg_match('/Firefox/i',$u_agent)) {
	  	$bname = 'Mozilla Firefox';
	  	$ub = "Firefox";
	} elseif(preg_match('/OPR/i',$u_agent)) {
	  	$bname = 'Opera';
	  	$ub = "Opera";
	} elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)) {
	  	$bname = 'Google Chrome';
	  	$ub = "Chrome";
	} elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)) {
	  	$bname = 'Apple Safari';
	  	$ub = "Safari";
	} elseif(preg_match('/Netscape/i',$u_agent)) {
	  	$bname = 'Netscape';
	  	$ub = "Netscape";
	} elseif(preg_match('/Edge/i',$u_agent)) {
	  	$bname = 'Edge';
	  	$ub = "Edge";
	} elseif(preg_match('/Trident/i',$u_agent)) {
	  	$bname = 'Internet Explorer';
	  	$ub = "MSIE";
	} else {
		$bname = '';
		$ub = '';
	}

	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
	  // we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
	  //we will have two since we are not using 'other' argument yet
	  //see if version is before or after the name
	  if (strripos($u_agent,"Version") < strripos($u_agent,$ub)) {
		  $version= $matches['version'][0];
	  } else {
		  $version= $matches['version'][1];
	  }
	} else {
	  $version= $matches['version'][0];
	}

	// check if we have a number
	if ($version==null || $version=="") { 
		$version="?"; 
	}

	return [
	  'userAgent' => $u_agent,
	  'name'      => $bname,
	  'version'   => $version,
	  'platform'  => $platform,
	  'pattern'    => $pattern
  ];
}

