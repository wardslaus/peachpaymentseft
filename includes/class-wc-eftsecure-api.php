<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Eftsecure_API class.
 *
 * Communicates with EftSecure API.
 */
class WC_Eftsecure_API {

	/**
	 * Secret API Username.
	 * @var string
	 */
	private static $username = '';

    /**
     * Secret API Password.
     * @var string
     */
    private static $password = '';

    private static $settings = [
        'api_endpoint' => 'https://services.callpay.com/api/v1/',
        'app_domain' => 'https://eftsecure.callpay.com'
    ];

    /**
     * @param $setting
     * @return mixed|null
     */
    public static function getSetting($setting) {
        $settings = self::$settings;
        if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
            $settings = array_merge($settings, require(dirname( __FILE__ ) . '/local-config.php'));
        }

        if (isset($settings[$setting])) {
            return $settings[$setting];
        }
        return null;
    }

	/**
	 * Set api username.
	 * @param string $username
	 */
	public static function set_username( $username ) {
		self::$username = $username;
	}

	/**
	 * Get secret key.
	 * @return string
	 */
	public static function get_username() {
		if ( ! self::$username ) {
			$options = get_option( 'woocommerce_eftsecure_settings' );

			if ( isset($options['username'])) {
				self::set_username( $options['username']  );
			}
		}
		return self::$username;
	}

    /**
     * Set api password.
     * @param string $password
     */
    public static function set_password( $password ) {
        self::$password = $password;
    }

    /**
     * Get api password.
     * @return string
     */
    public static function get_password() {
        if ( ! self::$password ) {
            $options = get_option( 'woocommerce_eftsecure_settings' );

            if ( isset($options['password'])) {
                self::set_password( $options['password']  );
            }
        }
        return self::$password;
    }

	/**
	 * Send the request to EftSecure's API
	 *
	 * @param mixed $request
	 * @param string $api
	 * @return array|WP_Error
	 */
	public static function request( $request, $api = 'user/login', $method = 'POST' ) {
	    $url = self::getSetting('api_endpoint') . $api;
		WC_Eftsecure::log( "{$api} request to ".$url . print_r( $request, true ) );

		$response = wp_remote_post(
			self::getSetting('api_endpoint') . $api,
			array(
				'method'        => $method,
				'headers'       => array(
					'Authorization'  => 'Basic ' . base64_encode( self::get_username(). ':' . self::get_password() ),
				),
				'body'       => apply_filters( 'woocommerce_eftsecure_request_body', $request, $api ),
				'timeout'    => 70,
				'user-agent' => 'WooCommerce ' . WC()->version,
                'x-domain' => home_url( '/' )
			)
		);

        $parsed_response = json_decode( $response['body'] );

        if(empty( $response['body'] ) ) {
            WC_Eftsecure::log( "Error Response: " . print_r( $response, true ) );
            return new WP_Error( 'eftsecure_error', __( 'There was a problem connecting to the payment gateway.'.$parsed_response->name, 'woocommerce-gateway-eftsecure' ) );
        }

        if ( is_wp_error( $response )) {
			WC_Eftsecure::log( "Error Response: " . print_r( $response, true ) );
			return new WP_Error( 'eftsecure_error', __( 'There was a problem connecting to the payment gateway.'.$parsed_response->name, 'woocommerce-gateway-eftsecure' ) );
		}

		// Handle response
		if ( ! empty( $parsed_response->status ) && $parsed_response->status != 200 ) {
			$error = new WP_Error( $parsed_response->status, $parsed_response->name );
			return $error;
		} else {
			return $parsed_response;
		}
	}

    /**
     * Fetches an api token
     *
     * @return mixed
     * @throws Exception
     */
	public static function get_token_data() {
        $response = self::request( '', 'token', 'POST' );
        if ( is_wp_error( $response ) ) {
            WC_Eftsecure::log( 'EFTsecure Token API Error: '.$response->get_error_message() );
            throw new Exception('EFTsecure Token: '.$response->get_error_message());
        }
        return $response;
    }

    /**
     * @param $transaction_id
     * @return stdClass|WP_Error
     * @throws Exception
     */
    public static function get_transaction_data($transaction_id) {
        $response = self::request('', 'gateway-transaction/'.$transaction_id, 'GET');
        if ( is_wp_error( $response ) ) {
            WC_Eftsecure::log( 'EFTsecure Transaction API Error: '.$response->get_error_message() );
            throw new Exception('EFTsecure Transaction: '.$response->get_error_message());
        }
        return $response;
    }

    /**
     * Fetches a payment key
     *
     * @param $data array
     * @return stdClass|WP_Error
     * @throws Exception
     */
    public static function get_payment_key_data($data = []) {
        $query = http_build_query($data);
        $response = self::request( $query, 'eft/payment-key', 'POST' );
        if ( is_wp_error( $response ) ) {
            WC_Eftsecure::log( 'EFTsecure Payment Key API Error: '.$response->get_error_message() );
            throw new Exception('EFTsecure Payment Key: '.$response->get_error_message());
        }
        return $response;
    }
}
