<?php
/**
 * User Template Tags
 * If the function name starts with aleph_the_user, the template tag must be used in user loops only.
 * If the function name starts with aleph_the_author, it must be used in post loops only.
 */


/// prints the ID of the current user in a user-loop
function aleph_the_user_ID() {
	$ID = aleph_get_user_ID();
	if ($ID) 
		echo $ID;
}

/// returns the ID of the current user in a user-loop
function aleph_get_user_ID() {
	global $user;
	if ($user)
		return $user->ID;
	else 
		return false;
}

/// prints a link to the user's posts page, i.e., <a href="http://site/author/user">caption</a>.
function aleph_the_user_posts_link($caption = '', $u = NULL) {
	global $user;
	if (!$u && $user)
		$u = $user;
	if ($u) {
		if (empty($caption)) 
			$caption = $u->display_name;
		echo '<a href="' . get_author_posts_url($u->ID, $u->user_nicename) . '">' . $caption . '</a>';
	} 
}

/// returns the url of the user's posts page, i.e., http://site/author/user.
function aleph_get_user_posts_url($u = NULL) {
	global $user;
	if (!$u && $user)
		$u = $user;
	if ($u) {
		return get_author_posts_url($u->ID, $u->user_nicename);
	}
}


function aleph_the_user_email() {
	$email = aleph_get_user_email();
	if ($email) 
		echo $email;
}


function aleph_get_user_email() {
	global $user;
	if ($user)
		return $user->user_email;
	else
		return false;
}

function aleph_get_user_meta($key, $u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u->{$key})
		return $u->{$key};
	else
		return get_usermeta($u->ID, $key);
}

function aleph_get_user_complete_name($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u && ($u->first_name || $u->last_name)) {
		if ($u->first_name && $u->last_name)
			return $u->first_name . ' ' . $u->last_name;
		else if ($u->first_name)
			return $u->first_name;
		else
			return $u->last_name;	
	}
	return false;
}	

function aleph_the_user_complete_name($before = '', $after = '') {
	global $user;
	if ($user && ($user->first_name || $user->last_name))
		echo $before . $user->first_name . ' ' . $user->last_name . $after;
}	

function aleph_the_user_avatar($before = '', $after = '', $avatar_size = 96) {
	global $user;
	if ($user) {
		if (get_option('show_avatars'))
			echo $before . get_avatar($user->ID, $avatar_size) . $after;
	}
}

/**
 * For integration with UserPhoto.
 *
 * @param unknown_type $user
 * @return unknown
 */
function aleph_get_userphoto($u = NULL){
	global $user;
	if (!$u)
		$u = $user;
    if ($u && $u->userphoto_image_file)
        return get_option('siteurl') . '/wp-content/uploads/userphoto/' . $u->userphoto_image_file;
	else
        return false;
}

function aleph_get_user_description($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u)
		return $u->description;
}

function aleph_the_user_description(){
	global $user;
	if ($user)
		echo wpautop(wptexturize($user->description));
}

function aleph_the_user_url($caption = '') {
		global $user;
		$url = aleph_get_user_url($user);
		if ($url) {
			if (empty($caption))
				$caption = $url;
			echo '<a href="' . $url . '">' . $caption . '</a>';
		}
}

function aleph_get_user_url($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u && $u->user_url && !empty($u->user_url) && $u->user_url != 'http://')
		return $u->user_url;
	else
		return false;
}

function aleph_the_user_registration_date() {
	global $user;
	if (!$user) 
		return;
	$registered = aleph_get_user_registration_date($user);
	if ($registered)
		echo $registered;
}

function aleph_get_user_registration_date($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u && $u->user_registered) {
		return mysql2date(get_option('date_format'), $u->user_registered);
	} else
		return false;
}

function aleph_get_user_display_name($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u) {
		if (!empty($u->display_name))
			return $u->display_name;
		else
			return $u->user_login;
	} 
	return false;
}

function aleph_the_user_display_name() {
	global $user;
	if ($user) {
		if (!empty($user->display_name))
			echo $user->display_name;
		else
			echo $user->user_login;
	}
}

function aleph_the_user_login() {
	global $user;
	if ($user)
		echo $user->user_login;
}

function aleph_the_user_nicename() {
	$nicename = aleph_get_user_nicename();
	if ($nicename)
		echo $nicename;
}

function aleph_get_user_nicename() {
	global $user;
	if ($user) {
		if (!empty($user->user_nicename))
			return $user->user_nicename;
		else
			return $user->user_login;
	}
	return false;
}

function aleph_get_user_location($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u && $u->from)
		return $u->from;
	else
		return false;
}

function aleph_the_user_location() {
	$location = aleph_get_user_location();
	if ($location)
		echo wptexturize($location);
}

/* PROFILE */

/// displays a link to a user profile in a users loop.
function aleph_the_user_profile_link($caption = '') {
	global $user;
	$profile_url = aleph_get_user_profile_url($user);
	if ($profile_url !== false) {
		if (empty($caption)) {
			$caption = !empty($user->display_name) ? $user->display_name : $user->user_login;
		}
		echo '<a href="' . $profile_url . '">' . $caption . '</a>';
	}
}

/// displays a link to a user profile in a posts loop.
function aleph_the_author_profile_link($caption = '') {
	global $authordata;
	aleph_the_user_profile_link($caption, $authordata);
}

function aleph_get_user_profile_url($u = NULL) {
	global $user;
	global $wp_rewrite;
	if (!$u)
		$u = $user;
	if ($u) {
		if ($wp_rewrite->using_permalinks())
			$url = get_option('siteurl') . '/' . __('people', 'aleph') . '/' . $u->user_nicename;
		else 
			$url = get_option('siteurl') . '/index.php?uid=' . $u->ID;
		return $url; //TODO: aplicarle filtros
	}
	else
		return false;
}

// cimy extra user fields compatibilty

function aleph_get_user_cimy_field($field, $field_value = false) {
	$ID = aleph_get_user_ID();
	if ($ID)
		return get_cimyFieldValue($ID, $field, $field_value);
	return false;
}

function aleph_print_user_cimy_field($field, $field_value = false) {
	$value = aleph_get_user_cimy_field($field, $field_value);
	if ($value)
		echo $value;
}


?>