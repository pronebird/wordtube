<?php

/*
Plugin Name: wordTube
Plugin URI: http://alexrabe.boelinger.com/?page_id=20
Description: This plugin creates your personal YouTube plugin for wordpress. Ready for Wordpress 2.1
Author: Alex Rabe
Version: 1.41a
Author URI: http://alexrabe.boelinger.com/

Copyright 2006-2007  Alex Rabe (email : alex.rabe@lycos.de)

THX to the plugin's from Thomas Boley (myGallery) and GaMerZ (WP-Polls),
which gives me a lot of education.

The wordTube button is taken from the Silk set of FamFamFam. See more at 
http://www.famfamfam.com/lab/icons/silk/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// define URL
$myabspath = str_replace("\\","/",ABSPATH);  // required for windows
define('WORDTUBE_URLPATH', get_settings('siteurl').'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');
define('WORDTUBE_ABSPATH', $myabspath.'wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');

// Load language
load_plugin_textdomain('wpTube','wp-content/plugins/wordtube');

// database pointer
$wpdb->wordtube					= $table_prefix . 'wordtube';
$wpdb->wordtube_playlist		= $table_prefix . 'wordtube_playlist';
$wpdb->wordtube_med2play		= $table_prefix . 'wordtube_med2play';

// Insert table menu
function add_wpTube() {
	if (function_exists('add_submenu_page')) {
		add_submenu_page( 'edit.php' , __('Media Center','wpTube'), __('Media Center','wpTube'), 9 , 'wordtube/wordtube-admin.php' );
		add_options_page(__('wordTube','wpTube'), __('wordTube','wpTube'), 9 , 'wordtube/wordtube-options.php');
	}
}

// integrate SWF Object in HEADER
function integrate_swfobject() {

 	$swfobject="\n".'<script type="text/javascript" src="'.WORDTUBE_URLPATH.'swfobject.js'.'"></script>';
	echo $swfobject;

}

// ### Serach for [Media=X] in Content
function searchvideo($content) {
global $wpdb;

// check for player type and prefer the mediaplayer	
if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) $thisplayer = 'mp3player.swf';
if (file_exists(WORDTUBE_ABSPATH.'flvplayer.swf')) $thisplayer = 'flvplayer.swf';
if (file_exists(WORDTUBE_ABSPATH.'mediaplayer.swf')) $thisplayer = 'mediaplayer.swf';
if (!$thisplayer) return $content;

//Tag VIDEO is Deprecated
$search = "/\[VIDEO=(\d+)\]/";   //search for 'video' entry
preg_match_all($search, $content, $matches);

if (is_array($matches[1])) {
	foreach ($matches[1] as $content_id) {

		$search = "[VIDEO=".$content_id."]";

		$dbresult = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE vid = '$content_id'");
		if ($dbresult) {
			$replace = replacevideo($dbresult[0]->vid, $thisplayer, "single");
			$content = str_replace ($search, $replace, $content);
			}
		}
	}

$search = "/\[MYPLAYLIST=(\d+)\]/";   //search for 'myplaylist' entry
preg_match_all($search, $content, $matches);


if (is_array($matches[1])) {
	foreach ($matches[1] as $content_id) {

		$search = "[MYPLAYLIST=".$content_id."]";

		$dbresult = $wpdb->get_results("SELECT * FROM $wpdb->wordtube_playlist WHERE pid = '$content_id'");
		if (($dbresult) or ($content_id == '0')) {
			$replace = replacevideo($content_id , $thisplayer ,"playlist");
			$content = str_replace ($search, $replace, $content);
			}
		}
	}
 
$search = "/\[MEDIA=(\d+)\]/";   //search for 'media' entry
preg_match_all($search, $content, $matches);

if (is_array($matches[1])) {
	foreach ($matches[1] as $content_id) {

		$search = "[MEDIA=".$content_id."]";

		$dbresult = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE vid = '$content_id'");
		if ($dbresult) {
			$replace = replacevideo($dbresult[0]->vid, $thisplayer ,"single");
			$content = str_replace ($search, $replace, $content);
			}
		}
	}

	return $content;
} // end search content

// ### Lookup for video content
function replacevideo($video_id = 0, $playertype, $playmode ="") {
global $wpdb;

	$wordtube_options=get_option('wordtube_options');
	$settings = '';

	if ($playmode == "playlist") {
	 	$winabspath = str_replace("\\","/",ABSPATH);  // required for win
//		$act_file = WORDTUBE_URLPATH."myextractXML.php?path=".$winabspath.$video_id;
		$act_file = WORDTUBE_URLPATH."myextractXML.php?id=".$video_id;
		$act_width = $wordtube_options[width];
		$act_height = $wordtube_options[height] + $wordtube_options[playlistsize];
		if ($wordtube_options[playlistsize] != 0 )$settings  = "\n\t".'so.addVariable("displayheight", "'.$wordtube_options[height].'");';
		if ($wordtube_options[thumbnail]) $settings .= "\n\t".'so.addVariable("thumbsinplaylist", "true");'; 
		if (!$wordtube_options[shuffle]) $settings .= "\n\t".'so.addVariable("shuffle", "false");';
		if ($wordtube_options[autostart]) $settings .= "\n\t".'so.addVariable("autostart", "true");'; 
		if ($wordtube_options[autoscroll]) $settings .= "\n\t".'so.addVariable("autoscroll", "true");'; 
	}
	 
	if ($playmode == "single") {
		$act_video = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE vid = $video_id ");
		$act_name = $act_video[0]->name;  // wozu ?
		$act_file = $act_video[0]->file;
		$act_image = $act_video[0]->image;
		$act_width = $act_video[0]->width;
		$act_height = $act_video[0]->height;
		$path_parts = pathinfo($act_file);
		if ($path_parts["extension"] == "mp3") {
			if ($wordtube_options[showeq]) {
				$settings .= "\n\t".'so.addVariable("showeq", "true");';
				$act_height = 70; // fixed for equalizer
				if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) $playertype = 'mp3player.swf'; 
			}
		}
		if ($act_video[0]->autostart) $settings .= "\n\t".'so.addVariable("autostart", "true");';
	}

	if ($wordtube_options[usewatermark]) $settings .= "\n\t".'so.addVariable("logo", "'.$wordtube_options[watermarkurl].'");'; 
	if ($wordtube_options[repeat]) $settings .= "\n\t".'so.addVariable("repeat", "true");'; 
	if ($wordtube_options[overstretch]) $settings .= "\n\t".'so.addVariable("overstretch", "'.$wordtube_options[overstretch].'");'; 
	if ($wordtube_options[showdigits]) $settings .= "\n\t".'so.addVariable("showdigits", "true");'; 
	if ($wordtube_options[showfsbutton]) $settings .= "\n\t".'so.addVariable("showfsbutton", "true");';
	if ($wordtube_options[statistic]) $settings .= "\n\t".'so.addVariable("callback", "'.WORDTUBE_URLPATH.'wordtube-statistics.php");';  
		
	$settings .= "\n\t".'so.addVariable("backcolor", "0x'.$wordtube_options[backcolor].'");'; 
	$settings .= "\n\t".'so.addVariable("frontcolor", "0x'.$wordtube_options[frontcolor].'");'; 
	$settings .= "\n\t".'so.addVariable("lightcolor", "0x'.$wordtube_options[lightcolor].'");'; 
	$settings .= "\n\t".'so.addVariable("volume", "'.$wordtube_options[volume].'");';
	$settings .= "\n\t".'so.addVariable("bufferlength", "'.$wordtube_options[bufferlength].'");';
	// neeeded for IE problems 
	$settings .= "\n\t".'so.addVariable("width", "'.$act_width.'");';
	$settings .= "\n\t".'so.addVariable("height", "'.$act_height.'");'; 
	
	if ($wordtube_options[showfsbutton]) {
		// obsolete in V3.5 (for Flash V9)
		$page_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; // need for fullscreen mode
		$fullscreen_path= WORDTUBE_URLPATH.'fullscreen.html';
		$settings .= "\n\t".'so.addVariable("fullscreenpage", "'.$fullscreen_path.'");';
		$settings .= "\n\t".'so.addVariable("fsreturnpage", "'.$page_url.'");';
		// required for V3.5
		$settings .= "\n\t".'so.addParam("allowfullscreen", "true");'; 
	} else {
		// transparent didn't work with fullscreen mode
		$settings .= "\n\t".'so.addVariable("showfsbutton", "false");'; 
		$settings .= "\n\t".'so.addParam("wmode", "transparent");'; 
	}

	if ($wordtube_options[center]) $replace .=	"\n".'</p>'; 
	if ($wordtube_options[center]) $replace .=	"\n".'<center>';  
	$replace .= "\n".'<div class="wordtube" id="'.$playmode.$video_id.'">';
	$replace .= '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the wordTube Media Player.</div>';
    $replace .= "\n\t".'<script type="text/javascript">';
    if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'<!--';
    if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'//<![CDATA[';
	$replace .= "\n\t".'var so = new SWFObject("'.WORDTUBE_URLPATH.$playertype.'", "'.$video_id.'", "'.$act_width.'", "'.$act_height.'", "7", "#FFFFFF");';
	$replace .= "\n\t".'so.addVariable("file", "'.$act_file.'");';
	$replace .= "\n\t".'so.addVariable("image", "'.$act_image.'");';
	$replace .= $settings;
	$replace .= "\n\t".'so.write("'.$playmode.$video_id.'");';
	if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'//]]>'; // Wordpress change the CDATA end tag
	if ($wordtube_options[xhtmlvalid]) $replace .= "\n\t".'// -->'; 
	$replace .= "\n\t".'</script>'."\n";
	if ($wordtube_options[center]) $replace .=	"\n".'</center>';
	if ($wordtube_options[center]) $replace .=	"\n".'<p>';
	
	// return custom message for RSS feeds
	if (is_feed()) {
		$replace = ""; // remove media file from RSS feed
		if (!empty($act_image)) $replace .= '<br /><img src="'.$act_image.'"><br />'."\n"; 
		if ($wordtube_options[activaterss]) $replace .= "[".$wordtube_options[rssmessage]."]";
	}
	return $replace;

}

// Add single media file to RSS feed
function add_wpTube_rss2_file()
{
	GLOBAL $wpdb, $post;
	
	$search = "/\[MEDIA=(\d+)\]/";   //search for 'media' entry
	preg_match_all($search, $post->post_content, $matches);

	if (is_array($matches[1])) {
	foreach ($matches[1] as $content_id) {

		$search = "[MEDIA=".$content_id."]";

		$dbresult = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE vid = '$content_id'");
		if ($dbresult) {
			$file_type = pathinfo($dbresult[0]->file);
			$mime_type = "application/unknown";
			if ($file_type["extension"] == "mp3") $mime_type = "audio/mpeg";
			if ($file_type["extension"] == "flv") $mime_type = "video/x-flv";
			if ($file_type["extension"] == "swf") $mime_type = "application/x-shockwave-flash";
			if ($file_type["extension"] == "jpg") $mime_type = "image/jpeg";			
			echo '<enclosure url="'.$dbresult[0]->file.'" type="'.$mime_type.'"/>'."\n";
			}
		}
	}
}

// get_playlist by ID
function get_playlistname_by_ID($pid = 0) {
	global $wpdb;
	return $wpdb->get_var("SELECT playlist_name FROM $wpdb->wordtube_playlist WHERE pid = $pid "); 
}

// get_playlist output für DBX
function get_playlist_for_dbx($mediaid) {
global $wpdb;

	// get playlist ID's 
	$playids = $wpdb->get_col("SELECT pid FROM $wpdb->wordtube_playlist");

	// get checked ID's'
	$checked_playlist = $wpdb->get_col("
		SELECT playlist_id
		FROM $wpdb->wordtube_playlist, $wpdb->wordtube_med2play
		WHERE $wpdb->wordtube_med2play.playlist_id = pid AND $wpdb->wordtube_med2play.media_id = '$mediaid'
		");
		
	if (count($checked_playlist) == 0) {
			$checked_playlist[] = 0;
	}
	
	$result = array ();
	
	if (is_array($playids)) {
		foreach ($playids as $playid) {
			$result[$playid]['playid'] = $playid;
			$result[$playid]['checked'] = in_array($playid, $checked_playlist);
			$result[$playid]['name'] = get_playlistname_by_ID($playid);
		}
	} // create an array with playid,checked status and name
	
	foreach ($result as $playlist) {
		echo '<label for="playlist-', $playlist['playid'], '" class="selectit"><input value="', $playlist['playid'], '" type="checkbox" name="playlist[]" id="playlist-', $playlist['playid'], '"', ($playlist['checked'] ? ' checked="checked"' : ""), '/> ', wp_specialchars($playlist['name']), "</label>\n";
	}
}

// return filename of a complete url
function wpt_filename($urlpath) {
 
	$filename = substr(($t=strrchr($urlpath,'/'))!==false?$t:'',1);
	return $filename;
}

// init wpTable in wp-database if plugin is activated
function wptube_check() {

	require_once(WORDTUBE_ABSPATH . 'wordtube-install.php');
	wordtube_install();
	
}

// Load the Script for the Button
function insert_wordtube_script() {	
 
// 	$wordtubeURL =  get_bloginfo('wpurl').'/wp-content/plugins/wordtube/';
 	$winpath = str_replace("\\","/",ABSPATH);  // required for XAXPP localhost
 	
	echo "\n".'
	<script type="text/javascript"> 
	function wpt_buttonscript()	{ 
		window.open("'.WORDTUBE_URLPATH.'wordtube-button.php?wpPATH='.$winpath.'", "SelectVideo",  "width=440,height=220,scrollbars=no");
	} 
	</script>'; 
	return;
}

function wpt_addbuttons() {
 
	global $wp_db_version;

	// Don't bother doing this stuff if the current user lacks permissions
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
 
	// If WordPress 2.1+ (or WPMU?) and using TinyMCE, we need to insert the buttons differently
	// Thanks to Viper007bond and An-archos for the pioneer work
	if ( 3664 <= $wp_db_version && 'true' == get_user_option('rich_editing') ) {
	 
	// add the button for wp21 in a new way
		add_filter("mce_plugins", "wptube_button_plugin", 5);
		add_filter('mce_buttons', 'wptube_button', 5);
		add_action('tinymce_before_init','wptube_button_script');
		}
	
	else {
	 	$button_image_url = WORDTUBE_URLPATH . '/javascript/wordtube.gif';
		buttonsnap_separator();
		buttonsnap_jsbutton($button_image_url, __('Insert Video', 'wpTube'), 'wpt_buttonscript();');
	}
}

// used to insert button in wordpress 2.1x editor
function wptube_button($buttons) {

	array_push($buttons, "separator", "wordTube");
	return $buttons;

}

// Tell TinyMCE that there is a plugin (wp2.1)
function wptube_button_plugin($plugins) {    

	array_push($plugins, "-wordTube","bold");    
	return $plugins;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.1)
function wptube_button_script() {	
 
 	$pluginURL =  WORDTUBE_URLPATH.'javascript/';
	echo 'tinyMCE.loadPlugin("wordTube", "'.$pluginURL.'");' . "\n"; 
	return;
}
	
// ButtonSnap needs to be loaded outside the class in order to work right
require(WORDTUBE_ABSPATH . 'wordtube-buttonsnap.php');

// init process for button control
add_action('init', 'wpt_addbuttons');
add_action('edit_page_form', 'insert_wordtube_script');
add_action('edit_form_advanced', 'insert_wordtube_script');

// Plugin activation
add_action('activate_wordtube/wordtube.php', 'wptube_check');

// Action activate CSS in header
add_filter('wp_head', 'integrate_swfobject');
if ( strpos( $_GET['page'], 'wordtube' ) !== false ) {
	add_filter('admin_head', 'integrate_swfobject');
}

// Action calls for all functions 
add_filter('the_content', 'searchvideo', 8);
add_filter('the_excerpt', 'searchvideo', 8);

// Action calls for RSS feed
add_action('rss2_item','add_wpTube_rss2_file');

// Insert the add_wpTube() sink into the plugin hook list for 'admin_menu'
add_action('admin_menu', 'add_wpTube');

// widget support
add_action('plugins_loaded', 'widget_wordtube');
function widget_wordtube() {
 
 	// Check for the required plugin functions. 
	if ( !function_exists('register_sidebar_widget') )
		return;
	
	function widget_show_wordtube($args) {
	 
	    extract($args);
	    
	    // check for player type and prefer the mediaplayer	
		if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) $thisplayer = 'mp3player.swf';
		if (file_exists(WORDTUBE_ABSPATH.'flvplayer.swf')) $thisplayer = 'flvplayer.swf';
		if (file_exists(WORDTUBE_ABSPATH.'mediaplayer.swf')) $thisplayer = 'mediaplayer.swf';
		if (!$thisplayer) return;
    
    	// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_wordtube');
		$title = $options['title'];
		$mediaid = $options['mediaid'];

		// These lines generate our output. 
		echo $before_widget . $before_title . $title . $after_title;
		$url_parts = parse_url(get_bloginfo('home'));
		echo '<p>'.replacevideo($mediaid , $thisplayer ,"single").'</p>';
		echo $after_widget;
		
	}	

	// Admin section
	function widget_wordtube_control() {
	 	global $wpdb;
	 	$options = get_option('widget_wordtube');
	 	if ( !is_array($options) )
			$options = array('title'=>'', 'mediaid'=>'0');
			
		if ( $_POST['wordtube-submit'] ) {

			$options['title'] = strip_tags(stripslashes($_POST['wordtube-title']));
			$options['mediaid'] = $_POST['wordtube-mediaid'];
			update_option('widget_wordtube', $options);
		}
		
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		// The Box content
		echo '<p style="text-align:right;"><label for="wordtube-title">' . __('Title:') . ' <input style="width: 200px;" id="wordtube-title" name="wordtube-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wordtube-mediaid">' . __('Select Media:', 'wpTube'). ' </label>';
		echo '<select size="1" name="wordtube-mediaid" id="wordtube-mediaid">';
			$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY 'vid' ASC ");
			if($tables) {
				foreach($tables as $table) {
				echo '<option value="'.$table->vid.'" ';
				if ($table->vid == $options['mediaid']) echo "selected='selected' ";
				echo '>'.$table->name.'</option>'."\n\t"; 
				}
			}
		echo '</select></p>';
		echo '<input type="hidden" id="wordtube-submit" name="wordtube-submit" value="1" />';
	 		
	}
	
	register_sidebar_widget(array('WordTube', 'widgets'), 'widget_show_wordtube');
	register_widget_control(array('WordTube', 'widgets'), 'widget_wordtube_control', 300, 100);
}

?>