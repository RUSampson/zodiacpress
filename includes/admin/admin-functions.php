<?php
/**
 * Functions that are needed only in admin
 */
if ( ! defined( 'ABSPATH' ) ) exit;
function zp_admin_notices() {
	global $zodiacpress_options;
	// On activation, show admin notice to inform that GeoNames username must be set up.
	if ( get_transient( 'zodiacpress_activating' ) ) {
		delete_transient( 'zodiacpress_activating' );
		// Only show notice if geonames username is not set
		if ( empty( $zodiacpress_options['geonames_user'] ) ) {
			include ZODIACPRESS_PATH . 'includes/admin/views/html-notice-install.php';
		}
	}
	// Success notices for ZP Tools.
	if ( isset( $_GET['zp-done'] ) ) {
		switch( $_GET['zp-done'] ) {
			case 'natal_in_signs':
				$success = __( 'Interpretations for natal planets in signs were erased.', 'zodiacpress' );
				break;
			case 'natal_in_houses':
				$success = __( 'Interpretations for natal planets in houses were erased.', 'zodiacpress' );
				break;
			case 'natal_aspects':
				$success = __( 'Interpretations for natal aspects were erased.', 'zodiacpress' );
				break;
			case 'settings-imported':
				$success = __( 'Your ZodiacPress settings have been imported.', 'zodiacpress' );
				break;
			case 'interps-imported':
				$success = __( 'Your ZodiacPress interpretations have been imported.', 'zodiacpress' );
				break;				
		}
		if ( isset( $success ) ) {
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', $success );
		}
	}
	// Notify when plugin cannot work
	if ( zp_is_admin_page() ) {
		if ( ! zp_is_func_enabled( 'exec' ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' .
			__( 'The PHP exec() function is disabled on your server. ZodiacPress requires the exec() function in order to create astrology reports. Please ask your web host to enable the PHP exec() function.', 'zodiacpress' ) .
			'</p></div>';
		}
		if ( zp_is_server_windows() ) {
			if ( ! defined( 'ZP_WINDOWS_SERVER_PATH' ) ) {
				echo '<div class="notice notice-error is-dismissible"><p>' .
				sprintf( __( 'Your website server uses Windows hosting. For ZodiacPress to work on your server, you need the %1$sZP Windows Server%2$s plugin. See <a href="%3$s" target="_blank" rel="noopener">this</a> for details.', 'zodiacpress' ), '<strong>', '</strong>', 'https://isabelcastillo.com/docs/windows-hosting' ) .
				'</p></div>';
			}
		}
	}
}
add_action( 'admin_notices', 'zp_admin_notices' );
/**
 * Add admin notice when file permissions on ephemeris will not permit the plugin to work.
 */
function zp_admin_notices_chmod_failed() {
	if ( zp_is_admin_page() ) {
		$msg = sprintf( __( 'Your server did not allow ZodiacPress to set the necessary file permissions for the Ephemeris. ZodiacPress requires this in order to create astrology reports. <a href="%s" target="_blank" rel="noopener">See this</a> to fix it.', 'zodiacpress' ), 'https://isabelcastillo.com/docs/file-permissions-swetest' );

		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $msg );
	}
}
/**
 * Add admin notice when swetest file is missing.
 */
function zp_admin_notices_missing_file() {
	if ( zp_is_admin_page() ) {
		$msg = sprintf( __( 'You are missing a file from ZodiacPress. This file is required in order to create astrology reports. <a href="%s" target="_blank" rel="noopener">See this</a> for more information.', 'zodiacpress' ), 'https://isabelcastillo.com/docs/missing-file' );
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $msg );
	}
}
/**
 * Erase Interpretations for Natal Planets in Signs when using ZP Cleanup Tools.
 */
function zp_erase_natal_in_signs() {
	if ( ! wp_verify_nonce( $_GET['_nonce'], 'zp_erase_natal_in_signs' ) ) {
		return false;
	}
	if ( ! current_user_can( 'manage_zodiacpress_interps' ) ) {
		return;
	}
	delete_option( 'zp_natal_planets_in_signs' );

	$url = esc_url_raw( add_query_arg( array(
		'page'	=> 'zodiacpress-tools',
		'tab'	=> 'cleanup',
		'zp-done'	=> 'natal_in_signs'
		), admin_url( 'admin.php' )
	) );
	wp_redirect( wp_sanitize_redirect( $url ) );
	exit;
}
add_action( 'admin_post_erase_natal_in_signs', 'zp_erase_natal_in_signs' );
/**
 * Erase Interpretations for Natal Planets in Houses when using ZP Cleanup Tools.
 */
function zp_erase_natal_in_houses() {
	if ( ! wp_verify_nonce( $_GET['_nonce'], 'zp_erase_natal_in_houses' ) ) {
		return false;
	}
	if ( ! current_user_can( 'manage_zodiacpress_interps' ) ) {
		return;
	}
	delete_option( 'zp_natal_planets_in_houses' );
	$url = esc_url_raw( add_query_arg( array(
		'page'	=> 'zodiacpress-tools',
		'tab'	=> 'cleanup',
		'zp-done'	=> 'natal_in_houses'
		), admin_url( 'admin.php' )
	) );
	wp_redirect( wp_sanitize_redirect( $url ) );
	exit;
}
add_action( 'admin_post_erase_natal_in_houses', 'zp_erase_natal_in_houses' );
/**
 * Erase Interpretations for Natal Aspects when using ZP Cleanup Tools.
 */
function zp_erase_natal_aspects() {
	if ( ! wp_verify_nonce( $_GET['_nonce'], 'zp_erase_natal_aspects' ) ) {
		return false;
	}
	if ( ! current_user_can( 'manage_zodiacpress_interps' ) ) {
		return;
	}
	foreach ( zp_get_planets() as $planet ) {
		$p = ( 'sun' == $planet['id'] ) ? 'main' : $planet['id'];
		delete_option( 'zp_natal_aspects_' . $p );
	}
	$url = esc_url_raw( add_query_arg( array(
		'page'	=> 'zodiacpress-tools',
		'tab'	=> 'cleanup',
		'zp-done'	=> 'natal_aspects'
		), admin_url( 'admin.php' )
	) );
	wp_redirect( wp_sanitize_redirect( $url ) );
	exit;
}
add_action( 'admin_post_erase_natal_aspects', 'zp_erase_natal_aspects' );
/**
 * Custom admin menu icon
 */
function zp_custom_admin_menu_icon() {
   echo '<style>@font-face {
  font-family: "zodiacpress";
  src:    url("' . ZODIACPRESS_URL . 'assets/fonts/zodiacpress.eot?fr7qsr");
  src:    url("' . ZODIACPRESS_URL . 'assets/fonts/zodiacpress.eot?fr7qsr#iefix") format("embedded-opentype"),
  url("' . ZODIACPRESS_URL . 'assets/fonts/zodiacpress.ttf?fr7qsr") format("truetype"),
  url("' . ZODIACPRESS_URL . 'assets/fonts/zodiacpress.woff?fr7qsr") format("woff"),
  url("' . ZODIACPRESS_URL . 'assets/fonts/zodiacpress.svg?fr7qsr#zodiacpress") format("svg");
  font-weight: normal;
  font-style: normal;
  }#adminmenu .toplevel_page_zodiacpress .dashicons-universal-access-alt.dashicons-before::before {font-family: "zodiacpress" !important}#adminmenu .toplevel_page_zodiacpress div.dashicons-universal-access-alt::before{content:"\e90c"}</style>';
}
add_action('admin_head', 'zp_custom_admin_menu_icon');
/**
 * Display links in the admin for ZP docs, rating, and extensions.
 */
function zp_admin_links() {
	$links = array(
		array(
			'extend',
			__( 'ZodiacPress Extensions', 'zodiacpress' ),
			'https://isabelcastillo.com/zodiacpress-extensions' ),
		array(
			'feedback',
			__( 'Feedback', 'zodiacpress' ),
			'https://wordpress.org/support/plugin/zodiacpress/reviews/' ),
		array(
			'docs',
			__( 'Documentation', 'zodiacpress' ),
			'https://isabelcastillo.com/docs/category/zodiacpress' )
	);
	foreach ( $links as $link ) {
		echo '<a href="' . $link[2] . '" class="zp-' . $link[0] . '-link alignright" target="_blank" rel="noopener">' . $link[1] . '</a>';
	}
}
function zp_add_debug_info( $debug_info ) {
	$debug_info['zodiacpress'] = array(
		'label'    => 'ZodiacPress',
		'fields'   => array(
			'swetest_exists' => array(
				'label'	=> 'swetest file',
				'value'	=> file_exists( ZODIACPRESS_PATH . 'sweph/swetest' ) ? 'okay' : 'Missing!',
			),
			'swetest_permission' => array(
				'label'	=> 'Ephemeris permissions',
				'value'	=> zp_is_sweph_executable() ? 'okay' : 'Not executable!',
			),
			'exec' => array(
				'label'	=> 'exec()',
				'value'	=> zp_is_func_enabled( 'exec' ) ? 'okay' : 'Disabled!',

			),
			'chmod' => array(
				'label'	=> 'chmod()',
				'value'	=> zp_is_func_enabled( 'chmod' ) ? 'okay' : 'Disabled!',

			),
			'php_shlib_suffix' => array(
				'label'	=> 'PHP_SHLIB_SUFFIX',
				'value'	=> PHP_SHLIB_SUFFIX
			),
		),
	);
	return $debug_info;
}
add_filter( 'debug_information', 'zp_add_debug_info' );
