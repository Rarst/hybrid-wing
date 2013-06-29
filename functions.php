<?php

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) )
	require __DIR__ . '/vendor/autoload.php';

require_once get_template_directory() . '/hybrid-core/hybrid.php';

$hybrid_wing = new Hybrid_Wing();