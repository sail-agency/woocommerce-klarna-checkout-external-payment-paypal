<?php
/*
Plugin Name: Klarna Checkout (V3) External Payment Method for WooCommerce
Plugin URI: http://krokedil.com
Description: Adds PayPal as an extra payment method in Klarna Checkout iframe. Works with V3 of Klarna Checkout.
Version: 2.0.0
Author: Krokedil
Author URI: http://krokedil.com
*/

/**
 * Extends KCO settings with External Payment Method - PayPal settings.
 */
add_filter( 'kco_wc_gateway_settings', 'kcoepm_form_fields' );
function kcoepm_form_fields( $settings ) {
	$settings['epm_paypal_settings_title'] = array(
		'title' => __( 'External Payment Method - PayPal', 'kco-epm-wc' ),
		'type'  => 'title',
	);
	$settings['epm_paypal_name']           = array(
		'title'       => __( 'Name', 'kco-epm-wc' ),
		'type'        => 'text',
		'description' => __( 'Title for PayPal payment method. This controls the title which the user sees in the checkout form.', 'kco-epm-wc' ),
		'default'     => __( 'PayPal', 'kco-epm-wc' ),
	);
	$settings['epm_paypal_description']    = array(
		'title'       => __( 'Description', 'kco-epm-wc' ),
		'type'        => 'textarea',
		'description' => __( 'Description for PayPal payment method. This controls the description which the user sees in the checkout form.', 'kco-epm-wc' ),
		'default'     => '',
	);
	$settings['epm_paypal_img_url']        = array(
		'title'       => __( 'Image url', 'kco-epm-wc' ),
		'type'        => 'text',
		'description' => __( 'The url to the PayPal payment Icon.', 'kco-epm-wc' ),
		'default'     => 'https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png',
	);
	$settings['epm_paypal_disable_button'] = array(
		'title'       => __( 'Disable other gateway button', 'kco-epm-wc' ),
		'type'        => 'checkbox',
		'description' => __( 'Disables the "Select another Payment method" button on the Klarna Checkout.', 'kco-epm-wc' ),
		'default'     => 'no',
	);

	return $settings;
}

/**
 * Add PayPal as Payment Method to the KCO iframe.
 */
add_filter( 'kco_wc_api_request_args', 'kcoepm_create_order_paypal' );
function kcoepm_create_order_paypal( $request_args ) {

	$kco_settings = get_option( 'woocommerce_kco_settings' );
	$name         = isset( $kco_settings['epm_paypal_name'] ) ? $kco_settings['epm_paypal_name'] : '';
	$image_url    = isset( $kco_settings['epm_paypal_img_url'] ) ? $kco_settings['epm_paypal_img_url'] : '';
	$description  = isset( $kco_settings['epm_paypal_description'] ) ? $kco_settings['epm_paypal_description'] : '';

	$klarna_external_payment = array(
		'name'         => $name,
		'image_url'    => $image_url,
		'description'  => $description,
		'redirect_url' => add_query_arg(
			array(
				'kco-external-payment' => 'paypal', // Set this to the ID of the relevant payment method in WooCommerce.
				'order_id'             => isset( $request_args['merchant_reference2'] ) ? $request_args['merchant_reference2'] : '{checkout.order.id}',
			),
			wc_get_checkout_url()
		),
	);

	$klarna_external_payment                  = array( $klarna_external_payment );
	$request_args['external_payment_methods'] = $klarna_external_payment;

	return $request_args;
}


add_action( 'init', 'kcoepm_remove_other_gateway_button' );
function kcoepm_remove_other_gateway_button() {
	$kco_settings   = get_option( 'woocommerce_kco_settings' );
	$disable_button = isset( $kco_settings['epm_paypal_disable_button'] ) ? $kco_settings['epm_paypal_disable_button'] : 'no';
	if ( 'yes' === $disable_button ) {
		remove_action( 'kco_wc_after_order_review', 'kco_wc_show_another_gateway_button', 20 );
	}
}
