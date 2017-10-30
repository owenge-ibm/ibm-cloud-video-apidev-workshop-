<?php
session_start ();
$home_page = "https://" . $_SERVER ["SERVER_NAME"];

if (strlen ( $_SESSION ["ustream_token"] ) == 0) {
	$_SESSION ["channel_create_error"] = "Missing access token";
	header ( "Location: " . $home_page );
	exit ();
} else {
	
	$CHANNEL_TITLE = $_POST ['channelTitle'];
	$curl_formData = array (
			'title' => $_POST ['channelTitle'],
			'description' => $_POST ['channelDescription']
	);
	$headerFields = array (
			'Authorization: Bearer ' . $_SESSION ["ustream_token"],
			'content-type: application/x-www-form-urlencoded'
	);
	
	$curl = curl_init ();
	curl_setopt ( $curl, CURLOPT_URL, "https://api.ustream.tv/users/self/channels.json" );
	curl_setopt ( $curl, CURLOPT_HTTPHEADER, $headerFields );
	curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query ( $curl_formData ) );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	$result = curl_exec ( $curl );
	
	$resultJSON = json_decode ( $result );
	$status = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
	
	curl_close ( $curl );
	
	if ($status == 201) {
		$_SESSION ["channel_create_error"] = "Channel '" . $CHANNEL_TITLE . "' successfully created";
	} else {
		if (strlen( $resultJSON->hint ) > 0) {
			$error_hint = ": " . $resultJSON->hint;
		} else {
			$error_hint = "";
		}
		
		$_SESSION ["channel_create_error"] = "(HTTP $status) " . "$resultJSON->error" . $error_hint;
	}

	header ( "Location: " . $home_page);
	exit ();
}


?>