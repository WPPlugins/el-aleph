<?php


/// user views

function get_user_query_var($var) {
	global $aleph_query;
	if (!$aleph_query || !is_object($aleph_query))
		return false;
	if (isset($aleph_query->query_vars[$var]))
		return $aleph_query->query_vars[$var];
	return false;
}

function aleph_register_user_view($slug, $title, $query, $url_slug = '') {
	return UserQuery::register_view($slug, $title, $query, $url_slug);
}

function aleph_unregister_user_view($slug) {
	return UserQuery::unregister_view($slug);
}

function aleph_current_user_view_title() {
	global $aleph_query;
	if ('' != get_user_query_var('user_view')) {
		$view = UserQuery::get_view(sanitize_title(get_user_query_var('user_view')));
		if (is_array($view))
			return $view['title'];
	}
	return false;
}

function aleph_get_user_view_link($view) {
	global $wp_rewrite;
	if (is_array($view)) {
		if ($wp_rewrite->using_permalinks()) { //TODO: sacar el cero
			if ($view['slug'] != 'all') 
				$url = get_option('siteurl') . '/' . __('searching', 'aleph') . '/' . __('people', 'aleph') . '/' . $view['url'] . '/';
			else
				$url = get_option('siteurl') . '/' . __('searching', 'aleph') . '/' . __('people', 'aleph') . '/';
		} else {
			$url = get_option('siteurl') . '/' . 'index.php?user_view=' . $view['slug'];
		}
		return $url;
	} else 
		return false;
}

/// the conditional template tags

function is_profile() {
	global $aleph_query;
	if ($aleph_query) 
		return $aleph_query->is_profile;
	return false;
}


function is_user_view($view_slug = '') {
	global $aleph_query;
	if ($aleph_query)  {
		if (empty($view_slug))
			return $aleph_query->is_user_list;
		else
			return $aleph_query->is_user_list && $view_slug == aleph_current_user_view_title();
	}
	return false;
}

/// query template tags

function &query_users($query) {
	unset($GLOBALS['aleph_query']);
	$GLOBALS['aleph_query'] =& new UserQuery($query);
	return $GLOBALS['aleph_query']->get_users();
}

function have_users(){
	global $aleph_query;
	if ($aleph_query)
		return $aleph_query->have_users();
	return false;
}

function the_user(){
	global $aleph_query;
	if (isset($aleph_query))
		return $aleph_query->the_user();
	return false;
}

function get_found_users() {
	global $aleph_query;
	if ($aleph_query)
		return (int) $aleph_query->found_users;
	return 0;
}

function get_found_user_pages() {
	global $aleph_query;
	if ($aleph_query)
		return (int) $aleph_query->max_num_pages;
	return 0;
}

function the_found_users() {
	$found = get_found_users();
	if ($found == 1)
		printf(__('<span>%s</span> user found.', 'aleph'), $found);
	else if ($found > 1)
		printf(__('<span>%s</span> users found.', 'aleph'), $found);
	else
		printf(__('No users found.', 'aleph'));
}

function aleph_get_title_for_user_profile() {
	global $aleph_query;
	if (is_profile()) {
		$display_name = !empty($aleph_query->queried_object->display_name) ?
			$aleph_query->queried_object->display_name :
			$aleph_query->queried_object->user_login; 
		return $display_name;
	}
	return false;
}

// tweak
function aleph_user_template_title() {
	$title = '';
	if (get_found_users() > 0) {
    	if (is_profile())
			$title = __('About', 'aleph') . ' ' . aleph_get_title_for_user_profile();
		else // is_user_view
			$title = apply_filters('aleph_user_template_title', aleph_current_user_view_title());
	} else  
		$title = __('No users found', 'aleph');
	return $title;
}

function aleph_user_list_title() {
	$title = '';
	if (is_user_view())
            $title = apply_filters('aleph_user_list_title', aleph_current_user_view_title());
    else if (is_profile()) {
    	if (get_found_users() > 0)
    		$title = __('About', 'aleph') . ' ' . aleph_get_title_for_user_profile();
    	else
            $title = __('User not found', 'aleph');
    }
	echo $title;
}

// convenient functions
function aleph_users_list($args = NULL) {
	global $user;
	
	$defaults = array(
		'before_title' => '<h2>',
		'after_title' => '</h2>',
		'before_list' => '',
		'after_list' => '',
		'before_user' => '',
		'after_user' => '',
		'before_username' => '<h3>',
		'after_username' => '</h3>',
		'before_found_users' => '',
		'after_found_users' => '',
		'before_navigation' => '',
		'after_navigation' => '',
		'show_avatars' => true,
		'before_avatar' => '',
		'after_avatar' => '',
		'before_list_meta' => '',
		'after_list_meta' => '',
		'avatar_size' => 96,
		'show_location' => true,
		'show_full_name' => true,
		'show_registration_date' => true,
		'show_url' => true,
		'url_caption' => __('Web Site', 'aleph'),
		'list_attributes' => 'id="user-list"',
		'show_navigation' => true
	);
	
	$args = wp_parse_args($args, $defaults);
	extract($args);
	
	echo $before_list_meta;
	
	echo $before_title;
	aleph_user_list_title();
	echo $after_title;
	
	echo $before_found_users;
	the_found_users();
	echo $after_found_users;
	
	echo $after_list_meta;
	
	echo $before_list; 
	
	if (have_users()) {
		
		?>
		<ul <?php echo $list_attributes; ?>">
			<?php while (have_users()) { the_user(); ?>
				<li class="user">
					<?php 
					echo $before_user;
					do_action('aleph_user_list_before_user'); 
					
					if ($show_avatars) 
						aleph_the_user_avatar($before_avatar, $after_avatar, $avatar_size);	
					
					echo $before_username;	
					aleph_the_user_profile_link(aleph_get_user_display_name());
					echo $after_username; 
					
					?>
						
					<ul class="user-fields">
						<?php do_action('aleph_user_list_user_fields_before_details'); ?>	
				
						<?php if ($show_full_name && aleph_get_user_complete_name()) { ?>
						<li><?php aleph_the_user_complete_name(); ?></li>
						<?php } ?>
				
						<?php if ($show_location && aleph_get_user_location()) { ?>
						<li><?php _e('Location', 'aleph'); echo ': '; aleph_the_user_location(); ?></li>
						<?php } ?>
						
						<?php if ($show_registration_date) { ?>
						<li><?php _e('Registration Date', 'aleph'); echo ': '; aleph_the_user_registration_date(); ?></li>
						<?php } ?>
				
						<?php if ($show_url && aleph_get_user_url()) { ?>
						<li><?php aleph_the_user_url($url_caption); ?></li>
						<?php } ?>	
				
						<?php do_action('aleph_user_list_user_fields_after_details'); ?>	
					</ul>
					<?php 
					do_action('aleph_user_list_after_user'); 
					echo $after_user;
					?>
				</li>
			<?php } ?>
		</ul>
		<?php 
		
		if ($show_navigation) 
			users_nav_links($before_navigation, $after_navigation); 
		
	} else { 
		printf('<p>%s</p>', __('Perhaps someday you will find someone on this list ... just be patient (or join the fun!).', 'aleph')); 
	}
	echo $after_list;
}

function aleph_user_profile($args = NULL) {
	
	$defaults = array(
		'before_title' => '<h2>',
		'after_title' => '</h2>',
		'before_section' => '<h3>',
		'after_section' => '</h3>',
		'before_avatar' => '',
		'after_avatar' => '',
		'show_avatar' => true,
		'before_meta' => '',
		'after_meta' => '',
		'avatar_size' => 96
	);
	
	$args = wp_parse_args($args, $defaults);
	extract($args);
	
	if (have_users()) {
		the_user();
		
		echo $before_meta;
		
		echo $before_title . __('About', 'aleph') . ' ' . aleph_get_user_display_name() . $after_title; 
		
		echo $after_meta;
		
		echo $before_profile_data;
		
		echo $before_section . __('Personal Information', 'aleph') . $after_section;
		
		do_action('aleph_before_user_profile'); ?>
		
		<?php if ($show_avatar) aleph_the_user_avatar($before_avatar, $after_avatar, $avatar_size); ?>
		
		<dl>
			<?php if (aleph_get_user_complete_name()) { ?>
			<dt><?php _e('Name', 'aleph'); ?></dt>
			<dd><?php echo aleph_get_user_complete_name(); ?></dd>
			<?php } ?>
			
			<?php if (aleph_get_user_location()) { ?>
			<dt><?php _e('Location', 'aleph'); ?></dt>
			<dd><?php aleph_the_user_location(); ?></dd>
			<?php } ?>
			
			<dt><?php _e('Registration Date', 'aleph'); ?></dt>
			<dd><?php aleph_the_user_registration_date(); ?></dd>
			
			<?php if (aleph_get_user_url()) { ?>
			<dt><?php _e('URL', 'aleph'); ?></dt>
			<dd><?php aleph_the_user_url(aleph_get_user_url()); ?></dd>
			<?php } ?>
			
			
			<?php if (aleph_get_user_description()) { ?>
			<dt>
			<?php _e('User Bio', 'aleph'); ?>
			</dt>
			<dd>
			<?php aleph_the_user_description(); ?>
			</dd>
			<?php } ?>
		
			<?php do_action('aleph_user_profile_fields'); ?>
		
		</dl>
		
		<?php do_action_ref_array('aleph_after_user_profile', array($args)); ?>
		
		<?php
		
		echo $after_profile_data;
		
	} else {
		echo $before_title . __('The user does not exists.', 'aleph') . $after_title; 
	}
}

function aleph_get_current_view() {
	global $aleph_query;
	if (is_user_view()) {
		return $aleph_query->get_current_view_name();
	}
	return false;
}

?>