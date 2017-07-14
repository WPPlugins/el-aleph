=== Aleph ===
Contributors: Detective
Donate link: https://www.amazon.com/gp/registry/wishlist/1Q7WE8X1H7QHV
Tags: user, users, profile, profiles, user lists, user views, members, member, member list
Requires at least: 2.7.1
Tested up to: 2.7.1
Stable tag: 0.8.1

Aleph implements profiles, paginated and customizable user lists.

== Description ==

Aleph implements profiles, paginated and customizable user lists. It supports localization and pretty permalinks. Suggestions, bug reports and fixes (specially security & bug fixes) are welcomed. PHP 5 is required.

Version 0.8 is a complete rewrite to get a simpler, faster and more flexible plugin with improved profiles and user lists. In the past Aleph used to have more features, but those were removed, as there are other alternatives for such a complex use.

Using Aleph, users become a important part of WordPress. If you run a site with WordPress, probably some users are authors, some are contributors, and most of them are only suscribers. With Aleph each user has his own profile, even if there are no public posts by that user. 

Also, it is possible to browse in "user lists" (named "User Views" in the plugin). There is one user list included in Aleph: the "all registered users" view. You can easily define other views through the hooks used in the plugin or through view registration. Now it is possible to filter authors (users with at least one public post) and by some meta key. See FAQ for examples.

If you find this plugin useful, please consider buying me something from my [Amazon Wishlist](https://www.amazon.com/gp/registry/wishlist/1Q7WE8X1H7QHV) :)


== Changelog ==

= Version 0.8.0 =

Complete rewrite of the plugin. Most of the old functionality has been removed. However, the plugin has improved its quality :)
 

== Installation ==

If you are upgrading: please deactivate the plugin and delete all files. Remove previous widgets and Aleph templates (probably they won't work).

= Requirements =

1. WordPress 2.7.1+
1. PHP5

= Compatible Plugins =

1. Cimy User Extra Fields
1. User Photo
1. Any other that uses the `user_meta` table.

= Mandatory steps = 

1. Download the file el-aleph.zip and decompress it in the `/wp-content/plugins/` directory (make sure that the directory 'el-aleph' is created).  
1. Activate the plugin through the 'Plugins' menu in WordPress. 
1. If you don't use the default theme, copy the example template files to your theme directory: `users.php` and `profile.php`. Modify them according to your needs.
1. If you have pages with "people" and "searching" as slugs, you must change them, otherwise the plugin might interfere with them.

After installation you can see profiles in http://example.com/people/username and the default user list in http://example.com/people (or http://example.com/searching/people).
Aleph comes ready for 'es_ES' language settings. In that case, the URLs become http://example.com/gente/ and http://example.com/busqueda/gente.

= Optional steps =

1. Add the user lists widget to your sidebar.
1. If you use Pretty Permalinks and the URLs don't work, flush the rules by re-saving your permalinks structure. If you are upgrading, please do this step again.

== Frequently Asked Questions ==

= How to make my own users/profile templates? =

Copy the files users.php and profile.php to your template directory. Edit those files, adding the HTML markup needed so they match your template.

Next, you need to tweak the parameters to the `aleph_*` functions used in those templates. I think they are pretty self-explanatory. Here is an example taken from my customized 
version of users.php:

`
aleph_users_list(
	array(
		'before_list' => '<div class="format_text">',
		'after_list' => '</div>',
		'before_list_meta' => '<div class="headline_area">',
		'after_list_meta' => '</div>',
		'before_found_users' => '<p class="headline_meta">',
		'after_found_users' => '</p>',
		'before_title' => '<h1>',
		'after_title' => '</h1>',
		'show_avatars' => false,
		'show_navigation' => false,
		'list_attributes' => 'id="user-list" style="padding-left: 0; margin-left: 0; list-style-type: none;"',
		'before_avatar' => '<div class="wp-caption alignright">',
		'after_avatar' => '</div>'
	)
);
	
users_nav_links('<div class="prev_next">', '</div>');
` 

You can see that i removed the navigation from the aleph_users_list and then called it directly. This allows me to add even more extra markup in case it is needed :) 

In a profile the parameters are similar:

`
aleph_user_profile(
	array(
		'before_profile_data' => '<div class="format_text">',
		'after_profile_data' => '</div>',
		'before_meta' => '<div class="headline_area">',
		'after_meta' => '</div>',
		'before_found_users' => '<p class="headline_meta">',
		'after_found_users' => '</p>',
		'before_title' => '<h1>',
		'after_title' => '</h1>',
		'before_avatar' => '<div style="width: 100px;" class="wp-caption alignright"">',
		'after_avatar' => '</div>',
		'avatar_size' => 96
	)
);
`

Please check the files inside the lib folder to see the full list of parameters and available functions.

= How to extend the information shown in my users/profile templates? =

You can use special hooks defined by Aleph. Those hooks are: 

1. In User Lists: `aleph_user_list_before_user`, `aleph_user_list_user_fields_before_details`, `aleph_user_list_user_fields_after_details`, `aleph_user_list_after_user`.
1. In Profiles: `aleph_before_user_profile`, `aleph_user_profile_fields`, `aleph_after_user_profile`.

For example, you can output the list of "something" owned by some user:

`
add_action('aleph_after_user_profile', 'display_user_something');

function display_user_something($args) {
	$user_id = aleph_get_user_ID();
	if (!$user_id)
		return;
		
	extract($args);
	$things = get_something_of_user($user_id);
	if (!$things)
		return;
		
	echo $before_section . 'User Things' . $after_section;
	echo '<ul id="user-things">';
	foreach ($things as $thing)
		printf('<li>%s</li>', $thing);
	echo '</ul>';
}
`

Put this code in a plugin or in the functions.php file of your template. 

= How to create user lists from other plugins and/or my theme functions.php? =

After the plugin is loaded, you can add new lists/views using a special function called `aleph_register_view`:

`
add_action('init', 'my_aleph_views', 100);

function my_aleph_views() {
	aleph_register_user_view('view_slug', 'My User List', 'user_key=some_meta_key');
}
`

The first parameter is the slug of the view, i.e., you can reach the view through `http://example.com/searching/people/view_slug`.

The second parameter is the name of the view, displayed in the document title, the list header and the user lists widget.

The third parameter is the "user query", in this case, we are querying for users that have a custom user_meta of key `some_meta_key`.

= Which features are planned for next releases? =

Currently, there are no planned "new" features, just improve what has already been done. Of course I would like to make the plugin easier to use, so I guess feedback is really needed. 

However, there are some unresolved things:

* Configurable slugs.
* Support the key/value user views.
* Allow registration of user views from the Dashboard using meta keys and key/value pairs.
* More friendly template options.

= But I need more features T___T =

Well, in that case, Aleph is not (anymore) for you. I recommend BuddyPress, which will soon be available for single WP.

= Does Aleph works with WordPress MU ? =

Yes! In fact, i use it on a MU installation. Care must be taken in where you install the plugin... it needs some tweaking, but it works. The "user things" code shown earlier comes from a plugin that lists user blogs.

= How to use Cimy User Extra Fields with Aleph? =

If you have a field named "Gender":

`
add_action('aleph_user_profile_fields', 'display_user_cimy_field');

function display_user_cimy_field() {
	$gender = aleph_get_user_cimy_field("Gender");
	if ($gender) {	
		echo '<dt>Gender</dt>';
		echo '<dd>' . $gender . '</dd>';
	}
}
`

You don't need the user ID, because the function `aleph_get_user_cimy_field` works on the "current" user.

= Aleph stopped working! It did work before ... =

If this happened to you, you probably did one of the following actions:

1. Activated a plugin that automatically flushed the rewrite rules.
1. Changed the language of your blog.
1. Registered a custom view inside a plugin.

Updating the permalink structure manually in your dashboard usually solves the problem.

