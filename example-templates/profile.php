<?php get_header(); ?>

<!-- 

This is a example template for Aleph. 
The function aleph_user_profile() outputs a formatted profile. You need to put it between the adequate markup of your template. 

 -->
	<div id="content" class="narrowcolumn">
	
		<?php 
		aleph_user_profile(
			array(
				'before_title' => '<h2 class="center">',
				'after_title' => '</h2>',
				'before_section' => '<h3>',
				'after_section' => '</h3>',
				'before_avatar' => '<div style="float: right;">',
				'after_avatar' => '</div>'
			)
		); 
		?>
		
	</div>
	
	
<?php get_sidebar(); ?>
	
<?php get_footer(); ?>