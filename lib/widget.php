<?php 

class wordTube_Widget {
	
	function wordTube_Widget() {
	
		// Run our code later in case this loads prior to any required plugins.
		add_action('widgets_init', array(&$this, 'widget_register'));
		
	}

	function widget_register() {
		if (!function_exists('register_sidebar_widget')) return;
		
		register_sidebar_widget('WordTube', array(&$this, 'widget_show_wordtube'), 'wid-show-wordtube');
		register_widget_control('WordTube', array(&$this, 'widget_wordtube_control'), 300, 230);
		
	}

	function widget_show_wordtube($args) {
		
		global $wordTube;
		 
		extract($args);
	    
    		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_wordtube');
		$title = $options['title'];
		$mediaid = $options['mediaid'];
		$width = $options['width'];
		$height = $options['height'];
		
		$dbresult = $wordTube->GetVidByID($mediaid);

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
			echo $wordTube->ReturnMedia($dbresult->vid, $dbresult->file, $dbresult->image, $width, $height, $dbresult->autostart, $dbresult);
		echo '</p>';
		echo $after_widget;
		
	}

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
}

// let's show it
$wordTubeWidget = new wordTube_Widget;	

?>