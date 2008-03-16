<?php
/*
Plugin Name: wordTube
Plugin URI: http://alexrabe.boelinger.com/?page_id=20
Description: This plugin creates your personal YouTube plugin for WordPress.
Author: Alex Rabe & Alakhnor
Version: 1.60
Author URI: http://alexrabe.boelinger.com/

Copyright 2006-2008 Alex Rabe , Alakhnor

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

global $wp_version;

// define URL
define('WORDTUBE_URLPATH', get_option('siteurl').'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');
define('WORDTUBE_ABSPATH', ABSPATH.'wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/');

// Check for WP2.5 installation
define('IS_WP25', version_compare($wp_version, '2.4', '>=') );

$WPwordTube = new wordTubeClass();

// Insert the add_wpTube() sink into the plugin hook list for 'admin_menu'
if (is_admin()) {
	include ( WORDTUBE_ABSPATH  . '/wordtube-admin.php' );
}

/******************************************************************
/* Main class.
******************************************************************/
class wordTubeClass {

	var $has_player;
	var $player = '';
	var $mp3player = '';
	var $wordtube_options;
	var $variableblock;
	var $displaywidth;
	var $displayheight;
	var $width;
	var $height;
	var $controlheight;
	var $PLTags = array('0', 'most', 'video', 'music');
	var $xmltag;
	var $GetFlashPlayer = '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the wordTube Media Player.</div>';
	var $randID;

	/******************************************************************
	/* Constructor: Loads parameters and pre-formatted string. Add filters
	******************************************************************/
	function wordTubeClass() {
	
		global $wpdb;
		
		// data	base pointer
		$wpdb->wordtube				= $wpdb->prefix . 'wordtube';
		$wpdb->wordtube_playlist	= $wpdb->prefix . 'wordtube_playlist';
		$wpdb->wordtube_med2play	= $wpdb->prefix . 'wordtube_med2play';

		// check for player type and prefer the mediaplayer
		if (file_exists(WORDTUBE_ABSPATH.'mp3player.swf')) { 	$this->player = 'mp3player.swf'; $this->mp3player = 'mp3player.swf'; }
		if (file_exists(WORDTUBE_ABSPATH.'flvplayer.swf')) 	$this->player = 'flvplayer.swf';
		if (file_exists(WORDTUBE_ABSPATH.'mediaplayer.swf')) 	$this->player = 'mediaplayer.swf';
		$this->has_player = ($this->player != '');
		if ($this->mp3player == '') $this->mp3player = $this->player;

		$this->wordtube_options = get_option('wordtube_options');
		if ($this->wordtube_options['showcontrols']) $this->controlheight = 20; else $this->controlheight = 0;
		$this->PermanentVideoVariables();

		// Action activate script in header
		add_filter('wp_head', 'integrate_swfobject');
	
		// Action calls for all functions 
		if ($this->wordtube_options['excerpt']) 
			add_filter('the_excerpt', array(&$this, 'excerpt_searchvideo'));
		else
			add_filter('the_excerpt', array(&$this, 'excerpt_searchblank'));

		add_filter('the_content', array(&$this, 'searchvideo'));
		remove_filter('get_the_excerpt', 'wp_trim_excerpt');
		add_filter('get_the_excerpt', array(&$this, 'wp_trim_excerpt'));

		// Action calls for RSS feed
		add_action('rss2_item', array(&$this, 'add_wpTube_rss2_file'));
		add_action('the_content_rss', array(&$this, 'add_wpTube_rss2_file'));
	
	}
	/******************************************************************
	/* Hooked filter for the_excerpt to skip the_content filter
	******************************************************************/
	function wp_trim_excerpt($text) { // Fakes an excerpt if needed
		global $post;
		if ( '' == $text ) {
			$text = get_the_content('');
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);
			$excerpt_length = 55;
			$words = explode(' ', $text, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				array_push($words, '[...]');
				$text = implode(' ', $words);
			}
		}
		return $text;
	}
	/******************************************************************
	/* Insert table menu
	******************************************************************/
	function add_wpTube2() {
	
		if (function_exists('add_submenu_page')) {
			add_submenu_page( 'edit.php' , __('Media Center','wpTube'), __('Media Center','wpTube'), 'edit_posts' , 'wordtube/wordtube-admin.php' );
			add_options_page(__('wordTube','wpTube'), __('wordTube','wpTube'), 'manage_options' , 'wordtube/wordtube-options.php');
		}
	}
	/***********************************************************************************/
	/* This function checks for Tags cheaply so we don't waste CPU cycles on regex if it's not needed
	/***********************************************************************************/
	function CheckForTags( $content ) {
	
		if ( stristr( $content, '[video' )) return TRUE;
		if ( stristr( $content, '[media' )) return TRUE;
		if ( stristr( $content, '[myplaylist' )) return TRUE;

		return FALSE;
	}
	/******************************************************************
	/* Search for [Media=X] in excerpt
	/* Each video in excerpt needs to be taken as a whole word.
	/* Therefore, do not show all the \n\t.
	******************************************************************/
	function excerpt_searchvideo($content) {
		return $this->searchvideo($content, true);
	}
	/******************************************************************
	/* Search for [Media=X][Video=X][Playlist=X] in Content
	/* $exc used with excerpt to strip code formatting tag (\n\t).
	******************************************************************/
	function searchvideo($content, $exc=false) {
		global $wpdb;

		if (!$this->CheckForTags($content)) return $content;

		//Tag VIDEO is Deprecated
		$search = "@(?:<p>)*\s*\[VIDEO\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
		if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

			foreach ($matches as $match) {

				$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube.' WHERE vid = '.$match[1]);
				if ($dbresult) {
					$replace = $this->ReturnMedia($dbresult->vid, $dbresult->file, $dbresult->image, $dbresult->width, $dbresult->height, $dbresult->autostart, $exc);
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		$search = "@(?:<p>)*\s*\[MYPLAYLIST\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
		if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

			foreach ($matches as $match) {

				$dbresult=false;
				if (!in_array($match[1], $this->PLTags))
					$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube_playlist.' WHERE pid = '.$match[1]);
				if (($dbresult) || in_array($match[1], $this->PLTags)) {
					$replace = $this->ReturnPlaylist($match[1] , $exc);
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		// To do: 1 loop to get the media list, then query, then one loop to replace (useful for post with multiple media)
		$search = "@(?:<p>)*\s*\[MEDIA\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i"; 
		if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

                	$auto = (is_single() && $this->wordtube_options['startsingle']);
                	$i=1;
			foreach ($matches as $match) {

				$dbresult = $this->GetVidByID($match[1]);
				if ($dbresult) {
					if ($i==1 && $auto) $autostart = true; else $autostart = $dbresult->autostart;
					$replace = $this->ReturnMedia($dbresult->vid, $dbresult->file, $dbresult->image, $dbresult->width, $dbresult->height, $autostart, $exc);
					// Premices of future output
					// $replace .= 'Counter: '.$dbresult->counter;
					$content = str_replace ($match[0], $replace, $content);
					$i++;
				}
			}
		}

		return $content;
	} // end search content
	/******************************************************************
	/* Search for [Media=X] in Content
	******************************************************************/
	function excerpt_searchblank($content, $exc=false) {

		if (!$this->CheckForTags($content)) return $content;

		//Tag VIDEO is Deprecated
		$search = "@(?:<p>)*\s*\[VIDEO\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
		if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$content = str_replace ($match[0], '', $content);
			}
		}

		$search = "@(?:<p>)*\s*\[MYPLAYLIST\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
		if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$content = str_replace ($match[0], '', $content);
			}
		}

		$search = "@(?:<p>)*\s*\[MEDIA\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i"; 
		if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$content = str_replace ($match[0], '', $content);
			}
		}

		return $content;
	} // end search content
	/******************************************************************
	/* Utility function: format a video with new params
	/* Additions: width and height can be set to new values
	******************************************************************/
	function ReturnMedia($video_id, $act_file, $act_image='', $new_width=0, $new_height=0, $autostart=false, $exc=false) {

//		$act_file=str_replace('&amp;', '&', htmlspecialchars($act_file, ENT_QUOTES));
		$act_file=rawurlencode($act_file);
		$playertype = $this->player;
		$settings = '';
		$playmode= 'single';
		if ($exc) $this->xmltag = ""; else $this->xmltag = "\n\t";

		if ($new_width==0) $act_width = 320; else $act_width = $new_width;
		if ($new_height==0) $act_height = 240; else $act_height = $new_height;
		$path_parts = pathinfo($act_file);

		// Special output for mp3
		if ($path_parts["extension"] == "mp3" && ($this->wordtube_options['mp3controls'] != 'default')) {
			if ($this->wordtube_options['mp3controls'] == 'mini') {
				$act_height = 20; // fixed for mp3
				$playertype = $this->mp3player;
			} else {
				$settings .= $this->xmltag.'so.addVariable("showeq", "true");';
				$act_height = 70; // fixed for equalizer
				$playertype = $this->mp3player;
			}
		} elseif (!$this->wordtube_options['showcontrols']) 
			$settings .= $this->xmltag.'so.addVariable("displayheight", "'.$act_height.'");';

		if ($autostart) $settings .= $this->xmltag.'so.addVariable("autostart", "true");';
		$settings .= $this->xmltag.'so.addVariable("id", "'.$video_id.'");';
	
		// neeeded for IE problems 
		$settings .= $this->xmltag.'so.addVariable("width", "'.$act_width.'");';
		$settings .= $this->xmltag.'so.addVariable("height", "'.$act_height.'");';
		$settings .= $this->variableblock; 
		
		// Builds object	
        $replace .= $this->ScriptHeader($video_id, 'single');
		$replace .= $this->xmltag.'var so = new SWFObject("'.WORDTUBE_URLPATH.$playertype.'", "'.$video_id.'", "'.$act_width.'", "'.$act_height.'", "7", "#FFFFFF");';
		$replace .= $this->xmltag.'so.addVariable("file", "'.$act_file.'");';
		$replace .= $this->xmltag.'so.addVariable("image", "'.$act_image.'");';
		$replace .= $settings;
		$replace .= $this->ScriptFooter($video_id, 'single');

		// returns custom message for RSS feeds
		if (is_feed()) {
			$replace = ""; // remove media file from RSS feed
			if (!empty($act_image)) $replace .= '<br /><img src="'.$act_image.'" alt="media"><br />'."\n";
			if ($this->wordtube_options['activaterss']) $replace .= "[".$this->wordtube_options['rssmessage']."]";
		}

		if ($exc) str_replace("\n\t", "", $replace);
		
		return $replace;

	}
	/******************************************************************
	// ### Returns playlist form video content search
	/* Additions: width and height can be set to new values
	******************************************************************/
	function ReturnPlaylist($video_id = 0, $exc=false, $dwidth=0, $dheight=0) {

		$playertype = $this->player;
		$settings = '';
		if ($exc) $this->xmltag = ""; else $this->xmltag = "\n\t";
		

		$act_file = WORDTUBE_URLPATH."myextractXML.php?id=".$video_id;
		if ($dwidth == 0) $dwidth = $this->wordtube_options['width'];
		if ($dheight == 0) $dheight = $this->wordtube_options['height'];
		if ($this->wordtube_options['playlistright']) {
			$width = $dwidth + $this->wordtube_options['playlistwidth'];
			$height = $dheight + $this->controlheight;
		} else {
			$width = $dwidth;
			$height = $dheight + $this->wordtube_options['playlistsize'];
		}
		$settings .= $this->PlaylistVariables($dwidth, $dheight);
	 
		// neeeded for IE problems (actually, needed to control playlist setup) 
		$settings .= $this->xmltag.'so.addVariable("width", "'.$width.'");';
		$settings .= $this->xmltag.'so.addVariable("height", "'.$height.'");';
		$settings .= $this->variableblock; 

		// Builds object
        $replace .= $this->ScriptHeader($video_id, 'playlist');
		$replace .= $this->xmltag.'var so = new SWFObject("'.WORDTUBE_URLPATH.$playertype.'", "'.$video_id.'", "'.$width.'", "'.$height.'", "7", "#FFFFFF");';
		$replace .= $this->xmltag.'so.addVariable("file", "'.$act_file.'");';
		$replace .= $settings;
		$replace .= $this->ScriptFooter($video_id, 'playlist');

		// returns custom message for RSS feeds
		if (is_feed()) {
			// remove media file from RSS feed
			$replace = "";
			// replace with media image if exists
			if (!empty($act_image)) $replace .= '<br /><img src="'.$act_image.'" alt="media"><br />'."\n"; 
			// add rss message if option checked
			if ($this->wordtube_options['activaterss']) $replace .= "[".$this->wordtube_options['rssmessage']."]";
		}

		if ($exc) str_replace("\n\t", "", $replace);
		
		return $replace;

	}
	/******************************************************************
	// ### Returns header part of inserted script
	/* Used for both media and playlist
	******************************************************************/
	function ScriptHeader($video_id, $playmode = 'single') {

		$replace = '';
		$this->rand = rand(0,1000);
		$replace .= "\n".'<div class="wordtube '.$playmode.$video_id.'" id="WT'.$this->rand.'">';
		$replace .= $this->GetFlashPlayer;
		$replace .= $this->xmltag.'<script type="text/javascript" defer="defer">';
		if ($this->wordtube_options['xhtmlvalid']) $replace .= $this->xmltag.'<!--';
		if ($this->wordtube_options['xhtmlvalid']) $replace .= $this->xmltag.'//<![CDATA['."\n";

        return $replace;
	}
	/******************************************************************
	// ### Returns footer part of inserted script
	/* Used for both media and playlist
	******************************************************************/
	function ScriptFooter($video_id, $playmode = 'single') {

		$replace = $this->xmltag.'so.write("WT'.$this->rand.'");';
		if ($this->wordtube_options['xhtmlvalid']) $replace .= $this->xmltag."//]]>"; // Wordpress change the CDATA end tag
		if ($this->wordtube_options['xhtmlvalid']) $replace .= $this->xmltag.'// -->'; 
		$replace .= $this->xmltag.'</script>'."\n";

        return $replace;
	}
	/******************************************************************
	// Sets up playlist part of code (fixed for playlist)
	******************************************************************/
	function PlaylistVariables($dwidth, $dheight) {
		
		$variables = '';

		// 0 = diable the control bar
		$variables  .= $this->xmltag.'so.addVariable("displayheight", "'.$dheight.'");';
		if ($this->wordtube_options['playlistwidth'] != 0 ) 
			$variables  .= $this->xmltag.'so.addVariable("displaywidth", "'.$dwidth.'");';
		if ($this->wordtube_options['thumbnail']) $variables .= $this->xmltag.'so.addVariable("thumbsinplaylist", "true");'; 
		if (!$this->wordtube_options['shuffle']) $variables .= $this->xmltag.'so.addVariable("shuffle", "false");';
		if ($this->wordtube_options['autostart']) $variables .= $this->xmltag.'so.addVariable("autostart", "true");'; 
		if ($this->wordtube_options['autoscroll']) $variables .= $this->xmltag.'so.addVariable("autoscroll", "true");'; 

		return $variables;
	}
	/******************************************************************
	// Sets up fixed part of code once
	/* Used for both media and playlist
	******************************************************************/
	function PermanentVideoVariables() {
	
		if ($this->wordtube_options['usewatermark']) 	$this->variableblock = "\n\t".'so.addVariable("logo", "'.$this->wordtube_options['watermarkurl'].'");'; 
		if ($this->wordtube_options['repeat']) 		$this->variableblock .= "\n\t".'so.addVariable("repeat", "true");'; 
		if ($this->wordtube_options['overstretch']) 	$this->variableblock .= "\n\t".'so.addVariable("overstretch", "'.$this->wordtube_options['overstretch'].'");'; 
		if ($this->wordtube_options['showdigits']) 	$this->variableblock .= "\n\t".'so.addVariable("showdigits", "true");'; 
		if ($this->wordtube_options['largecontrols']) 	$this->variableblock .= "\n\t".'so.addVariable("largecontrols", "true");';
		if ($this->wordtube_options['showfsbutton']) 	$this->variableblock .= "\n\t".'so.addVariable("showfsbutton", "true");';
		if (!$this->wordtube_options['showicons']) 	$this->variableblock .= "\n\t".'so.addVariable("showicons", "false");';

		$this->variableblock .= "\n\t".'so.addVariable("backcolor", "0x'.$this->wordtube_options['backcolor'].'");';
		$this->variableblock .= "\n\t".'so.addVariable("frontcolor", "0x'.$this->wordtube_options['frontcolor'].'");'; 
		$this->variableblock .= "\n\t".'so.addVariable("lightcolor", "0x'.$this->wordtube_options['lightcolor'].'");'; 
		$this->variableblock .= "\n\t".'so.addVariable("volume", "'.$this->wordtube_options['volume'].'");';
		$this->variableblock .= "\n\t".'so.addVariable("bufferlength", "'.$this->wordtube_options['bufferlength'].'");';
		
		if ($this->wordtube_options['showfsbutton']) {
			// obsolete in V3.5 (for Flash V9)
			$page_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; // need for fullscreen mode
			$fullscreen_path= WORDTUBE_URLPATH.'fullscreen.html';
			$this->variableblock .= "\n\t".'so.addVariable("fullscreenpage", "'.$fullscreen_path.'");';
			$this->variableblock .= "\n\t".'so.addVariable("fsreturnpage", "'.$page_url.'");';
			// required for V3.5
			$this->variableblock .= "\n\t".'so.addParam("allowfullscreen", "true");';
			// transparent doesn't work with fullscreen mode
			// $this->variableblock .= "\n\t".'so.addParam("wmode", "transparent");';
		} else {
			$this->variableblock .= "\n\t".'so.addVariable("showfsbutton", "false");'; 
			$this->variableblock .= "\n\t".'so.addParam("wmode", "transparent");';
		}
		if ($this->wordtube_options['statistic']) 	$this->variableblock .= "\n\t".'so.addVariable("callback", "'.WORDTUBE_URLPATH.'wordtube-statistics.php");';  
	
	}
	/******************************************************************
	// Add single media file to RSS feed
	******************************************************************/
	function add_wpTube_rss2_file() {
	
		GLOBAL $wpdb, $post;
	
		$search = "/\[MEDIA=(\d+)\]/";   //search for 'media' entry
		preg_match_all($search, $post->post_content, $matches);

		if (is_array($matches[1])) {
			foreach ($matches[1] as $content_id) :

				$search = "[MEDIA=".$content_id."]";
				$dbresult = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE vid = '$content_id'");
				if ($dbresult) {
					$file_type = pathinfo(strtolower($dbresult[0]->file));
					$mime_type = "application/unknown";
					if ($file_type["extension"] == "mp3") $mime_type = "audio/mpeg";
					elseif ($file_type["extension"] == "flv") $mime_type = "video/x-flv";
					elseif ($file_type["extension"] == "swf") $mime_type = "application/x-shockwave-flash";
					elseif ($file_type["extension"] == "jpg") $mime_type = "image/jpeg";			
					elseif ($file_type["extension"] == "jpeg") $mime_type = "image/jpeg";			
					echo '<enclosure url="'.$dbresult[0]->file.'" length="1" type="'.$mime_type.'"/>'."\n";
				}

			endforeach;
		}
	}
	/******************************************************************
	// init wpTable in wp-database if plugin is activated
	******************************************************************/
	function wptube_check() {

		require_once(WORDTUBE_ABSPATH . 'wordtube-install.php');
		wordtube_install();
	
	}
	/******************************************************************
	// Return a media record with its ID
	******************************************************************/
	function GetVidByID($id=0) {
		global $wpdb;

		if ($id == 'last' or $id == '0')
			$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube.' ORDER BY vid DESC LIMIT 1');
		elseif ($id == 'random')
			$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube.' ORDER BY RAND() LIMIT 1');
		else
			$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube.' WHERE vid = '.$id);

		return $dbresult;	
	}
	/******************************************************************
	/* Widget display section
	******************************************************************/
	function widget_show_wordtube($args) {
		 
		extract($args);
	    
    		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_wordtube');
		$title = $options['title'];
		$mediaid = $options['mediaid'];
		$width = $options['width'];
		$height = $options['height'];
		
		$dbresult = $this->GetVidByID($mediaid);

		// These lines generate our output. 
		echo $before_widget . $before_title . $title . $after_title;
		$url_parts = parse_url(get_bloginfo('home'));
		echo '<p>';
		if ($dbresult)
			if ($width == 0) $width = $dbresult->width;
			if ($height == 0) {
				if ($dbresult->width == 0)
					$height = $dbresult->height;
				else
					$height = $width / $dbresult->width * $dbresult->height;
			}
			echo $this->ReturnMedia($dbresult->vid, $dbresult->file, $dbresult->image, $width, $height, $dbresult->autostart);
		echo '</p>';
		echo $after_widget;
		
	}	
	/******************************************************************
	// Widget admin section
	******************************************************************/
	function widget_wordtube_control() {
	 	global $wpdb;

	 	$options = get_option('widget_wordtube');
	 	if ( !is_array($options) )
			$options = array('title'=>'', 'mediaid'=>'0');
				
		if ( $_POST['wordtube-submit'] ) {

			$options['title'] 	= strip_tags(stripslashes($_POST['wordtube-title']));
			$options['mediaid'] 	= $_POST['wordtube-mediaid'];
			$options['width'] 	= $_POST['wordtube-width'];
			$options['height'] 	= $_POST['wordtube-height'];
			
			if ($options['width'] == '') $options['width']=0;
			if ($options['height'] == '') $options['height']=0;
			update_option('widget_wordtube', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$width = $options['width'];
		$height = $options['height'];
			
		// The Box content
		echo '<p style="text-align:right;"><label for="wordtube-title">' . 
			__('Title:') . 
			' <input style="width: 200px;" id="wordtube-title" name="wordtube-title" type="text" value="'.
			$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wordtube-mediaid">' . __('Select Media:', 'wpTube'). ' </label>';
		echo '<select size="1" name="wordtube-mediaid" id="wordtube-mediaid">';
		echo '<option value="last" ';
		if ($options['mediaid'] == 'last') echo 'selected="selected" ';
		echo '>'.__('Last media', 'wpTube').'</option>'."\n\t";
		echo '<option value="random" ';
		if ($options['mediaid'] == 'random') echo 'selected="selected" ';
		echo '>'.__('Random media', 'wpTube').'</option>'."\n\t";

		$tables = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY 'vid' ASC ");
		if($tables) {
			foreach($tables as $table) {
				echo '<option value="'.$table->vid.'" ';
				if ($table->vid == $options['mediaid']) echo "selected='selected' ";
				echo '>'.$table->name.'</option>'."\n\t"; 
			}
		}
		echo '</select></p>';
		echo '<p style="text-align:right;"><label for="wordtube-mediaid">' . __('Width (0 for media default):', 'wpTube'). ' </label>';
		echo '<input type="text" id="wordtube-width" name="wordtube-width" value="'.$width.'" />';
		echo '<p style="text-align:right;"><label for="wordtube-mediaid">' . __('Height (0 for media default):', 'wpTube'). ' </label>';
		echo '<input type="text" id="wordtube-height" name="wordtube-height" value="'.$height.'" />';
		echo '<input type="hidden" id="wordtube-submit" name="wordtube-submit" value="1" />';
	}

} // end wordTube class

/***********************************************************************************/
/* Get wordtube options.
/***********************************************************************************/
function wt_get_options($option) {
	global $WPwordTube;
		return $WPwordTube->wordtube_options[$option];
}
/******************************************************************
/* Replace media/playlist in a content with custom width and height (for media)
/* If width & height = 0, returns database video sizes
/* If only height = 0, adjust height to width in proportions of database sizes
/* width and height auto-adjust only applies to media (not to playlist)
******************************************************************/
function searchvideo($content, $width=0, $height=0, $exc=false) {
	global $wpdb, $WPwordTube;

	if (!$WPwordTube->CheckForTags($content)) return $content;

	$search = "@(?:<p>)*\s*\[MYPLAYLIST\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
	if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

		foreach ($matches as $match) {

			$dbresult=false;
			if (!in_array($match[1], $WPwordTube->PLTags))
				$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube_playlist.' WHERE pid = '.$match[1]);
			if (($dbresult) || in_array($match[1], $this->PLTags)) {
				$replace = $WPwordTube->ReturnPlaylist($match[1] , $exc, $width, $height);
				$content = str_replace ($match[0], $replace, $content);
			}
		}
	}

	$search = "@(?:<p>)*\s*\[MEDIA\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
	if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

		$i=1;
		foreach ($matches as $match) {

			$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube.' WHERE vid = '.$match[1]);
			if ($dbresult) {
				if ($width == 0) $pwidth = $dbresult->width; else $pwidth = $width;
				if ($height == 0) {
					if ($dbresult->width == 0)
						$pheight = $dbresult->height;
					else
						$pheight = $pwidth / $dbresult->width * $dbresult->height;
				} else $pheight = $height;
				
				if ($startfirst && $i == 1) $autostart = true; else $autostart = $dbresult->autostart;

				$replace = $WPwordTube->ReturnMedia($dbresult->vid, $dbresult->file, $dbresult->image, $pwidth, $pheight, $autostart, $exc);
				$content = str_replace ($match[0], $replace, $content);
				$i++;
			}
		}
	}

	return $content;
}
/******************************************************************
/* Return video by id with new width and height
/* If width & height = 0, returns database video sizes
/* If only height = 0, adjust height to width in proportions of database sizes
******************************************************************/
function wt_GetVideo($id, $width=0, $height=0) {
	global $wpdb, $WPwordTube;

	$dbresult = $WPwordTube->GetVidByID($id);
	if ($dbresult) {
		if ($width == 0) $width = $dbresult->width;
		if ($height == 0) {
			if ($dbresult->width == 0)
				$height = $dbresult->height;
			else
				$height = $width / $dbresult->width * $dbresult->height;
		}
		return $WPwordTube->ReturnMedia($id, $dbresult->file, $dbresult->image, $width, $height, $dbresult->autostart);
	}
	return '';
}
/******************************************************************
/* Return video by id with new width and height
******************************************************************/
function wt_GetPlaylist($id, $width=0, $height=0, $exc=false) {
	global $WPwordTube;

	return $WPwordTube->ReturnPlaylist($id, $exc, $width, $height);
}

/******************************************************************
/* integrate SWF Object in HEADER
******************************************************************/
function integrate_swfobject() {

	echo '<!-- Start Of Script Generated By WordTube -->'."\n";
	if (function_exists('wp_enqueue_script')) {
		wp_enqueue_script('swfobject', WORDTUBE_URLPATH.'javascript/swfobject.js', false, '1.5');
		wp_print_scripts(array('swfobject'));
	} else
	{ ?>
		<script type="text/javascript" src="<?php echo WORDTUBE_URLPATH; ?>javascript/swfobject.js"></script>
	<?php }
	echo '<!-- End Of Script Generated By WordTube -->'."\n";

}

// Load tinymce button 
if (IS_WP25)
	include_once (dirname (__FILE__)."/tinymce/tinymce.php");
else
	include_once (dirname (__FILE__)."/javascript/wordtube-button.php");

/****************************************************************/
/* Loads language file at init
/****************************************************************/
function wt_lang_init () {

	// Load language file
	$locale = get_locale();
	if ( !empty($locale) )
		load_textdomain('wpTube', WORDTUBE_ABSPATH.'languages/' . 'wpTube-'.$locale.'.mo');
}
// init load language
add_action('init', 'wt_lang_init');

/******************************************************************
/* Widget
******************************************************************/
function widget_show_wordtube($args) {
	global $WPwordTube;
	
	$WPwordTube->widget_show_wordtube($args);
}
function widget_wordtube_control() {
	global $WPwordTube;
	
	$WPwordTube->widget_wordtube_control();
}

// Widget declaration
function wordtube_widget() {
	if (!function_exists('register_sidebar_widget')) return;
	register_sidebar_widget('WordTube', 'widget_show_wordtube', 'wid-show-wordtube');
	register_widget_control('WordTube', 'widget_wordtube_control', 300, 230);
}
// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'wordtube_widget');

?>