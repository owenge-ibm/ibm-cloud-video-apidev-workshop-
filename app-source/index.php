<?php
session_start ();
?>

<!DOCTYPE html>
<html>
<head>
<title>Streaming Manager API Demo</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="css/style.css" />
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src=js/tab-manage.js></script>
<script type="text/javascript" src=js/manage-player-selects.js></script>
<script type="text/javascript" src=js/player-controls.js></script>
<script type="text/javascript" src=js/ustream-embedapi.js></script>
</head>
<body>
	<div>
		<?php
		include 'php/set_key.php';
		?>
		
		<div class="container">
			<div class="demo-heading">
				<p class="heading">
					<span>IBM Cloud Video Streaming Manager API Demo</span>
				</p>
			</div>
			<div class="demo-heading demo-heading-lower">
				<p class="heading">
					<span>Demo user is authenticated via API as <?php echo "$CLIENT_ADDRESS (user ID: $CLIENT_ID)"; ?> </span>
				</p>
			</div>
			<div class="tab">
				<button class="tablinks active"
					onclick="openVideoApiFunction(event, 'access')">1. Accessing
					Content</button>
				<button class="tablinks"
					onclick="openVideoApiFunction(event, 'player')">2. Player</button>
				<button class="tablinks"
					onclick="openVideoApiFunction(event, 'channel')">3. Channel Create</button>
				<button class="tablinks"
					onclick="openVideoApiFunction(event, 'upload')">4. Video Upload</button>
				<button class="tablinks"
					onclick="openVideoApiFunction(event, 'auth')">5. Authentication</button>
			</div>

			<!--content for Content tab-->
			<div id="access" class="tabcontent" style="display: block;">
				<h3>Accessing content</h3>
				<p>You can access a user's channels and videos from the API. In the
					API response, you will get all the related metadata like titles,
					thumbnails, live status, length, etc.</p>

				<div>
					<h4>
					<?php
					if (isset ( $_SESSION ["channel_create_error"] ) && strlen ( $_SESSION ["channel_create_error"] ) > 0) {
						echo "<hr>";
						echo "Channel create response: " . $_SESSION ["channel_create_error"];
						$_SESSION ["channel_create_error"] = "";
					}
					if (isset ( $_SESSION ["upload_error"] ) && strlen ( $_SESSION ["upload_error"] ) > 0) {
						echo "Video upload response: " . $_SESSION ["upload_error"];
						$_SESSION ["upload_error"] = "";
					}
					if (isset ( $_SESSION ["token_error"] ) && strlen ( $_SESSION ["token_error"] ) > 0) {
						echo "Error acquiring authorization token: " . $_SESSION ["token_error"];
						$_SESSION ["token_error"] = "";
					}
					?>
					</h4>
				</div>

				<?php
				// save client keys for subsequent operations
				if (isset ( $CLIENT_KEY )) {
					$_SESSION ["client_key"] = $CLIENT_KEY;
					$_SESSION ["client_secret"] = $CLIENT_SECRET;
				}
				
				include 'php/channel-video_api.php';
				?>

			</div>
			<!--end of Content tab-->

			<!--content for Player tab-->
			<div id="player" class="tabcontent" style="display: none;">
				<h3>Player Setup</h3>
				<p>Select a channel or video to load into the player:</p>

				<div class="radio-block">
					<input class="radio-input" type="radio" id="radio-channel"
						name="player-source" value="channel" checked="checked"
						onclick="sourceRadio(this)" /> <label class="radio-label">Channel</label>
					<input class="radio-input" type="radio" name="player-source"
						value="video" onclick="sourceRadio(this)" /> <label
						class="radio-label">Video</label>
				</div>

				<div>
					<select id=load-channel onchange="sourceSelect(this)">
						<option>Select a channel</option>
					</select> <select id=load-video onchange="sourceSelect(this)">
						<option>Select a video</option>
					</select>

					<div id="video-button">
						<button type="button"
							class="video-button loader control-load-video">Load Video</button>
					</div>
					<div id="channel-button">
						<button type="button"
							class="video-button loader control-load-channel">Load Channel</button>
					</div>
				</div>
				<!--end of Player select buttons-->

				<div id="player-block">
					<div class="player-container">

						<div class="player-title">
							<hr>
							<span id="player-title">Some title of a video</span>
						</div>

						<div class="player-embed-left">
							<iframe id="UstreamIframe" width="360" height="220"
								allowfullscreen="" webkitallowfullscreen=""
								style="border: 0 none transparent;" src=""></iframe>
						</div>
						<!--end of player-embed-left block-->

						<div class="player-embed-right">

							<div id="player-status">
								<span style="display: none;" class="st-offline label">OFFLINE</span>
								<span style="display: none;" class="st-live label">LIVE</span> <span
									style="display: none;" class="st-playing label">PLAYING</span>
								<span style="display: none;" class="st-ended label">ENDED</span>
							</div>

							<div id="player-stats">
								<div class="spacer"></div>
								<div>
									<span id="VideoDurationTitle" class="stats">Duration</span>
								</div>
								<div>
									<span id="VideoDuration" class="stats"></span>
								</div>

								<div class="spacer"></div>
								<div>
									<span id="VideoProgressTitle" class="stats">Progress</span>
								</div>
								<div class="player-status">
									<span id="VideoProgress" class="stats"></span>
								</div>
							</div>

							<div class="player-quality">
								<div class="spacer"></div>
								<label>Video quality</label>
								<div class="control-select">
									<select class="quality-selector"></select>
									<div class="select">
										<span class="label">Video Quality</span>
									</div>
								</div>
							</div>

						</div>
						<!--end of player-embed-right block-->

					</div>
					<!--end of embed player-container block-->

					<div class="player-container">
						<div class="player-control">
							<button type="button" class="video-button control-play">Play</button>
						</div>
						<div class="player-control">
							<button type="button" class="video-button control-pause">Pause</button>
						</div>
						<div class="player-control">
							<input class="player-control-input" type="text" id="Seek"
								maxlength="4" />
						</div>
						<div class="player-control">
							<button type="button" class="video-button control-seek">Seek</button>
						</div>

					</div>
					<!--end of controls player-container block-->

				</div>
				<!--end of player-block block-->


			</div>
			<!--end of Player tab-->

			<!--content for Channel Create tab-->
			<div id="channel" class="tabcontent" style="display: none;">
				<h3>Channel Create</h3>
				<p>You can create a new channel via the API</p>

				<div>
					<!-- verify location redirect in script-->
					<form action="php/channel-create.php" method="post"
						enctype="multipart/form-data">
						<p>Channel Title</p>
						<div>
							<input type="text" name="channelTitle">
						</div>
						<p>Description</p>
						<div>
							<input class="channel-description" type="text"
								name="channelDescription">
						</div>
						<div>
							<input type="submit" value="Create Channel">
						</div>
					</form>
				</div>
			</div>
			<!--end of Channel Create tab-->

			<!--content for Video Upload tab-->
			<div id="upload" class="tabcontent" style="display: none;">
				<h3>Video Upload</h3>
				<p>You can upload a video from your computer to any of your channels</p>

				<form action="php/file-upload.php" method="post"
					enctype="multipart/form-data">
					<div>
						<select id=upload-channel name="channel" onchange="sourceSelect(this)">
							<option></option>
						</select>
					</div>
					<div class="spacer"></div>

					<div class="upload-container">
						<div id="upload-browse" class="upload-files">
							<button type="button" class="video-button loader"
								onclick="document.getElementById('uploader').click();">Choose a
								file ...</button>
							<input type="file" style="display: none;" name="my_file[]"
								id="uploader" onchange="sourceSelect(this)" multiple />
						</div>
						<div id="upload-show" class="upload-files">
							<ul id="upload-selects">

							</ul>
						</div>
					</div>

					<div id="upload-parameters" class="upload-title">
						<div>
							<p>Video Title</p>
						</div>
						<div>
							<input type="text" name="title" value="title" />
						</div>
						<div>
							<p>Description</p>
						</div>
						<div>
							<input type="text" name="description" value="description" />
						</div>
						<div>
							<input type="submit" value="Upload" />
						</div>
					</div>

				</form>

			</div>
			<!--end of Video Upload tab-->

			<!--content for Authentication tab-->
			<div id="auth" class="tabcontent" style="display: none;">
				<h3>Authentication</h3>
				<p>User authentication can be done with Streaming Manager OAuth</p>

				<div>
					<a id="oauth" href="https://www.ustream.tv/oauth2/authorize?response_type=code&client_id=<?php echo $CLIENT_KEY; ?>&redirect_uri=https://stmgr-wrkshp-1955a.mybluemix.net/get_access_token&device_name=D200&scope=offline+broadcaster&state=video">Re-authenticate
						with a different user</a>
				</div>
			</div>
			<!--end of Authentication tab-->

		</div>
	</div>
</body>
</html>