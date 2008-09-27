<?php
/*
Plugin Name: wordTube
Plugin URI: http://alexrabe.boelinger.com/?page_id=20
Description: This plugin manages the JW FLV MEDIA PLAYER 4.1 and makes it easy for you to put music, videos or flash movies onto your WordPress posts and pages. Various skins for the JW PLAYER are available via www.jeroenwijering.com
Author: Alex Rabe & Alakhnor
Version: 2.0.0
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

// The current version
define('WORDTUBE_VERSION', '2.0.0');

// Check for WP2.6 installation
if (!defined ('IS_WP26'))
	define('IS_WP26', version_compare($wp_version, '2.6', '>=') );

//This works only in WP2.6 or higher
if ( IS_WP26 == FALSE) {
	add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error fade"><p><strong>' . __('Sorry, wordTube works only under WordPress 2.6 or higher',"wpTube") . '</strong></p></div>\';'));
	return;
}

// define URL
define('WORDTUBE_ABSPATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('WORDTUBE_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('WORDTUBE_TAXONOMY', 'wt_tag');

// make no sense if the player didn't exist
if (!file_exists(WORDTUBE_ABSPATH.'player.swf')) { 
	add_action('admin_notices', create_function('', 'echo \'<div id="message" class="error fade"><p><strong>' . __('The player.swf is not in the wordTube folder, the player will not work.','wpTube') . '</strong></p></div>\';'));
	return;
}	

include (dirname (__FILE__)."/lib/functions.php");
include_once (dirname (__FILE__)."/lib/widget.php");
include_once (dirname (__FILE__)."/lib/shortcodes.php");
include_once (dirname (__FILE__)."/lib/wordtube.class.php");
include_once (dirname (__FILE__)."/lib/swfobject.php");
include_once (dirname (__FILE__)."/tinymce/tinymce.php");

// Insert the add_wpTube() sink into the plugin hook list for 'admin_menu'
if (is_admin()) {
	include_once ( WORDTUBE_ABSPATH  . '/admin/admin.php' );
	$wordTubeAdmin = new wordTubeAdmin ();
}

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $wordTube; $wordTube = new wordTubeClass();' ) );

/**
 * wt_install()
 * init wpTable in wp-database if plugin is activated
 * 
 * @return void
 */
function wt_install() {

	require_once(dirname (__FILE__). '/admin/install.php');
	wordtube_install();
} 

// Init options & tables during activation 
register_activation_hook( plugin_basename( dirname(__FILE__) ).'/wordtube.php','wt_install' );

/**
 * wt_lang_init() - Loads language file at init
 * 
 * @return void
 */
function wt_lang_init () {
	
	load_plugin_textdomain('wpTube', false, dirname( plugin_basename(__FILE__) ) . '/languages');
}

// init load language
add_action('init', 'wt_lang_init');

?>