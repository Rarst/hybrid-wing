<?php

namespace Rarst\Hybrid_Wing;

use Rarst\Composer\Locate_Vendor;

/**
 * Wraps Hybrid Core to adjust runtime configuration
 */
class Core extends \Hybrid {

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
	function hybrid_prefix() {
		return 'hw';
	}

	function constants() {

		if ( file_exists( dirname( __DIR__ ) . '/hybrid-core' ) ) {
			parent::constants();
		}
		else {
			define( 'HYBRID_VERSION', '1.6.2' );
			define( 'THEME_DIR', get_template_directory() );
			define( 'THEME_URI', get_template_directory_uri() );
			define( 'CHILD_THEME_DIR', get_stylesheet_directory() );
			define( 'CHILD_THEME_URI', get_stylesheet_directory_uri() );
			$reflector = new \ReflectionClass( 'Hybrid' );
			define( 'HYBRID_DIR', str_replace( '\\', '/', dirname( $reflector->getFileName() ) ) );
			define( 'HYBRID_URI', $this->content_url_from_path( HYBRID_DIR ) );
			define( 'HYBRID_ADMIN', trailingslashit( HYBRID_DIR ) . 'admin' );
			define( 'HYBRID_CLASSES', trailingslashit( HYBRID_DIR ) . 'classes' );
			define( 'HYBRID_EXTENSIONS', trailingslashit( HYBRID_DIR ) . 'extensions' );
			define( 'HYBRID_FUNCTIONS', trailingslashit( HYBRID_DIR ) . 'functions' );
			define( 'HYBRID_LANGUAGES', trailingslashit( HYBRID_DIR ) . 'languages' );
			define( 'HYBRID_IMAGES', trailingslashit( HYBRID_URI ) . 'images' );
			define( 'HYBRID_CSS', trailingslashit( HYBRID_URI ) . 'css' );
			define( 'HYBRID_JS', trailingslashit( HYBRID_URI ) . 'js' );
		}

		define( 'BOOTSTRAP_DIR', str_replace( '\\', '/', Locate_Vendor::get_package_path( 'twbs/bootstrap' ) ) );
		define( 'BOOTSTRAP_URI', $this->content_url_from_path( BOOTSTRAP_DIR ) );

		if ( ! defined( 'SCRIPT_DEBUG' ) )
			define( 'SCRIPT_DEBUG', false );
	}

	/**
	 * Calculate URL for arbitrary path in content directory.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	function content_url_from_path( $path ) {

		static $content_dir;

		if ( empty( $content_dir ) )
			$content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );

		if ( false !== stripos( $path, $content_dir ) )
			return content_url( str_ireplace( $content_dir, '', $path ) );

		trigger_error( 'Could not calculate URL constant for ' . esc_html( $path ), E_USER_WARNING );

		return '';
	}

	function default_filters() {

		parent::default_filters();
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'hw_before_content', 'breadcrumb_trail' );
		add_action( 'hw_after_container', array( $this, 'sidebar_primary' ) );
		add_action( 'hw_singular_entry_title', array( $this, 'singular_entry_title' ) );
		add_filter( 'img_caption_shortcode', array( $this, 'img_caption_shortcode' ), 10, 3 );
		add_action( 'hw_home_after_content', 'loop_pagination' );
		add_action( 'hw_archive_after_content', 'loop_pagination' );
		add_action( 'hw_search_after_content', 'loop_pagination' );
	}

	function theme_support() {

		parent::theme_support();
		add_theme_support( 'hybrid-core-shortcodes' );
		add_theme_support( 'hybrid-core-sidebars', array( 'primary' ) );
		add_theme_support( 'breadcrumb-trail' );
		add_theme_support( 'loop-pagination' );
	}

	function functions() {

		parent::functions();
		remove_filter( 'comment_form_defaults', 'hybrid_comment_form_args' );
	}

	function widgets_init() {

		register_widget( 'Nav_List_Menu_Widget' );
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
			$prefix . 'body_container_class' => 'container',
			$prefix . 'container_class'      => 'row',
			$prefix . 'content_class'        => 'col-md-9',
//			$prefix . 'entry_class' => 'span10',
			$prefix . 'sidebar_class'        => 'col-md-3',
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

		if ( ! empty( $this->grid[$current_filter] ) )
			$classes .= ' ' . $this->grid[$current_filter];

		return $classes;
	}

	/**
	 * Register and enqueue scripts and styles.
	 */
	function wp_enqueue_scripts() {

		$bootstrap_version = $this->get_package_info( BOOTSTRAP_DIR, 'version' );

		wp_register_style( 'bootstrap', BOOTSTRAP_URI . '/dist/css/bootstrap.min.css', array(), $bootstrap_version );
		wp_register_style( 'hybrid-wing', CHILD_THEME_URI . '/style.css', array( 'bootstrap' ) );
		wp_enqueue_style( 'hybrid-wing' );

		$scripts = glob( BOOTSTRAP_DIR . '/js/*.js' );

		foreach ( $scripts as $script ) {
			wp_register_script( 'bootstrap-' . basename( $script, '.js' ), BOOTSTRAP_URI . '/js/' . basename( $script ), array( 'jquery' ), $bootstrap_version, true );
		}
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

		$data = json_decode( file_get_contents( trailingslashit( $path ) . 'package.json' ) );

		if ( $field )
			return $data->$field;

		return $data;
	}

	function sidebar_primary() {

		get_sidebar( 'primary' );
	}

	/**
	 * Wrap singular titles in page header markup.
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	function singular_entry_title( $title ) {

		return '<div class="page-header">' . $title . '</div><!-- .page-header -->';
	}

	/**
	 * Override caption output to adjust inline style.
	 *
	 * @param string $empty
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	function img_caption_shortcode( $empty, $attr, $content ) {

		$attr = shortcode_atts(
			array(
				'id'      => '',
				'align'   => 'alignnone',
				'width'   => '',
				'caption' => '',
			),
			$attr
		);

		if ( 1 > (int) $attr['width'] || empty( $attr['caption'] ) )
			return $content;

		if ( ! empty( $attr['id'] ) )
			$attr['id'] = 'id="' . esc_attr( $attr['id'] ) . '" ';

		return '<div ' . $attr['id'] . 'class="wp-caption thumbnail ' . esc_attr( $attr['align'] ) . '" style="max-width: ' . (int) $attr['width'] . 'px">'
		. do_shortcode( $content ) . '<p class="wp-caption-text caption">' . $attr['caption'] . '</p></div>';
	}
}