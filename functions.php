<?php

function tcc_plugin_class_loader( $class ) {
	if ( substr( $class, 0, 4 ) === 'TCC_' ) {
		$load = str_replace( '_', '/', substr( $class, ( strpos( $class, '_' ) + 1 ) ) );
		$file = TCC_PLUGIN_DIR . "/classes/{$load}.php";
		if ( is_readable( $file ) ) include $file;
	}
}
spl_autoload_register( 'tcc_plugin_class_loader' );

if ( ! function_exists( 'is_url' ) ) {
	function is_url( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL );
	}
}
