<?php

function previous_users_link($text) {
	previous_posts_link($text, get_found_user_pages());
}

function next_users_link($text) {
	next_posts_link($text, get_found_user_pages());
}

function user_list_needs_pagination() {
	return get_found_user_pages() > 1;
}

function users_nav_links($before = '', $after = '') {
        global $wp, $aleph_query, $wp_rewrite;
        
        if (!is_user_view())
        	return;
        
        if (user_list_needs_pagination()) {
        		$base = UserQuery::get_view_url( $aleph_query->get_current_view_name() );
                if ($wp_rewrite->using_permalinks()) {
                        $base .= '/%_%';
                        $format = 'page/%#%';
                } else {
                        if (strpos($base, '?') === false)
                                $base .= '?%_%';
                        else
                                $base .= '&%_%';
                        $format = 'paged=%#%';
                }
                $current = (int) $wp->query_vars['paged'];
                if ($current <= 0)
                        $current = 1;
                $args = array(  'base' => $base,
                                'format' => $format,
                                'total' => get_found_user_pages(),
                                'current' => $current,
                                'end_size' => 4);
                echo $before . paginate_links($args) . $after;
        }
}

function aleph_user_lists($args = NULL) {
	global $wp_rewrite;
	
	$defaults = array(
		'title' => __('Our Users', 'aleph'), 
		'show_title' => true, 
		'before_title' => '<h3>', 
		'after_title' => '</h3>', 
		'type' => 'list');
	
	$args = wp_parse_args($defaults, $args);
	
	$lists = array();
	
	if ($args['show_title']) 
		printf('%s%s%s', $args['before_title'], $args['title'], $args['after_title']);
	
	foreach (UserQuery::get_views() as $view)
		$lists[$view['slug']] = '<a href="' . aleph_get_user_view_link($view) . '">' . $view['title'] . '</a>';
	
	$lists = apply_filters('aleph_user_lists', $lists);
		
	if ($args['type'] == 'list') {
		echo '<ul class="user-lists">';
		
		if (is_user_view())
			$current = aleph_get_current_view();
		else
			$current = false;
			
		foreach ($lists as $slug => $link) {
			if ($slug == $current) 
				printf('<li class="user-view-%s current-user-view">%s</li>', $slug, $link);
			else
				printf('<li class="user-view-%s">%s</li>', $slug, $link);
		}
		echo '</ul>';
	}
	else
		return $lists;
}

?>