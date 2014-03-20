<?php get_header(); ?>

	<div id="content" class="<?php echo apply_atomic( 'content_class', 'hfeed content' ); ?>">

		<?php do_atomic( 'before_content' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" class="<?php hybrid_post_class(); ?>">

					<?php do_atomic( 'before_entry' ); ?>

					<?php echo apply_atomic_shortcode( 'entry_title', '[entry-title]' ); ?>

					<div class="entry-content">
						<?php the_content( sprintf( __( 'Continue reading %1$s', 'hybrid-wing' ), the_title( ' "', '"', false ) ) ); ?>
						<?php wp_link_pages(); ?>
					</div>
					<!-- .entry-content -->

					<?php do_atomic( 'after_entry' ); ?>

				</div><!-- .hentry -->

				<?php if ( is_singular() ) { ?>

					<?php do_atomic( 'after_singular' ); ?>

					<?php comments_template( '/comments.php', true ); ?>

				<?php } ?>

			<?php endwhile; ?>

		<?php else : ?>

			<?php get_template_part( 'loop-error' ); // Loads the loop-error.php template. ?>

		<?php endif; ?>

		<?php do_atomic( 'after_content' ); ?>

	</div><!-- #content -->

<?php get_footer(); ?>