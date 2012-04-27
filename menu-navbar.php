<?php if ( has_nav_menu( 'navbar' ) ) : ?>

<div id="navbar-menu" class="navbar">

	<div class="navbar-inner">

			<?php do_atomic( 'before_navbar_menu' ); ?>

			<?php

			require THEME_DIR . '/class-walker-navbar-menu.php';

		wp_nav_menu( array( 'theme_location'  => 'navbar',
												'container_class' => 'container',
												'menu_class'      => 'nav',
												'fallback_cb'     => '',
//													'depth'     => 1,
												'walker'          => new Walker_Navbar_Menu(), ) );
			?>

			<?php do_atomic( 'after_navbar_menu' ); ?>

	</div>

</div>

<?php endif; ?>