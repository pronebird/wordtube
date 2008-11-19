<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function wordtube_admin_options()  {
	
	global $wpdb;	

	// get the options
	$wt_options = get_option('wordtube_options');

	// same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	$filepath    = admin_url() . 'admin.php?page='.$_GET['page'];

	if ( isset($_POST['updateoption']) ) {	
		check_admin_referer('wt_settings');
		// get the hidden option fields, taken from WP core
		if ( $_POST['page_options'] )	
			$options = explode(',', stripslashes($_POST['page_options']));
		if ($options) {
			foreach ($options as $option) {
				$option = trim($option);
				$value = trim($_POST[$option]);
				$wt_options[$option] = $value;
			}
		}

		// Save options
		update_option('wordtube_options', $wt_options);
	 	wordTubeAdmin::render_message(__('Update Successfully','wpTube'));
	}
	
	if ( isset($_POST['resetdefault']) ) {
		check_admin_referer('wt_settings');

		require_once (dirname (__FILE__). '/install.php');
		
		delete_option( "wordtube_options" );
				
		$wt_options = wt_get_DefaultOption();
		$wt_options['version'] = WORDTUBE_VERSION;
		
		update_option('wordtube_options', $wt_options);
		
		wordTubeAdmin::render_message(__('Reset all settings to default parameter','wpTube'));
	}

	if ( isset($_POST['uninstall']) ) {
		check_admin_referer('wt_settings');

		require_once (dirname (__FILE__). '/install.php');
		
		delete_option( "wordtube_options" );
				
		$wpdb->query("DROP TABLE $wpdb->wordtube");
		$wpdb->query("DROP TABLE $wpdb->wordtube_playlist");
		$wpdb->query("DROP TABLE $wpdb->wordtube_med2play");
		
		wordTubeAdmin::render_message(__('Tables and settings deleted, deactivate the plugin now','wpTube'));
	}
	
	?>
	<script type="text/javascript">
		jQuery(function() {
			jQuery('#slider > ul').tabs({ fxFade: true, fxSpeed: 'fast' });
		});
		function setcolor(fileid,color) {
			jQuery(fileid).css("background", color );
		};
	</script>
	
	<div id="slider" class="wrap">
	
		<ul id="tabs">
			<li><a href="#generaloptions"><?php _e('General Options', 'wpTube') ;?></a></li>
			<li><a href="#player"><?php _e('Media Player', 'wpTube') ;?></a></li>
			<li><a href="#playlist"><?php _e('Playlist', 'wpTube') ;?></a></li>
			<li><a href="#layout"><?php _e('Layout', 'wpTube') ;?></a></li>
			<li><a href="#longtail"><?php _e('LongTail Adsolution', 'wpTube') ;?></a></li>
			<li><a href="#setup"><?php _e('Setup', 'wpTube') ;?></a></li>
		</ul>

		<!-- General Options -->

		<div id="generaloptions">
			<h2><?php _e('General Options','wpTube'); ?></h2>
			<form name="generaloptions" method="post">
			<?php wp_nonce_field('wt_settings') ?>
			<input type="hidden" name="page_options" value="usewpupload,uploadurl,deletefile,xhtmlvalid,activaterss,rssmessage" />
				<table class="form-table">
					<tr>
						<th valign="top"><?php _e('Upload folder','wpTube') ?>:</th>
						<td>
							<label><input name="usewpupload" type="radio" value="1" <?php checked( true, $wt_options['usewpupload']); ?> /> <?php _e('Standard upload folder : ','wpTube') ?></label><code><?php echo get_option('upload_path'); ?></code><br />
							<label><input name="usewpupload" type="radio" value="0" <?php checked( false, $wt_options['usewpupload']); ?> /> <?php _e('Store uploads in this folder : ','wpTube') ?></label>
							<input type="text" size="50" maxlength="200" name="uploadurl" value="<?php echo $wt_options['uploadurl'] ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th><?php _e('Delete file with post','wpTube') ?></th>
						<td><input type="checkbox" name="deletefile" value="1" <?php checked('1', $wt_options['deletefile']); ?> />
						<?php _e('Should the media file be deleted, when pressing delete ? ','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Try XHTML validation (with CDATA)','wpTube') ?>:</th>
						<td><input name="xhtmlvalid" type="checkbox" value="1" <?php checked('1', $wt_options['xhtmlvalid']); ?> />
						<?php _e('Insert CDATA and a comment code. Important : Recheck your webpage with all browser types.','wpTube') ?></td>
					</tr>
					<tr valign="top">
						<th><?php _e('Activate RSS Feed message','wpTube') ?></th>
						<td><input name="activaterss" type="checkbox" value="1" <?php checked('1', $wt_options['activaterss']); ?> />
							<input type="text" name="rssmessage" value="<?php echo $wt_options['rssmessage'] ?>" size="50" maxlength="200" />
						</td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</form>	
		</div>	
		
		<!-- Media Player settings -->
		
		<div id="player">
			<h2><?php _e('Media Player','wpTube'); ?></h2>
			<form name="playersettings" method="POST" action="<?php echo $filepath.'#player'; ?>" >
			<?php wp_nonce_field('wt_settings') ?>
			<input type="hidden" name="page_options" value="repeat,stretching,displayclick,quality,showfsbutton,volume,bufferlength,media_width,media_height,startsingle" />
				<p> <?php _e('These settings are valid for all your flash video. The settings are used in the JW Flash Media Player Version 4.1', 'wpTube') ?> <br />
					<?php _e('See more information on the web page', 'wpTube') ?> <a href="http://www.jeroenwijering.com/?item=JW_FLV_Media_Player" target="_blank">Flash Media Player from Jeroen Wijering</a></p>
				<table class="form-table">
					<tr>
						<th><?php _e('Repeat','wpTube') ?></th>
						<td><input name="repeat" type="checkbox" value="1" <?php checked(true , $wt_options['repeat']); ?> />
						    <?php _e('Automatically repeat playing when a file is completed.','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Resize images','wpTube') ?></th>
						<td>
						<select size="1" name="stretching">
							<option value="exactfit" <?php selected("exactfit" , $wt_options['stretching']); ?> ><?php _e('exact fit', 'wpTube') ;?></option>
							<option value="fill" <?php selected("fill" , $wt_options['stretching']); ?> ><?php _e('fill', 'wpTube') ;?></option>
							<option value="uniform" <?php selected("uniform" , $wt_options['stretching']); ?> ><?php _e('uniform', 'wpTube') ;?></option>
							<option value="none" <?php selected("none" , $wt_options['stretching']); ?> ><?php _e('none', 'wpTube') ;?></option>
						</select>
						<br />
						<?php _e('Defines how to resize images in the display. Can be none (no stretching), exactfit (disproportionate), uniform (stretch with black borders) or fill (uniform, but completely fill the display).','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Click option','wpTube') ?></th>
						<td>
						<select size="1" name="displayclick">
							<option value="play" <?php selected("play" , $wt_options['displayclick']); ?> ><?php _e('play', 'wpTube') ;?></option>
							<option value="link" <?php selected("link" , $wt_options['displayclick']); ?> ><?php _e('link', 'wpTube') ;?></option>
							<option value="fullscreen" <?php selected("fullscreen" , $wt_options['displayclick']); ?> ><?php _e('fullscreen', 'wpTube') ;?></option>
							<option value="none" <?php selected("none" , $wt_options['displayclick']); ?> ><?php _e('none', 'wpTube') ;?></option>
							<option value="mute" <?php selected("mute" , $wt_options['displayclick']); ?> ><?php _e('mute', 'wpTube') ;?></option>
							<option value="next" <?php selected("next" , $wt_options['displayclick']); ?> ><?php _e('next', 'wpTube') ;?></option>
						</select>
						<br />
						<?php _e('Select what to do when one clicks the display. Can be play, link, fullscreen, none, mute, next.','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('High quality video','wpTube') ?></th>
						<td><input name="quality" type="checkbox" value="1" <?php checked(true , $wt_options['quality']); ?> />
						<?php _e('Enables high-quality playback. This sets the smoothing of videos on/off, the deblocking of videos on/off and the dimensions of the camera small/large.','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Enable Fullscreen','wpTube') ?></th>
						<td><input name="showfsbutton" type="checkbox" value="1" <?php checked(true , $wt_options['showfsbutton']); ?> />
						<?php _e('Show the fullscreen button.','wpTube') ?></td>
					</tr>
					<tr>					
						<th><?php _e('Volume','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="volume" value="<?php echo $wt_options['volume'] ?>" />
						<?php _e('Startup volume of the Flash player (default 80).','wpTube') ?></td>
					</tr>
					<tr>					
						<th><?php _e('Buffer length','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="bufferlength" value="<?php echo $wt_options['bufferlength'] ?>" />
						<?php _e('Number of seconds a media file should be buffered ahead before the player starts it. Set this smaller for fast connections or short videos. Set this bigger for slow connections (default 5).','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Default size (W x H)','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="4" name="media_width" value="<?php echo $wt_options['media_width'] ?>" /> x
						<input type="text" size="3" maxlength="4" name="media_height" value="<?php echo $wt_options['media_height'] ?>" />
						<?php _e('Define width and height of the media player screen.','wpTube') ?></td>
					</tr>	
					<tr>
						<th><?php _e('Autostart first single media','wpTube') ?></th>
						<td><input name="startsingle" type="checkbox" value="1" <?php checked(true , $wt_options['startsingle']); ?> />
						<?php _e('If checked, first media in a single post will automatically start.','wpTube') ?></td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</form>	
		</div>
		
		<!--Playlist Settings -->
		
		<div id="playlist">
			<h2><?php _e('Playlist Settings','wpTube'); ?></h2>
			<form name="playlistsettings" method="POST" action="<?php echo $filepath.'#playlist'; ?>" >
			<?php wp_nonce_field('wt_settings') ?>
			<input type="hidden" name="page_options" value="autostart,shuffle,width,height,playlistsize,playlist" />
				<p><?php _e('You can show all videos/media files in a playlist. Show the media player with the tag', 'wpTube') ?> <strong> [playlist id=XXX]</strong></p>
					<table class="form-table">
					<tr>
						<th><?php _e('Autostart','wpTube') ?></th>
						<td><input name="autostart" type="checkbox" value="1" <?php checked(true , $wt_options['autostart']); ?> />
						<?php _e('Automatically start playing the media files.','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Shuffle mode','wpTube') ?></th>
						<td><input name="shuffle" type="checkbox" value="1" <?php checked(true , $wt_options['shuffle']); ?> />
						<?php _e('Activate the shuffle mode in the playlist','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Default size (W x H)','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="4" name="width" value="<?php echo $wt_options['width'] ?>" /> x
						<input type="text" size="3" maxlength="4" name="height" value="<?php echo $wt_options['height'] ?>" />
						<?php _e('Define width and height of the Media Player when a playlist is shown','wpTube') ?></td>
					</tr>
					<tr>					
						<th><?php _e('Playlist size','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="playlistsize" value="<?php echo $wt_options['playlistsize'] ?>" /><br />						
						<?php _e('Size of the playlist. When below or above, this refers to the height, when right, this refers to the width of the playlist','wpTube') ?></td>
					</tr>
					<tr>
						<th><?php _e('Playlist position','wpTube') ?></th>
						<td>
						<select size="1" name="playlist">
							<option value="bottom" <?php selected("bottom" , $wt_options['playlist']); ?> ><?php _e('bottom', 'wpTube') ;?></option>
							<option value="over" <?php selected("over" , $wt_options['playlist']); ?> ><?php _e('over', 'wpTube') ;?></option>
							<option value="right" <?php selected("right" , $wt_options['playlist']); ?> ><?php _e('right', 'wpTube') ;?></option>
							<option value="none" <?php selected("none" , $wt_options['playlist']); ?> ><?php _e('none', 'wpTube') ;?></option>
						</select>
						<br />
						<?php _e('Position of the playlist. Can be set to bottom, over, right or none.','wpTube') ?></td>
					</tr>
					</table>

			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</form>	
		</div>

		<!--Layout -->
		
		<div id="layout">
			<h2><?php _e('Layout  / Skin','wpTube'); ?></h2>
			<form name="layout" method="POST" action="<?php echo $filepath.'#layout'; ?>" >
			<?php wp_nonce_field('wt_settings') ?>
			<input type="hidden" name="page_options" value="controlbar,skinurl,usewatermark,watermarkurl,backcolor,frontcolor,lightcolor,screencolor" />
				<p><?php _e('Here you can change the colors and skin of your player and playlist', 'wpTube') ?> </p>
					<table class="form-table">
						<tr>
							<th><?php _e('Controls position','wpTube') ?></th>
							<td>
							<select size="1" name="controlbar">
								<option value="bottom" <?php selected("bottom" , $wt_options['controlbar']); ?> ><?php _e('bottom', 'wpTube') ;?></option>
								<option value="over" <?php selected("over" , $wt_options['controlbar']); ?> ><?php _e('over', 'wpTube') ;?></option>
								<option value="none" <?php selected("none" , $wt_options['controlbar']); ?> ><?php _e('none', 'wpTube') ;?></option>
							</select>
							<br />
							<?php _e('Position of the controlbar. Can be set to bottom, over and none.','wpTube') ?></td>
						</tr>
						<tr>
							<th><?php _e('Skin file','wpTube') ?></th>
							<td><input type="text" size="60" maxlength="200" name="skinurl" value="<?php echo $wt_options['skinurl'] ?>" />
							<br /><?php _e('URL of a SWF skin file with the player graphics','wpTube') ?></td>
						</tr>
						<tr>
							<th><?php _e('Show logo','wpTube') ?></th>
							<td><input name="usewatermark" type="checkbox" value="1" <?php checked(true , $wt_options['usewatermark']); ?> />
							<input type="text" size="60" maxlength="200" name="watermarkurl" value="<?php echo $wt_options['watermarkurl'] ?>" />
							<br /><?php _e('URL to your watermark (PNG, JPG): ','wpTube') ?></td>
						</tr>						
						<tr>
							<th><?php _e('Background Color','wpTube') ?>:</th>
							<td><input type="text" size="6" maxlength="6" id="backcolor" name="backcolor" onchange="setcolor('#previewBack', this.value)" value="<?php echo $wt_options['backcolor'] ?>" />
							<input type="text" size="1" readonly="readonly" id="previewBack" style="background-color: #<?php echo $wt_options['backcolor'] ?>" />
							<?php _e('Background color of the controlbar and playlist','wpTube') ?></td>
						</tr>
						<tr>					
							<th><?php _e('Texts / Buttons Color','wpTube') ?>:</th>
							<td><input type="text" size="6" maxlength="6" id="frontcolor" name="frontcolor" onchange="setcolor('#previewFront', this.value)" value="<?php echo $wt_options['frontcolor'] ?>" />
							<input type="text" size="1" readonly="readonly" id="previewFront" style="background-color: #<?php echo $wt_options['frontcolor'] ?>" />
							<?php _e('Color of all icons and texts in the controlbar and playlist','wpTube') ?></td>
						</tr>
						<tr>					
							<th><?php _e('Rollover / Active Color','wpTube') ?>:</th>
							<td><input type="text" size="6" maxlength="6" id="lightcolor" name="lightcolor" onchange="setcolor('#previewLight', this.value)" value="<?php echo $wt_options['lightcolor'] ?>" />
							<input type="text" size="1" readonly="readonly" id="previewLight" style="background-color: #<?php echo $wt_options['lightcolor'] ?>" />
							<?php _e('Color of an icon or text when you rollover it with the mouse','wpTube') ?></td>
						</tr>
						<tr>					
							<th><?php _e('Screen Color','wpTube') ?>:</th>
							<td><input type="text" size="6" maxlength="6" id="screencolor" name="screencolor" onchange="setcolor('#previewScreen', this.value)" value="<?php echo $wt_options['screencolor'] ?>" />
							<input type="text" size="1" readonly="readonly" id="previewScreen" style="background-color: #<?php echo $wt_options['screencolor'] ?>" />
							<?php _e('Background color of the display','wpTube') ?></td>
						</tr>
					</table>

			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			
			</form>	
		</div>
		
		<!-- Longtail settings -->
		
		<div id="longtail">
			<h2><?php _e('LongTail Adsolution','wpTube'); ?></h2>
			<form name="longtail" method="POST" action="<?php echo $filepath.'#longtail'; ?>" >
			<?php wp_nonce_field('wt_settings') ?>
			<input type="hidden" name="page_options" value="activateAds,LTapiScript,LTchannelID" />
				<p><?php _e('With LongTail Adsolution you can embed any ad tag into your media player, allowing them to run pre-, overlay mid- and post-roll advertisements.', 'wpTube') ?><br />
					<?php _e('See more information on the web page', 'wpTube') ?> <a href="http://www.longtailvideo.com/adsolution.asp" target="_blank">LongTail Adsolution</a></p>
				<table class="form-table">
					<tr>
						<th valign="top"><?php _e('Activate Ads','wpTube') ?>:</th>
						<td><input name="activateAds" type="checkbox" value="1" <?php checked('1', $wt_options['activateAds']); ?> /></td>
					</tr>
					<tr>
						<th valign="top"><?php _e('API Script','wpTube') ?>:</th>
						<td><textarea name="LTapiScript" cols="80" rows="3"><?php echo htmlspecialchars( stripslashes ($wt_options['LTapiScript']) ) ?></textarea>
						<br /><?php _e('Look for the script code at your', 'wpTube') ?> <a href="http://dashboard.longtailvideo.com/default.aspx" target="_blank"><?php _e('LongTail dashboard', 'wpTube') ?></a> 
						</td>
					</tr>
					<tr>
						<th valign="top"><?php _e('Channel ID','wpTube') ?>:</th>
						<td><input type="text" name="LTchannelID" value="<?php echo $wt_options['LTchannelID'] ?>" size="10" />
						</td>
					</tr>
				</table>
			<div class="submit"><input type="submit" name="updateoption" value="<?php _e('Update') ;?> &raquo;"/></div>
			</form>	
		</div>
		
		<!-- Setup -->
		
		<div id="setup">
		<form name="setup" method="POST" action="<?php echo $filepath.'#setup'; ?>" >
		<?php wp_nonce_field('wt_settings') ?>
			<h2><?php _e('Setup','wpTube'); ?></h2>
			<p><?php _e('You can reset all options/settings to the default installation.', 'wpTube') ;?></p>
			<div align="center"><input type="submit" class="button" name="resetdefault" value="<?php _e('Reset settings', 'wpTube') ;?>" onclick="javascript:check=confirm('<?php _e('Reset all options to default settings ?\n\nChoose [Cancel] to Stop, [OK] to proceed.\n','wpTube'); ?>');if(check==false) return false;" /></div>
			<div>
				<p><?php _e('You don\'t like wordTube ?', 'wpTube') ;?></p>
				<p><?php _e('No problem, before you deactivate this plugin press the Uninstall Button, because deactivating wordTube does not remove any data that may have been created. ', 'wpTube') ;?>
			</div>
			<p><font color="red"><strong><?php _e('WARNING:', 'wpTube') ;?></strong><br />
			<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to backup all the tables first.', 'wpTube') ;?></font></p>
			<div align="center">
				<input type="submit" name="uninstall" class="button delete" value="<?php _e('Uninstall plugin', 'wpTube') ?>" onclick="javascript:check=confirm('<?php _e('You are about to Uninstall this plugin from WordPress.\nThis action is not reversible.\n\nChoose [Cancel] to Stop, [OK] to Uninstall.\n','wpTube'); ?>');if(check==false) return false;"/>
			</div>
		</form>
		</div>
	</div>

	<?php
}

?>