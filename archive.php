<?php while ( have_posts() ) : the_post(); ?>

	<div id="post-<?php the_ID(); ?>" class="<?php hybrid_entry_class(); ?>">

		<?php do_atomic( 'before_entry' ); ?>

		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div>

		<?php do_atomic( 'after_entry' ); ?>

	</div>

<?php endwhile; ?>