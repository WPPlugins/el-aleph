<?php 

/* Functions */

function aleph_settings() {
	get_currentuserinfo();
	load_plugin_textdomain('aleph', ALEPH_PATH);
	
	register_sidebar_widget(array('Aleph User Lists', 'aleph'), 'aleph_widget_user_list_links');
	UserQuery::register_view('authors', __('Contributors', 'aleph'), 'user_type=authors');
	UserQuery::register_view('all', __('Registered Users', 'aleph'), 'user_type=registered');
	UserQuery::$query_keys = apply_filters('users_query_vars', UserQuery::$query_keys);
}

function aleph_hook_custom_vars() {
	global $wp;
	
	$query_vars = &UserQuery::$query_keys;
	foreach ($query_vars as $var)
		$wp->add_query_var($var);
}

function aleph_detect_custom_vars($wp) {
	$qv =& $wp->query_vars;
	
	$is_aleph_request = apply_filters('aleph_detect_custom_vars', false);
	if (!$is_aleph_request) {
		
		//TODO: read values from UserQuey::$public_query_keys
		if (empty($qv['user']) && empty($qv['uid']) && empty($qv['user_view']))
			return;
	}
	
	aleph_initialize();
	
	add_filter('wp_title', 'aleph_wp_title');
	do_action('aleph_init_users_query');

	$user_query = '';
		
	if ($qv['user'] != '' || $qv['uid'] != '') {
		// we are in a profile
		if ($qv['user'] != '')
			$user_query = sprintf('user=%s', $qv['user']);
		else
			$user_query = sprintf('uid=%d', absint($qv['uid']));
	} else {
		// we are in a user list
		// page number
		$paged = $qv['paged'] != '' ? absint($qv['paged']) : 1;
		if ($paged <= 1)
			$paged = 1;	
			
		$user_view = $qv['user_view'] != '' ? $qv['user_view'] : 'all';
			
		$user_query = sprintf('user_view=%s&paged=%d', $user_view, $paged);
	}
	
	$GLOBALS['aleph_query']->query( apply_filters( 'users_query', $user_query ) );
	$GLOBALS['aleph_query']->setup_template();
	
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	include(TEMPLATEPATH . "/index.php");
	exit;
}

function aleph_initialize(){
	global $aleph_query;
	global $el_aleph_uq;
	
	$aleph_query = new UserQuery;
	$el_aleph_uq =& $aleph_query;
}

function aleph_filter_rewrite_rules($rules) {		
	do_action('aleph_filter_rewrite_rules');
	$rules = UserQuery::get_rewrite_rules() + $rules;
	return $rules;
}

function aleph_activation(){
	load_plugin_textdomain('aleph', ALEPH_PATH . '/');
	$GLOBALS['wp_rewrite']->flush_rules();
}

function aleph_deactivation(){
	remove_filter('rewrite_rules_array', 'aleph_filter_rewrite_rules');
	$GLOBALS['wp_rewrite']->flush_rules();
}

function aleph_uninstall(){
	$GLOBALS['wp_rewrite']->flush_rules();
}

function aleph_credits() {
	echo '<!-- Using Aleph for WordPress: http://wordpress.org/extend/plugins/el-aleph -->';
}

function aleph_wp_title($title, $sep = '', $seplocation = '') {
	global $aleph_query;
	
	if (!$aleph_query)
		return $title;
	
	if ($aleph_query->is_profile && $aleph_query->is_404)
		return $title;
		
	if (empty($sep))
		return aleph_user_template_title();
			
	if ( 'right' == $seplocation ) // sep on right, so reverse the order
		$title = aleph_user_template_title() . " $sep ";
	else
		$title = "$sep ". aleph_user_template_title();
		
	return $title;
}

?>