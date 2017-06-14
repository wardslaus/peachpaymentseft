<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters( 'wc_eftsecure_settings',
	array(
		'enabled' => array(
			'title'       => __( 'Enable/Disable', 'woocommerce-gateway-eftsecure' ),
			'label'       => __( 'Enable EFTsecure', 'woocommerce-gateway-eftsecure' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no'
		),
        'checkout_enabled' => array(
            'title'       => __( 'Enable Checkout Widget', 'woocommerce-gateway-eftsecure' ),
            'label'       => __( 'Enable javascript popup instead of redirect', 'woocommerce-gateway-eftsecure' ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
        ),
		'title' => array(
			'title'       => __( 'Title', 'woocommerce-gateway-eftsecure' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-eftsecure' ),
			'default'     => __( 'Instant Eft Payment (EFTsecure)', 'woocommerce-gateway-eftsecure' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'woocommerce-gateway-eftsecure' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-eftsecure' ),
			'default'     => __( 'Pay using your internet banking login.', 'woocommerce-gateway-eftsecure'),
			'desc_tip'    => true,
		),
		'username' => array(
			'title'       => __( 'API Username', 'woocommerce-gateway-eftsecure' ),
			'type'        => 'text',
			'description' => __( 'Get your API username from your EFTsecure account.', 'woocommerce-gateway-eftsecure' ),
			'default'     => '',
			'desc_tip'    => true,
		),
        'password' => array(
            'title'       => __( 'API Password', 'woocommerce-gateway-eftsecure' ),
            'type'        => 'password',
            'description' => __( 'Get your API password from your EFTsecure account.', 'woocommerce-gateway-eftsecure' ),
            'default'     => '',
            'desc_tip'    => true,
        ),
		'logging' => array(
			'title'       => __( 'Logging', 'woocommerce-gateway-eftsecure' ),
			'label'       => __( 'Log debug messages', 'woocommerce-gateway-eftsecure' ),
			'type'        => 'checkbox',
			'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'woocommerce-gateway-eftsecure' ),
			'default'     => 'no',
			'desc_tip'    => true,
		),
	)
);
