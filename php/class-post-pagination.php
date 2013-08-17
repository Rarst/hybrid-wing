<?php
namespace Rarst\Hybrid_Wing;

/**
 * Adjusts markup for pagination inside posts
 */
class Post_Pagination {

	protected $is_pager;

	/**
	 * Enable
	 */
	public function __construct() {
		$this->enable();
	}

	public function enable() {
		add_filter( 'wp_link_pages_args', array( $this, 'wp_link_pages_args' ) );
		add_filter( 'wp_link_pages_link', array( $this, 'wp_link_pages_link' ), 10, 2 );
	}

	public function disable() {
		remove_filter( 'wp_link_pages_args', array( $this, 'wp_link_pages_args' ) );
		remove_filter( 'wp_link_pages_link', array( $this, 'wp_link_pages_link' ), 10, 2 );
	}

	/**
	 * Adjust pagination/pager container
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function wp_link_pages_args( $args ) {

		if ( 'number' == $args['next_or_number'] ) {
			$this->is_pager = false;
			$args['before'] = '<div class="pagination pagination-centered"><ul>';
			$args['after']  = '</ul></div>';
		}
		else {
			$this->is_pager = true;
			$args['before'] = '<ul class="pager">';
			$args['after']  = '</ul>';
		}

		return $args;
	}

	/**
	 * Wrap pagination/pager links in list items
	 *
	 * @param string $link
	 * @param int    $page_number
	 *
	 * @return string
	 */
	public function wp_link_pages_link( $link, $page_number ) {

		global $page, $more;

		if ( $this->is_pager ) {
			if ( $page_number < $page )
				$link = '<li class="previous">' . $link . '</li>';
			else
				$link = '<li class="next">' . $link . '</li>';
		}
		else {
			// blame core
			$not_current_page = ( $page_number != $page );
			$more_front       = ( empty( $more ) && 1 == $page );

			if ( $not_current_page || $more_front )
				$link = '<li>' . $link . '</li>';
			else
				$link = '<li class="active"><span>' . $link . '</span></li>';
		}

		return $link;
	}
}