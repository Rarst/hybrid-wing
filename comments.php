<?php

/* Kill the page if trying to access this template directly. */
if ( 'comments.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) )
	die( __( 'Please do not load this page directly. Thanks!', hybrid_get_parent_textdomain() ) );

/* If a post password is required or no comments are given and comments/pings are closed, return. */
if ( post_password_required() || ( ! have_comments() && ! comments_open() && ! pings_open() ) )
	return;
?>

<div id="comments-template">

	<div class="comments-wrap">

		<div id="comments">

			<?php if ( have_comments() ) : ?>

			<h3 id="comments-number" class="comments-header"><?php comments_number( __( 'No Responses', hybrid_get_parent_textdomain() ), __( 'One Response', hybrid_get_parent_textdomain() ), __( '% Responses', hybrid_get_parent_textdomain() ) ); ?></h3>

			<?php do_atomic( 'before_comment_list' ); ?>

			<ul class="comment-list media-list">
				<?php wp_list_comments( hybrid_list_comments_args() ); ?>
			</ul>

			<?php do_atomic( 'after_comment_list' ); ?>

			<?php if ( get_option( 'page_comments' ) ) : ?>
				<div class="comment-navigation comment-pagination">
					<?php hw_paginate_comments_links(); ?>
				</div><!-- .comment-navigation -->
				<?php endif; ?>

			<?php endif; ?>

			<?php if ( pings_open() && ! comments_open() ) : ?>

			<p class="comments-closed pings-open">
				<?php printf( __( 'Comments are closed, but <a href="%1$s" title="Trackback URL for this post">trackbacks</a> and pingbacks are open.', hybrid_get_parent_textdomain() ), get_trackback_url() ); ?>
			</p><!-- .comments-closed .pings-open -->

			<?php elseif ( ! comments_open() ) : ?>

			<p class="comments-closed">
				<?php _e( 'Comments are closed.', hybrid_get_parent_textdomain() ); ?>
			</p><!-- .comments-closed -->

			<?php endif; ?>

		</div><!-- #comments -->

		<?php comment_form(); ?>

	</div><!-- .comments-wrap -->

</div><!-- #comments-template -->