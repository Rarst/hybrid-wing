		<?php while ( have_posts() ) : the_post(); ?>

			<div id="post-<?php the_ID(); ?>" class="<?php hybrid_entry_class(); ?>">

				<?php do_atomic( 'before_entry' ); ?>

				<div class="entry-content">
					<?php the_content( sprintf( __( 'Continue reading %1$s', 'hybrid-wing' ), the_title( ' "', '"', false ) ) ); ?>
					<?php hw_link_pages(); ?>
				</div><!-- .entry-content -->

				<?php do_atomic( 'after_entry' ); ?>

			</div><!-- .hentry -->

			<?php if ( is_singular() ) { ?>

				<?php do_atomic( 'after_singular' ); ?>

				<?php comments_template( '/comments.php', true ); ?>

			<?php } ?>

			<?php endwhile; ?>