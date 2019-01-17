<?php
	ini_set('max_execution_time', 0);
	ini_set("memory_limit", "-1");
	set_time_limit(0);
	ini_set('display_errors',0);
	error_reporting(E_ERROR);
	date_default_timezone_set('Asia/Jakarta');
	
	define("CONSUMER_KEY", "3lFzO8FrJZvT4rL6L1hNSQ");
	define("CONSUMER_SECRET","KLdLQUZZsGRxyTgZ54AOTSvNKdhQ4fE3TUtL56sU");
	define("ACCESS_TOKEN","470332552-PN53is6DaM3jhFSyC8qc2weu6dFgFPOHLxISqk4A");
	define("ACCESS_TOKEN_SECRET","Z49CbBB5ojnux1fE0chhw78dCOh2at8WPHTUFXDsCU6d0");
	
	$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
	$twitter_username = "juventusfcid";
	
	$oauth_access_token = ACCESS_TOKEN;
	$oauth_access_token_secret = ACCESS_TOKEN_SECRET;
	$consumer_key = CONSUMER_KEY;
	$consumer_secret = CONSUMER_SECRET;
	 
	$oauth = array( 'oauth_consumer_key' => $consumer_key,
					'oauth_nonce' => time(),
					'oauth_signature_method' => 'HMAC-SHA1',
					'oauth_token' => $oauth_access_token,
					'oauth_timestamp' => time(),
					'oauth_version' => '1.0',
					'screen_name' => $twitter_username);
	 
	$base_info = buildBaseString($url, 'GET', $oauth);
	$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
	$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
	$oauth['oauth_signature'] = $oauth_signature;
	 
	// Make Requests
	$header = array(buildAuthorizationHeader($oauth), 'Content-Type: application/json', 'Expect:');
	$options = array( CURLOPT_HTTPHEADER => $header,
					  //CURLOPT_POSTFIELDS => $postfields,
					  CURLOPT_HEADER => false,
					  CURLOPT_URL => $url . '?screen_name=' . $twitter_username,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_SSL_VERIFYPEER => false);
	$feed = curl_init();
	curl_setopt_array($feed, $options);
	$result = curl_exec($feed);
	$httpcode = curl_getinfo($feed, CURLINFO_HTTP_CODE);
	curl_close($feed);

	if($httpcode == "200"){
		$result_content = json_decode($result,TRUE);
		
		echo "<pre>";
		print_r($result_content);
		echo "</pre>";
	} else {
		echo "HTTP Error Code ".$httpcode." Username : ".$twitter_username."<br>\n";
	}
	
	
	
	
	
	function buildBaseString($baseURI, $method, $params) {
		$r = array();
		ksort($params);
		foreach($params as $key=>$value){
			$r[] = "$key=" . rawurlencode($value);
		}
		return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}
	 
	function buildAuthorizationHeader($oauth) {
		$r = 'Authorization: OAuth ';
		$values = array();
		foreach($oauth as $key=>$value)
			$values[] = "$key=\"" . rawurlencode($value) . "\"";
		$r .= implode(', ', $values);
		return $r;
	}
?>