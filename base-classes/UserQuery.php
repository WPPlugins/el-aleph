<?php

/// the class

class UserQuery {
	var $query;
	var $query_vars = array();
	var $queried_object;
	var $queried_object_id;
	var $request;

	var $is_profile;
	var $is_user_list;
	var $is_404;

	var $users;
	var $user;
	var $user_count;
	var $current_user = -1;
	var $in_the_loop = false;

	var $found_users = 0;
	var $max_num_pages = 0;
	
	static $views = array();
	
	static $query_keys = array('user', 'uid', 'users_per_page', 'user_key', 'user_view', 'user_type', 'paged', 'ordered');
	static $public_query_keys = array('user', 'uid', 'user_view');

	function init_query_flags(){
		$this->is_profile = false;
		$this->is_user_list = false;
		$this->is_404 = true;
	}

	function init(){
		unset($this->users);
		unset($this->query);
		$this->query_vars = array();
		unset($this->queried_object);
		unset($this->queried_object_id);
		$this->user_count = 0;
		$this->current_user = -1;
		$this->in_the_loop = false;

		$this->init_query_flags();
	}
	
	static function register_view($slug, $title, $query, $url_slug = '') {
		$slug = sanitize_title($slug);
		$title = wp_specialchars($title);
		if (empty($url_slug))
			$url_slug = $slug;
		else {
			if (strpos($url_slug, '/') !== false) {
				$slugs = array_map('sanitize_title', explode('/', $url_slug));
				$url_slug = implode('/', $slugs);
			} else
				$url_slug = sanitize_title($url_slug);
		}
		if (!isset(self::$views[$slug])) {
			self::$views[$slug] = array('slug' => $slug, 'title' => $title, 'query' => $query, 'url' => $url_slug);
			return true;
		}
		return false;
	}
	
	static function unregister_view($slug) {
		$slug = sanitize_title($slug);
		if (isset(self::$views[$slug])) {
			unset(self::$views[$slug]);
			return true;
		}
		return false;
	}
	
	static function &get_views() {
		return self::$views;
	}
	
	static function &get_view($slug) {
		if (isset(self::$views[$slug]))
			return self::$views[$slug];
		return false;
	}
	
	static function get_rewrite_rules() {
		$user_rules = array();
		$user_rules[__('people', 'aleph') . '/([^/]+)/?$'] = 'index.php?user=$matches[1]';
		$user_rules[__('people', 'aleph') . '/?$'] = 'index.php?user_view=all';
		$user_rules[__('people', 'aleph') . '/page/?([0-9]{1,})/?$'] = 'index.php?user_view=all&paged=$matches[1]';
		
		$user_rules[__('searching', 'aleph') . '/' . __('people', 'aleph') . '/page/?([0-9]{1,})/?$'] = 'index.php?user_view=all&paged=$matches[1]';
		$user_rules[__('searching', 'aleph') . '/' . __('people', 'aleph') . '/?$'] = 'index.php?user_view=all';
		
		foreach (self::$views as $view) {
			if ($view['slug'] == 'all')
				continue;
			$user_rules[__('searching', 'aleph') . '/' . __('people', 'aleph') . '/' . $view['url'] . '/page/?([0-9]{1,})/?$'] = 'index.php?user_view='. $view['slug'] .'&paged=$matches[1]';
			$user_rules[__('searching', 'aleph') . '/' . __('people', 'aleph') . '/' . $view['url'] . '?$'] = 'index.php?user_view='. $view['slug'];	
		}
		
		return $user_rules;
	}
	
	static function get_view_url($view_name) {
		global $wp_rewrite;
		if (isset(self::$views[$view_name])) {
			if ($wp_rewrite->using_permalinks())
				return get_option('home') . '/' . __('searching', 'aleph') . '/' . __('people', 'aleph');
			else
				return get_option('home') . '/index.php?user_view=' . $view_name;
		}
		return false;
	}
	
	function get_current_view_name() {
		if ($this->is_user_list)
			return $this->query_vars['user_view'];
		return false;
	}
	
	function parse_query_vars(){
		$this->parse_query('');
	}

	function fill_query_vars($array){		
		foreach (self::$query_keys as $key){
			if (!isset($array[$key]))
				$array[$key] = '';
		}
		
		return $array;
	}

	function parse_query($query){
		if (!empty($query) || !isset($this->query)){
			$this->init();
			if (is_array($query))
				$this->query_vars = $query;
			else
				parse_str($query, $this->query_vars);
			$this->query = $query;
		}
		
		$qv = &$this->query_vars;			
		
		$qv = $this->fill_query_vars($qv);
				
		
		if (!empty($qv['uid']) || !empty($qv['user'])){
			$this->is_profile = true;
			$this->is_user_list = false;
			$this->is_404 = false;
		} else if (!empty($qv['user_view'])) {
			$this->is_user_list = true;
			$this->is_404 = false;
			$this->is_profile = false;
			if (isset(self::$views[$qv['user_view']])) {
				$nqv = array();
				parse_str(self::$views[$qv['user_view']]['query'], $nqv);
				$qv = array_merge($qv, $nqv);
				unset($nqv);
			} else
				$this->is_404 = true;
		} else {
			$this->is_404 = true;
		}
		
		do_action('users_parse_query', $this);
		
	}

	function get($query_var){
		if (isset($this->query_vars[$query_var]))
			return $this->query_vars[$query_var];
		return '';
	}

	function set($query_var, $value){
		$this->query_vars[$query_var] = $value;
	}

	/*
	 * TODO:
	 * - if querying for a profile, get the user from cache
	 * - users per page is hardcoded. 
	 */
	function &get_users(){
		global $wpdb, $pagenow, $user_ID;
		
		if ($this->is_404)
			return false;

		$q = &$this->query_vars;

		$distinct = '';
		$result = '';
		$where = '';
		$limits = '';
		$join = '';
		$search = '';
		$groupby = '';
		$orderby = '';
		$found_rows = '';
		$fields = "$wpdb->users.*";

		$q['users_per_page'] = apply_filters('users_per_page', 15);

		if (!empty($q['user'])) {
			$q['user'] = sanitize_title($q['user']);
			$where = " AND $wpdb->users.user_nicename = '" . $q['user'] ."' ";
		} else if (!empty($q['uid'])) {
			$q['uid'] = absint($q['uid']);
			$where = " AND $wpdb->users.ID = '" . $q['uid'] ."' ";
		}
		
		if (!empty($q['user_key'])) {
			$q['user_key'] = preg_replace('|[^a-z0-9_]|i', '', $q['user_key']);
			if (!empty($q['user_key'])) {
				$join .= " INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) ";
				$where .= " AND $wpdb->usermeta.meta_key = '" . $q['user_key'] . "' ";
			} else
				$where .= " AND 1=0 ";
		}
		
		//TODO: case admins, moderators, commenters, etc.
		if (!empty($q['user_type'])) {
			switch($q['user_type']) {
				case 'authors':
					$distinct .= 'DISTINCT';
					$join .= " INNER JOIN $wpdb->posts ON ($wpdb->users.ID = $wpdb->posts.post_author) ";
					$where .= " AND $wpdb->posts.post_type = 'post' AND $wpdb->posts.post_status = 'publish' ";
					break;
				case 'registered':
					break;
				default:
					$where .= " AND 1=0 ";
					break;
			}
		}
		
		if (!empty($q['order'])) {
			switch($q['order']) {
				case 'alpha': 
					$orderby = "ORDER BY $wpdb->users.user_nicename ASC ";
					break;
				case 'last_joined': 
					$orderby = "ORDER BY $wpdb->users.user_registered DESC ";
					break;
				case 'joined':
				default:
					$orderby = " ORDER BY $wpdb->users.user_registered ASC ";
					break;
			}
		} else {
			$orderby = " ORDER BY $wpdb->users.user_registered ASC ";
		}

		if (!$this->is_profile) {
			$page = abs(intval($q['paged']));
			if (empty($page)) {
				$page = 1;
			}
			$pagestart = ($page - 1) * $q['users_per_page'];
			$limits = 'LIMIT ' . $pagestart . ', ' . $q['users_per_page'];
		}

		if (!empty($limits)){
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}

		$join = apply_filters('users_join', $join);
		$where = apply_filters('users_where', $where);
		$distinct = apply_filters('users_distinct', $distinct);
		$groupby = apply_filters('users_group_by', $groupby);
		$orderby = apply_filters('users_order_by', $orderby);

		$fields = apply_filters('users_fields', $fields);
		$this->request = "SELECT $found_rows $distinct $fields FROM $wpdb->users $join WHERE 1=1 $where $groupby $orderby $limits";

		$this->request = apply_filters('users_request', $this->request);

		$this->users = $wpdb->get_results($this->request);

		if (!empty($limits)) {
			$this->found_users = (int) $wpdb->get_var("SELECT FOUND_ROWS()");
			$this->max_num_pages = (int) ceil($this->found_users / $q['users_per_page']);
		} else {
			$this->found_users = count($this->users);
			$this->max_num_pages = 1;
		}

		if ($this->users && !empty($this->users)) {
			$ids = array();
			foreach ($this->users as $u){
				$ids[] = $u->ID;
			}
	
			// from WP's get_userdata()
			$metawhere = " WHERE user_id IN ('" . implode("', '", $ids) . "') ";
			$metawhere = apply_filters('users_meta_where', $metawhere);
			$metavalues = $wpdb->get_results("SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta $metawhere ");
	
			if ($metavalues) {
				foreach ($metavalues as $meta) {
					$value = maybe_unserialize($meta->meta_value);
					foreach ($this->users as $u){
						if ($u->ID == $meta->user_id){
							$value = maybe_unserialize($meta->meta_value);
							$u->{$meta->meta_key} = $value;
							break;
						} else {
							continue;
						}
					}
		
					// We need to set user_level from meta, not row
					if ($wpdb->prefix . 'user_level' == $meta->meta_key)
						$u->user_level = $meta->meta_value;
				} // end foreach
			}
	
			$this->user_count = count($this->users);
			$this->user = $this->users[0];
			$this->is_user_list = true;
			$this->current_user = -1;
			
			if (!$this->queried_object)
				$this->queried_object = $this->users[0];

			$this->is_404 = false;
			do_action('users_queried');
			return $this->users;
			
		} else {
			$this->is_404 = true;
			$this->user_count = 0;
			$this->user = NULL;
			return false;
		}
	}
	
	function next_user() {
		$this->current_user++;
		$this->user = $this->users[$this->current_user];
		return $this->user;
	}

	function the_user() {
		global $user;
		$this->in_the_loop = true;
		$user = $this->next_user();
	}

	function have_users() {
		if ($this->current_user + 1 < $this->user_count)
			return true;
		else if ($this->current_post + 1 == $this->user_count)
			$this->rewind_users();

		$this->in_the_loop = false;
		return false;
	}

	function rewind_users() {
		$this->current_user = -1;
		if ($this->user_count > 0)
			$this->user = $this->users[0];
	}

	function &query($query){
		$this->parse_query($query);
		return $this->get_users();
	}

	function __construct($query = ''){
		if (!empty($query)) // in this case, someone is calling our class inside a template or plugin
			$this->query($query);
	}
	
	function __destruct() {}

	function setup_template() {
		if ($this->is_profile && !$this->is_404){
			if (file_exists(TEMPLATEPATH . "/profile.php"))
				include(TEMPLATEPATH . "/profile.php");
			else if (file_exists(PLUGINDIR . "/el-aleph/example-templates/profile.php"))
				include(PLUGINDIR . "/el-aleph/example-templates/profile.php");				
			else
				wp_die(__('Please check your Aleph installation (missing templates).', 'aleph'));
			exit;
		}

		if ($this->is_user_list) {
			if (file_exists(TEMPLATEPATH . "/users.php"))
				include(TEMPLATEPATH . "/users.php");
			else if (file_exists(PLUGINDIR . "/el-aleph/example-templates/users.php"))
				include(PLUGINDIR . "/el-aleph/example-templates/users.php");
			else
				wp_die(__('Please check your Aleph installation (missing templates).', 'aleph'));
			exit;
		}	
		
		if (file_exists(TEMPLATEPATH . "/404.php")) {
			include(TEMPLATEPATH . "/404.php");
			exit;
		}
	}
}

?>