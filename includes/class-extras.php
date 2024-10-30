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

namespace PlugandPay\CityPay;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CityPay extras class.
 */
class Extras {

	/**
	 * __FILE__ from the root plugin file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $file;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string $file Must be __FILE__ from the root plugin file.
	 */
	public function __construct( $file ) {
		$this->file = $file;

		add_filter( 'woocommerce_gateway_icon', [ $this, 'add_gateway_icons' ], 10, 2 );
	}

	/**
	 * Add CityPay logo to the gateway.
	 *
	 * @since 1.0.0
	 * @param string $icons Html image tags.
	 * @param string $gateway_id Gateway id.
	 * @return string
	 */
	public function add_gateway_icons( $icons, $gateway_id ) {
		if ( 'citypay_gateway' === $gateway_id ) {
			$icons .= sprintf(
				'<img width="150" src="%1$sassets/citypay.svg" alt="CityPay.io" />',
				plugin_dir_url( $this->file )
			);
		}
		return $icons;
	}

}

