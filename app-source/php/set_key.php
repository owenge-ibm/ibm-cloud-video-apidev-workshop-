<?php

/*
 * Set up authentication context for subsequent code blocks
 * 
 * 1. Define URL constants for Streaming Manager API calls
 * 2. Retrieve authorization token from environment if it exists
 * 3. Or, use local server file to login and generate new authorization token
 * 4. Define global variable for authorization header in Streaming Manager API calls   
 * 5. Retrieve id and email address for current user for display on the home page
 *   
 */

// Define constants for API calls
define("API_URL", "api.ustream.tv");
define("WWW_URL", "www.ustream.tv");

// Initialize authentication variables
// Read channel access credentials from local file
$myfile = fopen ( "client_key.json", "r" ) or die ( "<!DOCTYPE html><html><body><h1>In set_key.php, can't find Streaming Manager credentials file 'client_key.json', or unable to open it!</h1></body></html>" );
$apiParmFile = fread ( $myfile, filesize ( "client_key.json" ) );
fclose ( $myfile );

// Convert to JSON object
$apiParms = json_decode ( $apiParmFile, true );

// And extract the authentication values
$CLIENT_KEY = $apiParms ['connKEY'];
$CLIENT_SECRET = $apiParms ['connSECRET'];

// If the access token has not been preloaded then a normal login is required
if (isset($_SESSION ["ustream_token"])) {
	// Save token from session environment
	$auth_token = $_SESSION ["ustream_token"];
	
} else {

	// Build the authentication POST fields
	$curl_formData = array ('client_id' => $CLIENT_KEY, 'grant_type' => 'client_credentials' );
	
	// Set up curl request to retrieve access token
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://".WWW_URL."/oauth2/token");
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $CLIENT_SECRET));
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_formData);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
	$result = curl_exec($curl);
	curl_close($curl);
	
	// Convert result string to a JSON object
	$resultJSON = json_decode ($result, TRUE);
	
	// Add to session environment for access by other API calls
	$_SESSION["ustream_token"] = $resultJSON ['access_token'];
	$auth_token = $resultJSON ['access_token'];
	
}

// Define common auth header
$API_HEADER = array('Authorization: Bearer ' . $auth_token, 'content-type: application/x-www-form-urlencoded');

// Retrieve account information to display user's id and email address 
$curl = curl_init ();
curl_setopt ( $curl, CURLOPT_URL, "https://" . API_URL . "/users/self.json" );
curl_setopt ( $curl, CURLOPT_HTTPHEADER, $API_HEADER );
curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec ( $curl );
$response = json_decode($result, TRUE);
$status = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
	
curl_close ( $curl );

// Create variables for display on the page
$CLIENT_ADDRESS = $response ['email'];
$CLIENT_ID = $response ['id'];

?>