<?php

namespace Rarst\Hybrid_Wing;

/**
 * Backend support for navbars in templates.
 */
class Navbar {

	/**
	 * Sets up hooks for navbar-related functionality.
	 */
	function __construct() {

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {

		register_nav_menu( 'navbar', 'Navbar' );

		add_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ), 9 );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function wp_nav_menu_args( $args ) {

		if ( ! empty( $args['navbar'] ) ) {
			$args['container']  = false;
			$args['menu_class'] = 'nav navbar-nav';
			$args['walker']     = new Walker_Navbar_Menu();

			wp_enqueue_script( 'bootstrap-dropdown' );
		}

		return $args;
	}
}
