/*
 * javascript for wordtube.
 * By Alakhnor (http://www.alakhnor.com/post-thumb)
 * Copyright (c) 2007 Alakhnor
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
*/

jQuery(document).ready(function() {

	jQuery(".wtedit").editable(wt_path+"admin/update.php", {
		indicator 	: "<img src='"+wt_path+"javascript/img/indicator.gif'>",
		style     	: "inherit",
		placeholder 	: placeholder1,
		tooltip		: tooltip1
	});

});

