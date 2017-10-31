function openVideoApiFunction(evt, apiFunction) {

	var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    tablinks = document.getElementsByClassName("tablinks");
    
    // Hide all of the content blocks
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    
    // Turn off the active class for all tabs
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    
    // Make the current tab active and expose its content block
    document.getElementById(apiFunction).style.display = "block";
    evt.currentTarget.className += " active";

    /*
     * Hide tab content elements that may have been already activated
     */
    
    /*
     * Player tab
     */
    if (apiFunction == "player") {
        // Reset the radio buttons and selection fields
    	document.getElementById('radio-channel').checked = true;
    	document.getElementById('load-channel').style.display = "inline";
    	document.getElementById('load-video').style.display = "none";
    	
        // Hide the load buttons and the player block
    	document.getElementById('channel-button').style.display = "none";
    	document.getElementById('video-button').style.display = "none";
    	document.getElementById('player-block').style.display = "none";    	
    }    
    /*
     * Upload tab
     */
    if (apiFunction == "upload") {
        // Hide the upload browse button and video parameter fields	
    	document.getElementById('upload-browse').style.display = "none";
    	document.getElementById('upload-parameters').style.display = "none";

     	document.getElementById('upload-show').style.display = "none";    	
    }    
    
}