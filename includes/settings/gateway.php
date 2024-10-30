<?php
/**
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

/**
 * Settings for CityPay Gateway
 */
return [
	'enabled'           => [
		'title'   => __( 'Enable/Disable', 'citypay' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable CityPay', 'citypay' ),
		'default' => 'yes',
	],
	'title'             => [
		'title'       => __( 'Title', 'citypay' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'citypay' ),
		'default'     => __( 'CityPay', 'citypay' ),
		'desc_tip'    => true,
	],
	'description'       => [
		'title'       => __( 'Description', 'citypay' ),
		'type'        => 'text',
		'description' => __( 'This controls the description which the user sees during checkout.', 'citypay' ),
		'default'     => __( 'Pay with CityPay', 'citypay' ),
		'desc_tip'    => true,
	],
	'order_button_text' => [
		'title'       => __( 'Order button text', 'citypay' ),
		'type'        => 'text',
		'description' => __( 'This controls the order button text which the user sees during checkout.', 'citypay' ),
		'default'     => __( 'Proceed to CityPay', 'citypay' ),
		'desc_tip'    => true,
	],
	'debug'             => [
		'title'       => __( 'Debug Log', 'citypay' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Logging', 'citypay' ),
		'default'     => 'no',
		/* translators: %s: log file path */
		'description' => sprintf( __( 'Log CityPay events inside: <code>%s</code>', 'citypay' ), wc_get_log_file_path( 'citypay_gateway' ) ),
	],
	'test_mode'         => [
		'title'   => __( 'Test mode', 'citypay' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable test mode', 'citypay' ),
		'default' => 'no',
	],
	'customer_id'       => [
		'title'       => __( 'Customer Id', 'citypay' ),
		'type'        => 'text',
		'description' => __( 'Customer ID is displayed in customer cabinet, under company name.', 'citypay' ),
		'desc_tip'    => true,
	],
	'access_token'      => [
		'title'       => __( 'Access token', 'citypay' ),
		'type'        => 'text',
		'description' => __( 'Authentication token are created from customer cabinet.', 'citypay' ),
		'desc_tip'    => true,
	],
];
