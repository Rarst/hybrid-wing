<?php if ( is_active_sidebar( 'primary' ) ) : ?>

	<div id="primary" class="<?php echo apply_atomic( 'sidebar_class', 'sidebar aside', 'primary' ); ?>">

		<?php do_atomic( 'before_primary' ); ?>

		<?php dynamic_sidebar( 'primary' ); ?>

		<?php do_atomic( 'after_primary' ); ?>

	</div>

<?php endif; ?>