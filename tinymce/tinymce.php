<?php

/**
 * @title TinyMCE V3 Button Integration (for Wp2.5)
 * @author Alex Rabe
 */

function wordTube_addbuttons() {

	// Don't bother doing this stuff if the current user lacks permissions
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
	
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
	 
	// add the button for wp2.5 in a new way
		add_filter("mce_external_plugins", "add_wordTube_tinymce_plugin", 5);
		add_filter('mce_buttons', 'register_wordTube_button', 5);
	}
}

// used to insert button in wordpress 2.5x editor
function register_wordTube_button($buttons) {

	array_push($buttons, "separator", "wordTube");

	return $buttons;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_wordTube_tinymce_plugin($plugin_array) {    

	$plugin_array['wordTube'] =  WORDTUBE_URLPATH.'tinymce/editor_plugin.js';
	
	return $plugin_array;
}

function wordTube_change_tinymce_version($version) {
	return ++$version;
}

// Modify the version when tinyMCE plugins are changed.
add_filter('tiny_mce_version', 'wordTube_change_tinymce_version');

// init process for button control
add_action('init', 'wordTube_addbuttons');

?>