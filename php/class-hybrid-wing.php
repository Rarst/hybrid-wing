<?php

/**
 * Main theme class, extends Hybrid Core.
 */
class Hybrid_Wing extends Hybrid {

	public $main_template;
	public $base;
	public $grid;

	/**
	 * Initial hooks on creation.
	 */
	function __construct() {

		add_filter( 'hybrid_prefix', array( $this, 'hybrid_prefix' ) );
		parent::__construct();
	}

	/**
	 * @return string 'hw'
	 */
	function hybrid_prefix() { return 'hw'; }

	function constants() {

		parent::constants();

		define( 'LESSJS_DIR', trailingslashit( THEME_DIR ) . 'less.js' );
		define( 'LESSJS_URI', trailingslashit( THEME_URI ) . 'less.js' );
		define( 'BOOTSTRAP_DIR', trailingslashit( THEME_DIR ) . 'bootstrap' );
		define( 'BOOTSTRAP_URI', trailingslashit( THEME_URI ) . 'bootstrap' );

		if ( ! defined( 'SCRIPT_DEBUG' ) )
			define( 'SCRIPT_DEBUG', false );
	}

	function default_filters() {

		parent::default_filters();
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'style_loader_tag', array( $this, 'style_loader_tag' ), 10, 2 );
		add_action( 'hw_header', array( $this, 'navbar_menu' ) );
		add_action( 'hw_before_navbar_menu', array( $this, 'navbar_brand' ) );
		add_action( 'hw_after_navbar_menu', array( $this, 'navbar_search' ) );
		add_action( 'hw_after_container', array( $this, 'sidebar_primary' ) );
		add_action( 'hw_before_entry', array( $this, 'entry_title' ) );
		add_action( 'hw_home_after_content', 'loop_pagination' );
		add_action( 'hw_archive_after_content', 'loop_pagination' );
		add_action( 'hw_search_after_content', 'loop_pagination' );
		add_action( 'loop_pagination_args', array( $this, 'loop_pagination_args' ) );
		add_action( 'loop_pagination', array( $this, 'loop_pagination' ) );
	}

	function theme_support() {

		parent::theme_support();
		add_theme_support( 'hybrid-core-shortcodes' );
		add_theme_support( 'hybrid-core-sidebars', array( 'primary' ) );
		add_theme_support( 'loop-pagination' );
		register_nav_menu( 'navbar', 'Navbar' );
	}

	/**
	 * Ready for conditionals, before template choice.
	 */
	function template_redirect() {

		global $hybrid;

		if ( is_page() ) {
			hybrid_get_context();
			$hybrid->context[] = 'singular-page-' . get_query_var( 'pagename' );
		}

		$prefix = hybrid_get_prefix() . '_';

		$default_grid = array(
			$prefix . 'body_container_class'      => 'container',
			$prefix . 'container_class'           => 'row',
			$prefix . 'content_class'             => 'span9',
//			$prefix . 'entry_class' => 'span10',
			$prefix . 'sidebar_class'             => 'span3',
		);

		$this->grid = apply_atomic( 'grid', $default_grid );

		foreach ( $this->grid as $hook => $classes ) {
			add_filter( $hook, array( $this, 'append_grid_class' ) );
		}
	}

	/**
	 * @param string $classes
	 *
	 * @return string
	 */
	function append_grid_class( $classes ) {

		$current_filter = current_filter();

		if( ! empty( $this->grid[$current_filter] ) )
			$classes .= ' ' .  $this->grid[$current_filter];

		return $classes;
	}

	/**
	 * Handle wrapper.
	 * @link http://scribu.net/wordpress/theme-wrappers.html
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	function template_include( $template ) {

		$this->main_template = $template;
		$this->base          = substr( basename( $this->main_template ), 0, - 4 );

		if ( 'index' == $this->base )
			$this->base = false;

		$templates = array( 'wrapper.php' );

		if ( $this->base )
			array_unshift( $templates, sprintf( 'wrapper-%s.php', $this->base ) );

		return locate_template( $templates );
	}

	/**
	 * Adjust HTML output for queued LESS stylesheets.
	 *
	 * @param string $html
	 * @param string $handle
	 *
	 * @return string
	 */
	function style_loader_tag( $html, $handle ) {

		global $wp_styles;

		if ( '.less' == substr( $wp_styles->registered[$handle]->src, - 5 ) )
			$html = str_replace( "'stylesheet'", "'stylesheet/less'", $html );

		return $html;
	}

	/**
	 * Register and enqueue scripts and styles.
	 */
	function wp_enqueue_scripts() {

		wp_register_style( 'style', trailingslashit( CHILD_THEME_URI ) . 'style.less' );
		wp_enqueue_style( 'style' );

		$less_version = $this->get_package_info( LESSJS_DIR, 'version' );

		if( ! SCRIPT_DEBUG )
			$less_version .= '.min';

		wp_register_script( 'less', LESSJS_URI . "/dist/less-{$less_version}.js", array(), $less_version, true );

		if( SCRIPT_DEBUG )
			wp_localize_script( 'less', 'less', array( 'env' => 'development' ) );

		$bootstrap_version = $this->get_package_info( BOOTSTRAP_DIR, 'version' );
		$scripts = glob( BOOTSTRAP_DIR . '/js/*.js' );

		foreach ( $scripts as $script ) {
			wp_register_script( basename( $script, '.js' ), BOOTSTRAP_URI . '/js/' . basename( $script ), array( 'jquery' ), $bootstrap_version, true );
		}

		wp_register_script( 'prettify', BOOTSTRAP_URI . '/docs/assets/js/google-code-prettify/prettify.js', array(), null, true );
		wp_register_style( 'prettify', BOOTSTRAP_URI . '/docs/assets/js/google-code-prettify/prettify.css', array(), null );

		if ( wp_style_is( 'style', 'queue' ) )
			wp_enqueue_script( 'less' );
	}

	/**
	 * Read and decode data in package.json from directory.
	 *
	 * @param string         $path
	 * @param boolean|string $field
	 *
	 * @return object
	 */
	function get_package_info( $path, $field = false ) {

		$data = json_decode( file_get_contents( trailingslashit( $path ) .'package.json' ) );

		if( $field )
			return $data->$field;

		return $data;
	}

	function navbar_menu() {

		get_template_part( 'menu', 'navbar' );
	}

	function navbar_brand() {

		$name = esc_html( get_bloginfo( 'name' ) );

		if ( is_home() )
			$brand = '<span class="brand">' . $name . '</span>';
		else
			$brand = '<a href="' . get_home_url() . '" class="brand">' . $name . '</a>';

		echo apply_atomic( 'navbar_brand', $brand );
	}

	function navbar_search() {
		?>
	<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar-search pull-right">
		<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="<?php esc_attr_e( 'Search' ); ?>" class="search-query" />
	</form>
	<?php
	}

	function sidebar_primary() {

		get_sidebar( 'primary' );
	}

	/**
	 * Entry title.
	 */
	function entry_title() {

		$title = hybrid_entry_title_shortcode( array() );

		if( is_singular() )
			$title = '<div class="page-header">' . $title . '</div><!-- .page-header -->';

		echo $title;
	}

	/**
	 * Adjust arguments of loop pagination function.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function loop_pagination_args( $args ) {

		global $wp_rewrite;

		$args['before'] = '<div class="pagination pagination-centered">';
		$args['after']  = '</div>';
		$args['type']   = 'list';


		if (  $wp_rewrite->using_permalinks() ) {
			$link  = get_pagenum_link();
			$parse = parse_url( $link );

			if( ! empty( $parse['query'] )  )
				$args['base'] = str_replace( '?' . $parse['query'], 'page/%#%/?' . $parse['query'], $link );
		}

		return $args;
	}

	/**
	 * Rewrite pagination output.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	function loop_pagination( $html ) {

		$html = str_replace( "<a class='page-numbers'", "<a class='page-numbers hidden-phone'", $html );
		$html = str_replace( '<span class="page-numbers dots"', '<span class="page-numbers dots hidden-phone"', $html );

		return $html;
	}
}