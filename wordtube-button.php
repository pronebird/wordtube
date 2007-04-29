<?php

/*
+----------------------------------------------------------------+
+	wordtube-button V1.44
+	by Alex Rabe
+   required for wordtube
+----------------------------------------------------------------+
*/
$wpconfig = realpath("../../../wp-config.php");

if (!file_exists($wpconfig))  {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}// stop when wp-config is not there

require_once($wpconfig);
require_once(ABSPATH.'/wp-admin/admin.php');

// check for rights
if(!current_user_can('edit_posts')) die;

global $wpdb;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>wordTube Browser</title>
<link rel="stylesheet" href="<?php echo get_settings('siteurl') ?>/wp-admin/wp-admin.css?version=<?php bloginfo('version'); ?>" type="text/css" />
<script type="text/javascript">
function insert_video() {
 
 	if (document.selectform.video.selectedIndex != 0) {
		var thetext= '[MEDIA=' + document.selectform.video.value + ']'; 
	
		mceWindow = window.opener;
		if(mceWindow.tinyMCE) {
			mceWindow.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, thetext);
		} else {
			edCanvas = mceWindow.document.getElementById('content');
			mceWindow.edInsertContent(edCanvas, thetext);
		}
	}
	window.close();
}

function insert_playlist() {
 
	var thetext= '[MYPLAYLIST=' + document.selectform.playlist.value + ']'; 
	
	mceWindow = window.opener;
	if(mceWindow.tinyMCE) {
		mceWindow.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, thetext);
	} else {
		edCanvas = mceWindow.document.getElementById('content');
		mceWindow.edInsertContent(edCanvas, thetext);
	}
	window.close();
}
</script>
</head>
<body>

<div class="wrap">
	<fieldset class="options">
	<legend><?php _e('Select media file', 'wpTube') ;?></legend>
	<form name="selectform" method="post" action="" >
		<input type="hidden" name="wppath" value="<?php echo $wppath ?>"/>
		<table>
		<tr>
		<td><select size="1" name="video" style="width:140px">
			<option value="0"><?php _e('No file', 'wpTube') ;?></option>
		<?php
			$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY 'vid' ASC ");
			if($tables) {
				foreach($tables as $table) {
				echo '<option value="'.$table->vid.'">'.$table->name.'</option>'; 
				}
			}
		?>		
		</select></td>
		<td><input type="submit" name="insert" onclick="javascript:insert_video();" value="<?php _e('Insert Player', 'wpTube'); ?> &raquo;" class="button" /></td>
		</tr>
		<tr>
		<td><select size="1" name="playlist" style="width:140px">
			<option value="0"><?php _e('All files', 'wpTube') ;?></option>
		<?php
			$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube_playlist ORDER BY 'pid' ASC ");
			if($tables) {
				foreach($tables as $table) {
				echo '<option value="'.$table->pid.'">'.$table->playlist_name.'</option>'; 
				}
			}
		?>		
		</select></td>
		<td><input type="submit" name="insert" onclick="javascript:insert_playlist();" value="<?php _e('Insert Playlist', 'wpTube'); ?> &raquo;" class="button" /></td>
		</tr>
		</table>
		</fieldset>
		<div class="submit"><input type="submit" name="exit" onclick="javascript:window.close();" value="<?php _e('Cancel'); ?>" class="button" /></div>
	</form>
</div>
</body>
</html>
<?php

?>