<?php

/**
 * Wrapper for comments pagination.
 *
 * @see paginate_comments_links()
 *
 * @param array $args
 *
 * @return string
 */
function hw_paginate_comments_links( $args = array() ) {

	$defaults = array(
		'echo'   => true,
		'type'   => 'list',
	);

	$args   = wp_parse_args( $args, $defaults );
	$args   = apply_filters( 'hw_paginate_comments_links_args', $args );
	$output = paginate_comments_links( array_merge( $args, array( 'echo' => false ) ) );
	$output = '<div class="pagination pagination-centered">' . $output . '</div>';
	$output = apply_filters( 'hw_paginate_comments_links', $output );

	if ( ! empty( $args['echo'] ) )
		echo $output;

	return $output;
}
