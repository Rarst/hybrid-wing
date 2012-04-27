<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php hybrid_document_title(); ?></title>

<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php wp_head(); // wp_head ?>

</head>

<body class="<?php hybrid_body_class(); ?>">

<?php do_atomic( 'before_html' ); ?>

<div id="body-container" class="<?php echo apply_atomic( 'body_container_class', '' ); ?>">

	<?php do_atomic( 'before_header' ); ?>

	<div id="header-container">

		<div id="header" class="<?php echo apply_atomic( 'header_class', '' ); ?>">

			<?php do_atomic( 'header' ); ?>

		</div>

	</div>

	<?php do_atomic( 'after_header' ); ?>

	<div id="container" class="<?php echo apply_atomic( 'container_class', '' ); ?>">

		<?php do_atomic( 'before_container' ); ?>