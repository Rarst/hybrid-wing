<?php

namespace Rarst\Hybrid_Wing;

use Rarst\Composer\Locate_Vendor;

/**
 * Wraps Hybrid Core to adjust runtime configuration
 */
class Core extends \Hybrid {

	/**
	 * Initial hooks on creation.
	 */
	function __construct() {

		add_filter( 'hybrid_prefix', array( $this, 'hybrid_prefix' ) );
		parent::__construct();
		add_action( 'after_setup_theme', array( $this, 'default_filters' ), 3 );
	}

	/**
	 * @return string 'hw'
	 */
	function hybrid_prefix() {
		return 'hw';
	}

	/**
	 * @inheritDoc
	 */
	function constants() {

		$hybrid_core_path = Locate_Vendor::get_package_path( 'justintadlock/hybrid-core' );
		define( 'HYBRID_DIR', trailingslashit( str_replace( '\\', '/', $hybrid_core_path ) ) );
		define( 'HYBRID_URI', $this->content_url_from_path( HYBRID_DIR ) );
		parent::constants();

		define( 'BOOTSTRAP_DIR', str_replace( '\\', '/', Locate_Vendor::get_package_path( 'twbs/bootstrap' ) ) );
		define( 'BOOTSTRAP_URI', $this->content_url_from_path( BOOTSTRAP_DIR ) );

		if ( ! defined( 'SCRIPT_DEBUG' ) ) {
			define( 'SCRIPT_DEBUG', false );
		}

		if ( ! defined( 'CHILD_THEME_URI' ) ) {
			define( 'CHILD_THEME_URI', get_stylesheet_directory_uri() );
		}
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

		$path_match = stripos( $path, $content_dir );

		if ( false !== $path_match ) {
			return content_url( substr( $path, $path_match + strlen( $content_dir ) ) );
		}

		trigger_error( 'Could not calculate URL constant for ' . esc_html( $path ), E_USER_WARNING );

		return '';
	}

	function default_filters() {

		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_filter( 'img_caption_shortcode', array( $this, 'img_caption_shortcode' ), 10, 3 );
		add_filter( 'hybrid_attr_content', array( $this, 'hybrid_attr_content' ) );
		add_filter( 'hybrid_attr_comment', array( $this, 'hybrid_attr_comment' ) );
	}

	function theme_support() {

		parent::theme_support();
		add_theme_support( 'hybrid-core-shortcodes' );
		add_theme_support( 'hybrid-core-sidebars', array( 'primary' ) );
		add_theme_support( 'breadcrumb-trail' );
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

	/**
	 * Override caption output to adjust inline style.
	 *
	 * @param string $empty
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	function img_caption_shortcode( $empty, $attr, $content ) { // TODO html5 caption

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


		$style         = '';
		$caption_width = $attr['width'] + 10; // 4px padding + 1px border
		$caption_width = apply_filters( 'img_caption_shortcode_width', $caption_width, $attr, $content );
		if ( $caption_width ) {
			$style = 'style="max-width: ' . (int) $caption_width . 'px" ';
		}

		return '<div ' . $attr['id'] . 'class="wp-caption thumbnail ' . esc_attr( $attr['align'] ) . '" ' . $style . '>'
			   . do_shortcode( $content ) . '<p class="wp-caption-text caption">' . $attr['caption'] . '</p></div>';
	}

	/**
	 * Add grid class to content block.
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	public function hybrid_attr_content( $attr ) {

		$attr['class'] .= ' col-md-9';

		return $attr;
	}

	/**
	 * Add media class to comment list items.
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	function hybrid_attr_comment( $attr ) {

		$attr['class'] .= ' media';

		return $attr;
	}
}