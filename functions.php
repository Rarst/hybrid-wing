<?php

require_once trailingslashit( TEMPLATEPATH ) . 'hybrid-core/hybrid.php';

class Hybrid_Wing extends Hybrid {

	public $main_template;
	public $base;
	public $grid;

	function __construct() {

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_filter_return( 'hybrid_prefix', 10, 'hw' );
		parent::__construct();
	}

	function constants() {

		parent::constants();

		define( 'LESSJS_DIR', trailingslashit( THEME_DIR ) . 'less.js' );
		define( 'LESSJS_URI', trailingslashit( THEME_URI ) . 'less.js' );
		define( 'BOOTSTRAP_DIR', trailingslashit( THEME_DIR ) . 'bootstrap' );
		define( 'BOOTSTRAP_URI', trailingslashit( THEME_URI ) . 'bootstrap' );

		if ( ! defined( 'SCRIPT_DEBUG' ) )
			define( 'SCRIPT_DEBUG', false );
	}

	function after_setup_theme() {

		add_theme_support( 'hybrid-core-menus', array( 'primary' ) );
		add_theme_support( 'hybrid-core-shortcodes' );
		add_theme_support( 'hybrid-core-sidebars', array( 'primary', 'secondary' ) );
		register_nav_menu( 'navbar', 'Navbar' );

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'style_loader_tag', array( $this, 'style_loader_tag' ), 10, 2 );
		add_action_with_args( 'hw_before_html', 'get_template_part', 10, 'menu', 'navbar' );
		add_action_with_args( 'hw_after_header', 'get_template_part', 10, 'menu', 'primary' );
		add_action_with_args( 'hw_after_container', 'get_sidebar', 10, 'primary' );
		add_action( 'hw_before_entry', array( $this, 'hw_entry_title' ) );

//		add_action_with_args( 'hw_after_container', 'get_sidebar', 10, 'secondary' );

		$this->grid = array(
			'body_container_class'      => 'container',
			'container_class'           => 'row',
			'content_class'             => 'span6',
//			'entry_class' => 'span10',
			'sidebar_class'             => 'span3',
		);
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

		$this->grid = apply_atomic( 'grid', $this->grid );

		foreach ( $this->grid as $hook => $classes ) {
			add_filter_append( "hw_{$hook}", 10, ' ' . $classes );
		}
	}

	/**
	 * Handle wrapper.
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

		$version = $this->get_package_info( LESSJS_DIR, 'version' );

		if( ! SCRIPT_DEBUG )
			$version .= '.min';

		wp_register_script( 'less', LESSJS_URI . "/dist/less-{$version}.js", array(), $version, true );

		if( SCRIPT_DEBUG )
			wp_localize_script( 'less', 'less', array( 'env' => 'development' ) );

		$version = $this->get_package_info( BOOTSTRAP_DIR, 'version' );
		$scripts = glob( BOOTSTRAP_DIR . '/js/*.js' );

		foreach ( $scripts as $script ) {
			wp_register_script( basename( $script, '.js' ), BOOTSTRAP_URI . '/js/' . basename( $script ), array( 'jquery' ), $version, true );
		}

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

	/**
	 * Entry title.
	 */
	function hw_entry_title() {

		$title = hybrid_entry_title_shortcode( array() );

		if( is_singular() )
			$title = '<div class="page-header">' . $title . '</div><!-- .page-header -->';

		echo $title;
	}
}

$hybrid_wing = new Hybrid_Wing();