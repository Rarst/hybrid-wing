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

//		if ( $this->args->sidebar ) {
//			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
//		}
	}

	function init() {

		register_nav_menu( 'navbar', 'Navbar' );

		add_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ), 9 );
	}

	function widgets_init() {

		global $wp_registered_sidebars;

		if ( empty( $wp_registered_sidebars[$this->args->name] ) ) {
			register_sidebar(
				array(
					'name'          => 'Navbar',
					'id'            => $this->args->name,
					'before_widget' => '<li id="%1$s" class="dropdown widget %2$s">',
					'before_title'  => '<a href="#" class="dropdown-toggle" data-toggle="dropdown">',
					'after_title'   => '<b class="caret"></b></a><div class="dropdown-menu">',
					'after_widget'  => '</div></li>',
				)
			);
		}
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
