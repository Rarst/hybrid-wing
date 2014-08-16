<?php global $post, $comment, $comment_depth; ?>


<?php $tag = $comment_depth > 1 ? 'div' : 'li'; ?>

<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" class="<?php hybrid_comment_class(); ?> media">

	<div class="media-object pull-left">
		<?php hybrid_avatar(); ?>
	</div>

	<div class="comment-content comment-text media-body">

		<?php if ( '0' == $comment->comment_approved ) echo apply_atomic_shortcode( 'comment_moderation', '<p class="alert moderation">' . __( 'Your comment is awaiting moderation.', 'hybrid-wing' ) . '</p>' ); ?>

		<?php echo apply_atomic_shortcode( 'comment_meta', '<div class="comment-meta media-heading">[comment-author] [comment-published] [comment-permalink before="| "] [comment-edit-link before="| "] [comment-reply-link before="| "]</div>' ); ?>

		<?php comment_text( $comment->comment_ID ); ?>
