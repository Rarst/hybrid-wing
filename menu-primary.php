<?php if ( has_nav_menu( 'primary' ) ) : ?>

	<div id="primary-menu" class="menu-container">

		<?php do_atomic( 'before_primary_menu' ); ?>

		<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container_class' => 'menu', 'menu_class' => '', 'fallback_cb' => '' ) ); ?>

		<?php do_atomic( 'after_primary_menu' ); ?>

	</div>

<?php endif; ?>