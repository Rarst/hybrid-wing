<?php

namespace Rarst\Hybrid_Wing;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) )
	require __DIR__ . '/vendor/autoload.php';

global $hybrid_wing;
$hybrid_wing = new Core();