<?php

/**
 * wordTubeClass
 * 
 * @package wordTube
 * @author Alex Rabe, Alakhnor
 * @copyright 2008
 * @access public
 */
class wordTubeClass {

	var $player = 'player.swf';
	var $options;
	var $PLTags = array('0', 'most', 'video', 'music');
	var $GetFlashPlayer = '<a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see the wordTube Media Player.';
	var $use_cache = false;
	var $counter = 1;
	var $addLongTail = false;
	var $enableAds = false;
	var $swfobject;
	var $media;

	/**
	 * wordTubeClass::wordTubeClass()
	 * Constructor. Loads parameters and pre-formatted string. Add filters
	 * 
	 * @return void
	 */
	function wordTubeClass() {
		global $wpdb;
	
		// Create taxonomy
		if (function_exists('register_taxonomy'))
			register_taxonomy( WORDTUBE_TAXONOMY, 'wordtube', array('update_count_callback' => '_update_media_term_count') );

		// database pointer
		$wpdb->wordtube				= $wpdb->prefix . 'wordtube';
		$wpdb->wordtube_playlist	= $wpdb->prefix . 'wordtube_playlist';
		$wpdb->wordtube_med2play	= $wpdb->prefix . 'wordtube_med2play';
		
		// get the options
		$this->options = get_option('wordtube_options');

		// Action activate script in header, but not in the admin section
		if ( !is_admin() )
			add_action('wp_print_scripts', array(&$this, 'integrate_js' ));		
	}

	/**
	 * wordTubeClass::ReturnMedia()
	 * Utility function: format a video with all parameters and return the script code
	 * 
	 * @param integer $id of the media
	 * @param string $url to the media
	 * @param string $image url to the image
	 * @param integer $width
	 * @param integer $height
	 * @param bool $autostart
	 * @param object $media database content of this media file
	 * @return string $out content of the media code
	 */
	function ReturnMedia($id, $url = '', $image = '', $width = 0, $height = 0, $autostart = false, $media = '') {

		// get some default values
		$width  = ( $width  == 0 ) ? $this->options['media_width'] : $width;
		$height = ( $height == 0 ) ? $this->options['media_height'] : $height;
		$this->media = $media;
		
		if (!is_object($this->media) )
			$this->media = $this->GetVidByID( intval($id) );
		
		// remove the code in a feed
		if ( is_feed() ) {
			$out = ''; 
			// remove media file from RSS feed
			if ( !empty($image) ) 
				$out .= '<br /><img src="' . $image . '" alt="media" /><br />'."\n";
			// returns custom message for RSS feeds
			if ( $this->options['activaterss'] ) 
				$out .= "[" . $this->options['rssmessage'] . "]";
			return $out;
		}
		
		// no ads in the admin section
		//TODO: disable ads for the overview page
		if ( is_single() || is_page() )
			$this->enableAds = ( $this->options['activateAds'] && !is_admin() && ($this->media->disableads == 0) ) ? true : false ;
		
		if ( empty($url) )
			$url = $this->media->file;
		
		// Builds object
		$this->swfobject = new swfobject( WORDTUBE_URLPATH . $this->player, 'WT'.$this->counter, $width, $height, '9.0.0', 'false');	
		
		// add all params & vars
		$this->addParameter();
		$this->ReturnLocation( $url );
		$this->globalFlashVars();
		$this->swfobject->add_flashvars( 'image', rawurlencode($image) );
		$this->swfobject->add_flashvars( 'title', rawurlencode($this->media->name) );
		$this->swfobject->add_flashvars( 'linktarget', '_self' );
		$this->swfobject->add_flashvars( 'autostart', $autostart, 'false', 'bool');
		$this->swfobject->add_attributes( 'id', 'WT'.$this->counter);
		$this->swfobject->add_attributes( 'name', 'WT'.$this->counter);
		
		// Build the output
    	$out  = $this->ScriptHeader( $id, 'single' );
		$out .= $this->ScriptFooter( $id, 'single' );

		return $out;
	}

	/**
	 * wordTubeClass::ReturnPlaylist()
	 * Utility function: Return a Playlist with all parameter 
	 * 
	 * @param integer $id
	 * @param integer $width
	 * @param integer $height
	 * @return string $out
	 */
	function ReturnPlaylist($id = 0, $width = 0, $height = 0) {
		
		// get the global option settings if not defined
		$width  = ( $width  == 0 ) ? $this->options['width']  : $width;
		$height = ( $height == 0 ) ? $this->options['height'] : $height;

		// returns custom message for RSS feeds
		if (is_feed()) {
			// remove media file from RSS feed
			$out = "";
			// add rss message if option checked
			if ($this->options['activaterss']) $out .= "[".$this->options['rssmessage']."]";
			return $out;
		}

		//TODO:for the moment we do not want Ads in playlist
		$this->enableAds = false;

		// Builds object
		$this->swfobject = new swfobject( WORDTUBE_URLPATH . $this->player, 'WT'.$this->counter, $width, $height, '9.0.0', 'false');	
 
 		$this->addParameter();
		$this->swfobject->add_flashvars( 'file', WORDTUBE_URLPATH . 'myextractXML.php?id=' . $id );
		// apply the parameters
		$this->globalFlashVars();
		$this->PlaylistVariables();
		$this->swfobject->add_attributes( 'id', 'WT'.$this->counter);
		$this->swfobject->add_attributes( 'name', 'WT'.$this->counter);		
		
		// Build the output
		$out  = $this->ScriptHeader( $id, 'playlist');
		$out .= $this->ScriptFooter( $id, 'playlist' );

		return $out;
	}

	/**
	 * wordTubeClass::ReturnLocation()
	 * Returns file or file+streamer for rmtp stream
	 * 
	 * @param mixed $file
	 * @param mixed $id
	 * @return string $filename
	 */
	function ReturnLocation( $file ) {
		// in the case it's a streamer we look for rtmp://streaming-server/?id=filename
		// for wowza we also need to look for : 
		// rtmp://[server-ip-address]/simplevideostreaming/?id=mp4:myvideos/Extremists.m4v
		if (substr($file, 0, 4) == 'rtmp') {
			preg_match('/^(.+)\?id=(.+)/', $file, $match);
			if (!empty ($match)) {		
				$this->swfobject->add_flashvars( 'streamer', rawurlencode( $match[1] ));
				$this->swfobject->add_flashvars( 'file', rawurlencode( $match[2] ) );
			} else
				$this->swfobject->add_flashvars( 'file', rawurlencode( $file ) );
		} else {
			$this->swfobject->add_flashvars( 'file', rawurlencode( $file ) );
			if ( $this->enableAds )
				$this->swfobject->add_flashvars( '\'ltas.mediaid\'', rawurlencode( $file ) );
		}

		return;
	}

	/**
	 * wordTubeClass::ScriptHeader()
	 * Returns header part of inserted script
	 * 
	 * @param mixed $id
	 * @param string $playmode
	 * @return string $out
	 */
	function ScriptHeader($id, $playmode = 'single') {
	
		// Get display div
		$this->swfobject->message = $this->GetFlashPlayer;
		$this->swfobject->classname = 'wordtube '. $playmode . $id;

		// Get display div
		$out  = '<div class="wordtube">' . $this->swfobject->output() . '</div>';

		// Add the Longtail Scritp to the footer and wrap a div around
		if ( $this->enableAds && $playmode == 'single' && !is_admin() ) {
			add_action('wp_footer', array(&$this, 'addLongtailFooter' ));
			$out  = "\n".'<div name="mediaspace" id="mediaspace">'. $out . "\n".'</div>';
		}
			
		// Set js open tag
		$out .= "\n\t".'<script type="text/javascript" defer="defer">';
		if ($this->options['xhtmlvalid']) {
			$out .= "\n\t".'<!--';
			$out .= "\n\t".'//<![CDATA['."\n";
		}

        return $out;
	}

	/**
	 * wordTubeClass::ScriptFooter()
	 * Returns footer part of inserted script
	 * 
	 * @param mixed $id
	 * @param string $playmode
	 * @return string $out
	 */
	function ScriptFooter($id, $playmode = 'single') {

		// Get the script code
		$out  = $this->swfobject->javascript();
		
		//NOTE : Wordpress change the CDATA end tag
		if ($this->options['xhtmlvalid']) {
			$out .= "\n\t"."//]]>"; 
			$out .= "\n\t".'// -->'; 
		}
		$out .= "\n\t".'</script>'."\n";
	
		// increase the internal counter
		$this->counter++;

        return $out;
	}

	/**
	 * wordTubeClass::PlaylistVariables()
	 * Sets up playlist part of code (fixed for playlist)
	 * 
	 * @return string
	 */
	function PlaylistVariables() {
		
		$this->swfobject->add_flashvars( 'shuffle', $this->options['shuffle'], 'true', 'bool');	
		$this->swfobject->add_flashvars( 'autostart', $this->options['autostart'], 'false', 'bool');	
		$this->swfobject->add_flashvars( 'playlistsize', $this->options['playlistsize'], 180);
		$this->swfobject->add_flashvars( 'playlist', $this->options['playlist'], 'none');		
		
		return;
	}

	/**
	 * wordTubeClass::GlobalFlashVars()
	 * Global parameters for palyler and playlist
	 * 
	 * @return string
	 */
	function GlobalFlashVars() {
		
		$this->swfobject->add_flashvars( 'volume', $this->options['volume'], 90);
		$this->swfobject->add_flashvars( 'bufferlength', $this->options['bufferlength'], 1);

		// Media Player V4.00 new settings
		$this->swfobject->add_flashvars( 'stretching', $this->options['stretching'], 'uniform');	
		$this->swfobject->add_flashvars( 'displayclick', $this->options['displayclick'], 'play');
		$this->swfobject->add_flashvars( 'quality', $this->options['quality'], 'true', 'bool');
		$this->swfobject->add_flashvars( 'controlbar', $this->options['controlbar'], 'bottom');	
				
		// Media Player V4.10 new settings
		$this->swfobject->add_flashvars( 'backcolor', $this->options['backcolor'], 'FFFFFF');	
		$this->swfobject->add_flashvars( 'frontcolor', $this->options['frontcolor'], '000000');
		$this->swfobject->add_flashvars( 'lightcolor', $this->options['lightcolor'], '000000');
		$this->swfobject->add_flashvars( 'screencolor', $this->options['screencolor'], '000000');				

		if ($this->options['usewatermark'])	
			$this->swfobject->add_flashvars( 'logo', rawurlencode($this->options['watermarkurl']));

		if (!empty ($this->options['skinurl']) )	
			$this->swfobject->add_flashvars( 'skin', rawurlencode($this->options['skinurl']));
		
		//Add longtail settings
		if ( $this->enableAds ) {
			$this->swfobject->add_flashvars( 'channel', $this->options['LTchannelID'], '');
			$this->swfobject->add_flashvars( 'plugins', 'ltas');
			$this->swfobject->add_flashvars( 'title', rawurlencode($this->media->name));
			$this->swfobject->add_flashvars( 'description', rawurlencode($this->media->description));
		}	
		
		return;	
	}
	
	/**
	 * wordTubeClass::addParameter()
	 * Return some paramater to control flash itself (See Adobe docs for more)
	 * 
	 * @return string
	 */
	function addParameter () {

		$this->swfobject->add_params('wmode', 'opaque');
		$this->swfobject->add_params('allowscriptaccess', 'always');
		$this->swfobject->add_params('allowfullscreen', $this->options['showfsbutton'], 'false', 'bool');
		
		return;
	}
	
	/**
	 * wordTubeClass::addLongtailFooter()
	 * Adding LongTials Ads to the footer
	 * 
	 * @return string
	 */
	function addLongtailFooter() {
		
		// ensure that it's loaded only one time
		if (!$this->addLongTail && !is_admin())
			echo "\n\t" . stripslashes( $this->options['LTapiScript'] ) ."\n";
		
		$this->addLongTail = true;
	}
	
	/**
	 * wordTubeClass::GetVidByID()
	 * Return a media record with its ID
	 * Use built-in cache if set (only useful if a media is displayed several times on a page)
	 * 
	 * @param integer $id
	 * @return array() of the database query
	 */
	function GetVidByID($id  = 0) {

		global $wpdb;

		// Get cache if exist. Does not cache not numeric id.
		$dbresult = false;
		if ( $this->use_cache === true && is_numeric($id) ) { // Use cache

			if ( $cache = wp_cache_get( 'media', 'wordtube' ) ) {
				if ( isset( $cache[$id] ) ) {
					$dbresult = $cache[$id];
				}
			}
		}

		// If cache does not exist, get datas and set cache
		if ( $dbresult === false || $dbresult === null) {

			if ($id == 'last' or $id == '0')
				$query = ' ORDER BY vid DESC LIMIT 1';
			elseif ($id == 'random')
				$query = ' ORDER BY RAND() LIMIT 1';
			else {
				$query = $wpdb->prepare(' WHERE vid = %s', $id);
			}

			$dbresult = $wpdb->get_row('SELECT * FROM '.$wpdb->wordtube.$query);

			// Set cache
			if ( $this->use_cache === true && is_numeric($id) ) { // Use cache
				$cache[$id] = $dbresult;
				wp_cache_set('media', $cache, 'wordtube');
			}
		}

		return $dbresult;
	}
	
	/**
	 * wordTubeClass::integrate_js()
	 * integrate SWF Object in HEADER
	 * 
	 * @return void
	 */
	function integrate_js() {
	
			wp_enqueue_script('swfobject', WORDTUBE_URLPATH.'javascript/swfobject.js', false, '2.1');
	
	}

} // end wordTube class
?>