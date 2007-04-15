<?php

/*
+----------------------------------------------------------------+
+	wordtube-options V1.41
+	by Alex Rabe
+   required for wordtube
+----------------------------------------------------------------+
*/

//TODO: Insert Show time for images

global $wpdb;

// check for player
if (!file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) 
if (!file_exists(WORDTUBE_ABSPATH.'flvplayer.swf')) 
if (!file_exists(WORDTUBE_ABSPATH.'mediaplayer.swf'))
$text = '<font color="red">'.__('The Flash player is not detected. Please recheck if you uploaded it to the wordTube folder.','wpTube').'</font>';

if ($_POST[wordtube]=='setoptions'){

	$wordtube_options[center]=$_POST[center];
	$wordtube_options[deletefile]=$_POST[deletefile];
	$wordtube_options[usewpupload]=$_POST[usewpupload];
	$wordtube_options[uploadurl]=$_POST[uploadurl];
	$wordtube_options[xhtmlvalid]=$_POST[xhtmlvalid];
	$wordtube_options[activaterss]=$_POST[activaterss];
	$wordtube_options[rssmessage]=$_POST[rssmessage];
	$wordtube_options[repeat]=$_POST[repeat];
	$wordtube_options[overstretch]=$_POST[overstretch];
	$wordtube_options[showdigits]=$_POST[showdigits];
	$wordtube_options[showfsbutton]=$_POST[showfsbutton];
	$wordtube_options[backcolor]=$_POST[backcolor];
	$wordtube_options[frontcolor]=$_POST[frontcolor];
	$wordtube_options[lightcolor]=$_POST[lightcolor];
	$wordtube_options[volume]=$_POST[volume];
	$wordtube_options[bufferlength]=$_POST[bufferlength];
	$wordtube_options[statistic]=$_POST[statistic];
	$wordtube_options[countcomplete]=$_POST[countcomplete];
	$wordtube_options[showeq]=$_POST[showeq];
	$wordtube_options[usewatermark]=$_POST[usewatermark];
	$wordtube_options[watermarkurl]=$_POST[watermarkurl];
	
	$wordtube_options[autostart]=$_POST[autostart];
	$wordtube_options[autoscroll]=$_POST[autoscroll];
	$wordtube_options[thumbnail]=$_POST[thumbnail];
	$wordtube_options[shuffle]=$_POST[shuffle];
	$wordtube_options[width]=$_POST[width];
	$wordtube_options[height]=$_POST[height];
	$wordtube_options[playlistsize]=$_POST[playlistsize];
	$wordtube_options[displaywidth]=$_POST[displaywidth];
	
	update_option('wordtube_options', $wordtube_options);
 	$text = '<font color="green">'.__('Update Successfully','wpTube').'</font>';
}

$wordtube_options=get_option('wordtube_options');

if ($wordtube_options[autostart]) $autostart='checked="checked"';
if ($wordtube_options[repeat]) $repeat='checked="checked"';
if ($wordtube_options[showdigits]) $showdigits='checked="checked"';
if ($wordtube_options[showfsbutton]) $showfsbutton='checked="checked"';
if ($wordtube_options[deletefile]) $deletefile='checked="checked"';
if ($wordtube_options[center]) $center='checked="checked"';
if ($wordtube_options[xhtmlvalid]) $xhtmlvalid='checked="checked"';
if ($wordtube_options[activaterss]) $activaterss='checked="checked"';
if ($wordtube_options[thumbnail]) $thumbnail='checked="checked"';
if ($wordtube_options[shuffle]) $shuffle='checked="checked"';
if ($wordtube_options[statistic]) $statistic='checked="checked"';
if ($wordtube_options[countcomplete]) $countcomplete='checked="checked"';
if ($wordtube_options[showeq]) $showeq='checked="checked"';
if ($wordtube_options[usewatermark]) $usewatermark='checked="checked"';
if ($wordtube_options[autoscroll]) $autoscroll='checked="checked"';

if ($wordtube_options[overstretch]=="true") $os_true="selected='selected'";
if ($wordtube_options[overstretch]=="false") $os_false="selected='selected'";
if ($wordtube_options[overstretch]=="fit") $os_fit="selected='selected'";
if ($wordtube_options[overstretch]=="none") $os_none="selected='selected'";

if ($wordtube_options[usewpupload]) $usewpupload='checked="checked"';
else $usenotwpupload='checked="checked"';

$wp_urlpath = get_settings('siteurl').'/'.get_settings('upload_path').'/';  // get URL path

?>
	<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
	<!-- Option -->
	<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
	<div class="wrap">
		<h2><?php _e('wordTube Option','wpTube'); ?></h2>
		<form name="playeroption" method="post">
		<input type="hidden" name="wordtube" value="setoptions" />
		<fieldset class="options"> 
			<legend><?php _e('General Options'); ?></legend>
			<table border="0" cellspacing="3" cellpadding="3">
				<tr>
					<th align="left"><?php _e('Upload folder','wpTube') ?></th>
					<td><input name="usewpupload" type="radio" value="1" <?php echo $usewpupload ?> /></td> 
					<td><?php _e('Standard upload folder : ','wpTube') ?>
					<code><?php echo $wp_urlpath ?></code></td>
				</tr>
				<tr>
					<th align="left"> </th>
					<td><input name="usewpupload" type="radio" value="0" <?php echo $usenotwpupload ?> /></td> 
					<td><?php _e('Store uploads in this folder : ','wpTube') ?>
					<input type="text" size="50" maxlength="200" name="uploadurl" value="<?php echo $wordtube_options[uploadurl] ?>" /></td>
				</tr>
				<tr>
					<th align="left"> <?php _e('Delete file with post','wpTube') ?> </th>
					<td><input name="deletefile" type="checkbox" value="1"  <?php echo $deletefile ?> /></td> 
					<td align="left"><?php _e('Should the media file deleted, when pressing delete ? ','wpTube') ?></td>
				</tr>
				<tr>
					<th align="left"> <?php _e('Center Flash','wpTube') ?> </th>
					<td><input name="center" type="checkbox" value="1"  <?php echo $center ?> /></td> 
					<td align="left"><?php _e('Center the player with the tag [CENTER]','wpTube') ?></td>
				</tr>
				<tr>
					<th align="left"> <?php _e('XHTML valid','wpTube') ?> </th>
					<td><input name="xhtmlvalid" type="checkbox" value="1"  <?php echo $xhtmlvalid ?> /></td> 
					<td align="left"><?php _e('Insert CDATA and a comment code. Important : Recheck your webpage with all browser types.','wpTube') ?></td>
				</tr>
				<tr>
					<th align="left"> <?php _e('Activate RSS Feed message','wpTube') ?> </th>
					<td><input name="activaterss" type="checkbox" value="1"  <?php echo $activaterss ?> /></td>
					<td><input type="text" size="50" maxlength="200" name="rssmessage" value="<?php echo $wordtube_options[rssmessage] ?>" /></td> 
				</tr>
			</table>
		</fieldset> 	
		<fieldset class="options"> 
		<legend><?php _e('Media Player Option','wpTube'); ?></legend>
		<p><?php _e('These settings are valid for all your flash video. The settings are used in the Flash Media Player Version 3.6', 'wpTube') ?> <br />
		   <?php _e('See more information on the web page', 'wpTube') ?> <a href="http://www.jeroenwijering.com/?item=Flash_Media_Player" target="_blank">Flash Media Player from Jeroen Wijering</a></p>
				<table border="0" cellspacing="3" cellpadding="3">
					<tr>
						<th align="left"><?php _e('Repeat','wpTube') ?></th>
						<td><input name="repeat" type="checkbox" value="1"  <?php echo $repeat ?> /></td> 
						<td align="left"><i><?php _e('Automatically repeat playing when a file is completed.','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Over stretch','wpTube') ?></th>
						<td>
						<select size="1" name="overstretch">
							<option value="true" <?php echo $os_true ?> ><?php _e('true', 'wpTube') ;?></option>
							<option value="false" <?php echo $os_false ?> ><?php _e('false', 'wpTube') ;?></option>
							<option value="fit" <?php echo $os_fit ?> ><?php _e('fit', 'wpTube') ;?></option>
							<option value="none" <?php echo $os_none ?> ><?php _e('none', 'wpTube') ;?></option>
						</select>
						</td>
						<td align="left"><i><?php _e('Over stretch the image/video to fill the entire display.','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Show digits','wpTube') ?></th>
						<td><input name="showdigits" type="checkbox" value="1"  <?php echo $showdigits ?> /></td> 
						<td align="left"><i><?php _e('Show the digits for loaded, elapsed and remaining time in the Flash player.','wpTube') ?></i></td>
					</tr>									
					<tr>
						<th align="left"><?php _e('Enable Fullscreen','wpTube') ?></th>
						<td><input name="showfsbutton" type="checkbox" value="1"  <?php echo $showfsbutton ?> /></td> 
						<td align="left"><i><?php _e('Show the fullscreen button. Note : Javascript objects (i.e Lightbox) are not always on the top.','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Background Color','wpTube') ?></th>
						<td><input type="text" size="6" maxlength="6" name="backcolor" value="<?php echo $wordtube_options[backcolor] ?>" /><input type="text" size="1" readonly="readonly" name="color" style="background-color:	#<?php echo $wordtube_options[backcolor] ?>" /></td>
						<td align="left"><i><?php _e('Backgroundcolor of the Flash player (default FFFFFF).','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Button Color','wpTube') ?></th>
						<td><input type="text" size="6" maxlength="6" name="frontcolor" value="<?php echo $wordtube_options[frontcolor] ?>" /><input type="text" size="1" readonly="readonly" name="color" style="background-color:	#<?php echo $wordtube_options[frontcolor] ?>" /></td>
						<td align="left"><i><?php _e('Texts / buttons color of the Flash player (default 000000).','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Active Color','wpTube') ?></th>
						<td><input type="text" size="6" maxlength="6" name="lightcolor" value="<?php echo $wordtube_options[lightcolor] ?>" /><input type="text" size="1" readonly="readonly" name="color" style="background-color:	#<?php echo $wordtube_options[lightcolor] ?>" /></td>
						<td align="left"><i><?php _e('Rollover/ active color of the Flash player (default 000000).','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Volume','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="volume" value="<?php echo $wordtube_options[volume] ?>" /></td>
						<td align="left"><i><?php _e('Startup volume of the Flash player (default 80).','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Buffer length','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="bufferlength" value="<?php echo $wordtube_options[bufferlength] ?>" /></td>
						<td align="left"><i><?php _e('Number of seconds a media file should be buffered ahead before the player starts it. Set this smaller for fast connections or short videos. Set this bigger for slow connections (default 5).','wpTube') ?></i></td>
					</tr>	
					<tr>					
						<th align="left"><?php _e('Activate statistic','wpTube') ?></th>
						<td><input name="statistic" type="checkbox" value="1"  <?php echo $statistic ?> /></td>
						<td align="left"><i><?php _e('Activate the internal view counter for each media file.','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Count complete','wpTube') ?></th>
						<td><input name="countcomplete" type="checkbox" value="1"  <?php echo $countcomplete ?> /></td>
						<td align="left"><i><?php _e('Count only media files which are played til the end.','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Equalizer','wpTube') ?></th>
						<td><input name="showeq" type="checkbox" value="1"  <?php echo $showeq ?> /></td>
						<td align="left"><i><?php _e('Shows a (purely cosmetic) equalizer in the display (only with the MP3 files).','wpTube') ?></i></td>
					</tr>
					<tr>
					<th align="left"><?php _e('Show logo','wpTube') ?></th>
					<td><input name="usewatermark" type="checkbox" value="1" <?php echo $usewatermark ?> /></td> 
					<td><i><?php _e('URL to your watermark (PNG, JPG): ','wpTube') ?></i>
					<input type="text" size="60" maxlength="200" name="watermarkurl" value="<?php echo $wordtube_options[watermarkurl] ?>" /></td>
				</tr>				
					</table>
			</fieldset>
			<fieldset class="options"> 
				<legend><?php _e('Playlist Settings','wpTube'); ?></legend>
				<p><?php _e('You can show all videos/media files in a playlist. Show the media player with the tag', 'wpTube') ?> <strong> [MYPLAYLIST=ID]</strong></p>
					<table border="0" cellspacing="3" cellpadding="3">
					<tr>
						<th align="left"><?php _e('Autostart','wpTube') ?></th>
						<td><input name="autostart" type="checkbox" value="1"  <?php echo $autostart ?> /></td> 
						<td align="left"><i><?php _e('Automatically start playing the media files.','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Activate autoscroll','wpTube') ?></th>
						<td><input name="autoscroll" type="checkbox" value="1"  <?php echo $autoscroll ?> /></td> 
						<td align="left"><i><?php _e('Let the playlist automatically scroll, based upon the mouse cursor.','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Show thumbnail','wpTube') ?></th>
						<td><input name="thumbnail" type="checkbox" value="1"  <?php echo $thumbnail ?> /></td> 
						<td align="left"><i><?php _e('Show a thumbnail in the playlist.','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Shuffle mode','wpTube') ?></th>
						<td><input name="shuffle" type="checkbox" value="1"  <?php echo $shuffle ?> /></td> 
						<td align="left"><i><?php _e('Activate the shuffle mode in the playlist','wpTube') ?></i></td>
					</tr>
					<tr>
						<th align="left"><?php _e('Flash size (W x H)','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="4" name="width" value="<?php echo "$wordtube_options[width]" ?>" /> x
						<input type="text" size="3" maxlength="4" name="height" value="<?php echo "$wordtube_options[height]" ?>" /></td>
						<td align="left"><i><?php _e('Define width and height of the media player screen.','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Playlist size','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="playlistsize" value="<?php echo $wordtube_options[playlistsize] ?>" /></td>
						<td align="left"><i><?php _e('Define height of the playlist, should be larger the 20. (0 = Disable control bar)','wpTube') ?></i></td>
					</tr>
					<tr>					
						<th align="left"><?php _e('Display width','wpTube') ?></th>
						<td><input type="text" size="3" maxlength="3" name="displaywidth" value="<?php echo $wordtube_options[displaywidth] ?>" /></td>
						<td align="left"><i><?php _e('You can set this to a size smaller than the Flash size width, to make the playlist appear at the right','wpTube') ?></i></td>
					</tr>
					</table>
					<div class="submit"><input type="submit" name="update" value="<?php _e('Update'); ?> &raquo;" class="button" /></div>
			</fieldset>
		</form>
	</div>
<?php

?>