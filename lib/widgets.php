<?php

function aleph_widget_user_list_links($args) {
	extract($args);
	
	echo $before_widget;
	
	$list_args = array('before_title' => $before_title, 'after_title' => $after_title);
	aleph_user_lists($list_args);
	
	echo $after_widget;
}

?>