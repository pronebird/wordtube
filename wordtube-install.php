<?php

/*
+----------------------------------------------------------------+
+	wordtube-install V1.50
+	by Alex Rabe
+   required for wordtube
+----------------------------------------------------------------+
*/

//#################################################################

function wordtube_install() {

global $wpdb;

	// set tablename
	$table_name 		= $wpdb->prefix . 'wordtube'; 		
	$table_playlist		= $wpdb->prefix . 'wordtube_playlist';
	$table_med2play		= $wpdb->prefix . 'wordtube_med2play';
	
	// upgrade function changed in WordPress 2.3	
	if (version_compare($wp_version, '2.3.alpha', '>='))		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	else
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		
     
	if($wpdb->get_var("show tables like '$table_name'") != $table_name){
	 
	      $sql = "CREATE TABLE ".$table_name." (
	      vid MEDIUMINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	      name MEDIUMTEXT NULL,
	      creator MEDIUMTEXT NULL,
	      file MEDIUMTEXT NULL,
	      image MEDIUMTEXT NULL,
	      link MEDIUMTEXT NULL,
	      width SMALLINT(5) NOT NULL,
	      height SMALLINT(5) NOT NULL,
	      autostart TINYINT(1) NULL DEFAULT '0'
	     );";
	     
    dbDelta($sql);

		$wordtube_options[center]=0;
		$wordtube_options[deletefile]=0;
		$wordtube_options[usewpupload]=1;
		$wordtube_options[uploadurl]=get_option('upload_path');
		$wordtube_options[autostart]=0; 
		$wordtube_options[repeat]=0;
		$wordtube_options[overstretch]="true";
		$wordtube_options[showdigits]=1;
		$wordtube_options[showfsbutton]=0;
		$wordtube_options[backcolor]="FFFFFF";
		$wordtube_options[frontcolor]="000000";
		$wordtube_options[lightcolor]="000000";
		$wordtube_options[volume]=80;
		$wordtube_options[bufferlength]=5;
		// new since 1.10
		$wordtube_options[thumbnail]=true;
		$wordtube_options[width]=400;
		$wordtube_options[height]=320;
		$wordtube_options[playlistsize]=120;
		$wordtube_options[shuffle]=false;
		// new since 1.20
		$wordtube_options[showeq]=false;
		$wordtube_options[statistic]=true;
		$wordtube_options[countcomplete]=false;
		// new since 1.30
		$wordtube_options[usewatermark]=false;
		$wordtube_options[watermarkurl]="";
		$wordtube_options[autoscroll]=true;
		$wordtube_options[xhtmlvalid]=false;
		$wordtube_options[activaterss]=false;
		$wordtube_options[rssmessage]=__('See post to watch Flash video','wpTube');
		// new since 1.43
		$wordtube_options[displaywidth]=400;
		// new since 1.50
		$wordtube_options[largecontrols]=false;
		
		update_option('wordtube_options', $wordtube_options);
	}
	
	if($wpdb->get_var("show tables like '$table_playlist'") != $table_playlist){
	 
	 	$sql = "CREATE TABLE ".$table_playlist." (
		pid BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		playlist_name VARCHAR(200) NOT NULL ,
		playlist_desc LONGTEXT NULL,
		playlist_order VARCHAR(50) NOT NULL DEFAULT 'ASC'
	    );";
	     
	    dbDelta($sql);
	
	}
	
	if($wpdb->get_var("show tables like '$table_med2play'") != $table_med2play){
	 
	 	$sql = "CREATE TABLE ".$table_med2play." (
		rel_id BIGINT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		media_id BIGINT(10) NOT NULL DEFAULT '0',
		playlist_id BIGINT(10) NOT NULL DEFAULT '0'
	    );";
	     
	    dbDelta($sql);
	
	}
		
		// update routine
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "creator"');
		if (!$result) $wpdb->query("ALTER TABLE ".$table_name." ADD creator VARCHAR(255) NOT NULL AFTER name");
		
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "autostart"');
		if (!$result) $wpdb->query("ALTER TABLE ".$table_name." ADD autostart TINYINT(1) NULL DEFAULT '0'");
		
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "counter"');
		if (!$result) $wpdb->query("ALTER TABLE ".$table_name." ADD counter MEDIUMINT(10) NULL DEFAULT '0'");
		
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "exclude"');
		if ($result) $wpdb->query("ALTER TABLE ".$table_name." CHANGE exclude autostart TINYINT(1) NULL DEFAULT '0'");
		
		// update to database v1.40
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "link"');
		if (!$result) {
			
		$wpdb->query("ALTER TABLE ".$table_name." ADD link MEDIUMTEXT NULL AFTER image ");
		$wpdb->query("ALTER TABLE ".$table_name." CHANGE creator creator MEDIUMTEXT NULL ");
		$wpdb->query("ALTER TABLE ".$table_name." CHANGE name name MEDIUMTEXT NULL ");
		$wpdb->query("ALTER TABLE ".$table_name." CHANGE file file MEDIUMTEXT NULL ");
		$wpdb->query("ALTER TABLE ".$table_name." CHANGE image image MEDIUMTEXT NULL ");

		}
}

?>