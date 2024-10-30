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
 * CityPay gateway class.
 */
class Gateway extends \WC_Payment_Gateway {

	/**
	 * __FILE__ from the root plugin file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $file;

	/**
	 * Whether or not logging is enabled.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public static $log_enabled = false;

	/**
	 * Logger instance.
	 *
	 * @since 1.0.0
	 * @var WC_Logger
	 */
	public static $log = false;

	/**
	 * Whether or not test-mode is enabled.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public static $test_mode = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param string $file Must be __FILE__ from the root plugin file.
	 */
	public function __construct( $file ) {
		$this->file               = $file;
		$this->id                 = 'citypay_gateway';
		$this->has_fields         = false;
		$this->method_title       = __( 'CityPay', 'citypay' );
		$this->method_description = __( 'Integrate the plugin to your website and start receiving payments in crypto.', 'citypay' );
		$this->supports           = array(
			'products',
		);

		$this->init();

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_api_' . $this->route_callback, [ $this, 'route_callback' ] );
		add_action( 'woocommerce_api_' . $this->route_cancel, [ $this, 'route_cancel' ] );
		add_action( 'woocommerce_api_' . $this->route_success, [ $this, 'route_success' ] );
	}

	/**
	 * Initialise gateway settings.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->form_fields = include 'settings/gateway.php';
		$this->init_settings();

		// User set variables.
		$this->title             = $this->get_option( 'title' );
		$this->description       = $this->get_option( 'description' );
		$this->order_button_text = $this->get_option( 'order_button_text' );
		$this->debug             = 'yes' === $this->get_option( 'debug', 'no' );
		self::$log_enabled       = $this->debug;
		self::$test_mode         = 'yes' === $this->get_option( 'test_mode', 'no' );
		$this->customer_id       = $this->get_option( 'customer_id' );
		$this->access_token      = $this->get_option( 'access_token' );

		// Routes.
		$this->route_callback = 'citypay/callback';
		$this->route_cancel   = 'citypay/cancel';
		$this->route_success  = 'citypay/success';

		// API urls.
		if ( self::$test_mode ) {

			$this->api_urls = [
				'generateOrder' => 'https://test-v2-listener.citypay.io/api/generateOrder',
				'orderDetails'  => 'https://test-v2-order-api.citypay.io',
			];

		} else {

			$this->api_urls = [
				'generateOrder' => 'https://v2-listener.citypay.io/api/generateOrder',
				'orderDetails'  => 'https://v2-order-api.citypay.io',
			];

		}
	}

	/**
	 * Logging method.
	 *
	 * @since 1.0.0
	 * @param string $message Log message.
	 * @param string $level Optional. Default 'info'. Possible values:
	 *                      emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, [ 'source' => 'citypay_gateway' ] );
		}
	}

	/**
	 * Display notices in admin dashboard.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_notices() {
		if ( ! $this->has_required_options() ) {
			/* translators: Gateway settings url */
			echo '<div class="error"><p>' . wp_kses_data( sprintf( __( 'CityPay for WooCommerce: Please fill out required options <a href="%s">here</a>.', 'citypay' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->id ) ) ) . '</p></div>';
		}
	}

	/**
	 * Output the gateway settings screen.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		echo '<h2>' . esc_html( $this->get_method_title() );
		wc_back_link( __( 'Return to payments', 'woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		echo '</h2>';
		echo wp_kses_post( wpautop( $this->get_method_description() ) );
		\WC_Settings_API::admin_options();

		$urls = [
			__( 'Success URL', 'citypay' )  => $this->get_route_url( $this->route_success ),
			__( 'Cancel URL', 'citypay' )   => $this->get_route_url( $this->route_cancel ),
			__( 'Callback URL', 'citypay' ) => $this->get_route_url( $this->route_callback ),
		];
		?>
		<div style="border: 1px dotted #ccc; padding-left: 15px;">
			<p><strong><?php esc_html_e( 'Plugin Info', 'citypay' ); ?></strong></p>
			<p><?php esc_html_e( 'To get an access token please login to CityPay and move to developers page. On the page click on add new token button. Fill all mandatory fields: cancel, success and callback urls including IP using information generated in the box below.', 'citypay' ); ?></p>
			<hr/>
			<ul>
				<?php foreach ( $urls as $name => $url ) { ?>
					<li><?php echo esc_html( $name ); ?> - <?php echo esc_attr( $url ); ?></li>
				<?php } ?>
			</ul>
			<hr/>
			<p><strong><?php esc_html_e( 'IP to whitelist', 'citypay' ); ?></strong>: <?php echo esc_html( $this->what_is_my_ip() ); ?> <i style="color:#b1b1b1;"><?php esc_html_e( 'Result is cached for an hour', 'citypay' ); ?>.</i></p>
		</div>
		<?php
	}

	/**
	 * Is this gateway available?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && $this->has_required_options();
	}

	/**
	 * Are all required options filled out?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_required_options() {
		return $this->customer_id && $this->access_token;
	}

	/**
	 * API request.
	 *
	 * @since 1.0.0
	 * @param array  $params Query string parameters.
	 * @param string $url URL.
	 * @param string $type Request type.
	 * @return array|false
	 */
	public function api_request( $params, $url, $type = 'POST' ) {

		$response = wp_remote_request(
			$url,
			[
				'method' => $type,
				'body'   => $params,
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->log( $response->get_error_message(), 'error' );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! $body ) {
			return false;
		}

		return json_decode( $body, true );

	}

	/**
	 * Process the payment and redirect client.
	 *
	 * @since 1.0.0
	 * @param  int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$params = [
			'order_id'     => $order->get_id(),
			'order_token'  => $this->generate_order_token( $order->get_id() ),
			'amount'       => $order->get_total(),
			'customer_id'  => $this->customer_id,
			'access_token' => $this->access_token,
		];

		$this->log( sprintf( 'Params to send: %s', wp_json_encode( $params, JSON_PRETTY_PRINT ) ), 'info' );

		$response = $this->api_request( $params, $this->api_urls['generateOrder'] );

		$this->log( sprintf( 'Response on generateOrder: %s, order_id: %d', wp_json_encode( $response, JSON_PRETTY_PRINT ), $order->get_id() ), 'info' );

		if ( ! $response || ! isset( $response['success'], $response['data']['payment_url'], $response['data']['token'] ) || true !== $response['success'] ) {
			$this->log( 'No valid response from CityPay', 'error' );
			wc_add_notice( __( 'CityPay not responding, please try again later.', 'citypay' ), 'error' );
			return;
		}

		$order->set_transaction_id( $response['data']['token'] );
		$order->save();

		$this->log( sprintf( 'Order id: %d, redirecting user to CityPay gateway: %s', $order->get_id(), $response['data']['payment_url'] ), 'notice' );

		return [
			'result'   => 'success',
			'redirect' => $response['data']['payment_url'],
		];
	}

	/**
	 * Route callback.
	 * Payment status is communicated via callback before success redirect.
	 *
	 * @since 1.0.0
	 */
	public function route_callback() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ( ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}

		$raw_post_data = $wp_filesystem->get_contents( 'php://input' );
		$post_data     = json_decode( $raw_post_data, true );

		$this->log( sprintf( 'Incoming callback, raw data posted: %s', $raw_post_data ), 'info' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$shop_order_id = $post_data['client_order_id']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$order         = wc_get_order( $shop_order_id );

		if ( ! $order ) {
			$this->log( sprintf( 'Cannot find order by shop_order_id %s.', $shop_order_id ), 'error' );
			status_header( 404 );
			exit;
		}

		if ( 'yes' !== $order->get_meta( 'citypay_status_received' ) ) {

			$token        = $order->get_transaction_id();
			$remote_order = $this->get_remote_order_details( $token );
			$status_code  = $remote_order['data']['status']['code'] ?? '';

			$this->log( sprintf( 'Check remote order data %s', wp_json_encode( $remote_order, JSON_PRETTY_PRINT ) ), 'info' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( 'CONFIRMED' === $status_code ) {

				$this->log( 'Payment success!', 'info' );
				$this->payment_complete( $order );

				$order->update_meta_data( 'citypay_status_received', 'yes' );
				$order->update_meta_data( 'citypay_status', $status_code );
				$order->save();

			}

		}

		status_header( 200 );
		exit;
	}

	/**
	 * Get order from remote API.
	 *
	 * @since 1.0.0
	 * @param string $token Order token.
	 * @return array|false
	 */
	public function get_remote_order_details( $token ) {
		return $this->api_request( [], sprintf( '%s/order/%s', $this->api_urls['orderDetails'], $token ), 'GET' );
	}

	/**
	 * Route success.
	 * When user is redirected back to shop after successful payment.
	 *
	 * @since 1.0.0
	 */
	public function route_success() {
		wp_safe_redirect( $this->get_safe_success_url() );
		exit;
	}

	/**
	 * Route cancel.
	 * When user clicks cancel on payment page.
	 *
	 * @since 1.0.0
	 */
	public function route_cancel() {
		wc_add_notice( __( 'Payment was cancelled.', 'citypay' ), 'notice' );
		wp_safe_redirect( wc_get_page_permalink( 'cart' ) );
		exit;
	}

	/**
	 * Get success return url (order received page) in a safe manner.
	 * https://github.com/woocommerce/woocommerce/issues/22049
	 *
	 * @since 1.0.0-
	 * @param WC_Order $order Order object.
	 * @return string
	 */
	public function get_safe_success_url( $order = false ) {
		if ( $order && $order->get_user_id() === get_current_user_id() ) {
			return $this->get_return_url( $order );
		} else {
			return wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) );
		}
	}

	/**
	 * Payment complete.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	public function payment_complete( $order ) {
		if ( $order->payment_complete() ) {
			$order->add_order_note( __( 'CityPay payment complete.', 'citypay' ) );
			return true;
		}
		return false;
	}

	/**
	 * Generate order token.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order id as prefix to token.
	 * @return int
	 */
	protected function generate_order_token( $order_id ) {
		return (int) ( $order_id . wp_rand( 100000, 999999 ) );
	}

	/**
	 * Get full route url.
	 *
	 * @since 1.0.0
	 * @param string $route Route.
	 * @return string
	 */
	public function get_route_url( $route ) {
		return sprintf(
			'%s/wc-api/%s',
			get_bloginfo( 'url' ),
			$route
		);
	}

	/**
	 * Determine my real ip.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function what_is_my_ip() {
		$ip = get_transient( 'woocommerce_' . $this->id . '_external_shop_ip' );
		if ( false === $ip ) {
			$resp = wp_remote_get( 'http://ipecho.net/plain' );
			$ip   = wp_remote_retrieve_body( $resp );
			set_transient( 'woocommerce_' . $this->id . '_external_shop_ip', $ip, HOUR_IN_SECONDS );
		}
		return $ip;
	}

}
