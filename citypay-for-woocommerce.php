<?php
/**
 * Plugin Name: CityPay for WooCommerce
 * Description: Integrate the plugin to your website and start receiving payments in crypto.
 * Version:     1.0.0
 * Author:      CityPay
 * Author URI:  https://www.citypay.io
 * License:     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * Domain Path: /languages
 * Text Domain: citypay
 * WC requires at least: 3.0.0
 * WC tested up to: 5.1
 *
 * Intellectual Property rights, and copyright, reserved by Plug and Pay, Ltd. as allowed by law include,
 * but are not limited to, the working concept, function, and behavior of this software,
 * the logical code structure and expression as written.
 *
 * @package     CityPay for WooCommerce
 * @author      Plug and Pay Ltd. http://plugandpay.ge/
 * @copyright   Copyright (c) Plug and Pay Ltd. (support@plugandpay.ge)
 * @since       1.0.0
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Composer autoload.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Init plugin.
 *
 * @param string $file Must be __FILE__ from the root plugin file.
 * @param string $software_version Current software version of this plugin.
 *                                 Starts at version 1.0.0 and uses SemVer - https://semver.org
 */
\PlugandPay\CityPay\Plugin_Factory::instance( __FILE__, '1.0.0' );
