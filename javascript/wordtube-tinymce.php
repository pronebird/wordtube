<?php

/*
+----------------------------------------------------------------+
+	wordtube-tinymce V1.50
+	by Alex Rabe
+   required for wordtube
+----------------------------------------------------------------+
*/
$wpconfig = realpath("../../../../wp-config.php");

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
	<title>wordTube</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
	function init() {
		tinyMCEPopup.resizeToInnerSize();
	}
	
	function insertwpTubeLink() {
		
		var tagtext;
		
		var media = document.getElementById('media_panel');
		var playlist = document.getElementById('playlist_panel');
		
		// who is active ?
		if (media.className.indexOf('current') != -1) {
			var mediaid = document.getElementById('mediatag').value;
			if (mediaid != 0 )
				tagtext = "[MEDIA=" + mediaid + "]";
			else
				tinyMCEPopup.close();
		}
	
		if (playlist.className.indexOf('current') != -1) {
			var playlistid = document.getElementById('playlist').value;
			tagtext = "[MYPLAYLIST=" + playlistid + "]";
		}
		
		if(window.tinyMCE) {
			window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
	//		tinyMCE.execCommand("mceCleanup");
	 		tinyMCE.selectedInstance.repaint();
		} else {
			edCanvas = mceWindow.document.getElementById('content');
			window.edInsertContent(edCanvas, tagtext);
		}
		tinyMCEPopup.close();
	}
	</script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('mediatag').focus();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="wordTube" action="#">
	<div class="tabs">
		<ul>
			<li id="media_tab" class="current"><span><a href="javascript:mcTabs.displayTab('media_tab','media_panel');" onmousedown="return false;"><?php _e("Media", 'wpTube'); ?></a></span></li>
			<li id="playlist_tab"><span><a href="javascript:mcTabs.displayTab('playlist_tab','playlist_panel');" onmousedown="return false;"><?php _e("Playlist", 'wpTube'); ?></a></span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper">
		<!-- media panel -->
		<div id="media_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="mediatag"><?php _e("Select media file", 'wpTube'); ?></label></td>
            <td><select id="mediatag" name="mediatag" style="width: 200px">
                <option value="0"><?php _e("No file", 'wpTube'); ?></option>
                <option value="last"><?php _e("Last media", 'wpTube'); ?></option>
                <option value="random"><?php _e("Random media", 'wpTube'); ?></option>
				<?php
					$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY vid DESC ");
					if($tables) {
						foreach($tables as $table) {
						echo '<option value="'.$table->vid.'">'.$table->name.'</option>'; 
						}
					}
				?>	
            </select></td>
          </tr>
        </table>
		</div>
		<!-- media panel -->
		
		<!-- playlist panel -->
		<div id="playlist_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
         <tr>
            <td nowrap="nowrap"><label for="playlist"><?php _e("Select playlist", 'wpTube'); ?></label></td>
            <td><select id="playlist" name="playlist" style="width: 200px">
                <option value="0"><?php _e("All files", 'wpTube'); ?></option>
				<?php
					$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube_playlist ORDER BY pid DESC ");
					if($tables) {
						foreach($tables as $table) {
						echo '<option value="'.$table->pid.'">'.$table->playlist_name.'</option>'; 
						}
					}
				?>		
            </select></td>
          </tr>
        </table>
		</div>
		<!-- playlist panel -->
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'wpTube'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'wpTube'); ?>" onclick="insertwpTubeLink();" />
		</div>
	</div>
</form>
</body>
</html>
<?php

?>