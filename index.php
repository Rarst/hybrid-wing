		<?php while ( have_posts() ) : the_post(); ?>

			<div id="post-<?php the_ID(); ?>" class="<?php hybrid_entry_class(); ?>">

				<?php do_atomic( 'before_entry' ); ?>

				<div class="entry-content">
					<?php the_content( sprintf( __( 'Continue reading %1$s', hybrid_get_parent_textdomain() ), the_title( ' "', '"', false ) ) ); ?>
					<?php hw_link_pages( array( 'before' => __( 'Pages:', hybrid_get_parent_textdomain() ) ) ); ?>
				</div><!-- .entry-content -->

				<?php do_atomic( 'after_entry' ); ?>

			</div><!-- .hentry -->

			<?php if ( is_singular() ) { ?>

				<?php do_atomic( 'after_singular' ); ?>

				<?php comments_template( '/comments.php', true ); ?>

			<?php } ?>

			<?php endwhile; ?>