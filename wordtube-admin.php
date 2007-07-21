<?php

/*
+----------------------------------------------------------------+
+	wordtube-admin V1.50
+	by Alex Rabe
+   required for wordtube
+----------------------------------------------------------------+
*/

global $wpdb;

// ### check for player and prefer the mediaplayer	
if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) $thisplayer = 'mp3player.swf';
if (file_exists(WORDTUBE_ABSPATH.'flvplayer.swf')) $thisplayer = 'flvplayer.swf';
if (file_exists(WORDTUBE_ABSPATH.'mediaplayer.swf')) $thisplayer = 'mediaplayer.swf';
if (!$thisplayer) $text = '<font color="red">'.__('The Flash player is not detected. Please recheck if you uploaded it to the wordTube folder.','wpTube').'</font>';

### Define common constant
define('WORDTUBE_RELPATH', '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)));

$base_name = plugin_basename('wordtube/wordtube-admin.php');
$base_page = 'admin.php?page='.$base_name;
$mode = trim($_GET['mode']);
$act_vid = trim($_GET['id']);
$act_pid = trim($_GET['pid']);

$wordtube_options=get_option('wordtube_options');

if ($wordtube_options[usewpupload]) {
	$wptfile_abspath = ABSPATH.get_option('upload_path').'/';
	$wp_urlpath = get_option('siteurl').'/'.get_option('upload_path').'/';
}
else {
 	$wptfile_abspath = ABSPATH.$wordtube_options[uploadurl].'/';
 	$wp_urlpath = get_option('siteurl').'/'.$wordtube_options[uploadurl].'/';
}

// ### Start the button form processing 
if (isset($_POST['do'])){
	switch(key($_POST['do'])) {
		case 0:	// ADD VIDEO
			$act_name = trim($_POST['name']);
			$act_creator = trim($_POST['creator']);
			$act_filepath = addslashes(trim($_POST['filepath']));
			$act_image = addslashes(trim($_POST['urlimage']));
			$act_width = trim($_POST['width']);
			$act_height = trim($_POST['height']);
			
			if ($act_height < 20 ) $act_height = 20 ;
			if ($act_width == 0 ) $act_width = 320 ;
			
			$upload_path_video = $wptfile_abspath.$_FILES['video_file']['name'];  // set upload path
			$upload_path_image = $wptfile_abspath.$_FILES['image_file']['name'];  // set upload path
			
			if($_FILES['video_file']['error']== 0) {
	 				move_uploaded_file($_FILES['video_file']['tmp_name'], $upload_path_video); // save temp file
					@chmod ($upload_path_video, 0666) or die ('<div class="updated"><p><strong>'.__('Unable to change permissions for file ', 'wpTube').$upload_path_image.'!</strong></p></div>');
	 				if (empty($act_name)) $act_name = $_FILES['video_file']['name'];
	 				if (file_exists($upload_path_video)) {
				 	 	$act_filepath = $wp_urlpath.$_FILES['video_file']['name'];
	 					if($_FILES['image_file']['error']== 0) {
						 	move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path_image); // save temp file
							@chmod ($upload_path_image, 0666) or die ('<div class="updated"><p><strong>'.__('Unable to change permissions for file ', 'wpTube').$upload_path_image.'!</strong></p></div>');
						 	if (file_exists($upload_path_image)) $act_image = $wp_urlpath.$_FILES['image_file']['name'];;
						}	
						$mode ='none';
					} else $text = '<font color="red">'.__('ERROR : File cannot be saved. Check the permission of the wordpress upload folder','wpTube').'</font>';
		 	} else $text = '<font color="red">'.__('ERROR : Upload failed. Check the file size','wpTube').'</font>';

			if (!empty($act_filepath)) {
				$insert_video = $wpdb->query(" INSERT INTO $wpdb->wordtube ( name, creator, file, image, width, height ) 
				VALUES ( '$act_name','$act_creator', '$act_filepath', '$act_image', '$act_width', '$act_height' )");
				if ($insert_video != 0) {
			 		$video_aid = $wpdb->insert_id;  // get index_id
					$text = '<font color="green">'.__('Media file','wpTube').' '.$video_aid.__(' added successfully','wpTube').'</font>';
				}
			}
		
			$mode = 'main';
		break;

		case 1:	// UPDATE VIDEO
			// read the $_POST values
			$act_name = addslashes(trim($_POST['act_name']));
			$act_creator = addslashes(trim($_POST['act_creator']));
			$act_filepath = addslashes(trim($_POST['act_filepath']));
			$act_image = addslashes(trim($_POST['act_image']));
			$act_link = addslashes(trim($_POST['act_link']));
			$act_width = addslashes(trim($_POST['act_width']));
			$act_height = addslashes(trim($_POST['act_height']));
			$act_counter = addslashes(trim($_POST['act_counter']));
			$act_autostart = $_POST['autostart'];
			$act_playlist = $_POST[playlist];
			
			if ($act_height < 20 ) $act_height = 20 ;
			if ($act_width == 0 ) $act_width = 320 ;
			
			if (!$act_playlist) $act_playlist = array();
			if (empty($act_autostart)) $act_autostart = 0; // need now for sql_mode, see http://bugs.mysql.com/bug.php?id=18551
						
			// Read the old playlist status
			$old_playlist = $wpdb->get_col(" SELECT playlist_id FROM $wpdb->wordtube_med2play WHERE media_id = $act_vid");
			if (!$old_playlist) {	
			 	$old_playlist = array();
			} else { 
				$old_playlist = array_unique($old_playlist);
			}
			
			// Delete any ?
			$delete_list = array_diff($old_playlist,$act_playlist);

			if ($delete_list) {
				foreach ($delete_list as $del) {
					$wpdb->query(" DELETE FROM $wpdb->wordtube_med2play WHERE playlist_id = $del AND media_id = $act_vid ");
				}
			}
			
			// Add any? 
			$add_list = array_diff($act_playlist, $old_playlist);

			if ($add_list) {
				foreach ($add_list as $new_list) {
					$wpdb->query(" INSERT INTO $wpdb->wordtube_med2play (media_id, playlist_id) VALUES ($act_vid, $new_list)");
				}
			}
			
			if(!empty($act_filepath)) {
				$wpdb->query("UPDATE $wpdb->wordtube SET name = '$act_name', creator = '$act_creator', file='$act_filepath' , image='$act_image' , link='$act_link' , width='$act_width' , height='$act_height' , autostart='$act_autostart' , counter='$act_counter' WHERE vid = '$act_vid' ");
			}
			// Finished
			$text = '<font color="green">'.__('Update Successfully','wpTube').'</font>';
		break;

		case 2:	// CANCEL
			$mode = 'main';
		break;

		case 3:	// ADD PLAYLIST
			$mode = 'playlist';
			$p_name = addslashes(trim($_POST['p_name']));
			$p_description = addslashes(trim($_POST['p_description']));
			$p_playlistorder = $_POST['sortorder'];
			if (empty($p_playlistorder)) $p_playlistorder = "ASC";
			if(!empty($p_name)) {
				$insert_plist = $wpdb->query(" INSERT INTO $wpdb->wordtube_playlist (playlist_name, playlist_desc, playlist_order) VALUES ('$p_name', '$p_description', '$p_playlistorder')"); 
				if ($insert_plist != 0) {
			 		$pid = $wpdb->insert_id;  // get index_id
					$text = '<font color="green">'.__('Playlist','wpTube').' '.$pid.__(' added successfully','wpTube').'</font>';
				}
			}
		break;
		
		case 4:	// UPDATE PLAYLIST
			$mode = 'playlist';
			$p_id = ($_POST['p_id']);
			$p_name = addslashes(trim($_POST['p_name']));
			$p_description = addslashes(trim($_POST['p_description']));
			$p_playlistorder = $_POST['sortorder']; 
			if(!empty($p_name)) {
				$wpdb->query(" UPDATE $wpdb->wordtube_playlist SET playlist_name = '$p_name', playlist_desc = '$p_description', playlist_order = '$p_playlistorder' WHERE pid = '$p_id' "); 
				$text = '<font color="green">'.__('Update Successfully','wpTube').'</font>';
			}
		break;
	}
}
if ($mode == 'edit'){
	// edit table
	$act_videoset = $wpdb->get_row("SELECT * FROM $wpdb->wordtube WHERE vid = $act_vid ");
	$act_name = htmlspecialchars(stripslashes($act_videoset->name));
	$act_creator = htmlspecialchars(stripslashes($act_videoset->creator));
	$act_filepath = stripslashes($act_videoset->file);
	$act_image = stripslashes($act_videoset->image);
	$act_link = stripslashes($act_videoset->link);
	$act_width = stripslashes($act_videoset->width);
	$act_height = stripslashes($act_videoset->height);
	$act_counter = $act_videoset->counter;
	if ($act_videoset->autostart) $autostart='checked="checked"';

	$flashplayer  = 'var so = new SWFObject("..'.WORDTUBE_RELPATH.'/'.$thisplayer.'", "mediapreview", "'.$act_width.'", "'.$act_height.'", "8", "#FFFFFF");';
	$flashplayer .= "\n\t\t\t\t".'so.addVariable("file", "'.$act_filepath.'");';
	$flashplayer .= "\n\t\t\t\t".'so.addVariable("image", "'.$act_image.'");';
	if ($wordtube_options[overstretch]) $flashplayer .= "\n\t\t\t\t".'so.addVariable("overstretch", "true");';
	?>
	<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
	<!-- Edit Video -->
	<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
	<script type="text/javascript" src="../wp-includes/js/tw-sack.js"></script>
	<script type="text/javascript" src="<?php echo WORDTUBE_URLPATH ?>dbx-key.js"></script>
	<div class="wrap">
		<h2><?php _e('Edit media file', 'wpTube') ?></h2>
		<div id="poststuff">
		<form name="table_options" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" id="video_options">
		<!--TODO: AJAX INTEGRATION -->
			<div id="moremeta" style="width:14.4em;">
				<div id="wptoptions" class="dbx-group">
					<fieldset class="dbx-box" id="playlistdiv">
						<h3 class="dbx-handle"><?php _e('Select Playlist','wpTube') ?></h3>
						<div class="dbx-content" >
							<p id="jaxcat"></p>
							<div id="playlistchecklist"><?php get_playlist_for_dbx($act_vid); ?></div>
						</div>
					</fieldset>
					<fieldset class="dbx-box" id="autostartdiv">
						<h3 class="dbx-handle"><?php _e('Autostart','wpTube') ?></h3>
						<div class="dbx-content" >
							<label class="selectit"><input name="autostart" type="checkbox" value="1"  <?php echo $autostart ?> /> <?php _e('Start file automatic ','wpTube') ?></label>
						</div>
					</fieldset>
					<fieldset class="dbx-box" id="clickcounterdiv">
						<h3 class="dbx-handle"><?php _e('Edit view counter','wpTube') ?></h3>
						<div class="dbx-content" >
							<input type="text" size="5" maxlength="5" name="act_counter" value="<?php echo "$act_counter" ?>" />
						</div>
					</fieldset>
				</div>
			</div>
		<!--END DBX-BOX -->
		<p><?php _e('Here you can edit the selected file. See global settings for the Flash Player under', 'wpTube') ?> <a href="options-general.php?page=wordtube/wordtube-options.php"><?php _e('Options->wordTube', 'wpTube')?></a> <br />
		<?php _e('If you want to show this media file in your page, enter the tag :', 'wpTube') ?><strong> [MEDIA=<?php echo $act_vid; ?>]</strong></p>
			<fieldset class="options"> 
				<table class="optiontable">
					<tr>
						<th scope="row"><?php _e('Media title','wpTube') ?></th>
						<td><input type="text" size="50"  name="act_name" value="<?php echo "$act_name" ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Creator / Author','wpTube') ?></th>
						<td><input type="text" size="50"  name="act_creator" value="<?php echo "$act_creator" ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Media URL','wpTube') ?></th>
						<td><input type="text" size="80"  name="act_filepath" value="<?php echo "$act_filepath" ?>" />
						<br /><?php _e('Here you need to enter the absolute URL to the file (MP3,FLV,SWF,JPG,PNG or GIF)','wpTube') ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Thumbnail URL','wpTube') ?></th>
						<td><input type="text" size="80"  name="act_image" value="<?php echo "$act_image" ?>" />
						<br /><?php _e('Enter the URL to show a preview of the media file','wpTube') ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Link URL','wpTube') ?></th>
						<td><input type="text" size="80" name="act_link" value="<?php echo "$act_link" ?>" />
						<br /><?php _e('Enter the URL to the page/file, if you click on the player','wpTube') ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default player size (Width x Heigth)','wpTube') ?></th>
						<td><input type="text" size="5" maxlength="5" name="act_width" value="<?php echo "$act_width" ?>" /> x
						<input type="text" size="5" maxlength="5" name="act_height" value="<?php echo "$act_height" ?>" />
						<?php _e('Note on heigth : 20 pixel are used for the player itself','wpTube') ?></td>
					</tr>
				</table>
				<div class="submit"><input type="submit" name="do[2]" value="<?php _e('Cancel'); ?>" class="button" />
				<input type="submit" name="do[1]" value="<?php _e('Update'); ?> &raquo;" class="button" /></div>
			</fieldset>
		</form>
		</div>
		<h2><?php _e('Preview', 'wpTube') ?></h2>
		<center>
		<p id="flvpreview">
		<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>
		<script type="text/javascript">
	    	//<![CDATA[
				<?php echo $flashplayer ?>
				so.addVariable("showdigits", "true");
				so.addParam("wmode", "transparent");
				so.write("flvpreview");
			//]]>
		</script>
		</center>
	</div>
	<?php
}

if ($mode == 'delete'){	  
 	// Delete A video

	if ($wordtube_options[deletefile]) {
		$act_videoset = $wpdb->get_row("SELECT * FROM $wpdb->wordtube WHERE vid = $act_vid ");
		$act_filename = wpt_filename($act_videoset->file);
		if (!empty($act_filename))
			{
			$wpt_checkdel = @unlink($wptfile_abspath.$act_filename);
			if(!$wpt_checkdel) $text = '<font color="red">'.__('Error in deleting file','wpTube').'</font>';					
		}
	
		$act_filename = wpt_filename($act_videoset->image);
		if (!empty($act_filename))
			{
			$wpt_checkdel = @unlink($wptfile_abspath.$act_filename);
			if(!$wpt_checkdel) $text = '<font color="red">'.__('Error in deleting file','wpTube').'</font>';					
		}
	} 
	//TODO: The problem of this routine : if somebody change the path, after he uploaded some files
	
	$wpdb->query("DELETE FROM $wpdb->wordtube_med2play WHERE media_id = $act_vid");
	
	$delete_video = $wpdb->query("DELETE FROM $wpdb->wordtube WHERE vid = $act_vid");
	
	if(!$delete_video) {
	 	$text = '<font color="red">'.__('Error in deleting media file','wpTube').' \''.$act_vid.'\' </font>';
	}
	if(empty($text)) {
		$text = '<font color="green">'.__('Media file','wpTube').' \''.$act_vid.'\' '.__('deleted successfully','wpTube').'</font>';
	}

	$mode = 'main'; // show main page
}

if ($mode == 'add'){	  
 	// Add A table
 	?>
		<!-- Add A Video -->
		<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
		<div class="wrap">
			<h2><?php _e('Add a new media file','wpTube'); ?></h2>
			<form id="addvideo" action="<?php echo $base_page; ?>" enctype="multipart/form-data" method="post">
				<fieldset class="options"> 
				<table class="optiontable">
					<tr>
						<th scope="row"><?php _e('Title / Name','wpTube') ?></th>
						<td><input type="text" size="50" maxlength="200" name="name" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Creator / Author','wpTube') ?></th>
						<td><input type="text" size="50" maxlength="200" name="creator" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Media file size (Width x Heigth)','wpTube') ?></th>
						<td><input type="text" size="4" maxlength="4" name="width" value="320" /> x
						<input type="text" size="4" maxlength="4" name="height" value="240" /></td>
					</tr>					
				</table>
				<legend><?php _e('Upload file','wpTube') ?></legend>
				<table class="optiontable">	
					<tr>
						<th scope="row"><?php _e('Select media file','wpTube') ?></th>
						<td><input type="file" size="50" name="video_file" />
						<br /><?php echo _e('Note : The upload limit on your server is ','wpTube') . "<strong>" . ini_get('upload_max_filesize') . "Byte</strong>\n"; ?>
						<br /><?php echo _e('The Flash Media Player handle : MP3,FLV,SWF,JPG,PNG or GIF','wpTube') ?>
					</tr>
					<tr>
						<th scope="row"><?php _e('Select thumbnail','wpTube') ?></th>
						<td><input type="file" size="50" name="image_file" />
						<br /><?php _e('Upload a image to show a preview of the media file (optional)','wpTube') ?></td>
					</tr>
					</table>
					<legend><?php _e('Enter URL to file','wpTube') ?></legend>
					<table class="optiontable">	
					<tr>
						<th scope="row"><?php _e('URL to media file','wpTube') ?></th>
						<td><input type="text" size="50" name="filepath" />
						<br /><?php _e('Here you need to enter the absolute URL to the media file','wpTube') ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('URL to thumbnail file','wpTube') ?></th>
						<td><input type="text" size="50" name="urlimage" />
						<br /><?php _e('Enter the URL to show a preview of the media file (optional)','wpTube') ?></td>
					</tr>					
					</table>
					<p class="submit"><input type="submit" name="do[0]" value="<?php _e('Add media file','wpTube'); ?> &raquo;" class="button" /></p>
				</fieldset>
			</form>
		</div>
	<?php
}

if ($mode == 'plydel'){	  
 	// Delete a playlist
 	$delete_plist = $wpdb->query("DELETE FROM $wpdb->wordtube_playlist WHERE pid = $act_pid");
	
	if($delete_plist) {
		$text = '<font color="green">'.__('Playlist','wpTube').' \''.$act_pid.'\' '.__('deleted successfully','wpTube').'</font>';
	}
 	$mode = 'playlist'; // show playlist
}

if (($mode == 'playlist') or ($mode == 'plyedit')) {  
 	// Edit or update playlst
		
 	$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube_playlist ");
 	if ($mode == 'plyedit')	$update = $wpdb->get_row("SELECT * FROM $wpdb->wordtube_playlist WHERE pid = $act_pid ");
 	?>
	<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>	
		<!-- Edit Playlist -->
		<div class="wrap">
			<h2><?php _e('Manage Playlist','wpTube'); ?></h2>
			<form id="editplist" action="<?php echo $base_page; ?>" method="post">
				<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">
				<thead>
				<tr>
					<th scope="col"><?php _e('ID','wpTube'); ?></th>
					<th scope="col"><?php _e('Name','wpTube'); ?></th>
					<th scope="col"><?php _e('Description','wpTube'); ?></th>
					<th scope="col" colspan="2"><?php _e('Action'); ?></th>
				</tr>
				</thead>
				<?php
					if($tables) {
						$i = 0;
						foreach($tables as $table) {
						 	if($i%2 == 0) {
								echo "<tr class='alternate'>\n";
							}  else {
								echo "<tr>\n";
							}
							echo "<th scope=\"row\">$table->pid</th>\n";
							echo "<td>".stripslashes($table->playlist_name)."</td>\n";
							echo "<td>".stripslashes($table->playlist_desc)."</td>\n";
							echo "<td><a href=\"$base_page&amp;mode=plyedit&amp;pid=$table->pid#addplist\" class=\"edit\">".__('Edit')."</a></td>\n";
							echo "<td><a href=\"$base_page&amp;mode=plydel&amp;pid=$table->pid\" class=\"delete\" onclick=\"javascript:check=confirm( '".__("Delete this file ?",'wpTube')."');if(check==false) return false;\">".__('Delete')."</a></td>\n";
							echo '</tr>';
							$i++;
						}
					} else {
						echo '<tr><td colspan="7" align="center"><b>'.__('No entries found','wpTube').'</b></td></tr>';
					}
				?>
				</table><br />					
				<div class="submit"><input type="submit" name="do[2]" value="<?php _e('Cancel'); ?>" class="button" /></div>
			</form>
		</div>
		<div class="wrap">
			<h2><?php
			if ($mode == 'playlist') echo _e('Add Playlist','wpTube');
			if ($mode == 'plyedit') echo _e('Update Playlist','wpTube');
			?></h2>
			<form id="addplist" action="<?php echo $base_page; ?>" method="post">
					<input type="hidden" value="<?php echo "$act_pid" ?>" name="p_id"/>
					<p><?php _e('Name:'); ?><br/><input type="text" value="<?php echo $update->playlist_name ?>" name="p_name"/></p>
					<p><?php _e('Description: (optional)'); ?><br/><textarea name="p_description" rows="3" cols="50" style="width: 97%;"><?php echo $update->playlist_desc ?></textarea></p>
					<p><?php _e('Media ID sorting order:','wpTube'); ?> <input name="sortorder" type="radio" value="ASC"  <?php if ($update->playlist_order == 'ASC') echo 'checked="checked"'; ?> /> <?php _e('ascending','wpTube'); ?> 
					<input name="sortorder" type="radio" value="DESC"  <?php if ($update->playlist_order == 'DESC') echo 'checked="checked"'; ?> /> <?php _e('descending','wpTube'); ?></p>	
					<div class="submit"><?php
					if ($mode == 'playlist') echo '<input type="submit" name="do[3]" value="'.__('Add Playlist').' &raquo;" class="button" />';
					if ($mode == 'plyedit') echo '<input type="submit" name="do[4]" value="'.__('Update Playlist').' &raquo;" class="button" />';
					?></div>
			</form>
		</div>
	<?php 
}		

/*** MAIN ADMIN PAGE ***/	
if ((empty($mode)) or ($mode == 'main')) {
 	// check for page navigation
	if ( isset( $_GET['apage'] ) )
		$page = (int) $_GET['apage'];
	else
		$page = 1; 
		
 	$start = $offset = ( $page - 1 ) * 10;
 
	$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY vid ASC LIMIT $start, 10");
	$total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->wordtube ");
	?>
	<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
	<!-- Manage Video-->
		<div class="wrap">
		<h2><?php _e('Manage Media files','wpTube'); ?></h2>
		<!-- Navigation -->
		<?php if ( $total > 10 ) {
		$total_pages = ceil( $total / 10 );
		$r = '';
		if ( 1 < $page ) {
			$args['apage'] = ( 1 == $page - 1 ) ? FALSE : $page - 1;
			$r .=  '<a class="prev" href="'. add_query_arg( $args ) . '">&laquo; '. __('Previous Page') .'</a>' . "\n";
		}
		if ( ( $total_pages = ceil( $total / 10 ) ) > 1 ) {
			for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) {
				if ( $page == $page_num ) {
					$r .=  "<span>$page_num</span>\n";
				} else {
					$p = false;
					if ( $page_num < 3 || ( $page_num >= $page - 3 && $page_num <= $page + 3 ) || $page_num > $total_pages - 3 ) {
						$args['apage'] = ( 1 == $page_num ) ? FALSE : $page_num;
						$r .= '<a class="page-numbers" href="' . add_query_arg($args) . '">' . ( $page_num ) . "</a>\n";
						$in = true;
					} elseif ( $in == true ) {
						$r .= "...\n";
						$in = false;
					}
				}
			}
		}
		if ( ( $page ) * 10 < $total || -1 == $total ) {
			$args['apage'] = $page + 1;
			$r .=  '<a class="next" href="' . add_query_arg($args) . '">'. __('Next Page') .' &raquo;</a>' . "\n";
		}
		echo "<p class='pagenav'>$r</p>\n";
		?>
		<?php } ?>
			<!-- Table -->
			<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">
			<thead>
			<tr>
				<th scope="col"><?php _e('ID','wpTube'); ?></th>
				<th scope="col"><?php _e('Title','wpTube'); ?></th>
				<th scope="col"><?php _e('Creator','wpTube'); ?></th>					
				<th scope="col"><?php _e('Path','wpTube'); ?></th>
				<th scope="col"><?php _e('Views','wpTube'); ?></th>
				<th scope="col" colspan="2"><?php _e('Action'); ?></th>
			</tr>
			</thead>
			<?php
				if($tables) {
					$i = 0;
					foreach($tables as $table) {
					 	if($i%2 == 0) {
							echo "<tr class='alternate'>\n";
						}  else {
							echo "<tr>\n";
						}
						echo "<th scope=\"row\">$table->vid</th>\n";
						echo "<td>".stripslashes($table->name)."</td>\n";
						echo "<td>".stripslashes($table->creator)."</td>\n";
						echo "<td>$table->file</td>\n";
						echo "<td>$table->counter</td>\n";
						echo "<td><a href=\"$base_page&amp;mode=edit&amp;id=$table->vid\" class=\"edit\">".__('Edit')."</a></td>\n";
						echo "<td><a href=\"$base_page&amp;mode=delete&amp;id=$table->vid\" class=\"delete\" onclick=\"javascript:check=confirm( '".__("Delete this file ?",'wpTube')."');if(check==false) return false;\">".__('Delete')."</a></td>\n";
						echo '</tr>';
						$i++;
					}
				} else {
					echo '<tr><td colspan="7" align="center"><b>'.__('No entries found','wpTube').'</b></td></tr>';
				}
			?>
			</table>
		<h3><a href="?page=wordtube/<?php echo basename(__FILE__); ?>&mode=add"><?php _e('Insert new media file','wpTube') ?> &raquo;</a></h3>
		</div>
		<!-- Manage Video-->
		<?php
			$show_playlist = $_POST['show_playlist']; // selected playlist
			if (!$show_playlist) $show_playlist = 0 ;
			$count = count($wpdb->get_results("SELECT * FROM $wpdb->wordtube"));
			$act_file = WORDTUBE_URLPATH."myextractXML.php?id=".$show_playlist;
			$act_width = $wordtube_options[width];
			$act_height = $wordtube_options[height] + (($count *43) + 20); // each entry with 43px + 20px for statusbar
			if ($act_height > 665) $act_height = 665; //limit to 15 videos
			
			$flashplayer  = 'var so = new SWFObject("..'.WORDTUBE_RELPATH.'/'.$thisplayer.'", "mediapreview", "'.$act_width.'", "'.$act_height.'", "8", "#FFFFFF");';
			$flashplayer .= "\n\t\t\t\t".'so.addVariable("file", "'.$act_file.'");';
			$flashplayer .= "\n\t\t\t\t".'so.addVariable("displayheight", "'.$wordtube_options[height].'");';
			$flashplayer .= "\n\t\t\t\t".'so.addVariable("thumbsinplaylist", "true");'."\n";
		?>
		<div class="wrap">
		<h2><?php _e('Playlist Preview', 'wpTube') ?> (<a href="?page=wordtube/<?php echo basename(__FILE__); ?>&mode=playlist"><?php _e('Edit','wpTube') ?></a>)</h2>
		<p><?php _e('You can show all videos/media files in a playlist. Show this playlist with the tag', 'wpTube') ?> <strong> [MYPLAYLIST=<?php echo $show_playlist ?>]</strong></p>
		<form name="selectlist" action="?page=wordtube/<?php echo basename(__FILE__); ?>" method="post">
		<legend><?php _e('Select Playlist :', 'wpTube'); ?></legend>
		<select name="show_playlist" id="show_playlist">
		<option value="0" ><?php _e('All files', 'wpTube') ?></option>
		<?php
		$playlists = $wpdb->get_results("SELECT * FROM $wpdb->wordtube_playlist ");
		if($playlists) {
			foreach($playlists as $playlist) {
			 	echo '<option value="'.$playlist->pid.'" ';
				if ($playlist->pid == $show_playlist) echo "selected='selected' ";
				echo '>'.$playlist->playlist_name.'</option>'."\n\t"; 
			}
		}
		?>
		</select>
		<input type="submit" value="<?php _e('OK','wpTube'); ?>"  />
		</form>
		<center>
			<p id="flvpreview">
			<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>
	    	<script type="text/javascript">
	    	//<![CDATA[
				<?php echo $flashplayer ?>
				so.addVariable("shuffle", "false");
				so.addParam("wmode", "transparent");
				so.write("flvpreview");
			//]]>
			</script>
		</center>
		</div>
	<?php

}

?>