/*
 * When the radio button are selected, change which content selection list is displayed
 */
function sourceRadio(rad)
{
	var type = rad.value;
  
	channelSelect = document.getElementById('load-channel');
	videoSelect = document.getElementById('load-video');
	
	channelSelect.selectedIndex = 0;
	videoSelect.selectedIndex = 0;

	// now hide or show the other elements
	if (type == "channel") {
		channelSelect.style.display = "inline";
		videoSelect.style.display = "none";
	} else {
		channelSelect.style.display = "none";
		videoSelect.style.display = "inline";
	}
	
	// hide the load buttons until a selection is made
	document.getElementById('channel-button').style.display = "none";
	document.getElementById('video-button').style.display = "none";
	
}

/*
 * once a selection is made, make the corresponding load button displayed
 */
function sourceSelect(sel) {
	
	var whichButton = sel.id;

	// Channel tab
	var channelButton = document.getElementById('channel-button');
	var videoButton = document.getElementById('video-button');
	
	// Upload Video tab
	var uploadFiles = document.getElementById('upload-browse');
	var uploadParms = document.getElementById('upload-parameters');
	var uploadShow = document.getElementById('upload-show');
	
	if (whichButton == "load-channel") {
		channelButton.style.display = "block";
		videoButton.style.display = "none";
	}
	if (whichButton == "load-video") {
		channelButton.style.display = "none";
		videoButton.style.display = "block";
	}
	if (whichButton == "upload-channel") {
		uploadFiles.style.display = "block";
	}
	if (whichButton == "uploader") {
		uploadParms.style.display = "block";
		uploadShow.style.display = "block";

		var fileSelect = document.getElementById("upload-selects");
		
		fileSelect.innerHTML = "";
		var fileListItem;
		var fileListValue;
		
		// append selected file names
		for(var i = 0; i < sel.files.length; i++) {
			fileListItem = document.createElement("li");
			fileListValue = document.createTextNode(sel.files[i].name);
			fileListItem.appendChild(fileListValue);
			fileSelect.appendChild(fileListItem);
		}
	}
}

function updateVideos(source, target) {
	
	// create arrays of the acquired account content
	var channels = document.getElementsByClassName('channel');
	var videos = document.getElementsByClassName('video');
	
	// create objects for the selection and target fields
	var typeField = document.getElementById(source);
	var idField = document.getElementById(target);
	
	// retrieve the content type selection - 'Channel' or 'Video' 
	var loadType = typeField.options[typeField.selectedIndex].text;
	// alert("selected content " + loadType);
	
	// initialize the option variables
	var optionVal = "";
	var optionText = "";
	var optionTypeOptions = [];
	
	// clear the existing target options
	// ----
    for (i = idField.options.length - 1 ; i >= 0 ; i--)
    {
        idField.remove(i);
    }
	
	// create a common processing variable
    switch (loadType) {
    	case "Channel":
    		optionTypeOptions = channels;
    		break;
    	case "Video":
    		optionTypeOptions = videos;
    		break;
    	default:
    		optionTypeOptions = [];
    }
	
	// create a selection option for each content entry
	for (i = 0; i < optionTypeOptions.length; i++) {
		var newOpt = document.createElement('option');
		newOpt.value = optionTypeOptions[i].id;
		newOpt.innerText = optionTypeOptions[i].innerText;
		
		idField.add(newOpt);
	}
}

function timeFormat(value) {
	return Math.floor(value / 60) + ":" + (Math.floor(value % 60) < 10 ? "0" + Math.floor(value % 60) : Math.floor(value % 60));
}