<?php
namespace Rarst\Hybrid_Wing;

/**
 * Adjusts markup of galleries for Bootstrap thumbnail grid
 */
class Gallery {

	public function enable() {

		add_filter( 'post_gallery', array( $this, 'post_gallery' ), 10, 2 );
	}

	public function disable() {

		remove_filter( 'post_gallery', array( $this, 'post_gallery' ), 10, 2 );
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
		$instance ++;

		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );

			if ( ! $attr['orderby'] )
				unset( $attr['orderby'] );
		}

		$r = shortcode_atts(
			array(
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
			),
			$attr
		);

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

		$captiontag   = tag_escape( $r['captiontag'] );
		$columns      = intval( $r['columns'] );
		$columns_wide = floor( 12 / $columns );
		$selector     = "gallery-{$instance}";
		$link_to_file = 'file' !== $r['link'];

		if ( 2 > $columns_wide ) {
			$columns_wide = 1;
			$captiontag   = false;
		}

		if ( ! empty( $r['size'] ) ) {
			$size       = $r['size'];
			$size_class = 'gallery-size-' . sanitize_html_class( $r['size'] );
		}
		else {
			$size       = $this->get_bootstrap_image_size( $columns_wide );
			$size_class = '';
		}

		$images = array();

		foreach ( $attachments as $id => $attachment ) {
			$link       = wp_get_attachment_link( $id, $size, $link_to_file );
			$item_class = 'col-md-' . $columns_wide;
			$image      = "<div class='{$item_class}'><div class='thumbnail'>\n\t{$link}\n";

			if ( $captiontag && trim( $attachment->post_excerpt ) ) {
				$image .= "\t<{$captiontag} class='caption gallery-caption'>"
						. wptexturize( $attachment->post_excerpt )
						. "</{$captiontag}>\n";
			}

			$image   .= "</div></div>\n";
			$images[] = $image;
		}

		$output = "<div id='{$selector}' class='thumbnails gallery galleryid-{$id} gallery-columns-{$columns} {$size_class}'>\n";

		foreach ( array_chunk( $images, $columns ) as $set ) {
			$output .= '<div class="row">' . implode( '', $set )  .'</div>';
		}

		$output .= "</div><!-- thumbnails -->\n";

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
}