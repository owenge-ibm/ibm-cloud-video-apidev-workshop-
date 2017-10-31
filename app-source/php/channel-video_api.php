<?php

/*
 * ----- List channels ------
 */
$channel_objects = getChannels();

// build an HTML grid with blocks for each video under the channel heading

foreach($channel_objects->channels as $key => $value) {

	// load the key to this channel object
	$channel = $channel_objects->channels->{$key};
	
	// extract the id and title
	$channelId = $channel->id;
	$channelTitle = $channel->title;
	

	//start channel container
	echo '<div class="channel-block">'."\n";
	echo '<div><hr><p><span id="'.$channelId.'" class="channel">Channel: '.$channelTitle.'</span></p></div>';
	
	getVideos($channelId);
	
	echo "\n</div>"; /* end of channel block */
	
}

/*
 * getChannels function to return JSON object with list of channel objects
 */
function getChannels() {
	
	$curl = curl_init();
	
	curl_setopt($curl, CURLOPT_URL, "https://".API_URL."/users/self/channels.json");
	curl_setopt($curl, CURLOPT_HTTPHEADER, $GLOBALS['API_HEADER']);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	
	$result = curl_exec($curl);
	
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	curl_close($curl);
	
	if ($status == 200) {
		return json_decode($result);
	}

	return [];
}

/*
 * getVideos function to return JSON object with list of video objects
 */
function getVideos($channel_id) {

	$curl = curl_init();
	
	$fields = array('filter' => array('protect' => 'private,public'));
	$field_query = http_build_query($fields);
		
	curl_setopt($curl, CURLOPT_URL, "https://".API_URL."/channels/$channel_id/videos.json/?$field_query");
	curl_setopt($curl, CURLOPT_HTTPHEADER, $GLOBALS['API_HEADER']);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	
	$result = curl_exec($curl);
	
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

		
	if ($status == 200) {
		
		$response = json_decode($result);
		
		foreach($response->videos as $videoEntry) {
			$videoTitle = str_replace("_", " ", $videoEntry->title);
			$videoId = $videoEntry->id;
			$videoUrl = $videoEntry->url;
			$videoThumb = $videoEntry->thumbnail->default;
			
			if ($videoEntry->protect != "private") {
				echo "\n".'<div class="channel-video-block">';
				echo "\n".'<img alt="" src="'.$videoThumb.'">';
				echo "\n".'<div><a href="'.$videoUrl.'"><span id="'.$videoId.'" class="video">'.$videoTitle.'</span></a></div>';
				echo "\n</div>";
			}
		}

	}
}

function printPretty($response) {
    echo json_encode($response, JSON_PRETTY_PRINT)."\n";
}

?>