<?php
function return_bytes($val) {
	assert ( '1 === preg_match("/^\d+([kmg])?$/i", $val)' );
	static $map = array (
			'k' => 1024,
			'm' => 1048576,
			'g' => 1073741824 
	);
	return ( int ) $val * @($map [strtolower ( substr ( $val, - 1 ) )] ?  : 1);
}

// include error number mappings
include './errno.php';

$debug_show = FALSE;

$home_page = "https://" . $_SERVER["SERVER_NAME"];

// get length of POST package and server limit on file uploads
//$content_len = $_SERVER ['CONTENT_LENGTH'];
$upload_max = return_bytes ( ini_get ( 'upload_max_filesize' ) );

// start session environment
session_start ();

// initial check of upload size against server limits
if (isset ( $_SERVER ['CONTENT_LENGTH'] ) && $_SERVER ['CONTENT_LENGTH'] > $upload_max) {
	$_SESSION ["upload_error"] = "File(s) exceeds maximum file upload size!";
} else {
	if (strlen ( $_SESSION ["ustream_token"] ) == 0) {
		$_SESSION ["upload_error"] = "Missing access token";
	} else {
		$myFile = $_FILES ['my_file'];
		$fileCount = count ( $myFile ["name"] );
				
		if ($fileCount < 1) {
			$_SESSION ["upload_error"] = "No file selected";
		} else {
			// retrieve channel id, title and description
			$CHANNEL_ID = $_POST ['channel'];
			$video_title = $_POST ['title'];
			$video_description = $_POST ['description'];

			// set query fields
			$fields = array (
					"type" => "videoupload-ftp"
			);
			$queryFields = http_build_query( $fields );
			
			// set POST Form fields
			$curl_videoFormData = array (
					'title' => $video_title,
					'description' => $video_description,
					'protect' => "public"
			);
			
			// set auth header fields
			$headerFields = array (
					'Authorization: Bearer ' . $_SESSION ["ustream_token"],
					'content-type: application/x-www-form-urlencoded' 
			);
			
			/*
			 * first pass is to get FTP connection parameters from Ustream.
			 * if this call returns a 201 then the connection was successful and the upload(s) can proceed.
			 */
			// for each file, upload to Ustream and make it active
			for($i = 0; $i < $fileCount; $i ++) {

				// if no title was entered, use the filename as the title
				if (strlen($video_title) == 0 || $video_title == "title") {
					$curl_videoFormData["title"] = pathinfo($myFile ['name'] [$i], PATHINFO_FILENAME);
				}
				
				// first get an FTP connection from Streaming Manager
				$curl = curl_init ();
				curl_setopt ( $curl, CURLOPT_URL, "https://api.ustream.tv/channels/$CHANNEL_ID/uploads.json/?$queryFields" );
				curl_setopt ( $curl, CURLOPT_HTTPHEADER, $headerFields );
				curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query( $curl_videoFormData ) );
				curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
				
				$result = curl_exec ( $curl );
				$status = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
				
				curl_close ( $curl );
				
				// check for successful return code
				if ($status == 201) {
					
					$response = json_decode ( $result );
					
					// pull the extension from the form field
					$ext = pathinfo($myFile ['name'] [$i], PATHINFO_EXTENSION);
					
					// the URL is provided, the only addition is the extension of the original file
					$ftp_url = $response->url . ".$ext";
					
					// the video id will be used when updating the status of the uploaded video
					$video_id = $response->videoId;
					
					// get a file descriptor to the local file
					$localFile = $myFile ['tmp_name'] [$i];
					$fp = fopen ( $localFile, 'r' );

					// set up the FTP parameters.
					$curl = curl_init ();
					curl_setopt ( $curl, CURLOPT_UPLOAD, TRUE);
					curl_setopt ( $curl, CURLOPT_URL, $ftp_url );
					curl_setopt ( $curl, CURLOPT_INFILE, $fp );
					curl_setopt ( $curl, CURLOPT_INFILESIZE, filesize ( $localFile ) );
					
					$result = curl_exec ( $curl );
					$error_no = curl_errno ( $curl );

					curl_close($curl);

					// check for errors
					if ($error_no == 0) {
						
						// for a successful upload go set the video to a ready state so it's visible
						$curl_readyFormData = array ( "status" => "ready" );
						
						$ready_url = "https://api.ustream.tv/channels/$CHANNEL_ID/uploads/$video_id.json";
						
							
						// make request to set ready state
						$curl = curl_init ();
						curl_setopt ( $curl, CURLOPT_URL, $ready_url );
						curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, "PUT");
						curl_setopt ( $curl, CURLOPT_HTTPHEADER, $headerFields );
						curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query($curl_readyFormData) );
						curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1);
						
						
						$result = curl_exec ( $curl );
						$status = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
						
						curl_close ( $curl );
						
						if ($status == 202) {

							$curl = curl_init ();
							curl_setopt ( $curl, CURLOPT_URL, $ready_url );
							curl_setopt ( $curl, CURLOPT_HTTPHEADER, $headerFields );
							curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1);
							
							$result = curl_exec ( $curl );
							$response = json_decode($result);
							$status = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
							
							curl_close ( $curl );

							if ($status == 200) {
								// success, show current uploaded state
								$_SESSION ["upload_error"] = "video $video_id: " . $response->status;
							} else {
								// some error retrieving the current state
								$_SESSION ["upload_error"] = "Status check failed: ($status) = '$result'";								
							}
						} else {
							// some error setting the ready state
							$_SESSION ["upload_error"] = "Set ready state failed: ($status) = '$result'";
						}

					} else {
						// the FTP upload itself failed
						$_SESSION ["upload_error"] = 'FTP upload error ' . $curl_errno[$error_no];
					}
				} else {
					// the initial connection to the FTP server failed
					$_SESSION ["upload_error"] = "FTP connection error: HTTP " . $status . ": " . $result;
				}
			} // end of file loop
			
			header("Location: " . $home_page);
			exit();
		}
	}
}

?>