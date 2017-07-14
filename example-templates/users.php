<?php get_header(); ?>

<!-- 

This is a example template for Aleph. 
The function aleph_users_list() outputs a formatted list of users, including pagination. You need to put it between the adequate markup of your template. 

 -->

	<div id="content" class="narrowcolumn">

	<?php 
	aleph_users_list(
		array(
			'before_title' => '<h2 class="center">',
			'after_title' => '</h2>',
			'before_found_users' => '<p class="center">',
			'after_found_users' => '</p>',
			'before_navigation' => '<div class="navigation">',
			'after_navigation' => '</div>'
		)
	);
	?>

	</div>
	
<?php get_sidebar(); ?>
<?php get_footer(); ?>