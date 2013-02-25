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

		require_once get_template_directory() . '/php/template-tags.php';

		add_filter( 'hybrid_prefix', array( $this, 'hybrid_prefix' ) );
		spl_autoload_register( array( $this, 'spl_autoload_register' ) );
		parent::__construct();
	}

	/**
	 * @param string $class_name
	 */
	function spl_autoload_register( $class_name ) {

		$class_path = THEME_DIR . '/php/class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		if ( file_exists( $class_path ) )
			include $class_path;
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

		// TODO refactor into theme supports feature
//		foreach ( array( 9, 4, 3, 2 ) as $columns ) {
//
//			list( $width, $height ) = $this->get_bootstrap_image_size( $columns, 'golden' );
//			add_image_size( "bootstrap-{$columns}-columns", $width, $height, true );
//		}
	}

	function default_filters() {

		parent::default_filters();
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'style_loader_tag', array( $this, 'style_loader_tag' ), 10, 2 );
		add_action( 'hw_before_content', array( $this, 'breadcrumb_trail' ) );
		add_action( 'hw_after_container', array( $this, 'sidebar_primary' ) );
		add_action( 'hw_singular_entry_title', array( $this, 'singular_entry_title' ) );
		add_filter( 'img_caption_shortcode', array( $this, 'img_caption_shortcode' ), 10, 3 );
		add_action( 'hw_home_after_content', 'loop_pagination' );
		add_action( 'hw_archive_after_content', 'loop_pagination' );
		add_action( 'hw_search_after_content', 'loop_pagination' );
		add_action( 'loop_pagination_args', array( $this, 'loop_pagination_args' ) );
		add_action( 'loop_pagination', array( $this, 'loop_pagination' ) );
		add_action( 'hw_paginate_comments_links', array( $this, 'loop_pagination' ) );
		add_filter( 'post_gallery', array( $this, 'post_gallery' ), 10, 2 );
		add_filter( 'hw_list_comments_args', array( $this, 'list_comments_args' ) );
		add_action( 'comment_form_defaults', array( $this, 'comment_form_defaults' ) );
		add_action( 'comment_form_top', array( $this, 'comment_form_top' ) );
		add_action( 'comment_form', array( $this, 'comment_form' ) );
	}

	function theme_support() {

		parent::theme_support();
		add_theme_support( 'hybrid-core-shortcodes' );
		add_theme_support( 'hybrid-core-sidebars', array( 'primary' ) );
		add_theme_support( 'breadcrumb-trail' );
		add_theme_support( 'loop-pagination' );
		add_theme_support( 'navbar' );
	}

	function functions() {

		parent::functions();
		remove_filter( 'comment_form_defaults', 'hybrid_comment_form_args' );
	}

	function extensions() {

		parent::extensions();

		if ( current_theme_supports( 'navbar' ) )
			new Hybrid_Wing_Navbar();
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

		wp_register_style( 'style-less', trailingslashit( CHILD_THEME_URI ) . 'style.less' );
		wp_register_style( 'style', trailingslashit( CHILD_THEME_URI ) . 'style.css' );

		if ( SCRIPT_DEBUG )
			wp_enqueue_style( 'style-less' );
		else
			wp_enqueue_style( 'style' );

		$less_version = $this->get_package_info( LESSJS_DIR, 'version' );

		if( ! SCRIPT_DEBUG )
			$less_version .= '.min';

		wp_register_script( 'less', LESSJS_URI . "/dist/less-{$less_version}.js", array(), $less_version, true );

		if( SCRIPT_DEBUG )
			wp_localize_script( 'less', 'less', array( 'env' => 'development', 'dumpLineNumbers' => 'all' ) );

		$bootstrap_version = $this->get_package_info( BOOTSTRAP_DIR, 'version' );
		$scripts = glob( BOOTSTRAP_DIR . '/js/*.js' );

		foreach ( $scripts as $script ) {
			wp_register_script( basename( $script, '.js' ), BOOTSTRAP_URI . '/js/' . basename( $script ), array( 'jquery' ), $bootstrap_version, true );
		}

		wp_register_script( 'prettify', BOOTSTRAP_URI . '/docs/assets/js/google-code-prettify/prettify.js', array(), null, true );
		wp_register_style( 'prettify', BOOTSTRAP_URI . '/docs/assets/js/google-code-prettify/prettify.css', array(), null );

		if ( wp_style_is( 'style-less', 'queue' ) )
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

	function breadcrumb_trail() {

		if ( ! current_theme_supports( 'breadcrumb-trail' ) )
			return;

		$args = array(
			'front_page'             => false,
			'show_home'              => __( 'Home', 'hybrid-wing' ),
			'singular_post_taxonomy' => false,
			'network'                => false,
		);

		$args  = apply_filters( 'hw_breadcrumb_trail_args', $args );
		$items = breadcrumb_trail_get_items( $args );

		if ( empty( $items ) )
			return;

		$output = '<ul class="breadcrumb">';
		$last   = array_pop( $items );

		foreach ( $items as $item ) {

			$output .= "<li>{$item} <span class='divider'>/</span></li>";
		}

		$output .= "<li class='active'>{$last}</li>";
		$output .= '</ul>';

		echo $output;
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

		$attr = shortcode_atts( array(
			'id'      => '',
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => '',
		), $attr );

		if ( 1 > (int) $attr['width'] || empty( $attr['caption'] ) )
			return $content;

		if ( ! empty( $attr['id'] ) )
			$attr['id'] = 'id="' . esc_attr( $attr['id'] ) . '" ';

		return '<div ' . $attr['id'] . 'class="wp-caption thumbnail ' . esc_attr( $attr['align'] ) . '" style="max-width: ' . (int) $attr['width'] . 'px">'
				. do_shortcode( $content ) . '<p class="wp-caption-text caption">' . $attr['caption'] . '</p></div>';
	}

	/**
	 * Adjust arguments of loop pagination function.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function loop_pagination_args( $args ) {

		/**
		 * @var WP_Rewrite $wp_rewrite
		 */
		global $wp_rewrite;

		$args['before'] = '<div class="pagination pagination-centered pagination-large">';
		$args['after']  = '</div>';
		$args['type']   = 'list';


		if (  $wp_rewrite->using_permalinks() ) {
			$link  = get_pagenum_link();
			$parse = parse_url( $link );

			if( ! empty( $parse['query'] )  )
				$args['base'] = str_replace( '?' . $parse['query'], $wp_rewrite->pagination_base . '/%#%/?' . $parse['query'], $link );
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
		$html = str_replace( "<li><span class='page-numbers current'>", "<li class='active'><span class='page-numbers current'>", $html );

		return $html;
	}

	/**
	 * Override gallery markup with Bootstrap thumbnail list.
	 *
	 * @see gallery_shortcode()
	 *
	 * @param string $empty
	 * @param array  $attr
	 *
	 * @return string
	 */
	function post_gallery( $empty, $attr ) {

		if ( is_feed() )
			return $empty;

		global $post;

		static $instance = 0;
		$instance++;

		if ( isset( $attr['orderby'] ) ) {

			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );

			if ( ! $attr['orderby'] )
				unset( $attr['orderby'] );
		}

		$r = shortcode_atts( array(
			'order'           => 'ASC',
			'orderby'         => 'menu_order ID',
			'id'              => $post->ID,
			'captiontag'      => 'div',
			'content_columns' => 9,
			'columns'         => 3,
			'size'            => false,
			'include'         => '',
			'exclude'         => '',
			'link'            => false,
		), $attr );

		$id = intval( $r['id'] );

		if ( 'RAND' == $r['order'] )
			$r['orderby'] = 'none';

		$get_args = array(
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => $r['order'],
			'orderby'        => $r['orderby'],
		);

		if ( ! empty( $r['include'] ) ) {

			$include      = preg_replace( '/[^0-9,]+/', '', $r['include'] );
			$_attachments = get_posts( array_merge( $get_args, array( 'include' => $include ) ) );
			$attachments  = array();

			foreach ( $_attachments as $key => $val ) {

				$attachments[$val->ID] = $_attachments[$key];
			}
		}
		elseif ( ! empty( $r['exclude'] ) ) {

			$exclude     = preg_replace( '/[^0-9,]+/', '', $r['exclude'] );
			$attachments = get_children( array_merge( $get_args, array( 'post_parent' => $id, 'exclude' => $exclude ) ) );
		}
		else {

			$attachments = get_children( array_merge( $get_args, array( 'post_parent' => $id ) ) );
		}

		if ( empty( $attachments ) )
			return '<!-- empty gallery -->';

		$captiontag      = tag_escape( $r['captiontag'] );
		$columns         = intval( $r['columns'] );
		$content_columns = intval( $r['content_columns'] );
		$columns_wide    = floor( intval( $content_columns ) / $columns );
		$selector        = "gallery-{$instance}";
		$link_to_file    = 'file' !== $r['link'];
		$i               = 0;

		if ( 2 > $columns_wide ) {

			$columns_wide = 1;
			$captiontag   = false;
		}

		if ( $columns_wide > $content_columns )
			$columns_wide = $content_columns;

		if( $columns > $content_columns )
			$clear_every = $content_columns;
		else
			$clear_every = $columns;

		if ( ! empty( $r['size'] ) ) {

			$size       = $r['size'];
			$size_class = 'gallery-size-' . sanitize_html_class( $r['size'] );
		}
		else {

			$size       = $this->get_bootstrap_image_size( $columns_wide );
			$size_class = '';
		}

		$output = "<ul id='{$selector}' class='thumbnails gallery galleryid-{$id} gallery-columns-{$columns} {$size_class}'>\n";

		foreach ( $attachments as $id => $attachment ) {

			$link       = wp_get_attachment_link( $id, $size, $link_to_file );
			$item_class = 'span' . $columns_wide;

			if ( (++$i - 1) % $clear_every == 0 )
				$item_class .= ' thumbnail-clear';

			$output .= "<li class='{$item_class}'><div class='thumbnail'>\n\t{$link}\n";

			if ( $captiontag && trim( $attachment->post_excerpt ) ) {
				$output .= "\t<{$captiontag} class='caption gallery-caption'>"
						. wptexturize( $attachment->post_excerpt )
						. "</{$captiontag}>\n";
			}

			$output .= "</div></li>\n";
		}

		$output .= "</ul><!-- thumbnails -->\n";

		return $output;
	}

	/**
	 * Calculate image dimensions to fit number of Bootstrap grid columns in width.
	 *
	 * @param int    $columns
	 * @param string $ratio proportion for height
	 * @param string $media responsive view
	 *
	 * @return array width, height
	 */
	function get_bootstrap_image_size( $columns, $ratio = 'square', $media = 'default' ) {

		switch ( $media ) {

			default:
				$column_width  = 60;
				$column_gutter = 20;
		}

		$width = $columns * $column_width + ( $columns - 1 ) * $column_gutter - 10;

		switch ( $ratio ) {

			case 'golden':
				$height = round( $width / 1.6 );
			break;

			default:
				$height = $width;
		}

		return array( $width, $height );
	}

	/**
	 * Adjust arguments for comments presentation.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function list_comments_args( $args  ) {

		$args['style']        = 'div';
		$args['avatar_size']  = 60;
		$args['end-callback'] = array( $this, 'comment_end_callback' );

		return $args;
	}

	/**
	 * Load closing template for comment.
	 */
	function comment_end_callback() {

		static $template;

		if ( empty( $template ) )
			$template = locate_template( 'comment-end.php' );

		require $template;
	}

	/**
	 * Adjust comment data for Bootstrap markup.
	 *
	 * @param array $defaults
	 *
	 * @return array
	 */
	function comment_form_defaults( $defaults ) {

		foreach ( $defaults['fields'] as $key => $field ) {

			$defaults['fields'][$key] = $this->make_comment_field_horizontal( $field );
		}

		$defaults['comment_field']        = $this->make_comment_field_horizontal( $defaults['comment_field'] );
		$defaults['logged_in_as']         = $this->make_comment_notes_help_block( $defaults['logged_in_as'] );
		$defaults['comment_notes_before'] = $this->make_comment_notes_help_block( $defaults['comment_notes_before'] );
		$defaults['comment_notes_after']  = $this->make_comment_notes_help_block( $defaults['comment_notes_after'] );

		return $defaults;
	}

	function comment_form_top() { echo '<div class="form-horizontal">';	}

	function comment_form() { echo '</div>'; }

	/**
	 * Rewrite markup to strip paragraph and wrap in horizontal form block markup.
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	function make_comment_field_horizontal( $field ) {

		$field = preg_replace( '|<p class="(.*?)">|', '<div class="$1 control-group">', $field );

		$field = strtr( $field, array(
			'<label'    => '<label class="control-label"',
			'<input'    => '<div class="controls"><input class="span5"',
			'<textarea' => '<div class="controls"><textarea class="span5"',
			'</p>'      => '</div>',
		) );

		$field .= '</div>';

		return $field;
	}

	/**
	 * Rewrite markup to wrap into horizontal form help block.
	 *
	 * @param string $note
	 *
	 * @return string
	 */
	function make_comment_notes_help_block( $note ) {

		$note = '<div class="control-group"><div class="controls span5">' . str_replace( '<p class="', '<p class="help-block ', $note ) . '</div></div>';

		return $note;
	}
}