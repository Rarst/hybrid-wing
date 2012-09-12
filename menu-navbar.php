
			<div id="navbar-menu" class="<?php echo apply_atomic( 'navbar_class', 'navbar' ) ?>">

				<div class="navbar-inner">

					<?php do_atomic( 'before_navbar_menu' ); ?>


					<?php

					if ( has_nav_menu( 'navbar' ) ) {

						wp_nav_menu( array( 'theme_location'  => 'navbar',
																'container'       => false,
																'menu_class'      => 'nav',
																'walker'          => new Walker_Navbar_Menu(), ) );
					}

					?>

					<?php do_atomic( 'after_navbar_menu' ); ?>

				</div><!-- .navbar-inner -->

			</div><!-- .navbar -->
