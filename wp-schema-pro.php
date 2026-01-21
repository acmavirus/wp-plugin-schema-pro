<?php
/**
 * Plugin Name: Schema Pro
 * Plugin URI: https://wpschema.com
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Description: Integrate Schema.org JSON-LD code in your website and improve SEO.
 * Version: 1.1.13
 * Text Domain: wp-schema-pro
 * License: GPL2
 *
 * @package Schema Pro
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Set constants.
 */
define( 'BSF_AIOSRS_PRO_FILE', __FILE__ );
define( 'BSF_AIOSRS_PRO_BASE', plugin_basename( BSF_AIOSRS_PRO_FILE ) );
define( 'BSF_AIOSRS_PRO_DIR', plugin_dir_path( BSF_AIOSRS_PRO_FILE ) );
define( 'BSF_AIOSRS_PRO_URI', plugins_url( '/', BSF_AIOSRS_PRO_FILE ) );
define( 'BSF_AIOSRS_PRO_VER', '1.1.13' );

/**
 * Initial file.
 */
require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro.php';
