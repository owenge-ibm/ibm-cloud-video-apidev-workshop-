   <?php

	// start session environment
	session_start ();	
 	
	/* parse the query string into variable $queryParm                                  */
	parse_str($_SERVER['QUERY_STRING'], $queryParm);
	
	
	$CLIENT_KEY = $_SESSION ["client_key"];
	$CLIENT_SECRET = $_SESSION ["client_secret"];
	
	/* build array of form parameters                                                   */
	/* the 'code' query parameter was returned by the Ustream OAuth2 authorization site */
	/* this is the authorization code tied to the Ustream credentials                   */
	$curl_formData = array (
			'client_id' => $CLIENT_KEY,
			'redirect_uri' => "https://".$_SERVER['SERVER_NAME'].substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '/?')),
			'grant_type' => 'authorization_code',
			'code' => $queryParm['code']
	);
	
	error_log("flamethrower", 0);
	error_log("client_id = $CLIENT_KEY", 0);
	error_log("redirect_uri = https://".$_SERVER['SERVER_NAME'].substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '/?')), 0);
	
	// start curl session
	$curl = curl_init ();
	
	// set required request options
	curl_setopt ( $curl, CURLOPT_URL, "https://www.ustream.tv/oauth2/token" );
	curl_setopt ( $curl, CURLOPT_HTTPHEADER, array ('Authorization: Basic ' . $CLIENT_SECRET) );
	curl_setopt ( $curl, CURLOPT_POSTFIELDS, $curl_formData );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	

	// issue request for access token
	$result = curl_exec ( $curl );
	curl_close ( $curl );

	// convert result to JSON object
	$resultJSON = json_decode ( $result, true );
	
	if (isset($resultJSON['error'])) {
		$_SESSION["token_error"] = $resultJSON['error_description'];
	} else {
		/* save access token for main site redirect                                 */
		$_SESSION["ustream_token"] = $resultJSON['access_token'];
	}
		
	header("Location: https://" . $_SERVER["SERVER_NAME"]);
	exit;
		
	?>
