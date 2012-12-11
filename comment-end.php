<?php global $comment_depth; ?>


	</div><!-- .comment-content .comment-text .media-body -->

	<?php do_atomic( 'after_comment' ); ?>

</<?php echo $comment_depth > 1 ? 'div' : 'li'; ?>><!-- .comment .media -->