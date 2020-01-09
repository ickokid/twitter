<?php
ini_set('max_execution_time', 0);
ini_set("memory_limit", "-1");
set_time_limit(0);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

define("PATH_PROJECT", "C:\\wamp64\\www\\test\\twitter");

require 'include/globalFunc.php';

define("CONSUMER_KEY", "3lFzO8FrJZvT4rL6L1hNSQ");
define("CONSUMER_SECRET","KLdLQUZZsGRxyTgZ54AOTSvNKdhQ4fE3TUtL56sU");
define("ACCESS_TOKEN","470332552-PN53is6DaM3jhFSyC8qc2weu6dFgFPOHLxISqk4A");
define("ACCESS_TOKEN_SECRET","Z49CbBB5ojnux1fE0chhw78dCOh2at8WPHTUFXDsCU6d0");

$username = isset($_GET['username'])?$_GET['username']:"juventusfcid";

$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";

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
				'screen_name' => $username);
 
$base_info = buildBaseString($url, 'GET', $oauth);
$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
$oauth['oauth_signature'] = $oauth_signature;
 
// Make Requests
$header = array(buildAuthorizationHeader($oauth), 'Content-Type: application/json', 'Expect:');
$options = array( CURLOPT_HTTPHEADER => $header,
				  //CURLOPT_POSTFIELDS => $postfields,
				  CURLOPT_HEADER => false,
				  CURLOPT_URL => $url . '?screen_name=' . $username,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_SSL_VERIFYPEER => false);
$feed = curl_init();
curl_setopt_array($feed, $options);
$result = curl_exec($feed);
$httpcode = curl_getinfo($feed, CURLINFO_HTTP_CODE);
curl_close($feed);
if($httpcode == "200"){
	$arrMedia = json_decode($result,TRUE);
	
	$newVal = array();
	
	if(count($arrMedia) > 0){
		foreach($arrMedia AS $media) {
			$id 				= isset($media['user']['id'])?$media['user']['id']:"";
			$username 			= isset($media['user']['screen_name'])?$media['user']['screen_name']:"";
			$full_name 			= isset($media['user']['name'])?$media['user']['name']:"";
			$biography 			= isset($media['user']['description'])?$media['user']['description']:"";
			$profile_pic_url 	= isset($media['user']['profile_image_url_https'])?$media['user']['profile_image_url_https']:0;
			$profile_pic_url_hd = isset($media['user']['profile_image_url_https'])?$media['user']['profile_image_url_https']:0;
			$follower 			= isset($media['user']['followers_count'])?$media['user']['followers_count']:0;
			$following 			= isset($media['user']['friends_count'])?$media['user']['friends_count']:0;
			
			$title			= $media['text'];
			$hashtags 		= array();findHashtag($title, $hashtags);
			$created		= $media['created_at'];
			$photoUrl		= isset($media['entities']['media'][0]['media_url_https'])?$media['entities']['media'][0]['media_url_https']:"";
			
			$arrContent = isset($media['extended_entities']['media'])?$media['extended_entities']['media'][0]:array();
			
			if(count($arrContent) > 0){
				$mediaId		= $arrContent['id']. '_' . $id;
				$mediaType		= $arrContent['type'];
				$photoUrl		= $arrContent['media_url_https'];
				
				if($mediaType == "photo"){
					$fileName	= $mediaId.'.jpg';
					$fileDest	= PATH_PROJECT . '\\tmp\\' . $fileName;
					
					$saved = file_put_contents($fileDest, file_get_contents($photoUrl));
											
					$imgSize 	= filesize($fileDest);
					$info		= getimagesize($fileDest);
					$extension	= image_type_to_extension($info[2], true);
					$mime		= $info['mime'];
					$imgWidth	= (int) $info[0];
					$imgHeight	= (int) $info[1];
					$created	= date("Ymdhis"); //new MongoDate();
					
					if($imgSize > 0){
						$arrVal	= array();
						$arrVal["title"] = $title;
						if( count($hashtags) > 0 ) {
							$arrVal["hashtag"] = $hashtags;	
						}
						$arrVal["photo"] = $photoUrl;	
						$arrVal["photo_size"] = $imgSize;	
						$arrVal["photo_width"] = $imgWidth;	
						$arrVal["photo_height"] = $imgHeight;
						$arrVal["type"] = "photo";	
						$arrVal["created"] = $created;	

						array_push($newVal, $arrVal);
					}
				} else if($mediaType == "video"){
					$videoUrl		= $arrContent['video_info']['variants'][0]['url'];
					
					if(!empty($videoUrl)){
						$fileVideoName			= $mediaId.'.mp4';
						$fileDestVideo			= PATH_PROJECT . '\\tmp\\' . $fileVideoName;
						
						$saved = file_put_contents($fileDestVideo, file_get_contents($videoUrl));
						$videoSize 	= filesize($fileDestVideo);
						
						if($videoSize > 0){
							$fileName		= $mediaId.'.jpg';
							$fileDest		= PATH_PROJECT.'\\tmp\\'.$fileName;
							
							$saved = file_put_contents($fileDest, file_get_contents($photoUrl));
							
							if(file_exists($fileDest)) {
								$imgSize 	= filesize($fileDest);
								$info		= getimagesize($fileDest);
								$extension	= image_type_to_extension($info[2], true);
								$mime		= $info['mime'];
								$imgWidth	= (int) $info[0];
								$imgHeight	= (int) $info[1];
								
								$arrVal	= array();
								$arrVal["title"] = $title;
								if( count($hashtags) > 0 ) {
									$arrVal["hashtag"] = $hashtags;	
								}
								$arrVal["photo"] = $photoUrl;	
								$arrVal["photo_width"] = $imgWidth;	
								$arrVal["photo_height"] = $imgHeight;	
								$arrVal["created"] = $created;	
								$arrVal["photo_size"] = $imgSize;	
								$arrVal["type"] = "video";	
								$arrVal["video"] = $videoUrl;
								$arrVal["video_size"] = $videoSize;
								
								array_push($newVal, $arrVal);
							}
						}	
					}
				}	
			}
		}
		
		echo "<pre>";
		print_r($newVal);
		echo "</pre>";
	} else {
		die('No Content Media');
	}
} else {
	die("Error HTTP Code ".$httpcode." Username : ".$twitter_username);
}
?>