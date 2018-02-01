<?php
/**
 * Admin Functions
 *
 * @package     ZodiacPress
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ZP Admin notices
 */
function zp_admin_notices() {
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
				sprintf( __( 'Your website server uses Windows hosting. For ZodiacPress to work on your server, you need the %1$sZP Windows Server%2$s plugin. See <a href="%3$s" target="_blank" rel="nofollow">this</a> for details.', 'zodiacpress' ), '<strong>', '</strong>', 'https://cosmicplugins.com/docs/your-site-windows-hosting/' ) .
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
		$msg = sprintf( __( 'Your server did not allow ZodiacPress to set the necessary file permissions for the Ephemeris. ZodiacPress requires this in order to create astrology reports. <a href="%s" target="_blank" rel="nofollow">See this</a> to fix it.', 'zodiacpress' ), 'https://cosmicplugins.com/docs/file-permissions-swetest/' );

		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $msg );
	}
}

/**
 * Add admin notice when swetest file is missing.
 */
function zp_admin_notices_missing_file() {
	if ( zp_is_admin_page() ) {
		$msg = sprintf( __( 'You are missing a file from ZodiacPress. This file is required in order to create astrology reports. <a href="%s" target="_blank" rel="nofollow">See this</a> for more information.', 'zodiacpress' ), 'https://cosmicplugins.com/docs/missing-file/' );
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $msg );
	}
}

/**
 * Perform cleanup actions for the ZP Cleanup Tools
 */
function zp_run_cleanup_tools() {

	/****************************************************
	* @todo now now must also not check for $_GET all over plugin.

	See what $_get has to be eliminated!!!!!
	This one has to be eliminated! use instead admin_post{action}


	* 
	****************************************************/
	if ( ! isset( $_GET['zp_cleanup_tool'] ) || ! isset( $_GET['_nonce'] ) ) {
		return false;
	}
	if ( ! wp_verify_nonce( $_GET['_nonce'], 'zp_cleanup_tool' ) ) {
		return false;
	}
	switch( $_GET['zp_cleanup_tool'] ) {

		case 'natal_in_signs';
			delete_option( 'zp_natal_planets_in_signs' );
			break;
		
		case 'natal_in_houses':
			delete_option( 'zp_natal_planets_in_houses' );
			break;
		
		case 'natal_aspects':
			foreach ( zp_get_planets() as $planet ) {
				$p = ( 'sun' == $planet['id'] ) ? 'main' : $planet['id'];
				delete_option( 'zp_natal_aspects_' . $p );
			}
			break;
	}
	/* Redirect in "read-only" mode */
	$url  = esc_url_raw( add_query_arg( array(
		'page'	=> 'zodiacpress-tools',
		'tab'	=> 'cleanup',
		'zp-done'	=> sanitize_text_field( $_GET['zp_cleanup_tool'] )
		), admin_url( 'admin.php' )
	) );
	wp_redirect( wp_sanitize_redirect( $url ) );
	exit;
}
add_action( 'admin_init', 'zp_run_cleanup_tools', 10, 0 );

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
 * Displays a link to see ZP extensions.
 */
function zp_extend_link() {
	echo '<a href="https://cosmicplugins.com/downloads/category/zodiacpress-extensions/" class="button-secondary zp-extend-link alignright" target="_blank" rel="nofollow">';
	_e( 'See ZodiacPress Extensions', 'zodiacpress' );
	echo '</a>';
}

/**
 * Displays a link to rate ZodacPress
 */
function zp_feedback_link() {
	echo '<a href="https://wordpress.org/support/plugin/zodiacpress/reviews/" class="button-secondary zp-feedback-link alignright" target="_blank" rel="nofollow">';
	_e( 'Feedback', 'zodiacpress' );
	echo '</a>';
}
