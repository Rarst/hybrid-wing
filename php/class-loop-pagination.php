<?php
namespace Rarst\Hybrid_Wing;

/**
 * Adjusts markup of Loop Pagination extension
 */
class Loop_Pagination {

	public function enable() {

		add_filter( 'loop_pagination_args', array( $this, 'loop_pagination_args' ) );
		add_filter( 'loop_pagination', array( $this, 'loop_pagination_markup' ) );
		add_filter( 'hw_paginate_comments_links', array( $this, 'loop_pagination_markup' ) );
	}

	public function disable() {

		remove_filter( 'loop_pagination_args', array( $this, 'loop_pagination_args' ) );
		remove_filter( 'loop_pagination', array( $this, 'loop_pagination_markup' ) );
		remove_filter( 'hw_paginate_comments_links', array( $this, 'loop_pagination_markup' ) );
	}

	/**
	 * Adjust arguments of loop pagination function.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function loop_pagination_args( $args ) {

		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		$args['before'] = '<div class="text-center">';
		$args['after']  = '</div>';
		$args['type']   = 'list';

		if ( $wp_rewrite->using_permalinks() ) {
			$link  = get_pagenum_link();
			$parse = parse_url( $link );

			if ( ! empty( $parse['query'] ) )
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
	function loop_pagination_markup( $html ) {

		$html = strtr(
			$html,
			array(
				"<ul class='page-numbers'"                => "<ul class='page-numbers pagination pagination-lg'",
				"<a class='page-numbers'"                 => "<a class='page-numbers hidden-sm'",
				'<span class="page-numbers dots"'         => '<span class="page-numbers dots hidden-sm"',
				"<li><span class='page-numbers current'>" => "<li class='active'><span class='page-numbers current'>",
			)
		);

		return $html;
	}
}