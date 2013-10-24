<?php
if ( !class_exists('swfobject') ) :
/**
 * swfobject - PHP class for creating dynamic content of SWFObject V2.1
 * 
 * @author Alex Rabe
 * @package wordTube
 * @version 0.6
 * @copyright GNU General Public License Version 2
 * @access public
 * @example http://code.google.com/p/swfobject/
 */
class swfobject {
	/**
     * id of the HTML element
     *
     * @var string
     */
    var $id;
	/**
     * specifies the width of your SWF
     *
     * @var string
     * @private
     */
    var $width;
	/**
     * specifies the height of your SWF
     *
     * @var string
     * @privat
     */
    var $height;
	/**
     * the javascript output
     *
     * @var string
     */
    var $js;
	/**
     * the replacemnt message
     *
     * @var string
     */
    var $message = 'The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and <a href="http://www.mozilla.com/firefox/">a browser with Javascript support</a> are needed..';			
	/**
     * the classname for the div element
     *
     * @var string
     */
    var $classname = 'swfobject';
	/**
     * array of flashvars
     *
     * @var array
     */
    var $flashvars;

	/**
	 * swfobject::swfobject()
	 * 
	 * @param string $swfUrl (required) specifies the URL of your SWF
	 * @param string $id (required) specifies the id of the HTML element (containing your alternative content) you would like to have replaced by your Flash content
	 * @param string $width (required) specifies the width of your SWF
	 * @param string $height (required) specifies the height of your SWF
	 * @param string $version (required) specifies the Flash player version your SWF is published for (format is: "major.minor.release")
	 * @param string $expressInstallSwfurl (optional) specifies the URL of your express install SWF and activates Adobe express install
	 * @param array $flashvars (optional) specifies your flashvars with name:value pairs
	 * @param array $params (optional) specifies your nested object element params with name:value pair
	 * @param array $attributes (optional) specifies your object's attributes with name:value pairs
	 * @return string the content
	 */
	function swfobject( $swfUrl, $id, $width, $height, $version, $expressInstallSwfurl = false, $flashvars = false, $params = false /* DEPRECATED */, $attributes = false /* DEPRECATED */ ) {
	
		global $swfCounter;
		
		// look for a other swfobject instance
		if ( !isset($swfCounter) )
			$swfCounter = 1;
		
		$this->id = $id . '_' . $swfCounter;
		$this->width = $width;
		$this->height = $height;
		$this->flashvars  = ( is_array($flashvars) )  ? $flashvars : array();

		$this->embedSWF = 'swfobject.embedSWF("'. $swfUrl .'", "'. $this->id .'", "'. $width .'", "'. $height .'", "'. $version .'", '. $expressInstallSwfurl .', this.flashvars, this.params , this.attr );' . "\n";
	}
	
	function output () {
		
		global $swfCounter;
		
		// count up if we have more than one swfobject
		$swfCounter++;
		
		$out  = "\n" . '<div class="'. $this->classname .'" id="'. $this->id  .'" style="width:'.$this->width .'px; height:'. $this->height .'px;">';
		$out .= "\n" . $this->message;
		$out .= "\n" . '</div>';
		
		return $out;
	}
	
	function javascript () {

		//Build javascript
		$this->js .= "(function () {\n";
		$this->js .= "\tjwplayer('" . $this->id . "').setup(";
		$this->js .= $this->add_js_parameters($this->flashvars);
		$this->js .= ");\n";
		$this->js .= "})();";

		return $this->js;
	}
	
	function add_flashvars ( $key, $value, $default = '', $type = '', $prefix = '' ) {

		if ( is_bool( $value ) )
			$value = ( $value ) ? 'true' : 'false';
		elseif ( $type == 'bool' )
			$value = ( $value == '1' ) ? 'true' : 'false';
		
		// do not add the variable if we hit the default setting 	
		if ( $value == $default )	
			return;
		
		if(is_array($value)) 
			$this->flashvars[$key] = $value;
		else
			$this->flashvars[$key] = $prefix . $value;
		return;
	}

	function add_js_parameters($params, $indent = 2) {
		$list = '';
		$tabs = "\n" . str_repeat("\t", $indent);

		if ( is_array($params) ) {
			foreach ($params as $key => $value) {
				if  ( !empty($list) )
					$list .= ",";

				$list .= $tabs . "'" . $key . '\': ';

				if(is_array($value)) {
					$list .= $this->add_js_parameters($value, $indent + 1);
				} else if(is_bool($value)) {
					$list .= $value ? 'true' : 'false';
				} else if(is_numeric($value)) {
					$list .= $value;
				} else {
					$list .= "'" . str_replace('&amp;', '&', esc_js($value)) . "'";
				}
			}
		}
		$js = '{' . $list . $tabs . '}';
		return $js;
	}
	
}
endif;

?>