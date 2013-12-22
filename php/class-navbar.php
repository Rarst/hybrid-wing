<?php

namespace Rarst\Hybrid_Wing;

/**
 * Reusable navbar component.
 */
class Navbar {

	/**
	 * @param array $args
	 */
	function __construct( $args = array() ) {

		$prefix = hybrid_get_prefix();

		$this->args = (object) wp_parse_args(
			$args,
			array(
				'name'     => 'navbar',
				'location' => $prefix . '_header',
				'classes'  => array( 'navbar-default' ),
				'brand'    => true,
				'menu'     => true,
				'sidebar'  => true,
				'search'   => true,
			)
		);

		if ( $this->args->menu )
			add_action( 'init', array( $this, 'init' ) );

		if ( $this->args->sidebar ) {
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		}

		add_action( $this->args->location, array( $this, 'output' ) );
	}

	function init() {

		if ( ! in_array( $this->args->name, get_registered_nav_menus() ) )
			register_nav_menu( 'navbar', 'Navbar' );
	}

	function widgets_init() {

		global $wp_registered_sidebars;

		if ( empty( $wp_registered_sidebars[$this->args->name] ) )
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
	
	function wp_enqueue_scripts() {

		if ( is_active_sidebar( $this->args->name ) )
			wp_enqueue_script( 'bootstrap-dropdown' );
	}

	function output() {
		?>
	<nav class="navbar <?php $this->classes(); ?>" role="navigation">

		<div class="navbar-header">
			<?php	if ( $this->args->brand ) $this->brand(); ?>
		</div>

		<?php
			if ( $this->args->menu ) $this->menu();

			if ( $this->args->sidebar ) $this->sidebar();

			if ( $this->args->search ) $this->search();
			?>
	</nav><!-- .navbar -->
	<?php
	}

	function classes() {

		if ( empty( $this->args->classes ) )
			return;

		$classes = $this->args->classes;

		if ( ! is_array( $classes ) )
			$classes = explode( ' ', $classes );

		$classes = array_map( 'sanitize_html_class', $classes );
		$classes = implode( ' ', $classes );

		echo apply_atomic( 'navbar_classes', $classes, $this->args );
	}

	function brand() {

		$name = esc_html( get_bloginfo( 'name' ) );

		if ( is_home() && ! is_paged() )
			$brand = '<span class="navbar-brand">' . $name . '</span>';
		else
			$brand = '<a href="' . get_home_url() . '" class="navbar-brand">' . $name . '</a>';

		echo apply_atomic( 'navbar_brand', $brand, $this->args );
	}

	function menu() {

		if ( has_nav_menu( $this->args->name ) )
			wp_nav_menu(
				array(
					'theme_location' => $this->args->name,
					'container'      => false,
					'menu_class'     => 'nav navbar-nav',
					'walker'         => new Walker_Navbar_Menu(),
				)
			);
	}

	function sidebar() {

		if ( is_active_sidebar( 'navbar' ) ) {
			echo '<ul class="nav navbar-nav">';

			dynamic_sidebar( 'navbar' );

			echo '</ul>';
		}
	}

	function search() {
		?>
	<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar-form navbar-right">
		<div class="form-group">
			<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="<?php esc_attr_e( 'Search' ); ?>" class="form-control" />
		</div>
	</form>
	<?php
	}
}
